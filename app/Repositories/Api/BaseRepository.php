<?php

namespace App\Repositories\Api;

use App\Models\User;
use App\Helpers\Authorization;

class BaseRepository { 
    
    protected $languages;
    protected $langCode;
    protected $limit = 10;

    public function __construct()
    {
        $this->languages = \Config::get('app.locales');
        $this->getLangAndSetLocale();
    }

    private function getLangAndSetLocale()
    {
        $lang = request()->header('lang');
        if ($lang == null || !in_array($lang, array_keys($this->languages))) {
            $lang = 'en';
        }
        $this->langCode = $lang;
        app()->setLocale($lang);
    }

    public function authUser()
    {
        $token = request()->header('authorization');
        $token = Authorization::validateToken($token);
        $user = null;
        if ($token) {
            $user = User::find($token->id);
        }
        return $user;
    }

    protected function buildTree($elements, $transformer = 'treeTransform', $parentId = 0)
    {
        $branches = array();
        foreach ($elements as $key => $element) {
            if ($element->parent_id == $parentId) {
                $childrens = array();
                $childrens = $this->buildTree($elements, $transformer, $element->id);
                if ($childrens) {
                    $element['childrens'] = $childrens;
                }
                $branches[] = $element->{$transformer}();
            }
        }
        return $branches;
    }

}
