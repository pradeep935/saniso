<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Pdf;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Http\Requests\Settings\PickingListTemplateSettingRequest;
use Botble\Ecommerce\Supports\PickingListHelper;
use Botble\Ecommerce\Supports\TwigExtension;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class PickingListTemplateSettingController extends SettingController
{
    public function edit(Request $request): View
    {
        $this->pageTitle(trans('plugins/ecommerce::picking-list-template.name'));

        Assets::addScriptsDirectly('vendor/core/core/setting/js/email-template.js');

        $templates = $this->getDefaultPickingListTemplates();
        
        $request->validate([
            'template' => ['nullable', 'string', Rule::in(array_keys($templates))],
        ]);

        $currentTemplate = $request->input('template', array_key_first($templates));
        $template = Arr::get($templates, $currentTemplate, array_key_first($templates));
        $templatesForSelect = collect($templates)
            ->mapWithKeys(fn ($template, $key) => [$key => Arr::get($template, 'label', $key)]);

        return view(
            'plugins/ecommerce::picking-list-template.settings',
            compact('templatesForSelect', 'template', 'currentTemplate')
        );
    }

    public function update(PickingListTemplateSettingRequest $request)
    {
        $templates = $this->getDefaultPickingListTemplates();
        $template = Arr::get($templates, $request->input('template', array_key_first($templates)));

        $filePath = value($template['customized_path']);

        File::ensureDirectoryExists(File::dirname($filePath));

        BaseHelper::saveFileData($filePath, $request->input('content'), false);

        return $this
            ->httpResponse()
            ->withUpdatedSuccessMessage();
    }

    public function reset(string $template)
    {
        $templates = $this->getDefaultPickingListTemplates();
        $templateData = Arr::get($templates, $template, array_key_first($templates));

        if (Arr::has($templateData, 'customized_path')) {
            File::delete(value($templateData['customized_path']));
        }

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/ecommerce::picking-list-template.reset_success'));
    }

    public function preview(string $template)
    {
        // Simply call our generatePreview method since we only have one template type for now
        return $this->generatePreview();
    }

    protected function getDefaultPickingListTemplates(): array
    {        
        return [
            'default' => [
                'label' => trans('plugins/ecommerce::picking-list-template.template'),
                'content' => function () {
                    $pickingListHelper = app(PickingListHelper::class);
                    return (new Pdf())
                        ->twigExtensions([new TwigExtension()])
                        ->supportLanguage($pickingListHelper->getLanguageSupport())
                        ->getContent(
                            $pickingListHelper->getPickingListTemplatePath(),
                            $pickingListHelper->getPickingListTemplateCustomizedPath()
                        );
                },
                'customized_path' => function () {
                    return app(PickingListHelper::class)->getPickingListTemplateCustomizedPath();
                },
                'variables' => function () {
                    return app(PickingListHelper::class)->getVariables();
                },
                'preview' => true,
            ],
        ];
    }

    protected function generatePreview(): Response
    {
        $pickingListHelper = app(PickingListHelper::class);

        // Create a sample order for preview
        $sampleOrder = (object) [
            'code' => 'SF-10000028',
            'created_at' => now(),
            'status' => (object) ['label' => 'Processing'],
            'notes' => 'Sample order notes for preview - Please handle with care',
            'address' => (object) [
                'name' => 'Alexander Petrov-Savchenko',
                'email' => 'flanger4@gmail.com',
                'phone' => '+31645926133',
                'address' => 'Wezerhof 1',
                'city' => 'Veghel',
                'zip_code' => '5462 AB',
                'state' => 'Noord-Brabant',
                'country_name' => 'Netherlands',
            ],
            'products' => collect([
                (object) [
                    'product_name' => 'Silicone Color KK64 310ml',
                    'qty' => 3,
                    'product' => (object) [
                        'sku' => 'SF-2443-LUKX',
                    ],
                    'product_options' => [
                        'Color' => 'Red',
                        'Size' => 'Large',
                    ],
                ],
                (object) [
                    'product_name' => 'Professional Sealant White 280ml',
                    'qty' => 2,
                    'product' => (object) [
                        'sku' => 'SF-2443-WHITE',
                    ],
                    'product_options' => null,
                ],
                (object) [
                    'product_name' => 'Clear Adhesive Premium 250ml',
                    'qty' => 1,
                    'product' => (object) [
                        'sku' => 'SF-2443-CLEAR',
                    ],
                    'product_options' => [
                        'Viscosity' => 'Medium',
                    ],
                ],
            ]),
            'shipment' => (object) [
                'shipping_method' => 'Standard Shipping',
            ],
        ];

        return (new Pdf())
            ->templatePath($pickingListHelper->getPickingListTemplatePath())
            ->destinationPath($pickingListHelper->getPickingListTemplateCustomizedPath())
            ->paperSizeA4()
            ->supportLanguage($pickingListHelper->getLanguageSupport())
            ->twigExtensions([
                new TwigExtension(),
            ])
            ->data($this->getPreviewData($sampleOrder))
            ->setProcessingLibrary(get_ecommerce_setting('invoice_processing_library', 'dompdf'))
            ->stream();
    }

    protected function getPreviewData($order): array
    {
        return [
            'order' => $order,
            'company_name' => 'SANISO',
            'company_address' => 'Sample Business Address 123',
            'company_city' => 'Amsterdam',
            'company_zipcode' => '1234 AB',
            'company_country' => 'Netherlands',
            'company_phone' => '+31 20 123 4567',
            'company_email' => 'info@saniso.nl',
            'company_logo_full_path' => null,
            'has_product_options' => true,
            'invoice_css' => file_get_contents(platform_path('plugins/ecommerce/resources/templates/invoice.css')),
            'settings' => [
                'font_family' => 'DejaVu Sans',
                'font_css' => '',
                'date_format' => 'F d, Y',
                'extra_css' => '',
                'header_html' => '',
                'footer_html' => '',
            ],
        ];
    }

}