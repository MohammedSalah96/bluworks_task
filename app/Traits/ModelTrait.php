<?php

namespace App\Traits;
use Image;

trait ModelTrait {

    protected $lang_code;
    protected static $limit = 10;
    protected static $distance = 1000000;
    protected $sizes;

    protected function getLangCode() {
        $lang_code = app()->getLocale();
        return $lang_code;
    }

    protected static function transformCollection($items, $type = null, $extra_params = array()) {

        $transformers = array();

        if ($type == null) {
            $transform = 'transform';
        } else {
            $transform = 'transform' . $type;
        }
        if (count($items)) {
            foreach ($items as $item) {
                if (count($extra_params) > 0) {
                    $transformers[] = self::$transform($item, $extra_params);
                } else {
                    $transformers[] = self::$transform($item);
                }
            }
        }

        return $transformers;
    }


    public function handleKeywordWhere($columns, $keyword) {
        $search_exploded = explode(" ", $keyword);
        $i = 0;
        $construct = " ";
        foreach ($columns as $col) {
            //pri($col);
            $x = 0;
            $i++;
            if ($i != 1) {
                $construct .= " OR ";
            }
            foreach ($search_exploded as $search_each) {
                $x++;
                if (count($search_exploded) > 1) {
                    if ($x == 1) {
                        $construct .= "($col LIKE '%$search_each%' ";
                    } else {
                        $construct .= "AND $col LIKE '%$search_each%' ";
                        if ($x == count($search_exploded)) {
                            $construct .= ")";
                        }
                    }
                } else {
                    $construct .= "$col LIKE '%$search_each%' ";
                }
            }
        }
        return $construct;
    }

    protected static function rmv_prefix($old_image) {
        return substr($old_image, strpos($old_image, '_') + 1);
    }

    protected static function iniDiffLocations($tableName, $lat, $lng) {
        $diffLocations = "SQRT(POW(69.1 * ($tableName.lat - {$lat}), 2) + POW(69.1 * ({$lng} - $tableName.lng) * COS($tableName.lat / 57.3), 2)) as distance";
        return $diffLocations;
    }

    public function upload($file, $path, $resize = false, $sizes_type = false, $base = false) {
        $image = '';
        $path = public_path() . "/uploads/$path";
        $extension = (!$base) ? '.' . strtolower($file->getClientOriginalExtension()) : '.png';
        $filename = time() . mt_rand(1, 1000000) . $extension;


        $image = Image::make($file);
        $names = array();
        if ($resize) {

            if (isset($this->sizes) && !empty($this->sizes)) {
                $sizes = ($sizes_type) ? $this->sizes[$sizes_type] : $this->sizes;
                foreach ($sizes as $prefix => $size) {
                    $path_with_filename = $path . '/' . $prefix . '_' . $filename;
                    $image->backup();
                    if ($size['width'] == null && $size['height'] != null) {
                        //dd($prefix);
                        $image->resize(null, $size['height'], function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else if ($size['height'] == null && $size['width'] != null) {
                        $image->resize($size['width'], null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else {
                        $image->resize($size['width'], $size['height']);
                    }


                    $image = $image->save($path_with_filename, 100);
                    $image->reset();
                    $names[] = $image->basename;
                    //$image->reset();
                }
                return $names[0];
            }
        }
        $path_with_filename = $path . '/' . $filename;
        $image = $image->save($path_with_filename);
        return $image->basename;
    }

    public function deleteUploaded($path, $old_image) {

        if (isset($this->sizes) && !empty($this->sizes)) {
            $files = array();
            $image_without_prefix = substr($old_image, strpos($old_image, '_') + 1); //without s_
            foreach ($this->sizes as $prefix => $size) {
                $files[] = public_path("uploads/$path/$prefix" . "_" . "$image_without_prefix");
            }
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (!is_dir($file)) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
            }
        } else {
            $file = public_path("uploads/$path/$old_image");
            if (!is_dir($file)) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function upload_simple($file, $path, $base = false, $type = false)
    {
        $uploaded_file = $file;
        if ($base) {
            $uploaded_file = base64_decode($file);
        }
        if (!$base) {
            $file_original_name = pathinfo($uploaded_file->getClientOriginalName(), PATHINFO_FILENAME);
        }

        $path = public_path() . "/uploads/$path";

        if ($type) {
            if ($base) {
                $filename = time() . mt_rand(1, 1000000) . '.' . $type;
            } else {
                $filename = $file_original_name . '_' . time() . mt_rand(1, 1000000) . '.' . $type;
            }
        } else {
            if ($base) {
                $filename = time() . mt_rand(1, 1000000) . '.' . $uploaded_file->getClientOriginalExtension();
            } else {
                $filename = $file_original_name . '_' . time() . mt_rand(1, 1000000) . '.' . $uploaded_file->getClientOriginalExtension();
            }
        }
        if ($base) {
            file_put_contents($path . '/' . $filename, $uploaded_file);
        } else if ($uploaded_file->move($path, $filename)) {
            $uploaded_file = $filename;
        }
        return $filename;
    }

}
