<?php 
/**
 * 连接数据库并返回数据库连接句柄
 */

$pdo = new PDO('mysql:host=localhost;dbname=api','root','123456');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);//查出的数据类型与数据库保持一致
return $pdo;
 ?>