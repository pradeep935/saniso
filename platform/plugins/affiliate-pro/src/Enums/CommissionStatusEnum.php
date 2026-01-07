<?php

namespace Botble\AffiliatePro\Enums;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static CommissionStatusEnum PENDING()
 * @method static CommissionStatusEnum APPROVED()
 * @method static CommissionStatusEnum REJECTED()
 */
class CommissionStatusEnum extends Enum
{
    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public static $langPath = 'plugins/affiliate-pro::commission.statuses';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            default => 'primary',
        };

        return BaseHelper::renderBadge($this->label(), $color);
    }
}
