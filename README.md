# 使用说明

## 克隆
```bash
git clone git@github.com:hsu1943/thinksocketio.git
```

## 安装
```bash
cd thinksocketio
composer install
```

## 设定服务端IP及数据库存储
模板文件`index.php`和`chat.php`中`IP地址`修改未你的`socket`服务端`IP地址`，使用`局域网IP`可以测试局域网聊天测试。

```bash
var socket = io('http://127.0.0.1:2021');
```

如果需要使用数据库存储测试消息，就根据下面文章中添加数据库及表，配置正确的数据库连接。
不用的话将数据库存储语句注释即可。

## 测试
[ThinkPHP 5.1下使用PHPSocket.IO实现websocket通讯](https://beltxman.com/archives/1885.html "ThinkPHP 5.1下使用PHPSocket.IO实现websocket通讯")

## 持续更新(对应最新代码和数据库)
[ThinkPHP 5.1+PHPSocket.IO实现websocket搭建聊天室+私聊](https://beltxman.com/archives/2329.html "ThinkPHP 5.1+PHPSocket.IO实现websocket搭建聊天室+私聊")