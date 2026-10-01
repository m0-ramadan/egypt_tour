# Apache Production Optimization Guide for Egypt Tour Pro

This deployment note outlines the recommended Apache web server configurations to achieve maximum performance and caching efficiency for Egypt Tour Pro.

> **Note:** Do NOT replace Apache configuration from application code directly. Apply these directives in your virtual host configuration (`/etc/apache2/sites-available/egypttourpro.conf`) or verify that `mod_headers`, `mod_expires`, `mod_brotli`/`mod_deflate` are enabled in Apache.

---

## 1. Enable Required Apache Modules

Ensure the following modules are enabled on the server:

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo a2enmod brotli   # Optional, if available
sudo systemctl restart apache2
```

---

## 2. Recommended Compression (`mod_deflate` / `mod_brotli`)

Compress text assets, JSON, SVG, CSS, and JS:

```apache
<IfModule mod_brotli.c>
    AddOutputFilterByType BROTLI_COMPRESS text/html text/plain text/css application/javascript application/json application/ld+json image/svg+xml
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css application/javascript application/json application/ld+json image/svg+xml
</IfModule>

<IfModule mod_headers.c>
    # Ensure proxies cache compressed and uncompressed variants separately
    Header append Vary User-Agent env=!dont-vary
    Header append Vary Accept-Encoding
</IfModule>
```

---

## 3. Immutable Long-Term Caching for Hashed Vite Assets

Vite produces unique content hashes for build artifacts (`assets/*-[hash].js`, `assets/*-[hash].css`). These can be cached permanently (1 year) with `immutable`:

```apache
<IfModule mod_headers.c>
    # Vite Hashed Assets (1 Year Immutable)
    <LocationMatch "^/build/assets/">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </LocationMatch>

    # Static Media & Fonts (1 Year Long Caching)
    <FilesMatch "\.(?:woff|woff2|ttf|eot|avif|webp|png|jpg|jpeg|svg|ico)$">
        Header set Cache-Control "public, max-age=31536000, immutable"
    </FilesMatch>

    # Dynamic HTML Responses (No Immutable Caching)
    <FilesMatch "\.(?:php|html)$">
        Header set Cache-Control "no-cache, no-store, must-revalidate"
    </FilesMatch>
</IfModule>
```

---

## 4. Expires Header Configuration (`mod_expires`)

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresDefault "access plus 1 month"

    # HTML
    ExpiresByType text/html "access plus 0 seconds"

    # CSS & JavaScript
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"

    # Images & Media
    ExpiresByType image/avif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"

    # Fonts
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
</IfModule>
```

---

## 5. Security & Header Best Practices

```apache
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" "expr=%{HTTPS} == 'on'"
</IfModule>
```
