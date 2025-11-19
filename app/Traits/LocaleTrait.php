<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

trait LocaleTrait
{
    public function getLocale(Request $request = null): string
    {
        $request ??= request();

        $locale = $request->header('Accept-Language') ?: $request->query('locale');
        $locale = $locale ?: config('app.locale');

        if (!in_array($locale, config('app.supported_locales'))) {
            $locale = config('app.fallback_locale');
        }

        return $locale;
    }
}