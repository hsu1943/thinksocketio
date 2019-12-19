<?php
/**
 * Author: 行星带
 * Url: https://beltxman.com
 */

namespace app\socketio\controller;

use app\socketio\model\Msg;
use think\Controller;
use think\Db;
use think\exception\DbException;
use think\facade\Config;
use Workerman\Worker;
use PHPSocketIO\SocketIO;

/** 服务端
 * Class Server
 * @package app\socketio\controller
 */
class Server extends Controller
{
    private $users;
    private $usersNum;
    private $saveMsg;

    protected function initialize()
    {
        // 读取配置文件是否需要将消息存储到数据库
        $this->saveMsg = Config::get('param.save_msg');
    }


    public function index()
    {
        $port = Config::get('param.ws.port');
        $io = new SocketIO($port);
        $io->on('connection', function ($socket) use ($io) {
            $socket->on('chat message', function ($msg) use ($io) {
                $io->emit('chat message', $msg);
            });

            $all = [
                'usersNum' => $this->usersNum,
                'currentUsers' => $this->users,
            ];

            $io->emit('sendMsg', json_encode($all, 256));

            echo 'new connection' . PHP_EOL;

            // 监听客户端连接成功发送数据
            $socket->on('success', function ($msg) use ($io, $socket) {
                $username = $msg['username'] ?: '';

                if (empty($username)) {
                    $username = str_rand(12);
                }
                // 将当前客户端加入以他的用户名定义的group
                $socket->join($username);
                $socket->username = $username;

                // 记录用户，同一个用户打开多个页面算一个，记录页面数量
                if (!isset($this->users[$username])) {
                    $this->users[$username] = 1;
                    ++$this->usersNum;
                    // 加入了聊天
                    $bc = [
                        'msg' => $username . ' join the chat',
                        'type' => 'join',
                        'usersNum' => $this->usersNum,
                        'currentUsers' => $this->users,
                    ];

                    $io->emit('sendMsg', json_encode($bc, 256));
                } else {
                    ++$this->users[$username];
                }

                $res = [
                    'username' => $username,
                    'msg' => 'success',
                    'usersNum' => $this->usersNum,
                    'currentUsers' => $this->users,
                ];
                $socket->emit('success', json_encode($res, 256));
                echo date('Y-m-d H:i:s') . " $username join" . PHP_EOL;
            });


            $socket->on('sendMsg', function ($msg) use ($io, $socket) {

                echo date('Y-m-d H:i:s') . " 收到广播消息：" . json_encode($msg, 256) . PHP_EOL;

                // 向所有客户端广播发送消息 *** SAY
                $bc = [
                    'msg' => $msg['msg'],
                    'from' => $msg['username'],
                    'type' => 'say',
                ];

                $io->emit('sendMsg', json_encode($bc, 256));

                // 向以用户名定义的组推送消息，达到一对一的推送的效果
                // $io->to($msg['username'])->emit('sendMsg', json_encode($response, JSON_UNESCAPED_UNICODE));

                // 向当前客户端推送消息
                // $socket->emit('sendMsg', json_encode($response));

                // 不需要存储公频消息注释这句
                $data = [
                    'msg' => $msg['msg'],
                    'from' => $msg['username'],
                    'to' => '',
                    'type' => 'public'
                ];

                if ($this->saveMsg) {
                    try {
                        $this->insertMsg($data);
                    } catch (\Exception $e) {
                        echo "消息入库失败: " . $e->getMessage() . PHP_EOL;
                    }
                }


            });

            // 一对一私聊
            $socket->on('private chat', function ($msg) use ($io, $socket) {

                echo date('Y-m-d H:i:s') . " 收到私聊消息：" . json_encode($msg, 256) . PHP_EOL;

                if (!empty($msg['to']) && !empty($msg['from'])) {

                    $io->to($msg['to'])->emit('private chat', json_encode($msg, 256));

                    if ($this->saveMsg) {
                        try {
                            $this->insertMsg($msg);
                        } catch (DbException $e) {
                            echo "消息入库失败: " . $e->getMessage() . PHP_EOL;
                        }
                    }

                }
            });

            // 关闭页面/离开聊天室
            $socket->on('disconnect', function () use ($socket) {
                if (!isset($this->users[$socket->username])) {
                    $this->users[$socket->username] = 0;
                }
                --$this->users[$socket->username];
                if ($this->users[$socket->username] <= 0) {
                    echo $socket->username . ' disconnect' . PHP_EOL;
                    unset($this->users[$socket->username]);
                    --$this->usersNum;
                    $socket->leave($socket->username);
                    $res = [
                        'username' => $socket->username,
                        'usersNum' => $this->usersNum,
                        'currentUsers' => $this->users,
                        'type' => 'left',
                    ];
                    $socket->broadcast->emit('sendMsg', json_encode($res, 256));
                    echo date('Y-m-d H:i:s') . " {$socket->username} leave" . PHP_EOL;
                }
            });

            $socket->on('changeName', function ($msg) use ($io, $socket) {

                $username = $msg['username'] ?: '';
                --$this->usersNum;
                $socket->leave($username);
                unset($this->users[$username]);
                echo $username . ' disconnect' . PHP_EOL;
                $res = [
                    'username' => $username,
                    'usersNum' => $this->usersNum,
                    'currentUsers' => $this->users,
                    'type' => 'left',
                ];
                $io->emit('sendMsg', json_encode($res, 256));
            });


        });

        // 当$io启动后监听一个http端口，通过这个端口可以给任意user或者所有user推送数据
        $io->on('workerStart', function () use ($io) {
            // 监听一个http端口
            $api_url = Config::get('param.ws.apiHost');
            $inner_http_worker = new Worker($api_url);
            // 当http客户端发来数据时触发
            $inner_http_worker->onMessage = function ($http_connection, $data) use ($io) {
                $params = $_POST ? $_POST : $_GET;
                // 推送数据的url格式 type=publish&to=user&content=xxxx
                switch (@$params['type']) {
                    case 'publish':
                        $to = @$params['to'];
                        // 有指定user则向user所在socket组发送数据
                        $msg = [
                            'msg' => $params['content'],
                            'from' => 'system',
                            'type' => 'system',
                        ];
                        echo date('Y-m-d H:i:s') . " api收到推送消息：" . json_encode($msg, 256) . PHP_EOL;
                        if ($to) {
                            $msg['to'] = $to ?: '';
                            if (isset($this->users[$to])) {
                                $io->to($to)->emit('sendMsg', json_encode($msg, 256));
                            } else {
                                return $http_connection->send($to . 'is offline');
                            }
                        } else {
                            $io->emit('sendMsg', json_encode($msg, 256));
                        }

                        if ($this->saveMsg) {
                            try {
                                $this->insertMsg($msg);
                                return $http_connection->send('ok');
                            } catch (DbException $e) {
                                echo "消息入库失败: " . $e->getMessage() . PHP_EOL;
                                return $http_connection->send('fail');
                            }
                        } else {
                            return $http_connection->send('ok');
                        }
                }
                return $http_connection->send('fail');
            };
            // 执行监听
            $inner_http_worker->listen();
        });

        Worker::runAll();
    }


    private function insertMsg($data)
    {
        Db::name('msg')->insert($data);
    }
}