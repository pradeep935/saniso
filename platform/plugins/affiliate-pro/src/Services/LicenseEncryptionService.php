<?php

namespace Botble\AffiliatePro\Services;

use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class LicenseEncryptionService
{
    /**
     * Migrate existing unencrypted purchase codes to encrypted format
     */
    public static function migrateExistingPurchaseCode(): bool
    {
        $purchaseCode = setting('affiliate_pro_license_purchase_code');

        if (! $purchaseCode) {
            return false;
        }

        // Try to decrypt the purchase code
        try {
            Crypt::decryptString($purchaseCode);

            // If decryption succeeds, it's already encrypted
            return true;
        } catch (Throwable) {
            // If decryption fails, it's unencrypted - encrypt it
            try {
                $encryptedPurchaseCode = Crypt::encryptString($purchaseCode);
                Setting::forceSet('affiliate_pro_license_purchase_code', $encryptedPurchaseCode)->save();

                return true;
            } catch (Throwable $encryptException) {
                // Log the error but don't fail
                report($encryptException);

                return false;
            }
        }
    }

    /**
     * Safely decrypt a purchase code
     */
    public static function decryptPurchaseCode(string $purchaseCode): string
    {
        try {
            return Crypt::decryptString($purchaseCode);
        } catch (Throwable) {
            // If decryption fails, return the original value
            return $purchaseCode;
        }
    }

    /**
     * Encrypt a purchase code
     */
    public static function encryptPurchaseCode(string $purchaseCode): string
    {
        return Crypt::encryptString($purchaseCode);
    }

    /**
     * Check if a purchase code is encrypted
     */
    public static function isPurchaseCodeEncrypted(string $purchaseCode): bool
    {
        try {
            Crypt::decryptString($purchaseCode);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
