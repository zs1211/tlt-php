<?php
/**
 * Created by
 * User: zhangsheng
 * Date: 2018/1/3
 * Time: 18:31
 */

require dirname(__FILE__) . '/TYT.class.php';
//微信的sessionId
$sessionId = '5jIPCdwkp0PXitnRcdXmH8E71eWCLjiF86rCQrOTd7w9o56QfFqzlYZIL7WmGlTuV4IkxCi6Os3DyfTlVCZRUfJhdDZxII0bbTInDFRHTrKhmP5tjPhxX9yDygE/0Wnm6SfWJ2fUZMu3pkXHC0SnEw==';
//你需要的分数
$score = 10;
$t = new TYT();
 try {
     echo   $t->set($score,$sessionId);
 } catch (Exception $e) {
     echo $e->getMessage();
 }