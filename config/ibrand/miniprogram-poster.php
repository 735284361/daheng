<?php

/*
 * This file is part of ibrand/laravel-miniprogram-poster.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
	'default'    => [
		'storage' => env('DEFAULT_POSTER_STORAGE', 'oss'),
		'app'     => env('APP_NAME', 'default'),
	],
	//图片存储位置
	'disks'      => [
		'qiniu'            => [
			'driver'     => 'qiniu',
			//七牛云access_key
			'access_key' => env('QINIU_ACCESS_KEY', ''),
			//七牛云secret_key
			'secret_key' => env('QINIU_SECRET_KEY', ''),
			//七牛云文件上传空间
			'bucket'     => env('QINIU_BUCKET', ''),
			//七牛云cdn域名
			'domain'     => env('QINIU_DOMAIN', ''),
			//与cdn域名保持一致
			'url'        => env('QINIU_DOMAIN', ''),
			'root'       => storage_path('app/public/qiniu'),
		],

        'oss' => [
            'driver'        => 'oss',
            'access_id'     => env('OSS_ACCESS_KEY_ID'),
            'access_key'    => env('OSS_ACCESS_KEY_SECRET'),
            'bucket'        => env('OSS_BUCKET'),
            'endpoint'      => env('OSS_ENDPOINT',''), // OSS 外网节点或自定义外部域名
            //'endpoint_internal' => '<internal endpoint [OSS内网节点] 如：oss-cn-shenzhen-internal.aliyuncs.com>', // v2.0.4 新增配置属性，如果为空，则默认使用 endpoint 配置(由于内网上传有点小问题未解决，请大家暂时不要使用内网节点上传，正在与阿里技术沟通中)
            'cdnDomain'     => '', // 如果isCName为true, getUrl会判断cdnDomain是否设定来决定返回的url，如果cdnDomain未设置，则使用endpoint来生成url，否则使用cdn
            'ssl'           => true, // true to use 'https://' and false to use 'http://'. default is false,
            'isCName'       => false, // 是否使用自定义域名,true: 则Storage.url()会使用自定义的cdn或域名生成文件url， false: 则使用外部节点生成url
            'debug'         => true
        ],
		'MiniProgramShare' => [
			'driver'     => 'local',
			'root'       => storage_path('app/public/share'),
			'url'        => env('APP_URL') . '/storage/share',
			'visibility' => 'public',
		],
	],
	//图片宽度
	'width'      => '575px',
	//放大倍数
	'zoomfactor' => 1.5,
	//1-9,9质量最高
	'quality'    => 9,
	//是否压缩图片
	'compress'   => true,
	//是否删除废弃图片文件
	'delete'     => true,
];
