@php
    $locales = [
        'en' => ['name' => 'English', 'flag' => '🇺🇸', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'flag' => '🇪🇬', 'dir' => 'rtl'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'dir' => 'ltr'],
        'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪', 'dir' => 'ltr'],
    ];

    $model = $model ?? null;
    $activeLocales = $activeLocales ?? ['en', 'ar'];

    if ($model) {
        foreach (['name', 'title', 'description', 'short_description', 'seo_title', 'seo_description'] as $attr) {
            $val = $model->getRawOriginal($attr) ?? ($model->{$attr} ?? null);
            if (is_array($val)) {
                foreach (array_keys($val) as $loc) {
                    if (isset($locales[$loc]) && !in_array($loc, $activeLocales, true)) {
                        $activeLocales[] = $loc;
                    }
                }
            }
        }
    }
@endphp

<div class="card bg-dark text-white border-secondary mb-4 lang-selector-card">
    <div class="card-header border-secondary d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0 text-white"><i class="fas fa-language me-2 text-primary"></i>Content Languages</h6>
            <small class="text-light opacity-75">Select which languages you want to enter content for. Missing languages
                will be auto-translated by AI.</small>
        </div>
        <span class="badge bg-primary" id="selectedLangCount">{{ count($activeLocales) }} Selected</span>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-4 align-items-center">
            @foreach ($locales as $code => $info)
                @php $isChecked = in_array($code, $activeLocales, true) || $code === 'en'; @endphp
                <div class="form-check form-check-inline">
                    <input class="form-check-input lang-checkbox" type="checkbox" id="lang_check_{{ $code }}"
                        value="{{ $code }}" data-lang="{{ $code }}" @checked($isChecked)
                        @disabled($code === 'en')>
                    <label class="form-check-label text-white fw-bold" for="lang_check_{{ $code }}">
                        <span class="me-1">{{ $info['flag'] }}</span> {{ $info['name'] }} ({{ strtoupper($code) }})
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>

<ul class="nav nav-tabs border-secondary mb-3" id="langTabs" role="tablist">
    @foreach ($locales as $code => $info)
        @php $isChecked = in_array($code, $activeLocales, true) || $code === 'en'; @endphp
        <li class="nav-item lang-tab-item lang-tab-{{ $code }}" role="presentation"
            style="{{ $isChecked ? '' : 'display:none;' }}">
            <button class="nav-link text-white {{ $code === 'en' ? 'active' : '' }}" id="tab-btn-{{ $code }}"
                data-bs-toggle="tab" data-bs-target="#tab-pane-{{ $code }}" type="button" role="tab"
                aria-controls="tab-pane-{{ $code }}" aria-selected="{{ $code === 'en' ? 'true' : 'false' }}">
                <span class="me-1">{{ $info['flag'] }}</span> {{ $info['name'] }}
            </button>
        </li>
    @endforeach
</ul>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.lang-checkbox');
        const countBadge = document.getElementById('selectedLangCount');

        function updateLangTabs() {
            let count = 0;
            checkboxes.forEach(cb => {
                const code = cb.getAttribute('data-lang');
                const tabItem = document.querySelector('.lang-tab-' + code);
                const isChecked = cb.checked || code === 'en';

                if (isChecked) {
                    count++;
                    if (tabItem) tabItem.style.display = 'block';
                } else {
                    if (tabItem) {
                        tabItem.style.display = 'none';
                        const btn = document.getElementById('tab-btn-' + code);
                        if (btn && btn.classList.contains('active')) {
                            const enBtn = document.getElementById('tab-btn-en');
                            if (enBtn) {
                                var bsTab = new bootstrap.Tab(enBtn);
                                bsTab.show();
                            }
                        }
                    }
                }
            });

            if (countBadge) {
                countBadge.textContent = count + ' Selected';
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateLangTabs);
        });

        updateLangTabs();
    });
</script>
