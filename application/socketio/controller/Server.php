<?php
namespace app\socketio\controller;
use Workerman\Worker;
use PHPSocketIO\SocketIO;
use think\Db;
class Server{

	public function index(){
		$io = new SocketIO(2021);
		$io->on('connection', function($socket)use($io){
			$socket->on('chat message', function($msg)use($io){
				$io->emit('chat message', $msg);
			});

			echo 'new connection'."\n";

			$socket->emit('success', '连接成功');

			$socket->on('sendMsg', function($msg)use($io){
				// $a = iconv("GB2312","UTF-8", $msg);
				echo $msg."\n";
				$io->emit('sendMsg', '收到"'.$msg.'"');
				$data['msg'] = $msg;
				Db::table('msg')->insert($data);
			});
		});
		Worker::runAll();
	}

	public function ceshi(){
		$msg = Db::table('msg')->select();
		var_dump($msg);
	}

}