<?php
/**
 * 控制器基类
 */
namespace core;

use core\http;

class controller {

    protected $_data = array();

    protected function assign($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function display($tpl = _ACTION_, $vars = array())
    {
        $params =  array_merge($vars, $this->_data);
        view::display($tpl, $params);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data,$type='',$json_option=0) {
        if(empty($type)) $type  =   conf('default_ajax_return');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));  
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);            
            default     :
        }
    }

    /**
     * Action跳转(URL重定向)
     * @access protected
     * @param string $url 跳转的URL表达式
     * @return void
     */
    protected function redirect($url) {
        http::redirect($url);
    }
}