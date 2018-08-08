<?php

/**
 * 别黑我，不耐操。
 * User: qyc <yichao.qin@beibei.com>
 * Date: 2018/8/8 下午10:19
 * @copyright Beidian Limited. All rights reserved.
 */
define('BASE_PATH', dirname(__FILE__));

$server = new swoole_http_server("127.0.0.1", 9502);
$server->set(
    [
        'worker_num'    => 1,
        'dispatch_mode' => 1, //轮询
    ]
);

$server->on('WorkerStart', function () {
    require_once BASE_PATH . '/JetBrainsServer.php';
});

$server->on('Request', function (swoole_http_request $request, swoole_http_response $response) {
    $path_info = $request->server['path_info'];
    $method = '';
    if (stripos($path_info, '/rpc/ping.action') !== FALSE) {
        $method = 'ping';
    } elseif (stripos($path_info, '/rpc/obtainTicket.action') !== FALSE) {
        $method = 'obtainTicket';
    } elseif (stripos($path_info, '/rpc/prolongTicket.action') !== FALSE) {
        $method = 'prolongTicket';
    } elseif (stripos($path_info, '/rpc/releaseTicket.action') !== FALSE) {
        $method = 'releaseTicket';
    } else {
        $response->end('beibeiwang la pi tiao number one');
    }
    $jetBrainsServer = new JetBrainsServer();
    if (method_exists($jetBrainsServer, $method)) {
        $salt = $request->get['salt'] ? $request->get['salt'] : $request->post['salt'];
        $response->end($jetBrainsServer->$method($salt));
    }
});

$server->start();