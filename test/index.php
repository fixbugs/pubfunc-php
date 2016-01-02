<?php

$now_path = dirname(__FILE__);
$test_include_path = PATH_SEPARATOR.$now_path.'/../'.'functions';
set_include_path(get_include_path().$test_include_path);
include("pubfunc.php");

pr(get_include_path());

