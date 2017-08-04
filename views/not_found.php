<!DOCTYPE html>
<html>
<head>
	<title>not found</title>
    <link rel="stylesheet" href="<?php echo conf('__PLUGINS__');?>/layui/css/layui.css">
	<style type="text/css">
		body{background-color: #f2f2f2}
        .pager{margin:auto;overflow:hidden;text-align: center;}
        .error{text-align: center;position: relative;margin: auto;max-width:600px;font-size:16px;color:#ff5722;}
    </style>
</head>
<body>
    <div class="pager">
        <img src="<?php echo conf('__PLUGINS__');?>/img/404.jpg">
    </div>
    <div class="error">
    	<blockquote class="layui-elem-quote layui-quote-nm"><?php echo isset($message)?$message:$messages;?></blockquote>
    	<a href="/" class="layui-btn layui-btn-normal">返回首页</a>
    </div>
</body>
</html>