<?php
/**
 * 参数过滤
 */
namespace core;

class filter
{
    private static $cityIdList;
    private static $provinceIdList;

    public static function filter_value($haystack, $needle, $default = '')
    {
        return isset($haystack[$needle]) ? $haystack[$needle] : $default;
    }

    //int
    public static function filter_int($haystack, $needle,$default=0)
    {
        return (int) self::filter_value($haystack, $needle,$default);
    }

    public static function filter_date($haystack, $needle)
    {
        return self::filter_value($haystack, $needle);
    }

    public static function filter_datetime($haystack, $needle)
    {
        return self::filter_value($haystack, $needle);
    }

    public static function filter_version($haystack, $needle){
    	return self::filter_value($haystack, $needle);
    }

    //只允许字母、数字、下划线
    public static function filter_username($haystack, $needle){
        $val= trim(self::filter_value($haystack, $needle));
        if(preg_match('/^[a-zA-Z0-9_]+$/',$val)){
            return $val;
        }
        return '';
    }
    /**
     * 只允许字母、数字、下划线、中划线、汉字
     * @param unknown $haystack
     * @param unknown $needle
     * @return unknown|string
     */
    public static function filter_name($haystack, $needle){
        $val= trim(self::filter_value($haystack, $needle));
        if(!preg_match('/[<>{}]+/',$val)){
            return $val;
        }
        return '';
    }

    //验证密码
    public static function filter_pwd($haystack, $needle){
        $val= trim(self::filter_value($haystack, $needle));
        if(preg_match('/^[a-zA-Z0-9_!@#$%^&*]{6,16}$/',$val) && !preg_match('/^[0-9]{1,9}$/',$val)){
            return $val;
        }
        return '';
    }

    //验证手机号码
    public static function filter_cellphone($haystack, $needle){
        $val= trim(self::filter_value($haystack, $needle));
        if(preg_match('/^1[3|4|5|7|8]\d{9}$/',$val)){
            return $val;
        }
        return '';
    }

    //验证邮箱
    public static function filter_email($haystack,$needle){
        $addr=self::filter_value($haystack, $needle);
        return filter_var($addr, \FILTER_VALIDATE_EMAIL);
    }

    //验证url
    public static function filter_url($haystack,$needle){
        $addr=self::filter_value($haystack, $needle);
        $extraSchemes=['http://','https://'];
        $valid=false;
        foreach ($extraSchemes as $scheme){
            if(substr($addr,0,strlen($scheme))==$scheme){
                $valid=true;
            }
        }
        return $valid?filter_var($addr, \FILTER_VALIDATE_URL):false;
    }

    public static function filter_amount($haystack, $needle,$precision=2)
    {
        $v= self::filter_value($haystack, $needle);
        if (! preg_match('/^(\d{0,9})(\.\d{0,'.$precision.'})?$/', $v)) {
            return 0;
        }
        return floatval($v);
    }

    public static function read_post_body($allow_null = false)
    {
        $stream = file_get_contents('php://input');
        if ($stream !== false) {
            $data = json_decode($stream,true);
            if (json_last_error() === JSON_ERROR_NONE) {
               return $data;
            }
        }
       return false;
    }
    
    /**
     * 过滤数字ID(s)
     */
    public static function filter_multi_ids($haystack, $needle, $default='', $sort=TRUE)
    {
        $ids_str = self::filter_value($haystack, $needle, $default);
    
        if (! $ids_str) {
            return '';
        } elseif (is_numeric($ids_str)) {
            return $ids_str;
        }elseif(is_array($ids_str)){
            $ids_arr=$ids_str;
        }else{
            $ids_arr = explode(',', $ids_str);
        }
        if ($sort) {
            sort($ids_arr);
        }
        $new_arr = array();
        foreach ($ids_arr as $k=>$v) {
            $temp = intval($v);
            if ($temp) {
                $new_arr[$k] = $temp;
            }
        }
        return implode(',', $new_arr);
    }
}