<?php
/**
 * 第三方授权配置项
 * @author srako
 * @date 2023/12/11 08:30
 * @page http://srako.github.io
 */

 return [
     'enable' => true,
     'driver' => [
         'qq' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'wechat' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
             // 开放平台 - 第三方平台所需
             'component' => [
                 // or 'app_id', 'component_app_id' as key
                 'id' => 'component-app-id',
                 // or 'app_token', 'access_token', 'component_access_token' as key
                 'token' => 'component-access-token',
             ]
         ],
         'weibo' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'taobao' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'alipay' => [
             // 这个键名还能像官方文档那样叫做 'app_id'
             'client_id' => 'your-app-id',

             // 请根据官方文档，在官方管理后台配置 RSA2
             // 注意： 这是你自己的私钥
             // 注意： 不允许私钥内容有其他字符
             // 建议： 为了保证安全，你可以将文本信息从磁盘文件中读取，而不是在这里明文
             'rsa_private_key' => '',

             // 确保这里的值与你在服务后台绑定的地址值一致
             // 这个键名还能像官方文档那样叫做 'redirect_url'
             'redirect' => 'http://your-callback-url',

             // 沙箱模式接入地址见 https://opendocs.alipay.com/open/220/105337#%E5%85%B3%E4%BA%8E%E6%B2%99%E7%AE%B1
             'sandbox' => false,
         ],
         'coding' => [
             'team_url' => 'https://{your-team}.coding.net',
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'ding-talk' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'baidu' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'azure' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'douban' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'dou-yin' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'facebook' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'fei-shu' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
             // 如果你想使用使用内部应用的方式获取 app_access_token
             // 对这个键设置了 'internal' 值那么你已经开启了内部应用模式
             'app_mode' => 'internal'
         ],
         'figma' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'gitee' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'git-hub' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'google' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'lark' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
             // 如果你想使用使用内部应用的方式获取 app_access_token
             // 对这个键设置了 'internal' 值那么你已经开启了内部应用模式
             'app_mode' => 'internal'
         ],
         'line' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'linkedin' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'open-wework' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'outlook' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'q-cloud' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'tapd' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
         'wework' => [
             'client_id' => 'your-app-id',
             'client_secret' => 'your-app-secret',
             'redirect' => 'http://your-callback-url',
         ],
     ]
 ];
