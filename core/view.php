<?php
/**
 * 视图层
 */
namespace core;
class view
{
    static function create($target, $tData, $expireTime)
    {
        if (($_SERVER['REQUEST_TIME'] - @filemtime($target)) > $expireTime) {
            $path = dirname($target);
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            return file_put_contents($target, $tData);
        }
        return "";
    }

    private static $tags = array(
        '/{{(.+?)}}/' => "core\\view::VEcho",
        '/{:(.+?)}/' => "core\\view::VPhp",
        '/{if:([\\a-zA-Z0-9\$_.|&]+)}/' => "core\\view::Vif",
        '/{elseif:([\\a-zA-Z0-9\|\$_.]+)}/' => "core\\view::VElf",
        '/{end}/' => 'core\\view::VEnd',
        '/{else}/' => 'core\\view::VElse',
        '/{fetch:([\\a-zA-Z0-9\$_.]+)}/' => "core\\view::VFetch",
        '/{import:([a-zA-Z0-9\$_]+)\/([a-zA-Z0-9\$_]+)}/' => "core\\view::VImport",
    );

    //解析
    static function compile($data)
    {
        foreach (self::$tags as $pattern => $func) {
            $data = preg_replace_callback($pattern, $func, $data);
        }
        return $data;
    }

    static function tag($tag, $callback)
    {
        self::$tags[$tag] = $callback;
    }

    static function display($tpl = _ACTION_, $vars = array())
    {
        //缓存或解析文件
        $target = ROOT_PATH . "/cache/" . _MODULE_ . "/" . $tpl . ".php";

        //视图文件
        if($tpl == 'not_found'){
            $file = VIEW_PATH . "/not_found.php";
        }else{
            $file = VIEW_PATH . "/" . _MODULE_ . '/' .$tpl . ".php";
        }

        if (file_exists($file)) {
            //解析正则规则匹配的标签
            $data = self::compile(file_get_contents($file));
            //创建文件
            self::create($target, $data, -1);
            
            if($file == VIEW_PATH . "/not_found.php"){
                $vars['messages'] = "Can't find the view file: $file";
            }

            //从数组中将变量导入到当前的符号表  EXTR_OVERWRITE表示如果有冲突，覆盖已有的变量。
            extract($vars);
            require_once file_exists($target) ? $target : $file;
        }
    }

    static function VEcho($matches)
    {
        return "<?php echo $matches[1]; ?>";
    }

    static function VPhp($matches)
    {
        return "<?php $matches[1]; ?>";
    }

    static function Vif($matches)
    {
        return "<?php if($matches[1]){ ?>";
    }

    static function VElf($matches)
    {
        return "<?php }elseif($matches[1]){ ?>";
    }

    static function VElse()
    {
        return "<?php }else{ ?>";
    }

    static function VEnd()
    {
        return "<?php } ?>";
    }

    static function VFetch($matches)
    {
        return "<?php foreach($matches[1]){ ?>";
    }

    static function VImport($matches)
    {
        $file = APP_PATH . "/" . $matches[1] . "/template/" . $matches[2] . ".php";
        if (file_exists($file)) {
            return self::compile(file_get_contents($file));
        }
    }
}