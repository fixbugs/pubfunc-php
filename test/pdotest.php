<?php
try{
$pdo = new PDO("mysql:host=127.0.0.1;dbname=tips","root","123456");
}catch (Exception $e){
    var_dump($e);
}
if($pdo -> exec("insert into news(title,slug,text) values('title','test','content')")){
    echo "插入成功！";
    echo $pdo -> lastinsertid();
}
