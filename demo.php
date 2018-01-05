<?php
/**
 * Created by
 * User: zhangsheng
 * Date: 2018/1/3
 * Time: 18:31
 */

require dirname(__FILE__) . '/TYT.class.php';
//微信的sessionId
$sessionId = 'iSoJPFo+1WyO6IxrivLJNF/sfgp92uZP/ll6ERDIiUi0s3j8N0Q7xwQBMhrZVH1LqnJe+Y4S7Urj1pVL1DPhi4sf1Jz4bgqqdjvj4+s5UePsVvnUjW2PcTvSHyHZahJD4yjy+rmtKgHCSRIpnTwTKQ==';
//你需要的分数
$score = 522;
$t = new TYT();
 try {
     echo   $t->set($score,$sessionId);
 } catch (Exception $e) {
     echo $e->getMessage();
 }