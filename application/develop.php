<?php
//开发环境配置

return [
    // +----------------------------------------------------------------------
    // | 自定义设置
    // +----------------------------------------------------------------------
    //图片资源静态路径
    'img_url'                =>  'http://www.tp5.local/uploads/',//'http://39.108.3.179/tp5/public/uploads/',//
    'img_path'               =>  '/uploads/',
    'video_url'              =>  'http://www.tp5.local/uploads/video/',//'http://39.108.3.179/tp5/public/uploads/video/',//
    //是否缓存文章内容
    'cache_art_content'      => false,
    //是否缓存地址数据
    'cache_address_data'     => false,

    //jpush配置
    'JPUSH'                  => [
        'appkey'             => 'e7d49bed968c93d917f4afe6',
        'masterSecret'       => '192143a15799484cbeacbabd'
    ],

    //静态文件路径
    'static_path'           => '/static',//'/tp5/public/static',//

    'request_base_url'      => 'http://www.tp5.local/',

    'sms'                   => [
        'code'                  => '1c1fa4bd55274d8b98c62a517b0fbada'
    ],

    'domain_correct'        => '/',

    //零钱支付
    'pay_secret'            => '9b7cec09ceadf91dc1601501770baccb',

    'pay_time'              => '310',

    //对称加密的Key
    'aes_key'               => 'JYZ@(##&~&##)@CY',

    //验票码过期时间
    'verify_expire_time'    => 86400*30*3,

    //百度地图的key 我的
    'baidu_map_key'         => 'sNgblvoj7soi2Z4zgcUGfkapCXxh2zVD',

    // +----------------------------------------------------------------------
    // | 系统设置
    // +----------------------------------------------------------------------

    'app_debug'              => true,

    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    'show_error_msg'         => true,
];