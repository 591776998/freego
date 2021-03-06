<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:76:"D:\UPUPW_AP5.6_64\vhosts\tp5\public/../application/branch\view\art\main.html";i:1505198687;}*/ ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>推首</title>
    <link href="__PUBLIC__/temp/css/bootstrap.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="" />
    <link href="__PUBLIC__/temp/css/dropload.css" rel="stylesheet">

    <style type="text/css">

        html,body{
            height: 100%;
        }
        @media (min-width:992px) {
            .row{
                width: 80%;
                margin:0 auto;


            }
            .comment{
                width: 100%;
                font-size:0.8em;
                color: #666666;

            }

            .main{
                padding: 10px 8px;
                margin: 0 18%;
            }
            .comm{
                width: 100%;

            }
            .comb{
                width: 95%;
                text-align: center;
                margin: 5px auto;
                padding: 10px 5px;
                border-bottom: 1px solid #DDDDDD;
            }
            .header{
                left: 0px;
            }

        }
        @media (max-width:376px){
            .comment{
                width: 100%;
                font-size:1.2em;
                padding-top: 10px;
                color: #666666;
                text-align: center;

            }
            .comm{
                width: 100%;

            }
            .comb{
                width: 95%;
                text-align: center;
                margin: 5px auto;
                padding: 10px 5px;
                border-bottom: 1px solid #DDDDDD;
            }
            .modal-dialog{
                width: 200px;
                margin: 280px auto;
                font-size: 0.8em;
            }
        }
        @media (min-width:410px){
            .comment{
                width: 100%;
                font-size:1.3em;
                padding-top: 10px;
                color: #666666;
                text-align: center;

            }
            .comm{
                width: 100%;

            }
            .comb{
                width: 95%;
                text-align: center;
                margin: 5px auto;
                padding: 10px 5px;
                border-bottom: 1px solid #DDDDDD;
            }
            .modal-dialog{
                width: 200px;
                margin: 280px auto;
                font-size: 0.8em;
            }
        }
        .header{
            width: 100%;

            color: #FFCC00;
            font-weight: bold;
            position: fixed;
            top: 0px;
            z-index: 99999;

        }
        .menu{
            width: 92%;
            background: #FFFFFF;
            font-size: 0.8em;
            font-weight:normal;
            margin: 7px 0;
        }
        .btn1{
            border: none;
            background: #F9F9F9;
        }
        .btn11{
            border: none;
            background: #FFFFFF;
            color:#FFCC00;
        }

        .btn2{
            border:1px solid #FFCC00;

            background: #FFFFFF;
            color: #FFCC00;
            width: 16%;
            padding: 3px 0;
            margin:0 -5px;
        }
        .btn3{
            border:1px solid #FFCC00;
            background:#FFFFFF;
            width:17%;
            border-radius: 6px 0 0 6px ;
            padding: 3px 0;
            margin: 0;
        }
        .btn4{
            border:1px solid #FFCC00;
            background:#FFFFFF;
            width:17%;
            border-radius:0 6px 6px 0;
            padding: 3px 0;
            margin: 0;

        }
        .btn5{
            border: none;
            background:#FFFFFF;
            font-size: 1.0em;
            font-weight: bold;
            color: #FFCC00;
            padding: 15px 5px 15px 0;
        }
        .btn6{
            width: 250px;
            font-size: 0.7em;
            padding: 5px 5px;
        }
        .btn7{
            border: none;
            background: #F9F9F9;
            width:100%;
            padding: 3px 5px;
        }
        .span1{
            font-size: 0.7em;
            font-weight: bold;
        }
        .img-rounded{
            width: 100px;
            height: 100px;
        }
        .hrline{
            width: 100%;
            height: 2px;
            border-top:1px solid #DDDDDD ;
        }
        .col-xs-10{
            color: #999999;
            text-align: left;
        }
        .col-xs-2{

        }
        .span1{
            font-size: 0.8em;
            font-weight: bold;
        }
        .span2{
            font-size: 0.7em;
        }
        .span3{
            margin-right: 4px;
            color: #FFCC00;
            font-size: 0.8em;
        }
        .span4{
            color: #999999;
        }
        button{
            outline: 0;
        }
        .cbtn{

            background:#FFFFFF;
            font-weight:normal;
            color:#FFCC00;
        }
        .btn_active{
            background:#FFCC00;
            font-weight:bold;
            color:#FFFFFF;
        }
        .item_wrap{
            height: 70px;
            display: flex;
            flex-direction: row;

        }
        .item_wrap .img{
            width: 70px;
            padding-right: 5px;
            margin-top: 10px;
        }
        .item_wrap .img img{
            width: 65px;
            height: 50px;
        }
        .item_wrap .btn_wrap{
            width: 40px;
            display: flex;
            flex-direction: column;
        }
        .item_wrap .content{
            flex: 1;
            font-size: 16px;
            text-align: left;
        }
        .item_wrap .content .title{
            display: block;
            height: 35px;
            line-height: 35px;
            overflow: hidden;
            text-overflow:ellipsis;
            -o-text-overflow:ellipsis;
        }
        .item_wrap .content .sub_content{
            text-align: left;
            font-size: 14px;
            display: flex;
            flex-direction: row;
        }
        .item_wrap .content .sub_content .sub_title{
            flex: 1;
            overflow: hidden;
            height: 35px;
            line-height: 35px;
        }

        .item_wrap .content .sub_content .sub_right{
            width: 140px;
            height: 35px;
            line-height: 35px;
            display: flex;
            flex-direction: row;
        }

        .item_wrap .content .sub_content .short_sub_right{
            width: 50px;
            height: 35px;
            line-height: 35px;
            display: flex;
            flex-direction: row;
        }

        .item_wrap .content .sub_content .icon{
            width: 16px;
            height: 16px;
            color: #FFCC00;
            font-size: 14px;
        }
        .item_wrap .content .sub_content .right_num{
            flex: 1;
            font-weight: bold;
        }
        .item_wrap .btn_wrap a{
            flex: 1;
            line-height: 35px;
            color: #FFCC00;
            text-decoration: none;
        }
        .item_wrap .btn_wrap .set_pass{
            flex: 1;
            line-height: 35px;
            color: #FFCC00;
            text-decoration: none;
        }
        .glyphicon{
            line-height: inherit;
        }
        .line{
            height: 1px;
            background: #ccc;
            width: 100%;
        }
        .item_wrap .content .sub_content .top_span{
            height: 16px;
            width: 16px;
            font-size: 12px;
            border: 1px solid #666;
            border-radius: 2px;
            display: inline-block;
            margin-left: 3px;
            margin-top: 10px;
        }
        .container{
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        #comentpage{
            height: auto;
            flex: 1;
            overflow-y: scroll;
        }
    </style>

</head>
<body>
<div class="container">
    <div class="comment">
        <div class="header">
            <div class="row" style=" background: #F9F9F9; padding: 7px 0 10px 0; border-bottom: 1px solid #DDDDDD">
                <div class="col-xs-4 col-md-4" style="text-align: left;padding: 0">
                    <button class="btn1 back">
                        <span class="glyphicon glyphicon-chevron-left"></span>返回
                    </button>
                </div>
                <div class="col-xs-4 col-md-4" style="color: #080808;margin-left: -10px; text-align: center;padding: 0">
                    <button class="btn1">首页申请</button></div>
            </div>
            <div class="menu">
                <button class="btn3 cbtn" type="101" >游记</button>
                <button class="btn2 cbtn" type="120" >影像</button>
                <button class="btn2 cbtn" type="103" >问答</button>
                <button class="btn2 cbtn" type="102" >活动</button>
                <button class="btn2 cbtn" type="105" >车主</button>
                <button class="btn4 cbtn" type="0" >全部</button>
            </div>
        </div>
    </div>

    <div style="height: 64px;"></div>
    <div id="comentpage">
        <div id="lists" class="lists">

        </div>
    </div>
    <div id="myModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content" style="text-align: center">
                <div id="content" style="height: 100px;margin-top: 30px">

                </div>
            </div>
        </div>
    </div>
</div>

<script src="__PUBLIC__/temp/js/jquery.min.js"> </script>
<script src="__PUBLIC__/temp/js/bootstrap.js"></script>
<script type="text/javascript" src="__PUBLIC__/temp/js/dropload.min.js"></script>
<script type="text/javascript" src="__PUBLIC__/js/branch.js"></script>
<script type="text/javascript">
    var pagePara    = {page:1,page_size:10};
    var pageUrl     = '';
    var artUrl = "<?php echo web_url('/branch/art/index'); ?>";
    var imgListUrl  = "<?php echo web_url('/branch/photo/index'); ?>";

    //置顶
    function put(id,func){
        //1.打开推送页面，2.
        $.ajax({
            type: "POST",
            url: "<?php echo web_url('/branch/top/save'); ?>",
            dataType: "json",
            data:{id:id},
            success: function (data) {
                if(200 == data.code) {
                    func();
                    msg('设置成功',1);
                }else{
                    msg(data.desc,2);
                }
            },
            error: function (data) {
                msg('网络不稳定，请稍后再试',2);
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
                    msg('设置成功',1);
                }else{
                    msg(data.desc,2);
                }
            },
            error: function (data) {
                msg('网络不稳定，请稍后再试',2);
            }
        })
    }

    //通过
    function pass(id,func){
        $.ajax({
            type: "POST",
            url: "<?php echo web_url('/branch/photo/update'); ?>",
            dataType: "json",
            data:{id:id},
            success: function (data) {
                if(200 == data.code) {
                    func();
                    msg('设置成功',1);
                }else{
                    msg(data.desc,2);
                }
            },
            error: function (data) {
                msg('网络不稳定，请稍后再试',2);
            }
        })
    }

    //删除精彩影像
    function deletePhoto(id,func) {
        $.ajax({
            type: "POST",
            url: "<?php echo web_url('/branch/photo/delete'); ?>",
            dataType: "json",
            data:{id:id},
            success: function (data) {
                if(200 == data.code) {
                    func();
                    msg('删除成功',1);
                }else{
                    msg(data.desc,2);
                }
            },
            error: function (data) {
                msg('网络不稳定，请稍后再试',2);
            }
        })
    }

    //切换选项卡
    function chooseTab(the) {
        the.siblings().removeClass('btn_active');
        the.addClass('btn_active');
    }

    $(function(){

        $('.back').click(function () {
            window.history.back();  //返回上一页
        });

        var list_wrap = $('#comentpage');
        var list = list_wrap.find('.lists');
        initFlushList(list_wrap,list);

        //初始化一次列表
        chooseTab($('.menu .cbtn:last-child'));
        pageUrl = artUrl;
        ajaxLoadData(list,function(){},true);

        //顶部切换
        $('.menu').on('click','.cbtn',function () {
            chooseTab($(this));
            pagePara.type       = $(this).attr('type');
            if(120 == pagePara.type){
                pageUrl = imgListUrl;
            }else {
                pageUrl = artUrl;
            }
            pagePara.page       = 1;
            ajaxLoadData(list,function(){},true);
        });

        //置顶
        $('body').on('click','.set_top',function () {
            var the = $(this);
            var top_dom_wrap = the.parent().parent().find('.top_dom_wrap');
           put($(this).attr('art_id'),function () {
               console.log('1111');
               top_dom_wrap.append('<span class="top_span">顶</span>');
               the.remove();
           });
        });

        //加精
        $('body').on('click','.set_best',function () {
            var the = $(this);
            var top_dom_wrap = the.parent().parent().find('.top_dom_wrap');
            perfect($(this).attr('art_id'),function () {
                console.log('1111');
                the.remove();
                top_dom_wrap.append('<span class="top_span">精</span>');
            });
        });

        //影像通过审核
        $('body').on('click','.set_pass',function () {
            var the = $(this);
            var top_dom_wrap = the.parent().parent().find('.top_dom_wrap');
            pass($(this).attr('photo_id'),function () {
                the.remove();
                top_dom_wrap.append('<span class="top_span">显</span>');
            });
        });

        //删除精彩影像
        $('body').on('click','.delete_photo',function () {
            var the = $(this);
            var wrap_dom = the.parents('.item_wrap');
            deletePhoto($(this).attr('photo_id'),function () {
                wrap_dom.remove();
            });
        });

        $('#comentpage').on('click','.item_wrap.art',function(){
            //跳转到文章详情
            var url = "<?php echo web_url('/index/web/art'); ?>";
            var id = $(this).data('id');
            window.location = url+'?id='+id+'&is_ex=101';
        });
    });


    //初始化上下拉刷新
    function initFlushList(dom,list) {
        var dp = dom.dropload({
            autoLoad:false,
            domUp : {
                domClass   : 'dropload-up',
                domRefresh : '<div class="dropload-refresh">↓下拉刷新</div>',
                domUpdate  : '<div class="dropload-update">↑释放更新</div>',
                domLoad    : '<div class="dropload-load"><span class="loading"></span>加载中...</div>'
            },
            domDown : {
                domClass   : 'dropload-down',
                domRefresh : '<div class="dropload-refresh">↑上拉加载更多</div>',
                domLoad    : '<div class="dropload-load"><span class="loading"></span>加载中...</div>',
                domNoData  : '<div class="dropload-noData">暂无数据</div>'
            },
            loadUpFn : function(me){
                ajaxLoadData(list,function (status) {
                    me.resetload();
                },true);
            },
            loadDownFn : function(me){
                ajaxLoadData(list,function (status) {
                    me.resetload();
                },false);
            }
        });
    }

    //异步加载商家数据 传入列表的dom ,消除加载效果的函数 ，是否覆盖
    function ajaxLoadData(dom,func,cover){
        if(cover){
            pagePara.page = 1;
        }else{
            pagePara.page++;
        }
        $.ajax({
            type:"POST",
            url:pageUrl,
            dataType:"json",
            data:pagePara,
            success:function(data){
                var go = true;
                if(200 == data.code) {
                    if(cover){
                        dom.html(renderList(data.data,pagePara.type));
                    }else{
                        dom.append(renderList(data.data,pagePara.type));
                    }
                    if(data.data.length != pagePara.page_size){
                        go = false;
                    }
                }else{
                    msg(data.desc,2);
                }
                func(go);
            },
            error:function(data){
                func(true);
                msg('网络错误，请稍后再试'.desc,2);
            }
        })
    }

    //渲染dom
    function renderList(data,type) {
        return data.map(function (item) {
            if(120 == type){
                var show_span = '';
                var btn_dom = '';
                    if(101 == item.status){
                        show_span ='<span class="top_span">显</span>';
                        btn_dom = '<a class="delete_photo" photo_id="'+item.id+'">删除</a>';
                    }else{
                        btn_dom = '<a class="set_pass" photo_id="'+item.id+'">通过</a><a class="delete_photo" photo_id="'+item.id+'">删除</a>';
                    }
                return '<div class="item_wrap"><div class="img"> <img src="'+item.img_url+'"> </div> <div class="content"> <span class="title">'+
                        item.location_str+
                        '</span><span class="sub_content"><span class="sub_title">'+
                        item.user.nick_name+
                        '</span><span class="top_dom_wrap">' +
                        show_span+
                        '</span><span class="short_sub_right"> <span class="glyphicon glyphicon-heart icon"></span>'+
                        '<span class="span4 right_num">'+
                        item.like_count+
                        '</span></span></span></div><div class="btn_wrap">'+
                        btn_dom+
                        '</div></div>';
            }else {
                var img = item.img_url?'<div class="img"><img src="' + item.img_url + '" /></div>':'';
                var sub_right = '';
                if(102 == item.type){
                    sub_right =
                            '<span class="icon"></span><span class="span4 right_num"></span>'+
                            '<span class="glyphicon glyphicon-eye-open icon"></span><span class="span4 right_num">' + item.read_count + '</span>' +
                            '<span class="glyphicon glyphicon-user icon"></span><span class="span4 right_num">' + item.joined_count + '</span>';

                }else{
                    sub_right = '<span class="glyphicon glyphicon-eye-open icon"></span><span class="span4 right_num">' + item.read_count + '</span>' +
                            '<span class="glyphicon glyphicon-thumbs-up icon"></span><span class="span4 right_num">' + item.like_count + '</span>' +
                            '<span class="glyphicon glyphicon-comment icon"></span><span class="span4 right_num">' + item.comments_count + '</span>';
                }
                var btn_wrap = '';
                var top_dom = '';
                // todo
                if(102 == item.top_status){
                    btn_wrap += '<a class="set_top" art_id="'+item.id+'">推首</a>';
                }
                if(item.is_top.id){
                    top_dom += '<span class="top_span">顶</span>';
                }
                if(!item.is_best.id){
                    btn_wrap += '<a class="set_best" art_id="'+item.id+'">加精</a> ';
                }else{
                    top_dom += '<span class="top_span">精</span>';
                }

                return '<div class="item_wrap art" data-id='+item.id+'>' +
                        img+
                        '<div class="content">' +
                        '<span class="title">' + item.title + '</span>' +
                        '<span class="sub_content">' +
                        '<span class="sub_title">' + item.user.nick_name + '</span>' +
                        '<span class="top_dom_wrap">' +
                        top_dom+
                        '</span>'+
                        '<span class="sub_right">' +
                        sub_right+
                        '</span> </span> </div>' +
                        '<div class="btn_wrap">' +
                        btn_wrap+
                        '</div></div>';
            }
        }).join('<div class="line"></div>');
    }

    /***********************************************/




</script>
</body>
</html>
