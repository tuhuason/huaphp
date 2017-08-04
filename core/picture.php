<?php
use core\http;
class picture
{

    //返回验证码图片
    static function code($len = "4", $fontSize = 25)
    {

        header("Content-Type:image/gif;charset=utf-8");
        $height = $fontSize * 2.2;
        $width = $len * $fontSize * 1.5 + $len * $fontSize/2;
        $color = array('e8ecfd', 'f1f7e0', 'fdf2f0');
        $img = palette::create($width, $height, $color[rand(0, 2)]);

        //设置调色板
        $red = palette::color($img, 'ff836e');
        $green = palette::color($img, 'b9e62a');
        $blue = palette::color($img, '6c85fd');
        $color = array($red, $green, $blue);

        //绘制模糊像素点
        for ($i = 0 ; $i < 10 ; $i++)
        {
            imagesetpixel($img, rand(0, $width), rand(0, $height), $color[rand(0, 2)]);
        }

        //绘制线条、矩形框
        palette::draw($img, $color[rand(0, 2)], array(
            rand(0, $width), rand(0, $height), rand($width / 2, $width), rand($height / 2, $height)
        ), 'line');
        palette::draw($img, $color[rand(0, 2)], array(
            rand(0, $width - 15), rand(0, $height - 15), rand(15, $width), rand(15, $height)
        ), 'rectangle');

        //绘制弧线
        $p1 = array(
            rand(-$width, $width), rand(-$height, $height), rand(30, $width * 2), rand(20, $height * 2), rand(0, 360),
            rand(0, 360)
        );
        $p2 = array(
            rand(-$width, $width), rand(-$height, $height), rand(30, $width * 2), rand(20, $height * 2), rand(0, 360),
            rand(0, 360)
        );

        palette::draw($img, $color[rand(0, 2)], $p1, 'arc');
        palette::draw($img, $color[rand(0, 2)], $p2, 'arc');

        $code = array();
        $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
        $codeNX = 0; // 验证码第N个字符的左边距
        
        //绘制文本
        for ($i = 0 ; $i < $len ; $i++)
        {
            $code[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
            $codeNX  += mt_rand($fontSize*1.2, $fontSize*1.6);
            imagettftext($img, $fontSize, rand(-40, 40), $codeNX, $fontSize*1.6, $color[rand(0, 2)], __DIR__ . '/fonts.ttf', $code[$i]);
        }
        
        $secode     =   array();
        $secode['verify_code'] = strtolower(implode('', $code));
        $secode['verify_time'] = time();  // 验证码创建时间

        http::setSession('code', $secode); // 把校验码保存到session

        //输出图片、清掉画布
        imagegif($img);
        imagedestroy($img);
    }

    //添加水印
    static function mark($picture, $mark = array('http://127.0.0.1', 'huaphp'), $file_name = array(
        'test.png', 'file/create/'
    ))
    {
        $image = palette::open($picture);
        $img_width = palette::info($image, 'width');
        $img_height = palette::info($image, 'height');
        $text_color = palette::color($image, 'fefefe');
        $alpha = imagecolorallocatealpha($image, 15, 15, 15, 85);
        $border_color = palette::color($image, 'efefef');
        palette::draw($image, $border_color, array(0, 0, $img_width - 1, $img_height - 1), 'rectangle');
        palette::fill($image, $alpha, array(1, $img_height - 35, $img_width - 2, $img_height - 2), 'rectangle');
        imagettftext($image, 9, 0, $img_width - 145, $img_height - 14, $text_color, __DIR__ . '/font.ttf', $mark[0]);
        return palette::save($image, $file_name[0], $file_name[1]);
    }

    //按照指定尺寸缩放图片
    static function zoom($picture, $file_name = array('zoom_test.png', '../picture/zoom/'), $length, $is_height = false)
    {
        $image = palette::open($picture);
        $start_width = palette::info($image, 'width');
        $start_height = palette::info($image, 'height');

        $end_width = $length;
        $end_height = round(( $start_height * $end_width ) / $start_width);
        if ( $is_height )
        {
            $end_width = round(( $start_width * $length ) / $start_height);
            $end_height = $length;
        }

        $canvas = palette::create($end_width, $end_height, 'ffffff', true);
        $param = array(0, 0, 0, 0, $end_width, $end_height, $start_width, $start_height);
        palette::copy($canvas, $image, $param, 4);
        palette::save($canvas, $file_name[0], $file_name[1]);

        return $file_name[1] . $file_name[0];
    }

    //生成指定尺寸的用户头像
    static function avatar($image, $true_size, $size_data = array(100, 50, 32), $save_name, $save_dir = '')
    {
        foreach ($size_data as $target_size)
        {
            $target_img = palette::create($target_size, $target_size, 'ffffff', true);
            $param = array(0, 0, 0, 0, $target_size, $target_size, $true_size, $true_size);
            $copy_result = palette::copy($target_img, $image, $param, 5);
            if ( $copy_result )
            {
                palette::save($target_img, $save_name . '.gif', $save_dir . $target_size . '/');
            }
        }
    }

}