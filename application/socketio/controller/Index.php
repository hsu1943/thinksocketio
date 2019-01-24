<?php

namespace app\socketio\controller;

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
}
