<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/22 15:19
 */

use Workerman\Lib\Timer;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;

require_once __DIR__ . '/vendor/autoload.php';

// 需要 workerman\Protocols 的命令空间, 使用别名加载自定的协议类
class_alias('App\WorkerMan\Protocols\MyTextProtocol', 'Protocols\MyTextProtocol');

// #### MyTextProtocol worker ####
$worker = new Worker("MyTextProtocol://0.0.0.0:7272");

/*
 * 收到一个完整的数据（结尾是换行）后，自动执行MyTextProtocol::decode('收到的数据')
 * 结果通过$data传递给onMessage回调
 */
$worker->onMessage =  function(TcpConnection $connection) use ($worker)
{
    /*
     * 给客户端发送数据，会自动调用MyTextProtocol::encode('hello world')进行协议编码，
     * 然后再发送到客户端
     */
    $connection->send("hello world");

    // 定时，每5秒一次
    Timer::add(5, function () use ($worker) {
        // 遍历当前进程所有的客户端连接，发送当前服务器的时间
        foreach ($worker->connections as $connection) {
            $connection->send(time());
        }
    });
};

// run all workers
Worker::runAll();
