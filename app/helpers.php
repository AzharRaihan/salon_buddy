<?php

use Illuminate\Support\Facades\Log;

/**
 * Get the system config
 * @demoCheck
 */
function demoCheck()
{
    return false;
}

/**
 * Get the site settings
 * @return array
 */
function setting($key)
{
    $setting = \App\Models\Setting::getSetting($key);
    return $setting;
}

/**
 * Logo Url
 */

function logo_url()
{
    $setting = \App\Models\Setting::getSetting('logo');

    if ($setting) {
        return asset('assets/images/logo') . '/' . $setting;
    }

    return asset('logo.png');
}

/**
 * Favicon Url
 */

function favicon_url()
{
    $setting = \App\Models\Setting::getSetting('favicon');

    if ($setting) {
        return asset('assets/images/favicon') . '/' . $setting;
    }

    return asset('favicon.ico');
}


// 