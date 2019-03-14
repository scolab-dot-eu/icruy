<?php

namespace App\Helpers;

/**
 * Class Helpers
 *
 */
class Helpers
{

    /**
     *
     * HT: https://stackoverflow.com/questions/28793614/laravel-5-link-to-with-icon-html
     *
     * @param       $name
     * @param       $html
     * @param array $parameters
     * @param array $attributes
     *
     * @return string
     */
    public static function link_to_route_html($name, $html, $parameters = array(), $attributes = array())
    {
        $url = route($name, $parameters);
        return '<a href="' . $url . '"' . app('html')->attributes($attributes) . '>' . $html . '</a>';
    }
    
    public static function set_boolean_value(& $array, $key) {
        if (array_key_exists($key, $array)) {
            $array[$key] = 1;
        }
        else {
            $array[$key] = 0;
        }
        return $array;
    }
    
    
    public static function domainDefToSelectArray($domainDef, $baseArray=null, $prependCode=False, $separator=' - ') {
        if (is_array($baseArray)) {
            $selectArray = $baseArray;
        }
        else {
            $selectArray = [];
        }
        if ($prependCode) {
            foreach ($domainDef as $domainElement) {
                $selectArray[$domainElement['code']] = $domainElement['code'].$separator.$domainElement['definition'];
            }
        }
        else {
            foreach ($domainDef as $domainElement) {
                $selectArray[$domainElement['code']] = $domainElement['definition'];
            }
        }
        return $selectArray;
    }

}
