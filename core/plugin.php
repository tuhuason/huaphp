<?php
namespace core;

class plugin
{
    private static $listeners = array();

    static function init()
    {
        $plugins = self::getPlugins();
        if (!empty($plugins)) {
            foreach ($plugins as $plugin) {
                if (file_exists(STATIC_PATH . "/$plugin/install.lock")) {
                    $action ="\\plugins\\".$plugin."\\action";
                    self::$listeners[] = new $action;
                }
            }
        }
    }

    static function hook($hook, $data = "")
    {
        foreach (self::$listeners as $listener) {
            if (method_exists($listener, $hook)) {
                $data = $listener->$hook($data);
            }
        }
        return $data;
    }

    static function enable($plugin, $switch = true)
    {
        $lock = STATIC_PATH . "/$plugin/install.lock";
        if ($switch) {
            if (file_exists(STATIC_PATH . "/$plugin/action.php")) {
                return file_put_contents($lock, time());
            }
            return false;
        }
        return @unlink($lock);
    }

    static function getPlugins()
    {
        $plugins = array();
        if (false != ($handle = opendir(STATIC_PATH))) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && !strpos($file, ".")) {
                    $plugins[] = iconv("gb2312//IGNORE", "utf-8", $file);
                }
            }
            closedir($handle);
        }
        return $plugins;
    }
}