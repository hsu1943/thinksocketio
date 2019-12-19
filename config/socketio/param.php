<?php
// 配置文件
return [
    'ws' => [
        'port' => 2021, // 服务端端口号
        'apiHost' => 'http://127.0.0.1:2121', // http api监听地址
    ],

    'save_msg' => true, // 是否将消息存储到mysql，请保证mysql正常连接，并导入项目根目录socketio.sql
];