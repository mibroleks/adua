<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        $record = Setting::where('key', $key)->first();
        return $record ? $record->value : $default;
    }
}
