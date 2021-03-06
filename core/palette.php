<?php

/**
 * 画板
 * point:点;line:线;arc:弧;fill:填充;border:边
 * ellipse:椭圆;rectangle:矩形;polygon:多边形
 * Class palette
 */
class palette
{
    static function create($width, $height, $bgColor = false, $type = false)
    {
        $image = $type ? imagecreatetruecolor($width, $height) : imagecreate($width, $height);
        $bgColor && self::color($image, $bgColor);
        return $image;
    }

    static function open($filename)
    {
        $format = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($format) {
            case 'gif':
                return imagecreatefromgif($filename);
            case 'png':
                return imagecreatefrompng($filename);
            case 'wbmp':
                return imagecreatefromwbmp($filename);
            case 'xbm':
                return imagecreatefromxbm($filename);
            case 'xmp':
                return imagecreatefromxpm($filename);
            default:
                return imagecreatefromjpeg($filename);
        }
    }

    //设置颜色
    static function color(&$image, $color = 'ffffff', $delete = false)
    {
        $cd = str_split($color, ((strlen($color) > 4) ? 2 : 1));
        $color = imagecolorallocate($image, hexdec($cd[0]), hexdec($cd[1]), hexdec($cd[2]));
        $delete && imagecolordeallocate($image, $color);
        return $color;
    }

    //绘制图形
    static function draw(&$image, $color, $param, $name = "line")
    {
        switch ($name) {
            case 'point': //像素点
                return imagesetpixel($image, $param[0], $param[1], $color);
            case 'arc': //弧(中心点/宽度-高度/起始角度-结束角度(0-360))
                return imagearc($image, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $color);
            case 'polygon': //多边形$param:各顶点坐标(一维数组),顶点数
                return imagepolygon($image, $param, count($param) / 2, $color);
            case 'ellipse': //椭圆(中心点/宽度-高度)
                return imageellipse($image, $param[0], $param[1], $param[2], $param[3], $color);
            case 'rectangle': //矩形(左顶点/右底点)
                return imagerectangle($image, $param[0], $param[1], $param[2], $param[3], $color);
            default: //线(起点/终点)
                return imageline($image, $param[0], $param[1], $param[2], $param[3], $color);
        }
    }

    //填充颜色
    static function fill(&$image, $color, $param, $name = 'rectangle')
    {
        switch ($name) {
            case 'fill': //填充
                return imagefill($image, $param[0], $param[1], $color);
            case 'arc': //弧形填充
                return imagefilledarc($image, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $color, $param[6]);
            case 'polygon': //填充多边形
                return imagefilledpolygon($image, $param, count($param) / 2, $color);
            case 'border': //区域填充到指定颜色的边界为止
                return imagefilltoborder($image, $param[0], $param[1], $param[2], $color);
            case 'ellipse': //椭圆
                return imagefilledellipse($image, $param[0], $param[1], $param[2], $param[3], $color);
            default : //填充矩形
                return imagefilledrectangle($image, $param[0], $param[1], $param[2], $param[3], $color);
        }
    }

    //创建文本
    static function text(&$image, $text, $font, $color, $mode = 2, $p_x = 0, $p_y = 0)
    {
        switch ($mode) {
            case 1:
                return imagechar($image, $font, $p_x, $p_y, $text, $color);
            case 2:
                return imagestring($image, $font, $p_x, $p_y, $text, $color);
            case 3:
                return imagecharup($image, $font, $p_x, $p_y, $text, $color);
            case 4:
            default:
                return imagestringup($image, $font, $p_x, $p_y, $text, $color);
        }
    }

    //旋转
    static function rotate(&$image, $angle, $color, $alpha = 0)
    {
        return imagerotate($image, $angle, $color, $alpha);
    }

    //复制图像
    static function copy(&$image, $picture, $param, $mode = 5)
    {
        switch ($mode) {
            case 1: //拷贝
                return imagecopy($image, $picture, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5]);
            case 2: //拷贝+合并
                return imagecopymerge($image, $picture, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6]);
            case 3: //灰度拷贝+合并
                return imagecopymergegray($image, $picture, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6]);
            case 4: //拷贝+调整大小
                return imagecopyresized($image, $picture, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7]);
            case 5: //采样+拷贝+调整大小
            default:
                return imagecopyresampled($image, $picture, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7]);
        }
    }

    //设置样式、风格
    static function set(&$image, $value, $style = 'mix')
    {
        switch ($style) {
            case 'border': //画线粗细
                return imagesetthickness($image, (int)($value));
            case 'style': //画线风格
                return imagesetstyle($image, (array)($value));
            case 'brush': //画笔图像
                return imagesetbrush($image, $value);
            case 'pattern': //填充的贴图 图案
                return imagesettile($image, $value);
            case 'alias': //抗锯齿
                return imageantialias($image, (bool)($value));
            case 'alpha': //alpha混色标志
                return imagelayereffect($image, (int)($value));
            case 'transparent': //透明色
                return imagecolortransparent($image, (int)($value));
            case 'mix': //混色模式
            default :
                return imagealphablending($image, (bool)($value));
        }
    }

    //添加滤镜
    static function filter(&$image, $type = IMG_FILTER_GRAYSCALE, $arg1 = 0, $arg2 = 0, $arg3 = 0)
    {
        return imagefilter($image, $type, $arg1, $arg2, $arg3);
    }

    //保存图像
    static function save(&$image, $filename = 'test.png', $dir = "../picture/")
    {
        $format = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($format) {
            case 'gif':
                imagegif($image, $dir . $filename);
                break;
            case 'png':
                imagepng($image, $dir . $filename);
                break;
            case 'wbmp':
                imagewbmp($image, $dir . $filename);
                break;
            case 'xbm':
                imagexbm($image, $dir . $filename);
                break;
            default:
                imagejpeg($image, $dir . $filename);
                break;
        }
        imagedestroy($image);
        return $dir . $filename;
    }

    //输出图像
    static function out($image, $format = 'gif')
    {
        switch ($format) {
            case 'gif':
                return imagegif($image);
            case 'png':
                return imagepng($image);
            case 'wbmp':
                return imagewbmp($image);
            case 'xbm':
                return imagexbm($image);
            default:
                return imagejpeg($image);
        }
    }

    //销毁图像
    static function clear(&$image)
    {
        return imagedestroy($image);
    }

    //获取图像相关信息
    static function info($image, $option)
    {
        if ($option == 'width') {
            return imagesx($image);
        }
        if ($option == 'height') {
            return imagesy($image);
        }
        if ($option == 'text_width') {
            return imagefontheight($image);
        }
        if ($option == 'text_height') {
            return imagefontwidth($image);
        }
        return array(gd_info(), imagetypes());
    }
}