<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:77:"D:\UPUPW_AP5.6_64\vhosts\tp5\public/../application/branch\view\art\rpush.html";i:1505292489;}*/ ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>失败原因</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="" />
    <style>
        html,body,.main{
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .wrap{
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .header{
            text-align: center;
            height: 50px;
            line-height: 50px;
            font-size: 1.2em;
            font-weight: bold;
        }
        .footer{
            height: 120px;
        }
        .body{
            flex: 1;
            width: 99%;
            padding: 0;
            /*height: 100px;*/
            border: none;
            display: flex;
            flex-direction: column;
        }
        .body .comb{
            height: 35px;
        }
        .select_wrap{
            padding-left: 10px;
            height: 88px;
        }
        .sub_select_wrap{
            height: 22px;
        }
        .select_wrap .s{
            height: 22px;
            margin-top: 10px;
            display: block;
        }
        .sub_select_wrap{
            height: 22px;
            margin-top: 10px;
            margin-left: 10px;
        }
        .data_wrap{
            flex: 1;
            overflow-y: scroll;
        }
        .footer{
            width: 100%;
        }
        .footer a{
            display: block;
            height: 60px;
            width: 100%;
            text-align: center;
            line-height: 60px;
            color: #FFCC00;
        }
        .line{
            height: 1px;
            background-color: #bbb;
            width: 100%;
        }
        table{
            width: 100%;
        }
        table p{
            display: flex;
            flex-direction: row;
        }
        table span{
            flex: 1;
        }
        table a{
            color: #ffcc00;
            width: 50px;
        }
    </style>
</head>
<body>
<div class="main">
    <div class="wrap">
        <div class="header">选择推首页的地方</div>
        <div class="body">
            <div class="select_wrap">
                <select class="s"></select>
            </div>
            <div class="sub_select_wrap">
                <select class="time_select">
                    <option value="86400">24小时</option>
                    <option value="172800">48小时</option>
                    <option value="259200">72小时</option>
                    <option value="345600">96小时</option>
                </select>
                <button style="padding: 0 15px" class="add">加添</button></div>
            <div class="data_wrap">
                <table class="table">
                    <thead>
                        <th><td></td></th>
                    </thead>
                    <tbody class="data_list"></tbody>
                </table>
            </div>
        </div>

        <div class="footer">
            <a class="send_btn">推送</a>
            <div class="line"></div>
            <a class="cancel_btn">取消</a>
        </div>
    </div>
</div>
<script src="__PUBLIC__/js/jquery.3.2.1.js"></script>
<script>
    var all = [];
    var art_id = "<?php echo $id; ?>";
    //1.根据id获取下级分会
    function getSub(dom,object,is_top) {
        $.ajax({
            type:"POST",
            url:"<?php echo web_url('/branch/branch/index'); ?>",
            dataType:"json",
            data:{branch_id:object.branch_id?object.branch_id:'0'},
            success:function(d){
                if(d.code == '200'){
                    if(0 != d.data.length || is_top) {

                        if('function' == typeof  dom){
                            dom = dom();
                        }

                        d.data.unshift(object);

                        var s = d.data.map(function (data) {
                            return '<option value="' + data.branch_id + '">' + data.name + '</option>';
                        }).join('');

                        dom.empty();
                        dom.append(s);
                    }else{

                        if('function' != typeof  dom){
                            dom.nextAll().remove();
                            dom.remove();
                        }
                    }
                }
            }
        })
    }

    function init() {
        getSub($('.select_wrap').find('select'),<?php echo json_encode($branch); ?>,true);
    }
    init();

    //当某项被选中时，创建下级选择器
    $('.select_wrap').on('change','select',function () {
        var the = $(this);
        var index = $(this).get(0).selectedIndex;
        if(0 == index){
            return the.nextAll().remove();
        }
        var selected = {branch_id:the.val(),name:the.find("option:selected").text()};
        if(0 < selected.branch_id){
            if( 0 == the.next('select').length ){
                getSub(function() {
                    $('.select_wrap').append('<select class="s"></select>');
                    return $('.select_wrap').find('select:last')
                },selected);
            }else{
                getSub($(this).next('select'),selected);
            }
        }else{
            the.nextAll().remove();
        }
    });

    //点击添加按钮
    $('.sub_select_wrap').on('click','.add',function () {
        //1.id
        var select = $('.select_wrap').find('select:last');
        var id = select.val();
        var text = select.find("option:selected").text();
        if(0 < id || 0 === id || '0' === id){
            var time = $('.time_select').val();
            addBranch(id,text,time);
        }else{
            alert('请选择一个协会');
        }
    });

    //点击发送
    $('.wrap').on('click','.send_btn',function () {
        //批量推首
        var branch_id = all.map(function (vo) {
            return vo.id;
        });
        if(0 == branch_id.length){
            return alert('请至少添加一个要推首的协会');
        }
        $.ajax({
            type: "POST",
            url: "<?php echo web_url('branch/top/save'); ?>",
            dataType: "json",
            data:{id:art_id,branch_id:branch_id},
            success: function (data) {
                if(200 == data.code) {
                    if(confirm('添加成功，是否前往查看')){
                        parent.window.location = "<?php echo web_url('/branch/art/putrecord'); ?>";
                    }else{
                        parent.closeI();
                    }
                }else{
                    alert(data.desc);
                }
            },
            error: function (data) {
                alert('网络不稳定，请稍后再试');
            }
        })
//        parent.closeI();
//        parent.noPut($(".reason").val());
    });

    $('.wrap').on('click','.cancel_btn',function () {
        parent.closeI();
    });

    //del_btn
    $('.wrap').on('click','.del_btn',function () {
        delBranch($(this).attr('the_id'));
    });

    //添加一个分会到数组
    function addBranch(id,title,time) {
        var temp = {id:id};
        if(!in_array(temp,all)){
            all.push(temp);
            //添加对应dom
            var dom = '<p><span>'+title+'</span><a class="del_btn" the_id="'+id+'">删除</a></p>';
            $('.data_list').append(dom);
        }
    }

    //删除一个
    function delBranch(id) {
        var temp = {id:id};
        var i = array_search(temp,all);
        if(false !== i){
            all.splice(i,1);
            //删除对应dom
            $('.data_list a[the_id='+id+']').parent().remove();
        }
    }

    //转字符串
    function toString($data) {
        if('object' == typeof $data){
            return JSON.stringify($data);
        }else if('array' == typeof $data){
            return $data.join('<<<lv_delimiter>>>');
        }else{
            return $data+'';
        }
    }

    function in_array(search,array){
        search = toString(search);
        for(var i in array){
            if(toString(array[i]) == search){
                return true;
            }
        }
        return false;
    }
    
    function array_search(search,array){
        search = toString(search);
        for(var i in array){
            if(toString(array[i]) == search){
                return i;
            }
        }
        return false;
    }


</script>
</body>
</html>
