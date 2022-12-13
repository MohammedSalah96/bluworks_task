<?php

namespace App\Traits;

trait Basic {


    protected static function getLangCode() {
        $lang_code = app()->getLocale();
        $this->lang = $lang_code;
        return $lang_code;
    }

    public function _view($main_content, $type = 'front') {
        $main_content = "main_content/$type/$main_content";
        return view($main_content, $this->data);
    }


    protected function lang_rules($columns_arr = array()) {
        $rules = array();

        if (!empty($columns_arr)) {
            foreach ($columns_arr as $column => $rule) {
                foreach ($this->languages as $lang_key => $locale) {
                    $key = $column . '.' . $lang_key;
                    $rules[$key] = $rule;
                }
            }
        }
        return $rules;
    }

}
