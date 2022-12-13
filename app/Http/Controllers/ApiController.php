<?php

namespace App\Http\Controllers;

use Request;
use App\Traits\Basic;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{

    use Basic;

    protected $langCode;

    public function __construct()
    {
        $this->getLangAndSetLocale();
    }

    private function getLangAndSetLocale()
    {
        $languages = \Config::get('app.locales');
        $lang = Request::header('lang');
        if ($lang == null || !in_array($lang, array_keys($languages))) {
            $lang = 'en';
        }
        $this->langCode = $lang;
        app()->setLocale($lang);
    }
}
