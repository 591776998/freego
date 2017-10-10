<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:74:"D:\UPUPW_AP5.6_64\vhosts\tp5\public/../application/index\view\web\art.html";i:1506695548;s:83:"D:\UPUPW_AP5.6_64\vhosts\tp5\public/../application/index\view\web\h5_view_head.html";i:1506693822;}*/ ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link charset="utf-8" rel="stylesheet" href="__PUBLIC__/css/mui.min.css">
    <link charset="utf-8" rel="stylesheet" href="__PUBLIC__/css/swiper.min.css">
    <link charset="utf-8" rel="stylesheet" href="__PUBLIC__/css/index2.css?v=1">
    <link charset="utf-8" rel="stylesheet" href="__PUBLIC__/css/art.css?v=2">
    <style>
        .mainColor{
            color: #f4a51d !important;
        }
        .noline.mui-table-view-cell:after{
            display: none !important;
        }
        li.pad0{
            padding-left: 0 !important;
        }
        .marT0{
            margin-top: 0 !important;
        }
        .backWihte{
            background-color: #fff;
        }
        .imgbox, .imgbox img {
            height: 220px;
        }
        a,a:hover,a:active{
            text-decoration: none !important;
            text-decoration-color: #fff;
        }

        .mui-bar .mui-title {
            right: 80px !important;
        }
        .vjs-big-play-button{
            display: none !important;
        }
        .mui-table-view-cell{
            /*padding-bottom: 0 !important;*/
        }
        .mui-bar-nav~.mui-content {
            padding-top: 64px;
        }
        .tag {
            margin-left: 10px;
            color: white;
            height: 20px;
            padding-left: 6px;
            padding-right: 6px;
            background-color: #ccc;
            border-radius: 4px;
        }
        .black40{
            height: 40px;
        }
        .footer .item a{
            display: block;
        }

        /*滑动插件*/
        .swiper-container {
            width: 100%;
            height: 100%;
        }
        .swiper-slide {
            overflow: hidden;
        }
        .album_wrap{
            height: 100%;
            width: 100%;
            background-color: black;
            z-index: 112;
            position: absolute;
        }
        .ablum_content .close_btn{
            position: absolute;
            left: 0;
            top: 0;
            width: 50px;
            height:50px;
            z-index: 2;
        }
        <?php if($is_app == '102'): ?>
        <?php echo $web_css; endif; ?>
        .mui-title{
            width: 95%;
            text-align: left;
            margin:0 15px;
            overflow: hidden;
        }
        .head{
            position: fixed;
            top: 0px;
            left: 0;
            width: 100%;
            height: 65px;
            background: none;
            z-index: 100000;
        }
        .header{
            position: absolute;
            top: 5px;
            left: 0;
            width: 100%;
            height: 64px;
            background: none;
            font-size: 1.3em !important;
            padding: 15px 6px;
        }
        .headimg{
            width:100%;
            height: 64px;
        }
        .mui-btn, button, input[type=button], input[type=reset], input[type=submit] {
            font-size: 16px;
            padding: 6px 8px;
        }
        .mui-table-view-cell:after{
            background-color:none;
        }
        #back{
            padding-top: 6px;
        }
        .sub_window_frame{
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 2;
            position: absolute;
        }
        .sub_window_frame .window_wrap{
            position: absolute;
            top: 50%;
            margin-top: -200px;
            left: 50%;
            margin-left: -150px;
            height: 400px;
            width: 300px;
            background-color: white;
            z-index: 2;
            overflow-y: hidden;
            border-radius: 5px;
            border: none;
        }
        .sub_window_frame .window_bg{
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background-color: black;
            opacity:0.2;
        }
    </style>
</head>

<body>

<div class="sub_window_frame" style="display: none;">
    <iframe class="window_wrap"></iframe>
    <div class="window_bg"></div>
</div>

<div class="album_wrap" style="display: none;">
    <div class="swiper-container ablum_content">
        <img class="close_btn" src="__PUBLIC__/img/close.png" />
        <div class="swiper-wrapper" >
            <?php if(is_array($content) || $content instanceof \think\Collection || $content instanceof \think\Paginator): $i = 0; $__LIST__ = $content;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;if($vo['para_type'] == '102'): if(isset($vo['img_url'])): ?>
                <div class="swiper-slide" >
                    <div class="swiper-zoom-container">
                        <img class="lazyload" data-src="<?php echo $vo['img_url']; ?>" src="__PUBLIC__/img/zhanwei.png" />
                    </div>
                </div>
            <?php endif; endif; endforeach; endif; else: echo "" ;endif; ?>

        </div>
        <!--<div class="swiper-pagination swiper-pagination-white swiper-pagination-bullets"></div>-->
    </div>

</div>

<div id="offCanvasWrapper" class="mui-off-canvas-wrap mui-slide-in">
    <!--目录导航-->
    <aside id="offCanvasSidel" class="mui-off-canvas-left backWihte">
        <div id="offCanvasSideScroll" class="mui-scroll-wrapper">
            <div class="mui-scroll offcanvlist">
                <li class="mui-table-view-cell head">目录</li>
                <!--<li class="mui-table-view-cell mui-collapse mui-active">-->
                <!--</li>-->
                <!--<a class=" " href="#">贵州省贵阳市 </a>-->
                <ul class="mui-table-view mui-table-view-chevron marT0">
                    <?php if(is_array($data['nav']) || $data['nav'] instanceof \think\Collection || $data['nav'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['nav'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <li class="mui-table-view-cell nav_li" nav_id="<?php echo $vo['id']; ?>" >
                        <a >
                            <?php echo $vo['content']; ?>
                        </a>
                    </li>
                    <li class="mui-table-view-cell mui-plus-visible nav_li" nav_id="<?php echo $vo['id']; ?>">
                        <a >
                            <?php echo $vo['content']; ?>
                        </a>
                    </li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div>
        </div>
    </aside>

    <!--路线导航-->
    <aside id="offCanvasSider" class="mui-off-canvas-right backWihte">
        <div id="offCanvasSideScroll2" class="mui-scroll-wrapper">
            <div class="mui-scroll offcanvlist">
                <li class="mui-table-view-cell head">路线<img class="lazyload"  data-src="__PUBLIC__/img/base/map.png"></li>
                <?php if(is_array($data['linenav']) || $data['linenav'] instanceof \think\Collection || $data['linenav'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['linenav'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <li class="mui-table-view-cell mui-collapse mui-active">
                        <a class="" href="#" style="height: 40px;"><&nbsp;&nbsp; <?php echo $vo['title']; ?> </a>
                        <ul class="mui-table-view mui-table-view-chevron">
                            <?php if(is_array($vo['sub']) || $vo['sub'] instanceof \think\Collection || $vo['sub'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['sub'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                            <li class="mui-table-view-cell" style="height: 40px;">
                                <a class="nav_li" href="#content_<?php echo $item['para_id']; ?>" nav_id="<?php echo $item['para_id']; ?>">
                                    <?php echo $item['title']; ?>
                                </a>
                            </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        </div>
    </aside>

    <!--主界面部分-->
    <div class="mui-inner-wrap">

        <?php if(101 == $is_editor): ?>
        <header class="mui-bar mui-bar-nav" style="padding-top: 20px;height: 64px;">
            <a id="<?php if($is_web == '101'): ?>back<?php else: ?>pop<?php endif; ?>" class="mui-icon mui-icon-left-nav mui-pull-left mainColor"><span class="ltext"></span></a>
            <h1 class="mui-title"><?php echo $title; ?></h1>
            <button class="mui-btn mui-btn-link mui-pull-right mainColor pop" >编辑</button>
        </header>
        <?php elseif(101 == $is_ex): ?>
        <div  class="head">
            <img class="headimg" src="/static/img/menulogo.png">
            <div class="header">
                <a class="mui-icon mui-icon-left-nav mui-pull-left mainColor mui-action-back"><span class="ltext"></span></a>

                <button class="mui-btn  mui-btn-link mui-pull-right mainColor set_best" >加精</button>
                <button class="mui-btn  mui-btn-link mui-pull-right mainColor set_top" >推首</button>
                <button class="mui-btn  mui-btn-link mui-pull-right mainColor set_not_top" >打回</button>
            </div>
        </div>
            <!--加精推首放这里-->

        <?php else: if($is_app == '102'): ?>
                <header class="top-fixed-download mui-bar mui-bar-nav">
    <div class="title">
        <img style="width: 48px;height: 48px;margin-top: 8px;margin-left: 8px;margin-right: 8px;border-radius: 8px;" src="<?php echo $logo_url; ?>" alt="自由GO" class="logo">
        <p>
            <span class="name">自由GO</span>
            <span class="desc the_header_title"><?php echo (isset($title) && ($title !== '')?$title:""); ?></span>
        </p>
        <a  class="download_btn the_download_btn">点击下载</a>

    </div>
</header>
            <?php else: ?>
                <div  class="head">
                    <img class="headimg" src="/static/img/menulogo.png">
                    <div class="header">
                        <a id="<?php if($is_web == '101'): ?>back<?php else: ?>pop<?php endif; ?>" class="mui-icon mui-icon-left-nav mui-pull-left mainColor"><span class="ltext"></span></a>

                        <?php if($is_my == '101'): ?>
                        <button class="mui-btn  mui-btn-link mui-pull-right mainColor editor" >编辑</button>
                        <?php else: ?>
                        <button class="mui-btn  mui-btn-link mui-pull-right collect_btn mainColor" is_collect="<?php if(isset($data['collect']['id'])): ?><?php echo $data['collect']['id']; ?>">取消<?php else: ?>0">收藏<?php endif; ?></button>
                        <?php endif; ?>
                        <button class="mui-btn  mui-btn-link mui-pull-right mainColor" id="share_btn">分享</button>
                    </div>
                </div>
            <?php endif; endif; ?>

        <div class="mui-content">
            <!--下拉刷新容器-->
            <div class=" contentList" style="overflow: auto;">
                <?php if($data['img_url'] != ''): ?>
                <li class="mui-table-view-cell imgbox posR" style=" padding: 0 0 !important;">
                    <img style="object-fit: cover;" class="posA lazyload" data-src="<?php echo $data['img_url']; ?>"/>
                </li>
                <?php endif; ?>
                <ul id="list" class="mui-table-view mui-table-view-chevron">
                    <li class="imgbox posR" style="width:100%;height:35px; padding: 0 0 !important;">
                        <h1 class="mui-title"><?php echo $title; ?></h1>
                    </li>
                    <li class="mui-table-view-cell headerimgbox posR user_info_btn" the_id="<?php echo $data['user']['id']; ?>">
                        <img class="posA lazyload" data-src="<?php echo $data['user']['head_img_url']; ?>"/>
                        <div class="boxc">
                            <div class="t">
                                <div class="l mui-ellipsis mainColor">
                                    <?php echo $data['user']['nick_name']; ?>
                                </div>
                                <?php if(is_array($data['tag']) || $data['tag'] instanceof \think\Collection || $data['tag'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['tag'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <div class="lable">
                                    <?php echo $vo['tag_name']; ?>
                                </div>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </div>
                            <div class="b">
                                <div >阅读:<?php echo $data['read_count']; if(!(empty($data['car_full_name']) || (($data['car_full_name'] instanceof \think\Collection || $data['car_full_name'] instanceof \think\Paginator ) && $data['car_full_name']->isEmpty()))): ?><span class="tag"><?php echo $data['car_full_name']; ?></span><?php endif; ?></div>
                            </div>
                        </div>
                    </li>
                    <?php if(!(empty($data['companion']) || (($data['companion'] instanceof \think\Collection || $data['companion'] instanceof \think\Paginator ) && $data['companion']->isEmpty()))): ?>
                    <li class="mui-table-view-cell peerman" id="companion">
                        <div class="t mainColor">同行的伙伴</div>
                        <div class="b" >
                            <?php if(is_array($data['companion']) || $data['companion'] instanceof \think\Collection || $data['companion'] instanceof \think\Paginator): $i = 0; $__LIST__ = $data['companion'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                <img class="lazyload" data-src="<?php echo $vo['head_img_url']; ?>"/>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </li>
                    <?php endif; ?>
                    <li class="mui-table-view-cell  rout" id="line">
                        <div class="t mainColor">
                            城市
                        </div>
                        <div class="c">
                            <?php echo $data['line_str']; ?>
                        </div>
                        <div class="b">
                            <div class="floatL w50">起始时间:<?php echo date("Y-m-d",$data['c_time']); ?></div>
                            <div class="floatL w50">更新时间:<?php echo date("Y-m-d",$data['m_time']); ?></div>
                        </div>
                    </li>

                    <?php if(is_array($content) || $content instanceof \think\Collection || $content instanceof \think\Paginator): $i = 0; $__LIST__ = $content;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;switch($vo['para_type']): case "101": ?>
                                <li class="mui-table-view-cell  article noline" id="content_<?php echo $vo['id']; ?>">
                                    <div class="b">
                                        <?php echo $vo['content']; ?>
                                    </div>
                                </li>
                            <?php break; case "102": if(isset($vo['img_url'])): ?>
                                <li style="width: 100%;" class="pad0 posR mui-table-view-cell lineCenter mui-ellipsis imgs noline" id="content_<?php echo $vo['id']; ?>">
                                    <img style="width: 100%;height: 100%;" data-src="<?php echo $vo['img_url']; ?>" class="lazyload" src="__PUBLIC__/img/zhanwei.png"/>
                                </li>
                                <?php endif; break; case "103": ?>
                                <li class="mui-table-view-cell loc noline place_btn mainColor" place_id="<?php echo $vo['place_id']; ?>" id="content_<?php echo $vo['id']; ?>">
                                    <div class="t mui-ellipsis posR">
                                        <img data-index="<?php echo $index++; ?>" class="posA" src="__PUBLIC__/img/base/place.png"/>
                                        <?php echo $vo['content']; ?>
                                    </div>
                                </li>
                            <?php break; case "104": ?>
                                <li class="mui-table-view-cell  article noline" id="content_<?php echo $vo['id']; ?>">
                                    <div class="b">
                                        <?php echo $vo['content']; ?>
                                    </div>
                                </li>
                            <?php break; case "105": ?>
                                <li class="mui-table-view-cell  article noline" id="content_<?php echo $vo['id']; ?>">
                                    <div class="t mui-ellipsis">
                                        <?php echo $vo['content']; ?>
                                    </div>
                                </li>
                            <?php break; case "106": ?>
                                <li class="mui-table-view-cell  video noline" style="padding-left:0 !important; " id="content_<?php echo $vo['id']; ?>">
                                    <div class="" is_play="102">
                                        <!--<div class="videoBg" style="width: 100%position: absolute;z-index: 1111;text-align: center;display: none;">-->
                                            <!--<img style="width:64px;height:64px;object-fit: cover;" src="__PUBLIC__/img/bofang.png"/>-->
                                        <!--</div>-->
                                        <video style="width: 100%;height: 100%;" controls="controls" id="my-video" webkit-playsinline="false" playsinline="false" class="video-js video_item" preload="preload" width="100%" height="210" poster="<?php echo $vo['img_url']; ?>" data-setup="{}" >
                                            <source src="<?php echo $vo['video_url']; ?>" type='video/mp4'>
                                        </video>
                                        <div class="hide" style="position: absolute;z-index: 10000;width: 100px;height: 100px;left: 0;top: 10px;"> </div>
                                    </div>
                                </li>
                            <?php break; case "107": ?>
                                <li class="mui-table-view-cell loc noline navigate_btn" data-lat="<?php echo $vo['lat']; ?>" data-lng="<?php echo $vo['lng']; ?>" data-title="<?php echo $vo['content']; ?>" id="content_<?php echo $vo['id']; ?>">
                                    <div class="t mui-ellipsis posR ">
                                        <img class="posA" src="__PUBLIC__/img/base/place.png"/>
                                        <?php echo $vo['content']; ?>
                                    </div>
                                </li>
                            <?php break; default: ?><?php echo $vo['content']; endswitch; endforeach; endif; else: echo "" ;endif; ?>
                    <div class="black40"></div>
                    <li class="mui-table-view-cell  article noline">
                        <div class="t mui-ellipsis">
                            &nbsp;
                        </div>
                    </li>
                </ul>
                <!--</div>-->
            </div>
        </div>
        <!-- off-canvas backdrop -->
        <div class="mui-off-canvas-backdrop"></div>
        <div class="footer " style="">
            <div class="item floatLeft w25">
                <a href="#offCanvasSidel"><img src="__PUBLIC__/img/mulu.png?v=1" />目录</a>
            </div>
            <div class="item floatLeft w25 footerR">
                <a href="#offCanvasSider">
                    <img src="__PUBLIC__/img/lupai.png?v=1" />路线
                </a>
            </div>
            <div class="item floatLeft w25 like_btn" is_like="<?php if(isset($data['like']['id'])): ?><?php echo $data['like']['id']; else: ?>0<?php endif; ?>"><img src="__PUBLIC__/img/dianzan.png?v=1" /><span class="like_count"><?php echo $data['like_count']; ?></span></div>
            <div class="item floatLeft w25" id="commentlist"><img src="__PUBLIC__/img/pinglun.png?v=1" /><?php echo $data['comments_count']; ?></div>
        </div>
    </div>
</div>
<script src="__PUBLIC__/js/mui.min.js"></script>
<script src="__PUBLIC__/js/lazysizes.min.js"></script>
<script src="__PUBLIC__/js/jquery.3.2.1.js"></script>
<!--<script src="__PUBLIC__/js/swiper.jquery.min.js"></script>-->
<script src="__PUBLIC__/js/swiper.min.js"></script>
<!--<script src="__PUBLIC__/js/videoJs.js"></script>-->
<script src="__PUBLIC__/js/_publicCom2.js"></script>
<script src="__PUBLIC__/js/shidian.js?v=2"></script>
<script>

    <?php if($need_delete_table_cache == '102'): ?>
        localStorage.shidian_need_delete_table_cache = 102;
    <?php endif; ?>

    var sd = new shiDianCommon({share_data:<?php echo json_encode($data['share']); ?>});

    var token = sd.token('<?php echo $token; ?>');
    var baseUrl = '<?php echo $baseUrl; ?>';
    var pulicS = '__PUBLIC__/img/';
    var isRightCanvas = false;

    mui.ready(function() {
        initAblum();

        //图片懒加载
//        mui(document).imageLazyload({
//            placeholder: '__PUBLIC__/img/zhanwei.png'
//        });

        mui('body').on('tap', '.footerR', function() {
            isRightCanvas = true;
        })
        mui('body').on('tap', '.mui-off-canvas-backdrop', function() {
            hideCehua();
        })

        mui('#offCanvasSidel').on('tap','.nav_li',function (e) {
            mui('#offCanvasSideScroll').offCanvas('close');
            hideCehua();
            var nav_id = this.getAttribute('nav_id');
            setTimeout(function() {
                $('.contentList').animate({scrollTop:$('#content_'+nav_id).position().top},500);
            }, 50);
        });

        //页面滚动
        mui('#offCanvasSider').on('tap','.nav_li',function (e) {
            mui('#offCanvasSideScroll2').offCanvas('close');
            hideCehua();
            var nav_id = this.getAttribute('nav_id');
            setTimeout(function() {
                $('.contentList').animate({scrollTop:$('#content_'+nav_id).position().top},500);
            }, 50);
        });

        document.querySelector('.contentList').style.height = document.body.clientHeight-40+'px';
        var source = document.querySelectorAll('source');
        for (var temp_i = 0; temp_i < source.length; ++temp_i) {
            source[temp_i].setAttribute('src',source[temp_i].dataset.lazyload_video);
        }

        //视频播放
        var iosOrandrod = _publicCom.CCisModels();
//        iosOrandrod == '安卓App'?$('.videoBg').removeClass('hide'):'';
//        if(iosOrandrod == '安卓App'){
//            $('.videoBg').show();
//            $('video').style
//            $('.contentList').on('click','.videoBg',function (e) {
//                var wrap = $(this).parent();
//                videoRun(wrap.find('video'));
//            });
//        }

        //收藏或取消
        $('.collect_btn').click(function () {
            var url = baseUrl+'collect';
            var this_btn = $(this);
            var type = '';
            var param = {token:token};
            var is_collect = this_btn.attr('is_collect');
            if(0 < is_collect){
                //取消
                type = 'DELETE';
                url += '/'+is_collect+'?token='+param.token;
            }else{
                type = 'POST';
                url += '/?type=103&target_id=<?php echo $data['id']; ?>&token='+param.token;
            }
            $.ajax({
                url:url,
                type:type,
                param:param,
                success:function(data){
                    var desc = '';
                    if(200 != data.code){
                        if(data.code == 401){
                            sd.app('login',{});
                        }
                        popTitle(data.desc,2);
                    }else{
                        if(0 < is_collect){
                            popTitle('取消收藏成功',1);
                            this_btn.attr('is_collect',0);
                            this_btn.text('收藏');
                        }else{
                            popTitle('收藏成功',1);
                            this_btn.attr('is_collect',data.data.id);
                            this_btn.text('取消');
                        }
                    }
                },
                error:function(e){
                    popTitle('出错了，请稍后再试！',2)
                }
            });
        });

        //点赞和取消
        $('.like_btn').click(function () {
            var url = baseUrl+'like';
            var this_btn = $(this);
            var type = '';
            var param = {token:token};
            var is_like = this_btn.attr('is_like');
            if(0 < is_like){
                //取消
                type = 'DELETE';
                url += '/'+is_like+'?token='+param.token;
            }else{
                type = 'POST';
                url += '/?like_type=103&target_id=<?php echo $data['id']; ?>&token='+param.token;
            }
            $.ajax({
                url:url,
                type:type,
                param:param,
                success:function(data){
                    if(200 != data.code){
                        if(data.code == 401){
                            sd.app('login',{});
                        }
                        popTitle(data.desc,2);
                    }else{
                        if(0 < is_like){
                            popTitle('取消点赞成功',1);
                            this_btn.attr('is_like',0);
                            $('.like_count').text(parseInt($('.like_count').text()) - 1);
                        }else{
                            popTitle('点赞成功',1);
                            this_btn.attr('is_like',data.data.id);
                            $('.like_count').text(parseInt($('.like_count').text()) + 1);
                        }
                    }
                },
                error:function(e){
                    popTitle('出错了，请稍后再试！',2)
                }
            });
        });

        //todo editor 编辑按钮
        $('.editor').click(function(){
            sd.app('art_editor',{art_id:'<?php echo $data['id']; ?>','art_type':'<?php echo $data['type']; ?>'});
        });

        //进入用户详情
        $('.user_info_btn').click(function () {
            sd.app('user',{id:$(this).attr('the_id')});
        });

        //同行伙伴
        sd.listen('#companion','tap',function () {
            sd.app('companion',{art_id:'<?php echo $data['id']; ?>'});
        });
        //路线
        sd.listen('#line','tap',function () {
            sd.app('line',{line_id:'<?php echo $data['line_id']; ?>'});
        });
        //评论列表
        sd.listen('#commentlist','tap',function () {
            sd.app('commentlist',{type:'103',main_id:'<?php echo $data['id']; ?>'});
        });
        //跳转地点详情
        mui('#list').on('tap','.place_btn',function () {
            self.location = "<?php echo web_url('place'); ?>?id="+this.getAttribute('place_id')+'&is_web=101';//;
        });

        //
        mui('#list').on('tap','.navigate_btn',function () {
            sd.app('navigation',[{"title":this.dataset.title+'',"lat":this.dataset.lat+'',"lng":this.dataset.lng+''}]);
        });

        n2br($(".article .b"));

        //置顶
        $('body').on('click','.set_top',function () {
            openI("<?php echo web_url('/branch/art/rpush',['id'=>$data['id']]); ?>");

//            var the = $(this);
//            put("<?php echo $data['id']; ?>",function () {
//                the.remove();
//            });
        });

        //加精
        $('body').on('click','.set_best',function () {
            var the = $(this);
            perfect("<?php echo $data['id']; ?>",function () {
                the.remove();
            });
        });

        //打回
        $('body').on('click','.set_not_top',function () {
            openI("<?php echo web_url('/branch/art/repulse',['id'=>$data['id']]); ?>");
        });
    });

    //初始化图片浏览器
    var ablum_object = '.album_wrap';
    function initAblum() {
        screen_width = window.screen.width;
        var swiper = new Swiper('.swiper-container', {
//            pagination: '.swiper-pagination',
            width:screen_width,
            freeMode: false,
            zoom:true
        });

        var all_img_para = $('.mui-content').find('li.imgs img');

        $('.mui-content').on('click','.imgs img',function () {
            var index = all_img_para.index($(this));
            showAblum(swiper,index);
        })
        //close_btn
        $(ablum_object).on('click','.close_btn',function () {
            closeAblum();
        });
    }

    function closeAblum() {
        $(ablum_object).hide();
    }

    function showAblum(swiper,index) {
        // TODO 这里切换图片到对应的位置
        console.log(index);
        swiper.slideTo(index, 300, false);
        $(ablum_object).show();
    }

    function n2br(dom) {
        dom.each(function (index,item) {
            var temp = $(this);
            temp.html(temp.text().replace(/\n/g,'<br/>'));
        });
    }

    function videoRun(object) {
        if(object[0].paused){
            launchFullScreen(object[0]);
            object[0].currentTime = 0;
            object[0].play();
            object.parents('.b').find('.videoBg').hide();
        }else{
            object[0].pause();
            object.parents('.b').find('.videoBg').show();
        }
    }

    function hideCehua() {
        setTimeout(function() {
            if(isRightCanvas) {
                $('#offCanvasSider').removeClass('mui-active')
                $('#offCanvasSider').css('transform', 'translate3d(' + $(window).width() / 2 + 'px, 0px, 0px)')
                isRightCanvas = false;
                console.log(isRightCanvas);
            }
        }, 50)
    }

    function launchFullScreen(element) {
        if(element.requestFullScreen) {
            element.requestFullScreen();
        } else if(element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if(element.webkitRequestFullScreen) {
            element.webkitRequestFullScreen();
        }
    }
    /*********************** hud **********************/
    //data显示的文字.action:0loading，1.正确，2.错误
    function popTitle(data,action){
        if(action==0){
            iosTooltip(data,pulicS+'oval.svg',300,pulicS+'cenel.png',0.1);
        }else if(action==1){
            iosTooltip(data,pulicS+'ok.png',300,'',0);
        }else if(action == 2){
            iosTooltip(data,pulicS+'cenel.png',300,'',0);
        }
    }

    function iosTooltip(content, url, animateTime, close, showAddTime) {
        console.log($);
        $('#Prompt').attr('id')?$('#Prompt').remove():'';
        $('#Prompt_z_index').attr('id')?$('#Prompt_z_index').remove():'';
        !showAddTime?showAddTime = 0:'';
        var html = '<div class=" " id="Prompt_z_index" style="opacity: 0;z-index: 1110; min-height: 95px; position: absolute; width: 100px; height: 100px;background: black;"></div><div id="Prompt" style="z-index: 1110; min-height: 95px; position: absolute; top: 36%;">'+
                '<div class="PromptBody" style="z-index: 1111; width: 100%; height: 100%;">'+
                '<div class="yesPrompttool" style="position: absolute;left: -8px;top: -8px;"> <img id="PromptColse" onclick="closeIosTooltip()" class="hide" style="width: 24px;height: 24px;" src="' + close + '"></div>'+
                '<div class="yesPrompttop" style="height: 60px;    text-align: center;"><img class="PromptBodyImg" style="height: 32px;width: 32px;margin-top: 18px " src="' + url + '"></div>'+
                '<div class="yesPrompttop" style="color: white;font-size: 14px;text-align: center;letter-spacing:2px;padding:0 15px 5px 15px;">' + content +'</div>'
        '</div>'+
        '</div>'
        $('body').children().eq(0).before(html);
        var domW = $('#Prompt').width();
        var domCanW = $('body').width()*0.8;
        var winW =  $('body').width();
        if(domW>=domCanW){
            $('#Prompt,#Prompt_z_index').css('width',domCanW+'px').css('left',$('body').width()*0.1).css('right',$('body').width()*0.1);
        }else if(domW<=110){
            $('#Prompt,#Prompt_z_index').css('left',parseInt(winW-110)/2+'px').css('right',parseInt(winW-110)/2+'px');
        }
        else{
            $('#Prompt,#Prompt_z_index').css('left',parseInt(winW-domW)/2+'px').css('right',parseInt(winW-domW)/2+'px');
        }
        $("body,html").css({"overflow":"hidden"});
        $('#Prompt,#Prompt_z_index').css('top',$(window).height()*0.36+$(window).scrollTop());
        $('#Prompt_z_index').css('height',$('#Prompt').height());
        $('#Prompt_z_index').css('width',$('#Prompt').width());
        if(close) {
            show_mask('', 0.1);
            $('#PromptColse').removeClass('hide');
        }
        $("#Prompt_z_index").animate({
            opacity: 0.85
        },animateTime);
        $("#Prompt").animate({
            opacity: 1
        },animateTime+100);
        if(!close){
            setTimeout(function(){
                $("#z-index").remove();
                hide_mask();
                $("#Prompt,#Prompt_z_index").animate({opacity: 0}, 300,function(){
                    $("#Prompt").remove();
                    $("#Prompt_z_index").remove();

                });
            },animateTime+1000+showAddTime)
        }
    }

    //隐藏遮罩
    function hide_mask() {
        $("#z-index").hide();
        $("#z-index,.z-index").remove();
    }

    //显示遮罩
    function show_mask(top, opacity, anmit) {
        if(!top) {
            top = 0;
        }
        if(!opacity) {
            opacity = 0.6;
        }
        if(!anmit) {
            anmit = 700;
        }
        var html = '<div id="z-index" class="z-index" style="position: fixed;height: 100%;width: 100%;background: #919191;z-index: 100;opacity: 0;top: ' + top + 'px;left:0"></div>	'
        $('body').children().eq(0).before(html);
        $("#z-index").animate({
            opacity: opacity,
        }, anmit);
    }

    //置顶
    function put(id,func){
        $.ajax({
            type: "POST",
            url: "<?php echo web_url('/branch/top/save'); ?>",
            dataType: "json",
            data:{id:id},
            success: function (data) {
                if(200 == data.code) {
                    func();
                    popTitle('设置成功',1);
                }else{
                    popTitle(data.desc,2);
                }
            },
            error: function (data) {
                popTitle('网络不稳定，请稍后再试',2);
            }
        })
    }

    //加精
    function perfect(id,func){
        $.ajax({
            type: "POST",
            url: "<?php echo web_url('/branch/best/save'); ?>",
            dataType: "json",
            data:{id:id},
            success: function (data) {
                if(200 == data.code) {
                    func();
                    popTitle('设置成功',1);
                }else{
                    popTitle(data.desc,2);
                }
            },
            error: function (data) {
                popTitle('网络不稳定，请稍后再试',2);
            }
        })
    }

    //打回
    function noPut(desc) {
        $.ajax({
            type: "POST",
            url: "<?php echo web_url('/branch/top/delete'); ?>",
            dataType: "json",
            data:{id:"<?php echo $data['id']; ?>",desc:desc},
            success: function (data) {
                if(200 == data.code) {
                    popTitle('打回成功',1);
                }else{
                    popTitle(data.desc,2);
                }
            },
            error: function (data) {
                popTitle('网络不稳定，请稍后再试',2);
            }
        })
    }

    //打开iframe弹出层
    function openI(url) {
        var frame = $('.sub_window_frame');
        frame.show();
        frame.find('.window_wrap').attr("src", url);
    }

    //关闭
    function closeI() {
        $('.sub_window_frame').hide();
    }


</script>
</body>

</html>