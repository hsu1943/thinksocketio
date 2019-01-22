<?php
/*
 3  * 生成随机字符串
 4  * @param int $length 生成随机字符串的长度
 5  * @param string $char 组成随机字符串的字符串
 6  * @return string $string 生成的随机字符串
 7  */
function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    if (!is_int($length) || $length < 0) {
        return false;
    }

    $string = '';
    for ($i = $length; $i > 0; $i--) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }

    return $string;
}