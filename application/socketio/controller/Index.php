<?php

namespace app\socketio\controller;

use app\socketio\model\Msg;
use think\Controller;
use think\facade\Request;

/**
 * 前端界面显示
 * Class Index
 * @package app\socketio\controller
 */
class Index extends Controller
{
    public function index()
    {
        return view();
    }

    public function chat()
    {
        $username = Request::get('username');
        return view('chat', ['to' => $username]);
    }

    public function system()
    {
        $data = Request::param();
        $to = $data['to'] ?? '';
        $content = $data['content'] ?? '';
        $res = Msg::send($to, $content);
        return $res == 'ok' ? '系统消息推送成功' : '系统消息推送失败';
    }
}
