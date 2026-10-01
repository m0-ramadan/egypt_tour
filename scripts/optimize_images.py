#!/usr/bin/env python3
"""
EgyptTourPro Production Image Optimizer
========================================
Run this on the production server to generate optimized AVIF/WebP variants
for all homepage card images.

Usage:
  python3 scripts/optimize_images.py            # optimize new files only
  python3 scripts/optimize_images.py --force    # re-generate everything
  python3 scripts/optimize_images.py --dry-run  # preview only

Requirements:
  pip install pillow  (system Python — may need: apt install python3-pil)
  OR: python3 -m pip install pillow --break-system-packages

What it does:
  - Generates AVIF + WebP at 420w, 640w, 768w for every card image
  - Preserves original files (never deletes anything)
  - Creates files alongside originals in the same directory
  - Skips already-generated files (idempotent)

Directories optimized:
  1. public/website/images/day-tours/          → optimized/ subfolder
  2. public/storage/images/articles/            → alongside originals
  3. public/storage/packages/                   → alongside originals
  4. public/storage/packages/imported/*/        → alongside originals
  5. public/website/images/articles/            → optimized/ subfolder
  6. public/website/photos/experiences/         → 420w variant added

After running, Laravel's img_srcset_data() helper will automatically
serve the optimized images via <picture> on the homepage.
"""

import os
import sys
import glob
from pathlib import Path

# ──────────────────────────────────────────────
# Try importing Pillow — give helpful error if missing
# ──────────────────────────────────────────────
try:
    from PIL import Image
except ImportError:
    print("ERROR: Pillow not installed.")
    print("Install with: python3 -m pip install pillow --break-system-packages")
    print("         OR: sudo apt install python3-pil")
    sys.exit(1)

# ──────────────────────────────────────────────
# Configuration
# ──────────────────────────────────────────────
BASE = Path(__file__).parent.parent  # project root

DRY_RUN  = "--dry-run" in sys.argv
FORCE    = "--force" in sys.argv

WEBP_QUALITY = 82   # high quality WebP — ~60-70% smaller than original JPEG
AVIF_QUALITY = 75   # high quality AVIF — ~80-93% smaller than original JPEG

CARD_WIDTHS = [420, 640, 768]  # widths generated for each card image

stats = {"created": 0, "skipped": 0, "errors": 0, "sources": 0}


# ──────────────────────────────────────────────
# Core helper
# ──────────────────────────────────────────────
def resize_and_save(src: Path, dst: Path, width: int, fmt: str, quality: int) -> bool:
    """Resize src to given width, save in fmt. Returns True if file was created."""
    if dst.exists() and not FORCE:
        stats["skipped"] += 1
        return False

    if DRY_RUN:
        print(f"      DRY-RUN → {dst.name}")
        return False

    dst.parent.mkdir(parents=True, exist_ok=True)

    try:
        with Image.open(src) as im:
            orig_w, orig_h = im.size
            target_w = min(width, orig_w)
            target_h = round(orig_h * target_w / orig_w)

            resized = im.resize((target_w, target_h), Image.LANCZOS) if target_w < orig_w else im.copy()

            # Ensure RGB for lossy formats
            if fmt in ("AVIF", "JPEG") and resized.mode not in ("RGB", "L"):
                bg = Image.new("RGB", resized.size, (255, 255, 255))
                if resized.mode in ("RGBA", "LA", "PA"):
                    mask = resized.split()[-1]
                    bg.paste(resized.convert("RGB"), mask=mask)
                else:
                    bg.paste(resized.convert("RGB"))
                resized = bg
            elif fmt == "WEBP" and resized.mode == "P":
                resized = resized.convert("RGBA")

            kwargs = {}
            if fmt == "AVIF":
                kwargs = {"quality": quality}
            elif fmt == "WEBP":
                kwargs = {"quality": quality, "method": 6}
            elif fmt == "JPEG":
                kwargs = {"quality": quality, "optimize": True, "progressive": True}

            resized.save(dst, fmt, **kwargs)
            stats["created"] += 1
            return True

    except Exception as e:
        print(f"      ERROR saving {dst.name}: {e}")
        stats["errors"] += 1
        return False


def optimize_set(src: Path, out_dir: Path, stem: str, widths: list, label: str = "") -> list:
    """Generate AVIF + WebP at each width for a source image."""
    if not src.exists():
        print(f"  [SKIP — not found] {src}")
        return []

    stats["sources"] += 1
    orig_kb = src.stat().st_size // 1024
    try:
        with Image.open(src) as im:
            orig_dims = f"{im.width}x{im.height}"
    except Exception:
        orig_dims = "?"

    print(f"\n  {label or src.name}  [{orig_dims}, {orig_kb} KB]")

    results = []
    for w in widths:
        for fmt, ext, q in [("AVIF", "avif", AVIF_QUALITY), ("WEBP", "webp", WEBP_QUALITY)]:
            dst = out_dir / f"{stem}-{w}.{ext}"
            created = resize_and_save(src, dst, w, fmt, q)
            if created and dst.exists():
                print(f"    [{fmt}] {w}w → {dst.name} ({dst.stat().st_size // 1024} KB)")
            elif dst.exists() and not created:
                pass  # already exists, counted as skipped
            results.append(dst)
    return results


# ──────────────────────────────────────────────
# GROUP 1: Day-tour destination card images
# ──────────────────────────────────────────────
print("\n" + "="*60)
print("GROUP 1: Day-tour destination images")
print("="*60)

DEST_SRC = BASE / "public/website/images/day-tours"
DEST_OUT = DEST_SRC / "optimized"

for name in [
    "cairo-destination",
    "luxor-destination",
    "aswan-destination",
    "hurghada-destination",
    "sharm-el-sheikh-destination",
    "marsa-alam-destination",
]:
    optimize_set(DEST_SRC / f"{name}.jpg", DEST_OUT, name, CARD_WIDTHS)

# ──────────────────────────────────────────────
# GROUP 2: Article images in storage
# ──────────────────────────────────────────────
print("\n" + "="*60)
print("GROUP 2: Article images (storage)")
print("="*60)

ART_DIR = BASE / "public/storage/images/articles"
if ART_DIR.exists():
    for src in sorted(ART_DIR.glob("*.jpg")) + sorted(ART_DIR.glob("*.png")):
        optimize_set(src, ART_DIR, src.stem, CARD_WIDTHS, f"article/{src.name}")
else:
    print(f"  [DIR NOT FOUND] {ART_DIR}")

# ──────────────────────────────────────────────
# GROUP 3: Package images in storage (top-level)
# ──────────────────────────────────────────────
print("\n" + "="*60)
print("GROUP 3: Package images (storage/packages)")
print("="*60)

PKG_DIR = BASE / "public/storage/packages"
if PKG_DIR.exists():
    for src in sorted(PKG_DIR.glob("*.jpg")) + sorted(PKG_DIR.glob("*.png")):
        optimize_set(src, PKG_DIR, src.stem, CARD_WIDTHS, f"package/{src.name}")

    # Imported sub-folders — featured images only
    for featured in sorted(PKG_DIR.glob("imported/*/featured*.jpg")) + \
                    sorted(PKG_DIR.glob("imported/*/featured*.png")):
        optimize_set(featured, featured.parent, featured.stem, CARD_WIDTHS, f"pkg/{featured.name}")

    # Already-resized WebP imported images — generate AVIF for them
    for featured in sorted(PKG_DIR.glob("imported/*/featured*.webp")):
        optimize_set(featured, featured.parent, featured.stem, CARD_WIDTHS, f"pkg-webp/{featured.name}")
else:
    print(f"  [DIR NOT FOUND] {PKG_DIR}")

# ──────────────────────────────────────────────
# GROUP 4: Static article images (public/website)
# ──────────────────────────────────────────────
print("\n" + "="*60)
print("GROUP 4: Static article images (public/website/images/articles)")
print("="*60)

STATIC_ART = BASE / "public/website/images/articles"
STATIC_ART_OUT = STATIC_ART / "optimized"

if STATIC_ART.exists():
    for src in sorted(STATIC_ART.glob("*.jpg")):
        optimize_set(src, STATIC_ART_OUT, src.stem, CARD_WIDTHS, f"static-art/{src.name}")
else:
    print(f"  [DIR NOT FOUND] {STATIC_ART}")

# ──────────────────────────────────────────────
# GROUP 5: Experience photos (add 420w)
# ──────────────────────────────────────────────
print("\n" + "="*60)
print("GROUP 5: Experience photos (adding 420w variant)")
print("="*60)

EXP = BASE / "public/website/photos/experiences"
for name in ["day-tours", "nile-cruises", "travel-packages"]:
    src = next((EXP / f"{name}.{ext}" for ext in ["jpg", "webp"] if (EXP / f"{name}.{ext}").exists()), None)
    if src:
        optimize_set(src, EXP, name, [420], f"experience/{name}")
    else:
        print(f"  [NOT FOUND] experiences/{name}")

# ──────────────────────────────────────────────
# REPORT
# ──────────────────────────────────────────────
print("\n" + "="*60)
print("SUMMARY")
print("="*60)
print(f"  Source images processed : {stats['sources']}")
print(f"  Files created           : {stats['created']}")
print(f"  Files skipped (exist)   : {stats['skipped']}")
print(f"  Errors                  : {stats['errors']}")
if DRY_RUN:
    print("\n  [DRY RUN — no files written]")
print("\nDone. Run 'php artisan cache:clear' on the server to clear image URL cache.")
