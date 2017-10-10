<?php
//生产环境配置

return [
    // +----------------------------------------------------------------------
    // | 自定义设置
    // +----------------------------------------------------------------------


    // +----------------------------------------------------------------------
    // | 系统设置
    // +----------------------------------------------------------------------

    //web页面的异常交给系统处理
    'exception_handle'       => '\\think\\exception\\Handle',

    //页面使用自定义的错误页面
    'exception_tmpl'         => APP_PATH . 'index' . DS . 'view'.DS.'web'.DS.'errorPage.html',
//    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    'show_error_msg'         => true,
];