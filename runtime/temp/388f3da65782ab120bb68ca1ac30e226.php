<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:79:"D:\UPUPW_AP5.6_64\vhosts\tp5\public/../application/branch\view\index\index.html";i:1504510183;}*/ ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>分会管理</title>
    <link href="__PUBLIC__/temp/css/bootstrap.css" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="" />
    <style type="text/css">
        @media (min-width:992px) {
            .row{
                width: 80%;
                margin:0 auto;


            }
            .comment{
                width: 100%;
                font-size:0.8em;
                color: #999999;

            }

            .main{
                padding: 10px 8px;
                margin: 0 18%;
            }
            .comm{
                width: 100%;
                margin: 35px auto;
            }
            .comb{
                width: 90%;text-align: center;
                margin: 30px auto;
            }
            .footer{
                left: 0;
            }

        }
        @media (max-width:376px){
            .comment{
                width: 100%;
                font-size:1.5em;
                color: #999999;
                text-align: center;

            }
            .comm{
                width: 100%;
                margin: 30px auto;
            }
            .comb{
                width: 90%;text-align: center;
                margin: 25px auto;
            }
            .footer{
                font-size: 1.2em ;
            }
            .modal-dialog{
                width: 200px;
                margin: 280px auto;

            }

        }
        @media (min-width:410px){
            .comment{
                width: 100%;
                font-size:1.6em;

                color: #999999;
                text-align: center;

            }
            .comm{
                width: 100%;
                margin: 35px auto;
            }
            .comb{
                width: 90%;text-align: center;
                margin: 25px 25px;
            }
            .footer{
                font-size: 1.3em ;
            }
            .modal-dialog{
                width: 200px;
                margin: 280px auto;

            }

        }
        .header{

            margin: 0 -15px;
            padding: 10px 0;
            background: #F9F9F9;
            color:#FFCC00;
            text-align: center;
            font-size: 0.8em;

        }
        .footer{
            border-top:1px solid #DDDDDD;
            padding: 16px 0;
            background: #F9F9F9;
            color: #DDDDDD;
            font-weight: bold;
            position: fixed;
            width: 100%;
            bottom: 0px;
            color: #999999;

        }
        .btn1{
            border: none;
            background: #F9F9F9;
        }

        .btn2{
            border: none;
            background: #F9F9F9;
            width: 100%
        }
        .btn3{
            border: none;
            padding: 4px 0;
        }
        .btn4{
            border: none;
            border-radius: 8px;
            padding: 3px 5px;
            font-size: 1.0em;
            background: #FFFFFF;
            color:#FFCC00;

        }
        .btn5{
            border: none;
            background:#F2F2F2;
            width:70%;
            padding: 3px 0px;
        }
        .btn6{
            border: none;
            padding: 15px 0px;
            font-size:0.8em;
            background: #FFFFFF;
            color:#FFCC00;
            margin-right: 8px;
        }
        .btn7{
            border: none;
            padding: 3px 0px;
            font-size:0.8em;
            background: #FFFFFF;
            color:#FFCC00;
            margin-left: 8px;
        }
        .left{
            width: 65%;
            float: left;
            text-align: left;
            padding-left: 25px;
        }
        .right{
            width: 35%;
            float: left;
            text-align: right;
            padding-right:  25px;
        }
        .hrline{
            width: 100%;
            height:2px ;
            border-top: 1px solid #DDDDDD;
        }
        .span1{
            font-size: 0.7em;
            font-weight: bold;
        }
        .span2{
            font-weight: normal;
            font-size: 0.7em;
        }
        .img-rounded{
            width: 100%;
            height:60px
        }
        .col-xs-2{
            padding: 0;
        }
        .col-xs-7{
            padding-left: 5px;
            padding-right: 5px;
        }
        .col-xs-4{
            padding: 0;
        }
        .col-xs-3{
            padding: 0;
        }
        .col-xs-6{
            padding: 0 5px;
        }
        button{
            outline: 0;
        }
        .header{
            font-size: 1.5em;
            height: 44px;
            line-height: 44px;
            padding: 0;
            display: flex;
            flex-direction: row;
        }
        .header .p_wrap{
            flex: 1;
            color: #999999;
        }
        .header .p_wrap .span_btn{
            color: #FFCC00;
        }
        .header .a_btn{
            width: 80px;
            color: #FFCC00;
        }
    </style>

</head>
<body>
<div class="container">
    <div class="header">
        <?php if(in_array(($data['login']['role']), explode(',',"101,103"))): ?>
        <a class="a_btn" href="<?php echo web_url('/branch/seller/infolist'); ?>">商家</a>
        <?php else: ?>
        <a class="a_btn" ></a>
        <?php endif; ?>
        <p class="p_wrap">XX分会<?php if($data['login']['role'] == '101'): ?>（<a class="span_btn" href="<?php echo web_url('/branch/manage/main'); ?>">管理</a>）<?php endif; ?></p>
        <?php if(in_array(($data['login']['role']), explode(',',"101,102"))): ?>
        <a class="a_btn" href="<?php echo web_url('/branch/art/main'); ?>">推首</a>
        <?php else: ?>
        <a class="a_btn" ></a>
        <?php endif; ?>
    </div>
    <div class="row" style="padding-bottom: 100px">
        <div class="col-xs-12 col-md-8 main">
            <div id="walletpage" class="comment">
                <div class="comb">
                    <div class="row">
                        <div class="left">审核机构代码：</div>
                        <div class="right"><?php echo $data['code']; ?></div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">现金总收入：</div>
                        <div class="right"><span class="btn3 data1" >230000</span>￥</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">直接发展用户总数：</div>
                        <div class="right"><span class="btn3 data2" >900</span>位</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">直接签约商家总数：</div>
                        <div class="right"><span class="btn3 data3" >900</span>家</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">下级发展用户总数：</div>
                        <div class="right"><span class="btn3 data4" >900</span>位</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">下级签约商家总数：</div>
                        <div class="right"><span class="btn3 data5" >900</span>家</div>
                    </div>
                </div>

                <div class="comb">
                    <div class="hrline"></div>
                </div>

                <div class="comb">
                    <div class="row">
                        <div class="left">今日现金收入：</div>
                        <div class="right"><span class="btn3 data6">9800</span>￥</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">今日直接发展用户总数：</div>
                        <div class="right"><span class="btn3 dat7" >5200</span>位</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">今日直接签约商家总数：</div>
                        <div class="right"><span class="btn3 data8">9800</span>家</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">今日下级发展用户总数：</div>
                        <div class="right"><span class="btn3 data9" >5200</span>位</div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">今日下级签约商家总数：</div>
                        <div class="right" style="color: red"><span class="btn3 data10"  >+80</span><span >家</span></div>
                    </div>
                </div>
                <div class="comb">
                    <div class="row">
                        <div class="left">用户较昨日情况：</div>
                        <div class="right" style="color: red"><span class="btn3 data11">+50</span><span >位</span></div>
                    </div>
                </div>
                <div class="comb">
                    <div class="hrline"></div>
                </div>
                <div class="comb">
                    <button class="btn4" id="monthdetail">查看月度营收</button>
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
    </div>

</div>

<script src="__PUBLIC__/temp/js/jquery.min.js"> </script>
<script src="__PUBLIC__/temp/js/bootstrap.js"></script>
<script src="__PUBLIC__/js/branch.js"></script>
<script type="text/javascript">
    var manageData={};
    manageData.key_word="";
    manageData.page="";
    manageData.size="";
    var counter = 1;
    var result="";
    function ajaxWallet(){//获取钱包信息
        $.ajax({
            type:"GET",
            url:"<?php echo web_url('/branch/wallet/total'); ?>",
            data:"",
            success:function(data){
                if(data.code=200){
//                        $("#totalmoney").text(data.data.total.money);
//                        $("#totalpoint").text(data.data.total.point);
//                        $("#monthmoney").text(data.data.month.money);
//                        $("#monthpoint").text(data.data.month.point);
//                        $("#todaymoney").text(data.data.today.money);
//                        $("#todaypoint").text(data.data.today.point);
//                        $("#todaymoney_diff").text(data.data.today.money_diff);
//                        $("#todaypoint_diff").text(data.data.today.point_diff);
                }else{
                    $("#content").text(data.desc);
                    $('#myModal').modal('toggle');
                }
            },
            error:function(data){
                $("#content").text("请求失败，请稍后重试");
                $('#myModal').modal('toggle');
            }
        })
    }
    $(function(){
        $("#monthdetail").click(function(){//跳转月度营收
            jump("<?php echo web_url('/branch/wallet/month'); ?>");
        });

        //渲染数据
        var zhuangge_url = 'http://fr.yoyopeilian.com/index/org/index';
        var param        = {orgid:"<?php echo $data['id']; ?>"};
        $.ajax({
            type:'POST',
            url:zhuangge_url,
            data:param,
            dataType:'json',
            success:function (data) {
                if(200 == data.code){
                    console.log(data.data);
                    for (var key in data.data){
                        console.log(key);
                        $('body .'+key).text(data.data[key]);
                    }
                }
            },
            error:function () {
                msg('网络错误，请稍后再试',2);
            }
        });
    })
</script>
</body>
</html>