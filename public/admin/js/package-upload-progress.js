/**
 * Package Upload & Save Progress Manager with live percentage indicator
 */
(function() {
    'use strict';

    function formatBytes(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    window.initPackageUploadProgress = function(options) {
        options = options || {};
        const formId = options.formId || 'packageWizardForm';
        const form = document.getElementById(formId);
        if (!form) return;

        const overlay = document.getElementById('packageUploadOverlay');
        const card = overlay?.querySelector('.package-upload-card');
        const percentNumber = document.getElementById('uploadPercentNumber');
        const progressBar = document.getElementById('uploadProgressBar');
        const statusTitle = document.getElementById('uploadStatusTitle');
        const statusSubtitle = document.getElementById('uploadStatusSubtitle');
        const sizeText = document.getElementById('uploadSizeText');
        const stateText = document.getElementById('uploadStateText');
        const iconElem = document.getElementById('uploadIconElement');

        const isArabic = (document.documentElement.lang || '').startsWith('ar') || document.dir === 'rtl';

        const i18n = {
            savingTitle: isArabic ? 'جاري حفظ الرحلة ورفع الملفات...' : 'Saving Package & Uploading Files...',
            savingSubtitle: isArabic ? 'برجاء الانتظار، جاري نقل البيانات والصور إلى الخادم...' : 'Please wait while your files and data are uploaded to the server...',
            processingTitle: isArabic ? 'تم اكتمال الرفع بنجاح!' : 'Upload Completed!',
            processingSubtitle: isArabic ? 'جاري معالجة الصور وحفظ بيانات الرحلة في قاعدة البيانات...' : 'Processing images and saving package details in the database...',
            successTitle: isArabic ? 'تم الحفظ بنجاح!' : 'Saved Successfully!',
            successSubtitle: isArabic ? 'جاري توجيهك إلى قائمة الرحلات...' : 'Redirecting you to packages list...',
            uploading: isArabic ? 'جاري الرفع' : 'Uploading',
            processing: isArabic ? 'جاري المعالجة' : 'Processing',
            completed: isArabic ? 'مكتمل' : 'Completed',
            errorTitle: isArabic ? 'حدث خطأ أثناء الحفظ' : 'An error occurred while saving',
            networkError: isArabic ? 'فشل الاتصال بالخادم. يرجى التحقق من اتصال الإنترنت والمحاولة مجدداً.' : 'Connection to server failed. Please check your internet and try again.',
            sessionExpired: isArabic ? 'انتهت جلستك، يرجى إعادة تحميل الصفحة وتسجيل الدخول مجدداً.' : 'Your session has expired. Please reload the page and try again.',
            serverError: isArabic ? 'حدث خطأ في الخادم أثناء معالجة الصور أو البيانات.' : 'A server error occurred while processing images or data.',
            conflictTitle: isArabic ? 'تعارض في التعديل' : 'Update Conflict Detected',
            conflictMessage: isArabic ? 'قام مستخدم آخر بتعديل هذه الرحلة أثناء عملك عليها. لتجنب الكتابة فوق التعديلات، يرجى تحديث الصفحة.' : 'Another user has modified this package since you opened it. To avoid overwriting changes, please reload the page.',
            lockTitle: isArabic ? 'العملية قيد التنفيذ' : 'Operation In Progress',
            lockMessage: isArabic ? 'يتم حفظ هذه الرحلة حالياً بواسطة عملية أخرى. يرجى الانتظار لحظة والمحاولة مجدداً.' : 'This package is currently being saved by another process. Please wait a moment and try again.',
            rateLimitTitle: isArabic ? 'طلبات متكررة' : 'Too Many Requests',
            rateLimitMessage: isArabic ? 'تم إرسال عدد كبير من الطلبات. يرجى الانتظار بضع ثوانٍ والمحاولة مرة أخرى.' : 'Too many requests have been sent. Please wait a few seconds and try again.',
            reloadPage: isArabic ? 'تحديث الصفحة' : 'Reload Page',
            cancel: isArabic ? 'إلغاء' : 'Cancel',
            tryAgain: isArabic ? 'إعادة المحاولة' : 'Try Again'
        };

        let isSubmitting = false;

        function getAllSubmitButtons() {
            return form.querySelectorAll('button[type="submit"], input[type="submit"], #submitWizardBtn');
        }

        function disableSubmitControls() {
            getAllSubmitButtons().forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
        }

        function enableSubmitControls() {
            getAllSubmitButtons().forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('disabled');
            });
        }

        function showOverlay() {
            if (!overlay) return;
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (card) card.classList.remove('is-success');
            if (iconElem) {
                iconElem.className = 'ti ti-cloud-upload';
            }
            if (percentNumber) percentNumber.textContent = '0';
            if (progressBar) progressBar.style.width = '0%';
            if (statusTitle) statusTitle.textContent = i18n.savingTitle;
            if (statusSubtitle) statusSubtitle.textContent = i18n.savingSubtitle;
            if (stateText) stateText.textContent = i18n.uploading;
            if (sizeText) sizeText.textContent = isArabic ? 'جاري تجهيز البيانات...' : 'Preparing data...';
        }

        function hideOverlay() {
            if (!overlay) return;
            overlay.style.display = 'none';
            document.body.style.overflow = '';
        }

        function setProgress(percent, loaded, total) {
            percent = Math.max(0, Math.min(100, Math.round(percent)));
            if (percentNumber) percentNumber.textContent = percent;
            if (progressBar) progressBar.style.width = percent + '%';

            if (loaded !== undefined && total !== undefined && sizeText) {
                sizeText.textContent = formatBytes(loaded) + ' / ' + formatBytes(total);
            }

            if (percent >= 100) {
                if (statusTitle) statusTitle.textContent = i18n.processingTitle;
                if (statusSubtitle) statusSubtitle.textContent = i18n.processingSubtitle;
                if (stateText) stateText.textContent = i18n.processing;
                if (iconElem) iconElem.className = 'ti ti-loader ti-spin';
            }
        }

        function markSuccess(redirectUrl, message) {
            setProgress(100);
            if (card) card.classList.add('is-success');
            if (statusTitle) statusTitle.textContent = message || i18n.successTitle;
            if (statusSubtitle) statusSubtitle.textContent = i18n.successSubtitle;
            if (stateText) stateText.textContent = i18n.completed;
            if (iconElem) iconElem.className = 'ti ti-check';

            setTimeout(function() {
                window.location.href = redirectUrl || '/admin/packages';
            }, 750);
        }

        // Attach AJAX submission to the form
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            // First check if validateStep or client validation is defined in page
            if (typeof options.validateAllSteps === 'function') {
                const firstInvalidStep = options.validateAllSteps();
                if (firstInvalidStep !== null) {
                    // Let the parent validation handle error highlight and stop
                    isSubmitting = false;
                    enableSubmitControls();
                    return;
                }
            }

            e.preventDefault();
            e.stopPropagation();

            isSubmitting = true;
            disableSubmitControls();
            showOverlay();

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            xhr.open(form.method.toUpperCase() || 'POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            // Listen to upload progress (files and body bytes)
            if (xhr.upload) {
                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable && event.total > 0) {
                        const percent = Math.min(99, (event.loaded / event.total) * 100);
                        setProgress(percent, event.loaded, event.total);
                    } else {
                        setProgress(50);
                    }
                };
            }

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    let response = {};
                    try {
                        response = JSON.parse(xhr.responseText);
                    } catch (parseErr) {
                        // Response might be HTML redirect or plain text
                    }

                    try {
                        localStorage.removeItem(options.draftKey || 'egypt_tour_package_wizard_draft');
                    } catch (err) {}

                    const targetUrl = response.redirect || xhr.responseURL || '/admin/packages';
                    markSuccess(targetUrl, response.message);
                } else if (xhr.status === 422) {
                    hideOverlay();
                    isSubmitting = false;
                    enableSubmitControls();

                    let errorData = {};
                    try {
                        errorData = JSON.parse(xhr.responseText);
                    } catch (err) {}

                    if (typeof options.onValidationError === 'function') {
                        options.onValidationError(errorData.errors || {}, errorData.message);
                    } else {
                        const firstMsg = errorData.message || (errorData.errors ? Object.values(errorData.errors)[0][0] : 'Validation failed');
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: i18n.errorTitle,
                                text: firstMsg
                            });
                        } else {
                            alert(firstMsg);
                        }
                    }
                } else if (xhr.status === 409) {
                    // Stale data / Optimistic Concurrency Conflict
                    hideOverlay();
                    isSubmitting = false;
                    enableSubmitControls();

                    let conflictData = {};
                    try {
                        conflictData = JSON.parse(xhr.responseText);
                    } catch (err) {}

                    const conflictMsg = conflictData.message || i18n.conflictMessage;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: i18n.conflictTitle,
                            text: conflictMsg,
                            showCancelButton: true,
                            confirmButtonText: i18n.reloadPage,
                            cancelButtonText: i18n.cancel
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        if (confirm(conflictMsg + '\n\n' + (isArabic ? 'هل تريد تحديث الصفحة الآن؟' : 'Do you want to reload the page now?'))) {
                            window.location.reload();
                        }
                    }
                } else if (xhr.status === 423) {
                    // Resource Locked by concurrent transaction
                    hideOverlay();
                    isSubmitting = false;
                    enableSubmitControls();

                    let lockData = {};
                    try {
                        lockData = JSON.parse(xhr.responseText);
                    } catch (err) {}

                    const lockMsg = lockData.message || i18n.lockMessage;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'info',
                            title: i18n.lockTitle,
                            text: lockMsg,
                            confirmButtonText: i18n.tryAgain
                        });
                    } else {
                        alert(lockMsg);
                    }
                } else if (xhr.status === 429) {
                    // Rate Limited
                    hideOverlay();
                    isSubmitting = false;
                    enableSubmitControls();

                    let rateLimitData = {};
                    try {
                        rateLimitData = JSON.parse(xhr.responseText);
                    } catch (err) {}

                    const rlMsg = rateLimitData.message || i18n.rateLimitMessage;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: i18n.rateLimitTitle,
                            text: rlMsg,
                            confirmButtonText: i18n.tryAgain
                        });
                    } else {
                        alert(rlMsg);
                    }
                } else if (xhr.status === 419) {
                    // CSRF / Session Timeout
                    hideOverlay();
                    isSubmitting = false;
                    enableSubmitControls();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: i18n.sessionExpired,
                            confirmButtonText: i18n.reloadPage
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        alert(i18n.sessionExpired);
                        window.location.reload();
                    }
                } else {
                    hideOverlay();
                    isSubmitting = false;
                    enableSubmitControls();

                    let msg = i18n.serverError;
                    let ref = null;
                    try {
                        const parsed = JSON.parse(xhr.responseText);
                        if (parsed.message) msg = parsed.message;
                        if (parsed.error_reference) ref = parsed.error_reference;
                    } catch (err) {}

                    const displayMsg = ref ? `${msg}\n(Reference: ${ref})` : msg;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: i18n.errorTitle,
                            text: displayMsg
                        });
                    } else {
                        alert(displayMsg);
                    }
                }
            };

            xhr.onerror = function() {
                hideOverlay();
                isSubmitting = false;
                enableSubmitControls();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: i18n.errorTitle,
                        text: i18n.networkError
                    });
                } else {
                    alert(i18n.networkError);
                }
            };

            xhr.send(formData);
        });
    };
})();

