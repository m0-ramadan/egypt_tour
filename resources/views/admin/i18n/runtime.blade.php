@php
    $adminCurrentLocale = app()->getLocale();
    $adminI18nDictionary = admin_translation_maps()[$adminCurrentLocale] ?? [];
@endphp
<script>
    (function() {
        'use strict';
        const locale = @json($adminCurrentLocale);
        const dictionary = @json($adminI18nDictionary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        const ignoredTags = new Set(['SCRIPT', 'STYLE', 'CODE', 'PRE', 'TEXTAREA', 'NOSCRIPT']);
        const attributes = ['placeholder', 'title', 'aria-label', 'data-bs-title', 'data-bs-original-title'];

        function configureLocalizedPlugins() {
            if (!window.jQuery) return;
            const $ = window.jQuery;

            if ($.fn?.dataTable) {
                $.extend(true, $.fn.dataTable.defaults, {
                    language: {
                        emptyTable: 'No data available in table',
                        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                        infoEmpty: 'Showing 0 to 0 of 0 entries',
                        infoFiltered: '(filtered from _MAX_ total entries)',
                        lengthMenu: 'Show _MENU_ entries',
                        loadingRecords: 'Loading...',
                        processing: 'Processing...',
                        search: 'Search:',
                        zeroRecords: 'No matching records found',
                        paginate: {
                            first: 'First',
                            last: 'Last',
                            next: 'Next',
                            previous: 'Previous'
                        },
                        buttons: {
                            copy: 'Copy',
                            excel: 'Excel',
                            print: 'Print',
                            colvis: 'Column Visibility'
                        }
                    }
                });
            }

            if ($.fn?.select2?.defaults?.set) {
                $.fn.select2.defaults.set('dir', 'ltr');
                $.fn.select2.defaults.set('language', {
                    errorLoading: () => 'The results could not be loaded.',
                    inputTooLong: () => 'Please delete some characters.',
                    inputTooShort: () => 'Please enter more characters.',
                    loadingMore: () => 'Loading more results...',
                    maximumSelected: () => 'Maximum selected items reached.',
                    noResults: () => 'No results found.',
                    searching: () => 'Searching...'
                });
            }
        }

        configureLocalizedPlugins();

        function translateValue(value) {
            if (!value || typeof value !== 'string') return value;
            const leading = value.match(/^\s*/)?.[0] || '';
            const trailing = value.match(/\s*$/)?.[0] || '';
            let core = value.trim();
            if (!core) return value;
            if (Object.prototype.hasOwnProperty.call(dictionary, core)) {
                const val = dictionary[core];
                if (locale === 'en' && /[\u0600-\u06FF]/.test(val) && !/[\u0600-\u06FF]/.test(core)) {
                    return value;
                }
                return leading + val + trailing;
            }
            const safePrefixes = [
                'Welcome to the dashboard of', 'Welcome to', 'Send a notification to all',
                'Bookings', 'Inquiries', 'Type:', 'ID:',
                'Welcome to the dashboard of', 'Welcome to', 'Send a notification to all',
                'Bookings', 'Inquiries', 'Type:', 'ID:'
            ];
            for (const source of safePrefixes) {
                if (core.startsWith(source) && Object.prototype.hasOwnProperty.call(dictionary, source)) {
                    const val = dictionary[source];
                    if (locale === 'en' && /[\u0600-\u06FF]/.test(val) && !/[\u0600-\u06FF]/.test(source)) {
                        continue;
                    }
                    return leading + val + core.slice(source.length) + trailing;
                }
            }
            return value;
        }

        function translateTextNode(node) {
            if (!node || node.nodeType !== Node.TEXT_NODE) return;
            const parent = node.parentElement;
            if (!parent || ignoredTags.has(parent.tagName) || parent.closest('[data-no-admin-translate]')) return;
            const next = translateValue(node.nodeValue);
            if (next !== node.nodeValue) node.nodeValue = next;
        }

        function translateElement(element) {
            if (!element || element.nodeType !== Node.ELEMENT_NODE) return;
            if (ignoredTags.has(element.tagName) || element.closest('[data-no-admin-translate]')) return;
            for (const attr of attributes) {
                if (element.hasAttribute(attr)) {
                    const oldValue = element.getAttribute(attr);
                    const newValue = translateValue(oldValue);
                    if (newValue !== oldValue) element.setAttribute(attr, newValue);
                }
            }
            for (const child of element.childNodes) {
                if (child.nodeType === Node.TEXT_NODE) translateTextNode(child);
                else if (child.nodeType === Node.ELEMENT_NODE) translateElement(child);
            }
        }

        function translatePage() {
            document.documentElement.lang = locale;
            document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
            document.body?.setAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr');
            if (document.title) document.title = translateValue(document.title);
            if (document.body) translateElement(document.body);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', translatePage, {
                once: true
            });
        } else {
            translatePage();
        }

        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.type === 'characterData') translateTextNode(mutation.target);
                for (const node of mutation.addedNodes) {
                    if (node.nodeType === Node.TEXT_NODE) translateTextNode(node);
                    else if (node.nodeType === Node.ELEMENT_NODE) translateElement(node);
                }
            }
        });
        if (document.documentElement) {
            observer.observe(document.documentElement, {
                childList: true,
                subtree: true,
                characterData: true
            });
        }

        const nativeAlert = window.alert ? window.alert.bind(window) : null;
        const nativeConfirm = window.confirm ? window.confirm.bind(window) : null;
        if (nativeAlert) window.alert = (message) => nativeAlert(translateValue(String(message)));
        if (nativeConfirm) window.confirm = (message) => nativeConfirm(translateValue(String(message)));

        window.adminTranslate = translateValue;
    })();
</script>
