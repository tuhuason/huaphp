<?php
/**
 * app
 */
use core\http;
use core\plugin;

class app
{
    private static $container = array();

    public static $config = array();

    static function join($name, $call)
    {
        self::$container[$name] = !is_callable($call) ? new $call() : $call();
    }

    static function singleton($class, $name=array())
    {
        $key = md5($class.serialize($name));
        if (!isset(self::$container[$key])) {
            self::$container[$key] = new $class();
        }
        return self::$container[$key];
    }

    //初始化
    static function init()
    {
        define("HUAPHP_VER", "1.0.1", false);
        defined("CORE_PATH") OR define("CORE_PATH", __DIR__);
        defined("ROOT_PATH") OR define("ROOT_PATH", dirname(CORE_PATH));
        defined("STATIC_PATH") OR define("STATIC_PATH", ROOT_PATH . "/static");
        defined("APP_PATH") OR define("APP_PATH", ROOT_PATH . "/app");
        defined("LOG_PATH") OR define("LOG_PATH", ROOT_PATH . "/logs");
        defined("VIEW_PATH") OR define("VIEW_PATH", ROOT_PATH . "/views");

        //autoload
        spl_autoload_register(function ($className) {
            $class = "/" . str_replace("\\", "/", $className) . ".php";
            self::autoLoad($class, array(CORE_PATH, APP_PATH, ROOT_PATH));
        });

        plugin::init();
        http::sessionStart();

        //加载配置
        self::$config = include ROOT_PATH . "/config/app.php";
        //连接数据库
        empty(self::$config['db']) || db::connect(self::$config['db']);
        empty(self::$config['rule']) || router::set(self::$config['rule']);
    }

    static function autoLoad($class, array $loadDir)
    {
        foreach ($loadDir as $dir) {
            if (file_exists($dir . $class)) {
                return require_once "$dir$class";
            }
        }
    }

    static function run()
    {
        self::init();
        router::initController();
    }
}
{
    function conf($key, $item = "")
    {
        if (!isset(app::$config[$key])) {
            $split = explode(".", $key);
            $dest = count($split) > 1 ? $split[0] . "/config/" . $split[1] : "config/" . $split[0];

            app::$config[$key] = include APP_PATH . "/$dest.php";
        }
        return empty($item) ? app::$config[$key] : app::$config[$key][$item];
    }

    // 校验验证码
    function check_code($code,$key = 'code') {
        // 验证码不能为空
        $secode = http::getSession($key);
        if(empty($code) || empty($secode)) {
            return false;
        }
        // session 过期
        if(time() - $secode['verify_time'] > 1800) {
            http::setSession($key, null);
            return false;
        }

        if($code == $secode['verify_code']) {
            http::setSession($key, null);
            return true;
        }

        return false;
    }
}