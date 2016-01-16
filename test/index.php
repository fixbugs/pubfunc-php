<?php

$start_time = microtime(true);

$now_path = dirname(__FILE__);
$test_include_path = PATH_SEPARATOR.$now_path.'/../'.'functions';
//设置公共pub库
set_include_path(get_include_path().$test_include_path);
include("pubfunc.php");
include("pub.serial.php");

$id = makeSerialId(300);
var_dump(extractVirtShardId($id));
var_dump(isGlobalSerialId($id));
var_dump(isValidSerialId($id));
var_dump($id);
echo "1";


$end_time = microtime(true);
$total_used_time = round($end_time - $start_time, 10);
pr("\n总计耗时:{$total_used_time}s");
