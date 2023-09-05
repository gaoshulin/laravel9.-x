<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Workerman\Worker;

class GatewayWorkerServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // php artisan gateway-worker:server start
    protected $signature = 'gateway-worker:server {action} {--daemon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start a GatewayWorker Server.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        global $argv;

        $commands = ['status', 'start', 'stop', 'restart', 'reload'];
        $action = $this->argument('action');
        if (!in_array($action, $commands)) {
            $this->error('Error arguments');
            exit;
        }

        $argv[0] = 'gateway-worker:server';
        $argv[1] = $action;
        $argv[2] = $this->option('daemon') ? '-d' : '';

        $this->start();

        Worker::runAll();
    }

    private function start()
    {
        $this->startRegister();
        $this->startGateWay();
        $this->startBusinessWorker();
    }

    private function startRegister()
    {
        new Register('text://0.0.0.0:1565');
    }

    private function startBusinessWorker()
    {
        $worker                  = new BusinessWorker();
        $worker->name            = 'BusinessWorker';    // 设置BusinessWorker进程的名称
        $worker->count           = 1;                   // 设置BusinessWorker进程的数量
        $worker->registerAddress = '127.0.0.1:1565';   // 注册服务地址

        // 设置使用哪个类来处理业务,业务类至少要实现onMessage静态方法，onConnect和onClose静态方法可以不用实现
        $worker->eventHandler    = \App\Http\Controllers\Api\EventsController::class;
    }

    private function startGateWay()
    {
        // 证书最好是申请的证书 - 启SSL 需要
        $context = [
//            'ssl' =>[
//                'local_cert' => '/ssl/214893176080649.pem',
//                'local_pk'   => '/ssl/214893176080649.key',
//                'verify_peer' => false,
//            ]
        ];
        $gateway = new Gateway("websocket://0.0.0.0:7275", $context);

        // $gateway->transport            = 'ssl';        // 开启SSL，websocket+SSL 即wss
        $gateway->name                 = 'Gateway';    // 设置Gateway进程的名称
        $gateway->count                = 1;            // 设置进程的数量
        $gateway->lanIp                = '127.0.0.1';  // 内网ip,多服务器分布式部署的时候需要填写真实的内网ip
        $gateway->startPort            = 2300;         // 监听本机端口的起始端口
        $gateway->pingNotResponseLimit = 1;            // 服务端主动发送心跳
        $gateway->pingInterval         = 30;           // 心跳间隔时间-秒
        $gateway->pingData             = '{"mode":"heart"}';
        $gateway->registerAddress      = '127.0.0.1:1565';
    }
}
