<?php

if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

require 'core/app.php';
date_default_timezone_set("PRC");
//运行框架
app::run();