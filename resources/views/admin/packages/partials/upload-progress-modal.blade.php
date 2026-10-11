@php
    $isRtl = app()->getLocale() === 'ar';
@endphp

<!-- Package Upload & Save Progress Modal Overlay -->
<div id="packageUploadOverlay" class="package-upload-overlay" style="display: none;" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="package-upload-backdrop"></div>
    <div class="package-upload-dialog">
        <div class="package-upload-card">
            <!-- Animated Icon -->
            <div class="package-upload-icon-wrapper" id="uploadStatusIcon">
                <div class="upload-icon-pulse"></div>
                <div class="upload-icon-circle">
                    <i class="ti ti-cloud-upload" id="uploadIconElement"></i>
                </div>
            </div>

            <!-- Title & Subtitle -->
            <h3 class="package-upload-title" id="uploadStatusTitle">
                {{ admin_t('Saving Package & Uploading Images...') }}
            </h3>
            <p class="package-upload-subtitle" id="uploadStatusSubtitle">
                {{ admin_t('Please wait while your files and changes are being uploaded and processed.') }}
            </p>

            <!-- Big Percentage Display -->
            <div class="package-upload-percentage">
                <span id="uploadPercentNumber">0</span><span class="percent-sign">%</span>
            </div>

            <!-- Progress Bar -->
            <div class="package-upload-progress-track">
                <div class="package-upload-progress-fill" id="uploadProgressBar" style="width: 0%;">
                    <div class="progress-glow"></div>
                </div>
            </div>

            <!-- Progress Info / Details -->
            <div class="package-upload-meta">
                <span class="upload-detail-size" id="uploadSizeDetail">
                    <i class="ti ti-database"></i> <span id="uploadSizeText">{{ admin_t('Preparing data...') }}</span>
                </span>
                <span class="upload-detail-speed" id="uploadStateBadge">
                    <span class="badge-dot"></span> <span id="uploadStateText">{{ admin_t('Uploading') }}</span>
                </span>
            </div>

            <!-- Note / Warning -->
            <div class="package-upload-notice">
                <i class="ti ti-info-circle"></i>
                <span>{{ admin_t('Please do not close or refresh this page until the process completes.') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
.package-upload-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    font-family: inherit;
}

.package-upload-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 17, 26, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    animation: puFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.package-upload-dialog {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 520px;
    animation: puScaleIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.package-upload-card {
    background: linear-gradient(145deg, #1f2233 0%, #171825 100%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 24px;
    padding: 36px 32px;
    text-align: center;
    color: #ffffff;
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(235, 140, 44, 0.2);
}

.package-upload-icon-wrapper {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-icon-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(235, 140, 44, 0.35) 0%, rgba(235, 140, 44, 0) 70%);
    animation: puPulse 2s infinite ease-out;
}

.upload-icon-circle {
    position: relative;
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(235, 140, 44, 0.25) 0%, rgba(235, 140, 44, 0.08) 100%);
    border: 2px solid rgba(235, 140, 44, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #eb8c2c;
    font-size: 32px;
    box-shadow: 0 8px 24px rgba(235, 140, 44, 0.3);
}

.package-upload-title {
    font-size: 1.28rem;
    font-weight: 700;
    margin: 0 0 8px;
    color: #ffffff;
    letter-spacing: -0.01em;
}

.package-upload-subtitle {
    font-size: 0.88rem;
    color: rgba(255, 255, 255, 0.65);
    margin: 0 0 24px;
    line-height: 1.5;
}

.package-upload-percentage {
    font-size: 3.2rem;
    font-weight: 900;
    line-height: 1;
    color: #eb8c2c;
    margin-bottom: 18px;
    font-feature-settings: "tnum";
    font-variant-numeric: tabular-nums;
    text-shadow: 0 4px 20px rgba(235, 140, 44, 0.4);
}

.package-upload-percentage .percent-sign {
    font-size: 1.8rem;
    margin-left: 4px;
    opacity: 0.85;
}

.package-upload-progress-track {
    position: relative;
    width: 100%;
    height: 14px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.package-upload-progress-fill {
    position: relative;
    height: 100%;
    background: linear-gradient(90deg, #d97706 0%, #eb8c2c 50%, #f59e0b 100%);
    border-radius: 999px;
    transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 16px rgba(235, 140, 44, 0.6);
}

.progress-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.4) 50%, transparent 100%);
    animation: puShimmer 1.8s infinite linear;
}

.package-upload-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 4px;
    font-size: 0.84rem;
    color: rgba(255, 255, 255, 0.65);
    margin-bottom: 22px;
}

.package-upload-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.upload-detail-speed .badge-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
    animation: puBlink 1.4s infinite ease-in-out;
}

.package-upload-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px dashed rgba(255, 255, 255, 0.15);
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.55);
}

.package-upload-notice i {
    font-size: 1.1rem;
    color: #eb8c2c;
}

/* Success State */
.package-upload-card.is-success .upload-icon-circle {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(16, 185, 129, 0.08) 100%);
    border-color: rgba(16, 185, 129, 0.6);
    color: #10b981;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}
.package-upload-card.is-success .package-upload-percentage {
    color: #10b981;
    text-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
}
.package-upload-card.is-success .package-upload-progress-fill {
    background: linear-gradient(90deg, #059669 0%, #10b981 100%);
    box-shadow: 0 0 16px rgba(16, 185, 129, 0.6);
}

@keyframes puFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes puScaleIn {
    from {
        opacity: 0;
        transform: scale(0.92) translateY(12px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes puPulse {
    0% { transform: scale(0.9); opacity: 0.8; }
    50% { transform: scale(1.35); opacity: 0.2; }
    100% { transform: scale(1.5); opacity: 0; }
}

@keyframes puShimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes puBlink {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}
</style>

