<?php

namespace Botble\Ecommerce\Supports;

use Botble\Base\Supports\Pdf;
use Botble\Ecommerce\Models\Order;
use Botble\Media\Facades\RvMedia;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PickingListHelper
{
    public function makePickingListPDF(Order $order): Pdf
    {
        return (new Pdf())
            ->templatePath($this->getPickingListTemplatePath())
            ->destinationPath($this->getPickingListTemplateCustomizedPath())
            ->supportLanguage($this->getLanguageSupport())
            ->paperSizeA4()
            ->data($this->getDataForPickingListTemplate($order))
            ->twigExtensions([
                new TwigExtension(),
            ])
            ->setProcessingLibrary(get_ecommerce_setting('invoice_processing_library', 'dompdf'));
    }

    public function generatePickingList(Order $order): string
    {
        $storageDisk = Storage::disk('local');

        $pickingListFile = sprintf('ecommerce/picking-lists/picking-list-%s.pdf', $order->code);

        $pickingListPath = $storageDisk->path($pickingListFile);

        if ($storageDisk->exists($pickingListFile)) {
            return $pickingListPath;
        }

        File::ensureDirectoryExists(dirname($pickingListPath));

        $this->makePickingListPDF($order)->save($pickingListPath);

        return $pickingListPath;
    }

    public function downloadPickingList(Order $order): Response|string|null
    {
        return $this->makePickingListPDF($order)->download(sprintf('picking-list-%s.pdf', $order->code));
    }

    public function streamPickingList(Order $order): Response|string|null
    {
        return $this->makePickingListPDF($order)->stream(sprintf('picking-list-%s.pdf', $order->code));
    }

    public function getPickingListTemplate(): string
    {
        return (new Pdf())
            ->supportLanguage($this->getLanguageSupport())
            ->twigExtensions([
                new TwigExtension(),
            ])
            ->getContent($this->getPickingListTemplatePath(), $this->getPickingListTemplateCustomizedPath());
    }

    public function getPickingListTemplatePath(): string
    {
        return plugin_path('ecommerce/resources/templates/picking-list.tpl');
    }

    public function getPickingListTemplateCustomizedPath(): string
    {
        return storage_path('app/templates/ecommerce/picking-list.tpl');
    }

    protected function getDataForPickingListTemplate(Order $order): array
    {
        $logo = get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option(
            'logo_in_picking_lists'
        ) ?: Theme::getLogo());

        if ($logo) {
            // Clean the logo path and get proper URL
            $logo = ltrim($logo, './');
            $logo = RvMedia::getImageUrl($logo, null, false, RvMedia::getDefaultImage());
        }

        $companyLogo = get_ecommerce_setting('company_logo_for_invoicing');

        if ($companyLogo) {
            // Clean the company logo path and get proper URL  
            $companyLogo = ltrim($companyLogo, './');
            $companyLogo = RvMedia::getImageUrl($companyLogo, null, false, RvMedia::getDefaultImage());
        }

        $order->load([
            'products',
            'address',
            'shipment',
        ]);

        $hasProductOptions = false;

        foreach ($order->products as $orderProduct) {
            if ($orderProduct->product_options) {
                $hasProductOptions = true;
                break;
            }
        }

        return [
            'order' => $order,
            'logo_full_path' => $logo,
            'company_logo_full_path' => $companyLogo,
            'site_title' => theme_option('site_title') ?: get_ecommerce_setting('store_name'),
            'company_name' => get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name'),
            'company_address' => get_ecommerce_setting('company_address_for_invoicing') ?: get_ecommerce_setting('store_address'),
            'company_country' => $this->getCompanyCountry(),
            'company_state' => $this->getCompanyState(),
            'company_city' => $this->getCompanyCity(),
            'company_zipcode' => get_ecommerce_setting('company_zipcode_for_invoicing') ?: get_ecommerce_setting('store_zip_code'),
            'company_phone' => get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone'),
            'company_email' => get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email'),
            'company_tax_id' => get_ecommerce_setting('company_tax_id_for_invoicing'),
            'has_product_options' => $hasProductOptions,
            'invoice_css' => file_get_contents(platform_path('plugins/ecommerce/resources/templates/invoice.css')),
            'settings' => [
                'using_custom_font_for_picking_list' => (bool) get_ecommerce_setting('using_custom_font_for_picking_list'),
                'custom_font_family' => get_ecommerce_setting('picking_list_font_family', 'DejaVu Sans'),
                'font_family' => (int) get_ecommerce_setting('using_custom_font_for_picking_list', 0) == 1
                    ? get_ecommerce_setting('picking_list_font_family', 'DejaVu Sans')
                    : 'DejaVu Sans',
                'font_css' => '',
                'date_format' => get_ecommerce_setting('picking_list_date_format', 'F d, Y'),
                'extra_css' => get_ecommerce_setting('picking_list_extra_css'),
                'header_html' => get_ecommerce_setting('picking_list_header_html'),
                'footer_html' => get_ecommerce_setting('picking_list_footer_html'),
            ],
        ];

        return $data;
    }

    public function getLanguageSupport(): string
    {
        return static::getLanguageSupportStatic();
    }

    public static function getLanguageSupportStatic(): string
    {
        $languageSupport = get_ecommerce_setting('picking_list_language_support');

        if (! empty($languageSupport)) {
            return $languageSupport;
        }

        if (get_ecommerce_setting('picking_list_support_arabic_language', false)) {
            return 'arabic';
        }

        if (get_ecommerce_setting('picking_list_support_bangladesh_language', false)) {
            return 'bangladesh';
        }

        return '';
    }

    public function getVariables(): array
    {
        return [
            'order.*' => trans('plugins/ecommerce::picking-list-template.variables.order_data'),
            'logo_full_path' => trans('plugins/ecommerce::picking-list-template.variables.site_logo'),
            'company_logo_full_path' => trans('plugins/ecommerce::picking-list-template.variables.company_logo'),
            'site_title' => trans('plugins/ecommerce::picking-list-template.variables.site_title'),
            'company_name' => trans('plugins/ecommerce::picking-list-template.variables.company_name'),
            'company_address' => trans('plugins/ecommerce::picking-list-template.variables.company_address'),
            'company_country' => trans('plugins/ecommerce::picking-list-template.variables.company_country'),
            'company_state' => trans('plugins/ecommerce::picking-list-template.variables.company_state'),
            'company_city' => trans('plugins/ecommerce::picking-list-template.variables.company_city'),
            'company_zipcode' => trans('plugins/ecommerce::picking-list-template.variables.company_zipcode'),
            'company_phone' => trans('plugins/ecommerce::picking-list-template.variables.company_phone'),
            'company_email' => trans('plugins/ecommerce::picking-list-template.variables.company_email'),
            'company_tax_id' => trans('plugins/ecommerce::picking-list-template.variables.company_tax_id'),
        ];
    }

    public function getCompanyCountry(): ?string
    {
        return get_ecommerce_setting('company_country_for_invoicing', get_ecommerce_setting('store_country'));
    }

    public function getCompanyState(): ?string
    {
        return get_ecommerce_setting('company_state_for_invoicing', get_ecommerce_setting('store_state'));
    }

    public function getCompanyCity(): ?string
    {
        return get_ecommerce_setting('company_city_for_invoicing', get_ecommerce_setting('store_city'));
    }
}