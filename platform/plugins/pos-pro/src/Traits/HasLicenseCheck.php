<?php

namespace Botble\PosPro\Traits;

use Botble\PosPro\Services\LicenseEncryptionService;
use Illuminate\Http\RedirectResponse;

trait HasLicenseCheck
{
    protected function checkLicenseActivation(): bool
    {
        $licenseStatus = setting('pos_pro_license_status');
        $purchaseCode = setting('pos_pro_license_purchase_code');
        $activatedAt = setting('pos_pro_license_activated_at');

        // Check if all required data exists
        if ($licenseStatus !== 'activated' || ! $purchaseCode || ! $activatedAt) {
            return false;
        }

        // Try to decrypt the purchase code to verify it's valid
        // This also handles migration of old unencrypted codes
        return LicenseEncryptionService::isPurchaseCodeEncrypted($purchaseCode) || ! empty($purchaseCode);
    }

    protected function redirectToLicenseActivation(?string $message = null): RedirectResponse
    {
        $defaultMessage = trans('plugins/pos-pro::pos.license.activation_required_message');

        return redirect()
            ->route('pos-pro.license.index')
            ->with('warning', $message ?: $defaultMessage);
    }

    protected function handleLicenseCheck(): ?RedirectResponse
    {
        if (! $this->checkLicenseActivation()) {
            return $this->redirectToLicenseActivation();
        }

        return null;
    }

    /**
     * Safely decrypt a purchase code, handling both encrypted and unencrypted values
     */
    protected function decryptPurchaseCode(string $purchaseCode): string
    {
        return LicenseEncryptionService::decryptPurchaseCode($purchaseCode);
    }

    /**
     * Encrypt a purchase code for storage
     */
    protected function encryptPurchaseCode(string $purchaseCode): string
    {
        return LicenseEncryptionService::encryptPurchaseCode($purchaseCode);
    }
}
