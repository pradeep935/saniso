<?php

use Botble\AIContentGenerator\Models\AIContentGeneratorSetting;

if (!function_exists('ai_setting')) {
    /**
     * Get AI Content Generator setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function ai_setting($key, $default = null) {
        return AIContentGeneratorSetting::get($key, $default);
    }
}
