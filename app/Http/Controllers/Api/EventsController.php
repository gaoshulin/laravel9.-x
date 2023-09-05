<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/5/15 11:42
 */
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\Log;

/**
 * Gateway Worker Events
 *
 * Class EventsController
 * @package App\Http\Controllers\Api
 */
class EventsController extends Controller
{
    public static function onConnect($client_id)
    {
        var_dump($client_id);
        Gateway::sendToClient($client_id, json_encode(['type' => 'init', 'client_id' => $client_id]));
    }

    public static function onMessage($client_id, $message)
    {
        $response = ['code' => 0, 'msg' => 'ok', 'data' => []];
        $message = json_decode($message);

        // dump($message);
        switch ($message->tag) {
            case 'name':
                $arr['tag'] = 'name';
                $arr['content'] = $message->content;
                $response['data'] =  json_encode($arr);
                break;
            case "msg":
                $arr['tag'] = 'content';
                $arr['content'] = $message->content;;
                $arr['name'] = $message->name;;
                $response['data'] =  json_encode($arr);
                break;
            default:
                # code...
                break;
        }

        Gateway::sendToClient($client_id,json_encode($response));
        // Gateway::sendToAll(json_encode($response));
        // Gateway::isOnline();//判断客户端是否在线
    }

    public static function onClose($client_id)
    {
        Log::info('close connection' . $client_id);
    }
}
