<?php
/**
 * This file is part of Simps
 *
 * @link     https://github.com/simps/mqtt
 * @contact  Lu Fei <lufei@simps.io>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 */

include __DIR__ . '/vendor/autoload.php';

use Simps\MQTT\Client;
use Simps\MQTT\Config\ClientConfig;
use function Swoole\Coroutine\run;

run(function () {
    $config = require_once __DIR__ . '/config.php';

    // MQTT 客户端ID 后缀，DeviceId，业务方自由指定，需要保证全局唯一，禁止 2 个客户端连接使用同一个 ID
    $deviceId = Client::genClientID();
    $qos = 0;
    $port = 1883;
    $keepalive = 90;
    $cleanSession = true;
    $clientId = $config['group_id'] . '@@@' . $deviceId;
    echo "ClientId: {$clientId}", PHP_EOL;

    // 设置鉴权参数，参考 MQTT 客户端鉴权代码计算 username 和 password
    $username = 'Signature|' . $config['access_key'] . '|' . $config['instance_id'];
    $sigStr = hash_hmac("sha1", $clientId, $config['secret_key'], true);
    $password = base64_encode($sigStr);
    echo "UserName: {$username} \r\nPassword: {$password}", PHP_EOL;

    // 初始化客户端配置
    $clientConfig = new ClientConfig();
    $clientConfig->setUserName($username)
        ->setPassword($password)
        ->setClientId($clientId)
        ->setKeepAlive($keepalive)
        ->setMaxAttempts(0)
        ->setSwooleConfig([
            'open_mqtt_protocol' => true,
            'package_max_length' => 2 * 1024 * 1024,
        ]);

    try {
        // 初始化客户端
        $client = new Client($config['end_point'], $port, $clientConfig);

        $connect = $client->connect($cleanSession);
        // 连接状态
        var_dump($connect);

        $topics[$config['topic']] = $qos;
        $subStatus = $client->subscribe($topics);
        // 订阅状态
        var_dump($subStatus);

        $publishStatus = $client->publish($config['topic'], "Hello MQTT PHP Demo", $qos);
        // 发布状态
        var_dump($publishStatus);

        $buffer = $client->recv();
        // 订阅消息接收
        var_dump($buffer);

        echo 'Finished';
    } catch (\Throwable $e) {
        echo $e->getMessage();
    }
});
