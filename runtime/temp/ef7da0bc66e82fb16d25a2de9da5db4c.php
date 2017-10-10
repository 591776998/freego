<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:79:"D:\UPUPW_AP5.6_64\vhosts\tp5\public/../application/branch\view\seller\main.html";i:1505286341;}*/ ?>
<!doctype html>
<html lang="zh-cn">
<head>
    <meta name="viewport" content="initial-scale=1, user-scalable=0, minimal-ui" charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>地点列表</title>
    <link href="__PUBLIC__/temp/css/bootstrap.css" rel="stylesheet">
    <link href="__PUBLIC__/temp/css/dropload.css" rel="stylesheet">
    <style>
        html,body{
            height: 100%;
        }
        .outer{
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .outer .inner{
            flex: 1;
            overflow-y: scroll;
        }
        .header{
            background: #f5f5f5;
            display: flex;
            flex-direction: row;
            height: 64px;
            line-height: 34px;
            padding-top: 15px;
            position: fixed;
            width: 100%;
            z-index: 1;
        }
        .header a{
            height: 32px;
            text-align: center;
            display: block;
            color: #FFCC00;
            font-size: 18px;
        }
        .header form{
            flex: 1;
        }
        .header form input{
            height: 32px;
            width: 100%;
        }


        .lists{
            display: flex;
            flex-direction: column;
            padding-left: 15px;
            padding-right: 15px;
        }
        .mainColor{
            color: #FFCC00;
        }
        .comb{
            width: 100%;
            height: 90px;
            display: flex;
            flex-direction: row;
            padding-top: 20px;
        }
        .comb .left_img {
            width: 80px;
        }
        .comb .c{
            flex: 1;
        }
        .comb .r{
            width: 80px;
        }

        .comb .left_img img{
            width: 55px;
            height: 55px;
        }

        .comb .span1{
            height: 33px;
            line-height: 33px;
            font-size: 20px;
        }
        .comb .span2{
            height: 22px;
            line-height: 22px;
            font-size: 18px;
        }
        .comb .span3{
            font-size: 20px;
            height: 55px;
            line-height: 35px;
        }
        a,a:hover,a:focus{
            color: #FFCC00;
            text-decoration: none;
        }
        .line{
            height: 1px;
            background: #eee;
        }
        .w50{
            width: 50px;
        }
        .w70{
            width: 70px;
        }
        .dropdown-menu{
            max-height: 680px;
            overflow: scroll;
        }
        .footer{
            height: 50px;
            line-height: 50px;
            width: 100%;
            text-align: center;
            background-color: #ddd;
            font-size: 16px;
            color: black;
        }
    </style>
</head>
<body>
<div class="outer">
    <div class="header dropdown">
        <a href="javascript:;" class="back_btn w70"><&nbsp;后退</a>
        <form class="search_form"><input class="form-control" type="search" /></form>
        <a style="width: 100px;font-size: 14px;" href="javascript:;" class="lock" id="choose" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="address_name"><?php echo $branch['name']; ?></span><span class="caret"></span></a>
        <ul id="choosemenu" class="dropdown-menu dropdown-menu-right" style="right:0;width: 100px;top:45px" aria-labelledby="choose">
            <li><a location_name="<?php echo $branch['location_name']; ?>" locaion_value="<?php echo $branch['location_value']; ?>" href="#"><?php echo $branch['name']; ?></a></li>
            <?php if(is_array($sub_city) || $sub_city instanceof \think\Collection || $sub_city instanceof \think\Paginator): $i = 0; $__LIST__ = $sub_city;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <li><a branch_id="<?php echo $vo['branch_id']; ?>" href="#"><?php echo $vo['name']; ?></a></li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
    <div style="height: 64px;"></div>
    <div class="inner">
        <div class="lists"></div>
    </div>

    <a href="<?php echo web_url('/branch/place/create'); ?>" class="footer">
        创建地点
    </a>
</div>
<script src="__PUBLIC__/temp/js/jquery.min.js"> </script>
<script src="__PUBLIC__/temp/js/bootstrap.js"></script>
<script type="text/javascript" src="__PUBLIC__/temp/js/dropload.min.js"></script>
<script src="__PUBLIC__/js/branch.js"> </script>
<script type="text/javascript">
    var pagePara={page_size:20,page:1};
    var pageUrl = "<?php echo web_url('/branch/seller/index'); ?>";
    branch_id = "<?php echo $branch['branch_id']; ?>";

    $(function(){
        //返回按钮
        $('.back_btn').on('click',function () {
            window.history.back();
        });

        var list_wrap = $('.inner');
        var list = list_wrap.find('.lists');
        initFlushList(list_wrap,list);

        //加载一次数据
        ajaxLoadData(list,function(){},true);

        //搜索
        $(".search_form").on('submit',function(){
            pagePara.key_word = $(this).find('input').val();
            ajaxLoadData(list,function(){},true);
        });

        $("#choose").click(function(){//查看下级信息
            //ajaxaddress();
        });

        //切换城市
        $('#choosemenu').on('click','a',function () {
            var the = $(this);
            branch_id  = the.attr('branch_id');

            var name           = the.text();
            $('#choose .address_name').text(name);
            //加载一次数据
            ajaxLoadData(list,function (status) {

            },true);
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

        pagePara.branch_id  = branch_id;

        $.ajax({
            type:"POST",
            url:pageUrl,
            dataType:"json",
            data:pagePara,
            success:function(data){
                var go = true;
                if(200 == data.code) {
                    if(cover){
                        dom.html(renderList(data.data));
                    }else{
                        dom.append(renderList(data.data));
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
    function renderList(data) {
        return data.map(function (item) {
            return  '<div class="comb">'+
                    '<div class="left_img"> ' +
                    '<img class="img-rounded" src="'+
                    item.img_url+
                    '"></div>'+
                    '<div class="c" style="text-align: left">'+
                        '<span class="span1">'+
                    item.title +
                    '</span><br>'+
                    '<span class="span2" >负责人：</span><span class="span2">'+
                    item.boss+
                    '</span>'+
                    '</div>'+
                    '<div class="span3 r">' +
                    '<a class="btn6 mainColor do_look" href="<?php echo web_url('/branch/place/editor'); ?>?id='+
                    item.id+
                    '">编辑</a>'+
                    '</div>'+
                    '</div>';
        }).join('<div class="line"></div>');
    }
    
    function ajaxaddress(){//加载地区下级选择
        var addressData={};
        $.ajax({
            type:"POST",
            url:"",
            dataType:"json",
            data:addressData,
            success:function(data){
                if(data.code=200) {
                    var s='';
                    for(var i=0;i<data.data.length;i++){
                        s+=' <li><a href="_public_/branch/seller/main?"></a></li>'
                    }
                    $("#choose").append(s);
                }
                if(data.code=201){
                    alert(data.desc);
                }
            },
            error:function(data){
               alert(data.desc);

            }
        })
    }
</script>
</body>
</html>