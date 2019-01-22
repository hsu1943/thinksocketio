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
                $res = [
                    'username' => $username,
                    'msg' => 'success',
                ];
                $socket->emit('success', json_encode($res));

//                ***加入了聊天
                $bc = [
                    'msg' => $username . ' join the chat',
                    'type' => 'join',
                ];

                $io->emit('sendMsg', json_encode($bc));
            });


            $socket->on('sendMsg', function ($msg) use ($io, $socket) {

                var_dump($msg);

//                向所有客户端广播发送消息 *** SAY
                $bc = [
                    'msg' => $msg['username'] . ' SAY : "' . $msg['msg'] . '"',
                    'type' => 'say',
                ];

                $io->emit('sendMsg', json_encode($bc));


                $response = [
                    'msg' => 'Your message : "' . $msg['msg'] . '" has been sent successfully!',
                ];

//                向以用户名定义的组推送消息，达到一对一的推送的效果
                $io->to($msg['username'])->emit('sendMsg', json_encode($response));

//                向当前客户端推送消息
//                $socket->emit('sendMsg', json_encode($response));
                Db::table('msg')->insert($msg);
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