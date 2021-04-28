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

return [
    'access_key' => '', // 阿里云帐号 AccessKey
    'secret_key' => '', // 阿里云帐号 SecretKey
    'end_point' => '', // 接入点地址，购买实例后从控制台获取
    'instance_id' => '', // 实例 ID，购买后从控制台获取
    'topic' => '', // MQTT Topic,其中第一级 Topic 需要在 MQTT 控制台提前申请
    'group_id' => '', //  MQTT 客户端ID 前缀， GroupID，需要在 MQTT 控制台申请
];