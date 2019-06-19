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
模板文件`index.html`和`chat.html`中`IP地址`修改为你的`socket`服务端`IP地址`，使用`局域网IP`可以测试局域网聊天测试。

```bash
var socket = io('http://127.0.0.1:2021');
```

如果需要使用数据库存储聊天记录消息，导入下面数据表，配置正确的数据库连接。
不用的话将数据库存储语句注释即可。

## 数据表

导入根目录下`socketio.sql`，配置数据库；

## 主动推送系统消息接口

修改配置文件`/config/socketio/app.php`中的配置为监听消息推送地址：

```bash
return [
    'ws' => [
        'apiHost' => 'http://127.0.0.1:2121',
    ],
];
```

其他项目POST，GET请求接口即可推送消息

```html
向username推送系统消息
http://test.com/system?to=username&content=系统推送消息测试
广播消息
http://test.com/system?content=系统推送消息测试
```

其中`http://test.com`为本项目可访问地址。

本项目中推送系统消息：

```php
$res = Msg::send($to, $content);
return $res == 'ok' ? '系统消息推送成功' : '系统消息推送失败';
```

## 测试

运行服务端：

```bash
php ./public/server.php
```

访问以下地址即可进入公频：

```bash
http://test.com/socketio
```

点击消息列表中的用户名即可进入私聊。

## 更新

* 2019-06-19 增加在线人数统计，在线用户列表，修改昵称，添加系统主动推送接口（广播或私信）；



## 持续更新(对应最新代码和数据库)

[ThinkPHP 5.1+PHPSocket.IO实现websocket搭建聊天室+私聊](https://beltxman.com/archives/2329.html "ThinkPHP 5.1+PHPSocket.IO实现websocket搭建聊天室+私聊")

## 基础测试

[ThinkPHP 5.1下使用PHPSocket.IO实现websocket通讯](https://beltxman.com/archives/1885.html "ThinkPHP 5.1下使用PHPSocket.IO实现websocket通讯")