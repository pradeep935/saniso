<?php

namespace Botble\Ecommerce\Facades;

use Botble\Ecommerce\Supports\PickingListHelper as BasePickingListHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Botble\Base\Supports\Pdf makePickingListPDF(\Botble\Ecommerce\Models\Order $order)
 * @method static string generatePickingList(\Botble\Ecommerce\Models\Order $order)
 * @method static \Illuminate\Http\Response downloadPickingList(\Botble\Ecommerce\Models\Order $order)
 * @method static \Illuminate\Http\Response streamPickingList(\Botble\Ecommerce\Models\Order $order)
 * @method static string getPickingListTemplate()
 * @method static string getPickingListTemplatePath()
 * @method static string getPickingListTemplateCustomizedPath()
 * @method static array getVariables()
 * @method static string getLanguageSupport()
 * @method static string getLanguageSupportStatic()
 * @method static string|null getCompanyCountry()
 * @method static string|null getCompanyState()
 * @method static string|null getCompanyCity()
 *
 * @see \Botble\Ecommerce\Supports\PickingListHelper
 */
class PickingListHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BasePickingListHelper::class;
    }
}