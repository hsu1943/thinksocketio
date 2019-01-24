<?php

namespace app\socketio\controller;

use Workerman\Worker;
use PHPSocketIO\SocketIO;
use think\Db;

/** 服务端
 * Class Server
 * @package app\socketio\controller
 */
class Server
{

    public function index()
    {
        $io = new SocketIO(2021);
        $io->on('connection', function ($socket) use ($io) {
            $socket->on('chat message', function ($msg) use ($io) {
                $io->emit('chat message', $msg);
            });

            echo 'new connection' . PHP_EOL;

//            监听客户端连接成功发送数据
            $socket->on('success', function ($msg) use ($io, $socket) {

                $username = $msg['username'] ?: '';

                if (empty($username)) {
                    $username = str_rand(12);
                }
//                将当前客户端加入以他的用户名定义的group
                $socket->join($username);
                $socket->username = $username;
                $res = [
                    'username' => $username,
                    'msg' => 'success',
                ];
                $socket->emit('success', json_encode($res, JSON_UNESCAPED_UNICODE));

//                ***加入了聊天
                $bc = [
                    'msg' => $username . ' join the chat',
                    'type' => 'join',
                ];

                $io->emit('sendMsg', json_encode($bc, JSON_UNESCAPED_UNICODE));
            });


            $socket->on('sendMsg', function ($msg) use ($io, $socket) {

                var_dump($msg);

//                向所有客户端广播发送消息 *** SAY
                $bc = [
                    'msg' => $msg['msg'],
                    'from' => $msg['username'],
                    'type' => 'say',
                ];

                $io->emit('sendMsg', json_encode($bc, JSON_UNESCAPED_UNICODE));


                $response = [
                    'msg' => 'Your message : "' . $msg['msg'] . '" has been sent successfully!',
                    'type' => 'response',
                ];

//                向以用户名定义的组推送消息，达到一对一的推送的效果
                $io->to($msg['username'])->emit('sendMsg', json_encode($response, JSON_UNESCAPED_UNICODE));

//                向当前客户端推送消息
//                $socket->emit('sendMsg', json_encode($response));

//                不需要存储公频消息注释这句
                Db::table('msg')->insert($msg);
            });

//            一对一私聊
            $socket->on('private chat', function ($msg) use ($io, $socket) {

                var_dump($msg);

                if (!empty($msg['to']) && !empty($msg['from'])) {

                    $io->to($msg['to'])->emit('private chat', json_encode($msg, JSON_UNESCAPED_UNICODE));

//                    不需要存储私聊消息注释这句
                    Db::table('private_chat')->insert($msg);
                }
            });

//            ***离开聊天室
            $socket->on('disconnect', function () use ($socket) {
                echo $socket->username . ' disconnect' . PHP_EOL;
                $socket->leave($socket->username);
                $res = [
                    'username' => $socket->username,
                    'type' => 'left',
                ];
                $socket->broadcast->emit('sendMsg', json_encode($res, JSON_UNESCAPED_UNICODE));
            });
        });
        Worker::runAll();
    }

    /**
     * 测试数据库连接
     */
    public function testDb()
    {
        $msg = Db::table('msg')->select();
        var_dump($msg);
    }

}