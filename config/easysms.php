<?php

/*
 * This file is part of the leonis/easysms-notification-channel.
 * (c) yangliulnn <yangliulnn@163.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'submail',
            'errorlog',
        ],
    ],

    // 可用的网关配置
    'gateways' => [
        // 失败日志
        'errorlog' => [
            'channel' => 'stack',
        ],

        // 云片
//        'yunpian' => [
//            'api_key' => 'e3ba9c48af0bb0c06ab186b73c2fb608',
//        ],

        'submail' => [
            'app_id' => '46638',
            'app_key' => '4c2c37c5c91ef4f6e667afeaedb68d3e',
            'project' => '', // 默认 project，可在发送时 data 中指定
        ],

        // ...
    ],

    'custom_gateways' => [
        'errorlog' => \Leonis\Notifications\EasySms\Gateways\ErrorLogGateway::class,
        'winic' => \Leonis\Notifications\EasySms\Gateways\WinicGateway::class,
    ],
];
