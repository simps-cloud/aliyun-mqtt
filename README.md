# aliyun-mqtt

使用 [simps/mqtt](https://github.com/simps/mqtt) 调用阿里云的[微消息队列 MQTT 版](https://www.aliyun.com/product/mq4iot)

在阿里云的文档中PHP推荐的SDK是 [Mosquitto-PHP](https://github.com/mgdm/Mosquitto-PHP) ，这是一个基于回调和异步操作的 PHP MQTT 扩展，同时还依赖 `libmosquitto`。

而 [simps/mqtt](https://github.com/simps/mqtt) 是纯 PHP 代码实现的协议解析，客户端实现基于 Swoole 的同步阻塞客户端和协程客户端，可以用于PHP-FPM和CLI两种模式。

并且 [simps/mqtt](https://github.com/simps/mqtt) 支持 MQTT 5.0 协议，是 PHP 首个支持 MQTT 5.0 协议的类库，后期如果阿里云的微消息队列 MQTT 版支持了 MQTT 5.0 协议，可以无缝升级。

以下为使用 simps/mqtt 来实现之前所提供的 Mosquitto-PHP 的示例代码

```bash
git clone https://github.com/simps-cloud/aliyun-mqtt.git
cd aliyun-mqtt
composer install
```

> 示例代码仅实现了测试逻辑，具体业务使用还需要进一步完善。

- 配置文件

[config.php](https://github.com/simps-cloud/aliyun-mqtt/blob/main/config.php)

```php
return [
    'access_key' => '', // 阿里云帐号 AccessKey
    'secret_key' => '', // 阿里云帐号 SecretKey
    'end_point' => '', // 接入点地址，购买实例后从控制台获取
    'instance_id' => '', // 实例 ID，购买后从控制台获取
    'topic' => '', // MQTT Topic,其中第一级 Topic 需要在 MQTT 控制台提前申请
    'group_id' => '', //  MQTT 客户端ID 前缀， GroupID，需要在 MQTT 控制台申请
];
```

- 单独使用MQTT消息收发示例

[sendMessageToMQTT.php](https://github.com/simps-cloud/aliyun-mqtt/blob/main/sendMessageToMQTT.php)

```php
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
```

- MQTT签名示例

[connectUseSignatureMode.php](https://github.com/simps-cloud/aliyun-mqtt/blob/main/connectUseSignatureMode.php)

- MQTT Token示例

[connectUseTokenMode.php](https://github.com/simps-cloud/aliyun-mqtt/blob/main/connectUseTokenMode.php)

- MQTT发送顺序消息RocketMQ订阅顺序消息示例

[sendOrderMessage.php](https://github.com/simps-cloud/aliyun-mqtt/blob/main/sendOrderMessage.php)

- P2P消息收发模式

[sendP2PMessageToMQTT.php](https://github.com/simps-cloud/aliyun-mqtt/blob/main/sendP2PMessageToMQTT.php)