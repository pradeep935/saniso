<?php

namespace Botble\AffiliatePro\Enums;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\Enum;
use Illuminate\Support\HtmlString;

/**
 * @method static AffiliateStatusEnum PENDING()
 * @method static AffiliateStatusEnum APPROVED()
 * @method static AffiliateStatusEnum REJECTED()
 * @method static AffiliateStatusEnum SUSPENDED()
 * @method static AffiliateStatusEnum BANNED()
 */
class AffiliateStatusEnum extends Enum
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const SUSPENDED = 'suspended';
    public const BANNED = 'banned';

    public static $langPath = 'plugins/affiliate-pro::enums.affiliate_statuses';

    public function toHtml(): string|HtmlString
    {
        return match ($this->value) {
            self::PENDING => Html::tag('span', self::PENDING()->label(), ['class' => 'badge bg-warning text-warning-fg']),
            self::APPROVED => Html::tag('span', self::APPROVED()->label(), ['class' => 'badge bg-success text-success-fg']),
            self::REJECTED => Html::tag('span', self::REJECTED()->label(), ['class' => 'badge bg-danger text-danger-fg']),
            self::SUSPENDED => Html::tag('span', self::SUSPENDED()->label(), ['class' => 'badge bg-secondary text-secondary-fg']),
            self::BANNED => Html::tag('span', self::BANNED()->label(), ['class' => 'badge bg-dark text-dark-fg']),
            default => parent::toHtml(),
        };
    }
}
