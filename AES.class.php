<?php
/**
 * Created by
 * User: zhangsheng
 * Date: 2018/1/2
 * Time: 15:40
 */


class AES {
    public static function encrypt ($text, $key, $iv){
        $text =self::addPKCS7Padding($text);
        $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_CBC, $iv);
        return base64_encode($encrypt_str);
    }


    public static function decrypt ($text,$key,$iv) {
        $str = base64_decode($text);
        $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_CBC, $iv);
        $encrypt_str = self::stripPKSC7Padding($encrypt_str);
        return $encrypt_str;
    }


    public static function addPKCS7Padding($source) {
        $source = trim($source);
        $block = mcrypt_get_block_size('rijndael-128', 'cbc');
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }


    public  static function stripPKSC7Padding($source) {
        $char = substr($source, -1);
        $num = ord($char);
        $source = substr($source, 0, -$num);
        return $source;
    }

}