<?php

namespace app\socketio\model;

use think\Model;
use think\facade\Config;

class Msg extends Model
{
    public static function send($to = '', $content)
    {
        // 推送的url地址，使用自己的服务器地址
        $push_api_url = Config::get('app.ws.apiHost');

        $post_data = array(
            "type" => "publish",
            "content" => $content ?: '推送消息为空',
            "to" => $to,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }
}