document.addEventListener('DOMContentLoaded', function () {
    const packageTypeSelect = document.getElementById('package_type');

    function syncPricingEngineBlocks() {
        const currentType = packageTypeSelect ? packageTypeSelect.value : 'day_tour';

        document.querySelectorAll('.pricing-type-block').forEach(function (block) {
            const targetType = block.getAttribute('data-pricing-type');
            const isActive = targetType === currentType;

            block.style.display = isActive ? 'block' : 'none';
            block.querySelectorAll('input, select, textarea, button').forEach(function (el) {
                el.disabled = !isActive;
            });
        });
    }

    if (packageTypeSelect) {
        packageTypeSelect.addEventListener('change', syncPricingEngineBlocks);
        syncPricingEngineBlocks();
    }

    // ==========================================
    // 1. Day Trip Group-Size Pricing Tiers JS
    // ==========================================
    const groupTiersWrapper = document.getElementById('groupTiersWrapper');
    const btnAddGroupTier = document.getElementById('btnAddGroupTier');
    const btnLoadDefaultGroupTiers = document.getElementById('btnLoadDefaultGroupTiers');
    const groupTierTemplate = document.getElementById('groupTierTemplate');

    function reindexGroupTiers() {
        if (!groupTiersWrapper) return;
        const rows = groupTiersWrapper.querySelectorAll('.group-tier-row');
        rows.forEach(function (row, idx) {
            const numSpan = row.querySelector('.tier-number');
            if (numSpan) numSpan.textContent = idx + 1;

            row.querySelectorAll('input, select, textarea').forEach(function (el) {
                const name = el.getAttribute('name');
                if (name) {
                    const newName = name.replace(/group_pricing_tiers\[\d+\]/, 'group_pricing_tiers[' + idx + ']');
                    el.setAttribute('name', newName);
                }
            });
        });
    }

    if (btnAddGroupTier && groupTiersWrapper && groupTierTemplate) {
        btnAddGroupTier.addEventListener('click', function () {
            const count = groupTiersWrapper.querySelectorAll('.group-tier-row').length;
            let html = groupTierTemplate.innerHTML;
            html = html.replace(/__INDEX__/g, count).replace(/__INDEX_PLUS_1__/g, count + 1);

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html.trim();
            const newRow = tempDiv.firstElementChild;

            groupTiersWrapper.appendChild(newRow);
            reindexGroupTiers();
        });
    }

    if (groupTiersWrapper) {
        groupTiersWrapper.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.js-remove-tier');
            if (removeBtn) {
                const row = removeBtn.closest('.group-tier-row');
                if (row) {
                    row.remove();
                    reindexGroupTiers();
                }
            }
        });
    }

    if (btnLoadDefaultGroupTiers && groupTiersWrapper) {
        btnLoadDefaultGroupTiers.addEventListener('click', function () {
            if (groupTiersWrapper.children.length > 0 && !confirm('هل أنت تأكد من استبدال الشرائح الحالية بالشرائح الافتراضية الـ 6؟')) {
                return;
            }
            groupTiersWrapper.innerHTML = '';
            const defaults = [
                { title: "Solo Traveler", min: 1, max: 1, price: 236.00, badge: "" },
                { title: "Couple's Journey", min: 2, max: 2, price: 162.00, badge: "Most Popular" },
                { title: "Small Group", min: 3, max: 3, price: 153.00, badge: "" },
                { title: "Family Adventure", min: 4, max: 4, price: 145.00, badge: "" },
                { title: "Extended Group", min: 5, max: 5, price: 140.00, badge: "" },
                { title: "Large Group", min: 6, max: 99, price: 135.00, badge: "Best Value" }
            ];

            defaults.forEach(function (d, idx) {
                const row = document.createElement('div');
                row.className = 'repeat-box group-tier-row mb-3 p-3 border rounded bg-white';
                row.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary text-white">الشريحة #${idx + 1}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger js-remove-tier"><i class="ti ti-trash"></i> حذف</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">اسم الشريحة</label>
                            <input type="text" name="experience[group_pricing_tiers][${idx}][title]" class="form-control form-control-sm" value="${d.title}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">عدد الأشخاص / أدنى</label>
                            <input type="number" min="1" name="experience[group_pricing_tiers][${idx}][min]" class="form-control form-control-sm" value="${d.min}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">أقصى (اختياري)</label>
                            <input type="number" min="1" name="experience[group_pricing_tiers][${idx}][max]" class="form-control form-control-sm" value="${d.max}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">سعر الفرد ($)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="experience[group_pricing_tiers][${idx}][price_per_person]" class="form-control" value="${d.price}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">شارة مميزة</label>
                            <input type="text" name="experience[group_pricing_tiers][${idx}][badge_label]" class="form-control form-control-sm" value="${d.badge}">
                        </div>
                    </div>
                `;
                groupTiersWrapper.appendChild(row);
            });
        });
    }

    // ==========================================
    // 2. Tour Package Accommodations & Hotels JS
    // ==========================================
    const accommodationsWrapper = document.getElementById('accommodationsWrapper');
    const btnAddAccommodation = document.getElementById('btnAddAccommodation');

    function reindexAccommodations() {
        if (!accommodationsWrapper) return;
        const emptyNotice = document.getElementById('emptyAccommodationsNotice');
        const accRows = accommodationsWrapper.querySelectorAll('.accommodation-row');

        if (emptyNotice) {
            emptyNotice.style.display = accRows.length === 0 ? 'block' : 'none';
        }

        accRows.forEach(function (accRow, accIdx) {
            accRow.setAttribute('data-acc-index', accIdx);
            const numSpan = accRow.querySelector('.acc-number');
            if (numSpan) numSpan.textContent = accIdx + 1;

            // Reindex accommodation level inputs
            accRow.querySelectorAll('input, select, textarea').forEach(function (input) {
                const name = input.getAttribute('name');
                if (name && name.startsWith('tour_package_accommodations[')) {
                    const newName = name.replace(/^tour_package_accommodations\[\d+\]/, 'tour_package_accommodations[' + accIdx + ']');
                    input.setAttribute('name', newName);
                }
            });
        });
    }

    if (btnAddAccommodation && accommodationsWrapper) {
        btnAddAccommodation.addEventListener('click', function () {
            const accIdx = accommodationsWrapper.querySelectorAll('.accommodation-row').length;
            const newAcc = document.createElement('div');
            newAcc.className = 'repeat-box accommodation-row mb-4 p-3 border rounded bg-white';
            newAcc.setAttribute('data-acc-index', accIdx);

            newAcc.innerHTML = `
                <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary"><i class="ti ti-building me-1"></i>مستوى إقامة #<span class="acc-number">${accIdx + 1}</span></span>
                        <input type="text" name="tour_package_accommodations[${accIdx}][name]" class="form-control form-control-sm fw-bold" placeholder="e.g. 5-Star Deluxe Accommodations" style="min-width: 280px;">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-accommodation"><i class="ti ti-trash"></i> حذف هذا المستوى</button>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">وصف المستوى (اختياري)</label>
                    <input type="text" name="tour_package_accommodations[${accIdx}][description]" class="form-control form-control-sm" placeholder="e.g. Includes breakfast and private transfers">
                </div>
                <div class="p-3 mb-3 bg-light rounded border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h7 class="fw-bold text-dark"><i class="ti ti-calendar me-1"></i> أسعار المواسم والغرف (Seasons & Occupancy Prices)</h7>
                        <button type="button" class="btn btn-xs btn-outline-primary js-add-acc-season" data-acc-index="${accIdx}">+ إضافة موسم جديد</button>
                    </div>
                    <div class="acc-seasons-wrapper stack-list"></div>
                </div>
                <div class="p-3 bg-light rounded border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h7 class="fw-bold text-dark"><i class="ti ti-map-pin me-1"></i> الفنادق المقترحة حسب المدن (Assigned Hotels per City)</h7>
                        <button type="button" class="btn btn-xs btn-outline-primary js-add-acc-hotel" data-acc-index="${accIdx}">+ إضافة فندق جديد</button>
                    </div>
                    <div class="acc-hotels-wrapper stack-list"></div>
                </div>
            `;

            accommodationsWrapper.appendChild(newAcc);
            reindexAccommodations();

            // Automatically add 1 default season and 1 default hotel row
            addSeasonRow(newAcc.querySelector('.acc-seasons-wrapper'), accIdx);
            addHotelRow(newAcc.querySelector('.acc-hotels-wrapper'), accIdx);
        });
    }

    function addSeasonRow(seasonsWrapper, accIdx) {
        if (!seasonsWrapper) return;
        const sIdx = seasonsWrapper.querySelectorAll('.season-row').length;
        const sRow = document.createElement('div');
        sRow.className = 'repeat-box season-row mb-3 p-3 bg-white rounded border';

        sRow.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="row g-2 align-items-center flex-grow-1 me-2">
                    <div class="col-md-4">
                        <input type="text" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][name][en]" class="form-control form-control-sm fw-bold" placeholder="e.g. May to August (Low Season)">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][date_from]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][date_to]" class="form-control form-control-sm">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-season"><i class="ti ti-x"></i></button>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">غرفة ثلاثية (Triple Room)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="hidden" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][0][occupancy_type]" value="triple">
                        <input type="hidden" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][0][label][en]" value="Per Person in Triple Room">
                        <input type="number" step="0.01" min="0" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][0][price]" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">غرفة مزدوجة (Double Room)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="hidden" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][1][occupancy_type]" value="double">
                        <input type="hidden" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][1][label][en]" value="Per Person in Double Room">
                        <input type="number" step="0.01" min="0" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][1][price]" class="form-control" placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">غرفة مفردة (Single Room)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="hidden" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][2][occupancy_type]" value="single">
                        <input type="hidden" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][2][label][en]" value="Single Room Supplement">
                        <input type="number" step="0.01" min="0" name="tour_package_accommodations[${accIdx}][seasons][${sIdx}][items][2][price]" class="form-control" placeholder="0.00">
                    </div>
                </div>
            </div>
        `;
        seasonsWrapper.appendChild(sRow);
    }

    function addHotelRow(hotelsWrapper, accIdx) {
        if (!hotelsWrapper) return;
        const hIdx = hotelsWrapper.querySelectorAll('.hotel-row').length;
        const hRow = document.createElement('div');
        hRow.className = 'repeat-box hotel-row mb-2 p-2 bg-white rounded border';

        hRow.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="tour_package_accommodations[${accIdx}][hotels][${hIdx}][city_name]" class="form-control form-control-sm" placeholder="e.g. Cairo / Luxor">
                </div>
                <div class="col-md-5">
                    <input type="text" name="tour_package_accommodations[${accIdx}][hotels][${hIdx}][hotel_name]" class="form-control form-control-sm" placeholder="e.g. Pyramisa Isis / Steigenberger">
                </div>
                <div class="col-md-3">
                    <select name="tour_package_accommodations[${accIdx}][hotels][${hIdx}][star_rating]" class="form-select form-select-sm">
                        <option value="5" selected>5 Stars ★★★★★</option>
                        <option value="4">4 Stars ★★★★</option>
                        <option value="3">3 Stars ★★★</option>
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-hotel"><i class="ti ti-x"></i></button>
                </div>
            </div>
        `;
        hotelsWrapper.appendChild(hRow);
    }

    if (accommodationsWrapper) {
        accommodationsWrapper.addEventListener('click', function (e) {
            const removeAccBtn = e.target.closest('.js-remove-accommodation');
            if (removeAccBtn) {
                const accRow = removeAccBtn.closest('.accommodation-row');
                if (accRow) {
                    accRow.remove();
                    reindexAccommodations();
                }
                return;
            }

            const addSeasonBtn = e.target.closest('.js-add-acc-season');
            if (addSeasonBtn) {
                const accRow = addSeasonBtn.closest('.accommodation-row');
                const accIdx = accRow ? accRow.getAttribute('data-acc-index') : 0;
                const wrapper = accRow ? accRow.querySelector('.acc-seasons-wrapper') : null;
                addSeasonRow(wrapper, accIdx);
                return;
            }

            const removeSeasonBtn = e.target.closest('.js-remove-season');
            if (removeSeasonBtn) {
                const sRow = removeSeasonBtn.closest('.season-row');
                if (sRow) sRow.remove();
                return;
            }

            const addHotelBtn = e.target.closest('.js-add-acc-hotel');
            if (addHotelBtn) {
                const accRow = addHotelBtn.closest('.accommodation-row');
                const accIdx = accRow ? accRow.getAttribute('data-acc-index') : 0;
                const wrapper = accRow ? accRow.querySelector('.acc-hotels-wrapper') : null;
                addHotelRow(wrapper, accIdx);
                return;
            }

            const removeHotelBtn = e.target.closest('.js-remove-hotel');
            if (removeHotelBtn) {
                const hRow = removeHotelBtn.closest('.hotel-row');
                if (hRow) hRow.remove();
                return;
            }
        });
    }
});
