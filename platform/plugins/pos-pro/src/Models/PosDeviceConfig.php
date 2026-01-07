<?php

namespace Botble\PosPro\Models;

use Botble\ACL\Models\User;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosDeviceConfig extends BaseModel
{
    protected $table = 'pos_device_configs';

    protected $fillable = [
        'user_id',
        'device_ip',
        'device_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get device config for a specific user
     */
    public static function getForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set device config for a user
     */
    public static function setForUser(int $userId, ?string $deviceIp, ?string $deviceName = null): self
    {
        return static::updateOrCreate(
            ['user_id' => $userId],
            [
                'device_ip' => $deviceIp,
                'device_name' => $deviceName,
                'is_active' => ! empty($deviceIp),
            ]
        );
    }

    /**
     * Check if IP is valid private IP
     */
    public function isValidPrivateIp(): bool
    {
        if (! $this->device_ip || ! filter_var($this->device_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        // Check if IP is in private ranges
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.0/8', // localhost
        ];

        foreach ($privateRanges as $range) {
            if ($this->ipInRange($this->device_ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) == $subnet;
    }
}
