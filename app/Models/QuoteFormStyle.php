<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteFormStyle extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value'
    ];

    protected $casts = [
        'setting_value' => 'array'
    ];

    /**
     * Get style setting by key
     */
    public static function getSetting($key, $default = [])
    {
        $style = self::where('setting_key', $key)->first();
        return $style ? $style->setting_value : $default;
    }

    /**
     * Set style setting
     */
    public static function setSetting($key, $value)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }

    /**
     * Get all style settings as array
     */
    public static function getAllSettings()
    {
        return self::pluck('setting_value', 'setting_key')->toArray();
    }

    /**
     * Generate CSS from style settings
     */
    public static function generateCSS()
    {
        $settings = self::getAllSettings();
        $css = '';

        // Form container styles
        if (isset($settings['form_container'])) {
            $container = $settings['form_container'];
            $css .= ".quote-form-container {\n";
            if (isset($container['background_color'])) $css .= "  background-color: {$container['background_color']};\n";
            if (isset($container['border_radius'])) $css .= "  border-radius: {$container['border_radius']};\n";
            if (isset($container['padding'])) $css .= "  padding: {$container['padding']};\n";
            if (isset($container['margin'])) $css .= "  margin: {$container['margin']};\n";
            if (isset($container['box_shadow'])) $css .= "  box-shadow: {$container['box_shadow']};\n";
            if (isset($container['max_width'])) $css .= "  max-width: {$container['max_width']};\n";
            $css .= "}\n\n";
        }

        // Form field styles
        if (isset($settings['form_fields'])) {
            $fields = $settings['form_fields'];
            
            // Labels
            $css .= ".quote-form-container label {\n";
            if (isset($fields['label_color'])) $css .= "  color: {$fields['label_color']};\n";
            if (isset($fields['label_font_size'])) $css .= "  font-size: {$fields['label_font_size']};\n";
            if (isset($fields['label_font_weight'])) $css .= "  font-weight: {$fields['label_font_weight']};\n";
            $css .= "}\n\n";

            // Form controls
            $css .= ".quote-form-container .form-control, .quote-form-container .form-select {\n";
            if (isset($fields['input_border_color'])) $css .= "  border-color: {$fields['input_border_color']};\n";
            if (isset($fields['input_border_radius'])) $css .= "  border-radius: {$fields['input_border_radius']};\n";
            if (isset($fields['input_padding'])) $css .= "  padding: {$fields['input_padding']};\n";
            if (isset($fields['input_font_size'])) $css .= "  font-size: {$fields['input_font_size']};\n";
            if (isset($fields['input_background'])) $css .= "  background-color: {$fields['input_background']};\n";
            $css .= "}\n\n";

            // Field groups
            if (isset($fields['field_margin_bottom'])) {
                $css .= ".quote-form-container .form-group {\n";
                $css .= "  margin-bottom: {$fields['field_margin_bottom']};\n";
                $css .= "}\n\n";
            }
        }

        // Button styles
        if (isset($settings['form_buttons'])) {
            $buttons = $settings['form_buttons'];
            $css .= ".quote-form-container .btn-submit {\n";
            if (isset($buttons['submit_bg_color'])) $css .= "  background-color: {$buttons['submit_bg_color']};\n";
            if (isset($buttons['submit_text_color'])) $css .= "  color: {$buttons['submit_text_color']};\n";
            if (isset($buttons['submit_border_radius'])) $css .= "  border-radius: {$buttons['submit_border_radius']};\n";
            if (isset($buttons['submit_padding'])) $css .= "  padding: {$buttons['submit_padding']};\n";
            if (isset($buttons['submit_font_size'])) $css .= "  font-size: {$buttons['submit_font_size']};\n";
            if (isset($buttons['submit_font_weight'])) $css .= "  font-weight: {$buttons['submit_font_weight']};\n";
            if (isset($buttons['button_margin_top'])) $css .= "  margin-top: {$buttons['button_margin_top']};\n";
            $css .= "  border: none;\n";
            $css .= "}\n\n";
        }

        // Responsive styles
        if (isset($settings['responsive_breakpoints'])) {
            $responsive = $settings['responsive_breakpoints'];
            
            // Mobile styles
            if (isset($responsive['mobile']) && isset($responsive['mobile_padding'])) {
                $css .= "@media (max-width: {$responsive['mobile']}) {\n";
                $css .= "  .quote-form-container {\n";
                $css .= "    padding: {$responsive['mobile_padding']};\n";
                $css .= "  }\n";
                $css .= "}\n\n";
            }

            // Tablet styles
            if (isset($responsive['tablet']) && isset($responsive['tablet_padding'])) {
                $css .= "@media (min-width: {$responsive['mobile']}) and (max-width: {$responsive['tablet']}) {\n";
                $css .= "  .quote-form-container {\n";
                $css .= "    padding: {$responsive['tablet_padding']};\n";
                $css .= "  }\n";
                $css .= "}\n\n";
            }

            // Desktop styles
            if (isset($responsive['desktop']) && isset($responsive['desktop_padding'])) {
                $css .= "@media (min-width: {$responsive['desktop']}) {\n";
                $css .= "  .quote-form-container {\n";
                $css .= "    padding: {$responsive['desktop_padding']};\n";
                $css .= "  }\n";
                $css .= "}\n\n";
            }
        }

        return $css;
    }
}