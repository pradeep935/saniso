<div class="qr-code-container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('plugins/affiliate-pro::affiliate.qr_code') }}</h5>
        </div>
        <div class="card-body text-center">
            <div class="mb-3">
                <p>{{ trans('plugins/affiliate-pro::affiliate.qr_code_description') }}</p>
            </div>

            <div class="qr-code-image mb-3">
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code" class="img-fluid" style="max-width: 200px;">
            </div>

            <div class="qr-code-actions">
                <a href="data:image/svg+xml;base64,{{ $qrCode }}" download="affiliate-qr-code.svg" class="btn btn-primary">
                    <x-core::icon name="ti ti-download" /> {{ trans('plugins/affiliate-pro::affiliate.download_qr_code') }}
                </a>
            </div>
        </div>
    </div>
</div>
