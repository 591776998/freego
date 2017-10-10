<?php
namespace app\index\controller;
use app\index\model as model;
use think\Controller;
use think\Request;
class Web extends Controller
{
    protected $beforeActionList = [
        'webinit' =>  ['except'=>'errorHtml,getArtType,getAddressFullName']
    ];
    protected $param  = [];

    protected function webinit(){
        $this->param = Request::instance()->param();

        $this->view->replace([
            '__PUBLIC__'    =>  config('static_path')
        ]);

        $css = '        
        /*************** 顶部 *******************/
        .top-fixed-download{
            height: 64px;
            background: hsla(0,0%,100%,0.9);
        }
        .top-fixed-download .title{
            height: 64px;
            line-height: 64px;
            display: flex;
            flex-direction: row;
        }
        .top-fixed-download .title p{
            flex: 1;
            overflow: hidden;
        }
        .top-fixed-download .download_btn{
            margin-top: 15px;
            margin-right: 10px;
            height: 34px;
            line-height: 14px;
            padding: 10px;
            border: 1px solid #666;
            border-radius: 8px;
            width: 80px;
        }
        /************** 顶部结束 *****************/';

        //logo
        $logo_url = '/static/img/logo.png';
        $this->assign('logo_url',$logo_url);
        $this->assign('title',$logo_url);
        $this->assign('web_css',$css);
        $this->assign('is_app',isset($this->param['is_app'])?$this->param['is_app']:'');
        $this->assign('token',isset($this->param['token'])?$this->param['token']:'');
        $this->assign('is_web',isset($this->param['is_web'])?$this->param['is_web']:'102');
        $this->assign('baseUrl',config('request_base_url'));

//        debug(APP_PATH.'web_config.php');
        \Think\Config::load(APP_PATH.'web_config.php');

    }

    //地点的定位地点选择页面
    public function addresslist(){
        $check = checkModel($this->param);
        if(!$check->index('id',['number','r','min:0'])){
            return $this->errorHtml('错误的ID');
        }

        $data = model('place')->where('id',$this->param['id'])->find();
        $park = model('placePark')->where('place_id',$this->param['id'])->select();

        $this->assign('data',$data);
        $this->assign('park',$park);
        $this->assign('title','选择目的地');
        return $this->fetch();
    }

    public function mapwrap(){
        $check = checkModel($this->param);
        $this->assign('url',$this->param['url']);
        $this->assign('title','导航');
        return $this->fetch();
    }

    //沿途玩乐
    public function ontheway(){
        $check = checkModel($this->param);
        $lat = $check->param('lat','0');
        $lng = $check->param('lng','0');
        $location_name = $check->has('location_name')?$check->param('location_name'):'';
        $location_value = $check->has('location_value')?$check->param('location_value'):'';
        if(!$location_name &&$check->has('lat') && $check->has('lng')){
            $lo = model('address','service')->getLocationV2($check->param('lat'),$check->param('lng'));
            if('200' == $lo['code']){
                $location_name = $lo['data']['location_name'];
                $location_value = $lo['data']['location_value'];
            }
        }

        //判断是否是县一级，如果是，就重设为市一级
        if('t_code' == $location_name){
            $res = model('addressTown')->where('t_code',$location_value)->find();
            if($res){
                $location_name  = 'c_code';
                $location_value = $res['c_code'];
            }
        }

        if(!$location_name || !$location_value){
            $location_name = 'c_code';
            $location_value = '510100';
        }
        $l = model('address','service')->info( $location_name, $location_value);

        $chanel = model('Chanel','service')->index( 102);
        $this->assign('chanel',$chanel['data']);

        $art_101 = model('Art','service')->index([], 101, 0, 0, 0, '', 0,  0, 'hot',  0, 1, 10, 0,  101,  0,  0,$location_name,$location_value);
        $this->assign('art_101',$art_101);

        $place_101 = model('place','service')->index([], '', 101, 'hot', 0,  0, 0, 1, 10, 0,$location_name,$location_value);
        $this->assign('place_101',$place_101);

        $place_103 = model('place','service')->index([],'',103,'hot', 0, 0, 0, 1, 10,0,$location_name,$location_value);
        $this->assign('place_103',$place_103);

        $place_102 = model('place','service')->index([],'',102,'hot', 0, 0, 0, 1, 10,0,$location_name,$location_value);
        $this->assign('place_102',$place_102);

        $art_103 = model('Art','service')->index([], 103, 0, 0, 0, '',0,0,'hot',0,1,3,$location_name,$location_value);
        $this->assign('art_103',$art_103);

        $output = [];
        $this->assign('location_name',$location_name);
        $this->assign('location_value',$location_value);
        $this->assign('lat',$lat);
        $this->assign('lng',$lng);
        $this->assign('data',$output);
        $this->assign('info',$l);
        $this->assign('address_select_url', web_url('addressselect',['location_name'=>$location_name,'location_value'=>$location_value,'token'=>$check->param('token',''),'is_web'=>'101']));
        $this->assign('title','沿途玩乐');
        return $this->fetch();
    }

    //地点选择页面
    public function addressselect(){
        $check = checkModel($this->param);

        //1.获取地址数据
        $data = model('AddressProvince')->order('first_char')->select();
        $arr = ['A'=>[],'B'=>[],'C'=>[],'D'=>[],'E'=>[],'F'=>[],'G'=>[],'H'=>[],'I'=>[],'J'=>[],'K'=>[],'L'=>[],'M'=>[],'N'=>[],'O'=>[],'P'=>[],'Q'=>[],'R'=>[],'S'=>[],'T'=>[],'U'=>[],'V'=>[],'W'=>[],'X'=>[],'Y'=>[],'Z'=>[]];
        if($data){
            foreach ($data as $k => $v){
                $arr[$v['first_char']][] = $v;
            }
        }
        $all = [];
        if($data){
            $p = array_map(function($d){return $d['p_code'];},$data);
            $all_data = model('AddressCity')->where('p_code','in',$p)->select();
            foreach ($all_data as $k => $v){
                $v['a_url'] = web_url('ontheway',['location_name'=>'c_code','location_value'=>$v['c_code'],'token'=>$check->param('token',''),'isindex'=>'101']);
                $all[$v['p_code']][$v['c_code']] = $v;
            }
        }

        $this->assign('data', $arr);
        $this->assign('all_json', json_encode($all));
        $this->assign('title','选择地点');
        return $this->fetch();
    }

    //座驾选择
    public function carselect(){
        $check = checkModel($this->param);
        $brand = model('car_brand')->select();
        $arr = ['A'=>[],'B'=>[],'C'=>[],'D'=>[],'E'=>[],'F'=>[],'G'=>[],'H'=>[],'I'=>[],'J'=>[],'K'=>[],'L'=>[],'M'=>[],'N'=>[],'O'=>[],'P'=>[],'Q'=>[],'R'=>[],'S'=>[],'T'=>[],'U'=>[],'V'=>[],'W'=>[],'X'=>[],'Y'=>[],'Z'=>[]];
        if($brand){
            foreach ($brand as $k => $v){
                $arr[$v['first_char']][] = $v;
            }
        }

        $all_json = model('car_brand')->with('sub')->select();
        $temp = [];
        foreach ($all_json as $k => $v){
            $temp[$v['id']] = $v;
        }

        if($check->has('h')){
            $this->assign('h',$check->param('h'));
        }else{
            $this->assign('h',0);
        }
        $this->assign('all_json',json_encode($temp));
        $this->assign('data',$arr);
        $this->assign('title','选择品牌');
        return $this->fetch();
    }

    //协会选择
    public function assocmullist(){
        $check = checkModel($this->param);

        $data = model('Association')->with(['sub'=>function($query){
            $query->with('sub');
        }])->where('p_id','0')->select();

        if($check->has('h')){
            $this->assign('h',$check->param('h'));
        }else{
            $this->assign('h',0);
        }
        $this->assign('data',$data);
        $this->assign('title','协会列表');
        return $this->fetch();
    }

    //文章详情页面
    public function artContent(){
        $check = checkModel($this->param);
        
        if(!$check->index('id',['number','r','min:0'])){
            //响应一个错误的web页面
            return $this->errorHtml('没有获取到文章');
        }

        $art = model('Art','service');
        $result = $art->artContent($check->param('id',0));
        if($this->isError($result)){
            return $this->errorHtml($result['desc']);
        }else{
            return $result['data']['content'];
        }
    }

    //地点3级选择列表
    public function addressmullist(){
        $check = checkModel($this->param);

        $res = model('Address','service')->index(103);

        if($check->has('h')){
            $this->assign('h',$check->param('h'));
        }else{
            $this->assign('h',0);
        }
        $this->assign('data',$res['data']);
        $this->assign('title','选择地点');
        return $this->fetch();
    }

    //论坛选择
    public function forummulist(){
        $check = checkModel($this->param);

        $data = model('Forum')->with(['sub'=>function($query){
            $query->with('sub');
        }])->where('p_id','0')->order('order_weight desc')->order('id asc')->select();
        //添加热门
        array_unshift($data,$this->hot());
        $this->assign('data',$data);
        $this->assign('title','选择论坛');
        return $this->fetch();
    }

    //获取热门论坛
    protected function hot(){
        return [
            'id'                   => '0',
            'title'                => '热门',
            'del_time'             => '0',
            'c_time'               => '0',
            'order_weight'         => '0',
            'status'               => '101',
            'p_id'                 => '0',
            'is_forum'             => '102',
            'location_name'        => '',
            'location_value'       => '',
            't_code'               => '0',
            'c_code'               => '0',
            'p_code'               => '0',
            'city'                 => '0',
            's_code'               => '0',
            'is_hot'               => '101',
            'sub'                  => model('Forum')->with(['sub'])->where('is_hot',101)->select()
        ];
    }

    //论坛列表
    public function forum(){
        $check = checkModel($this->param);

        $forum = model('forum');

        //如果没论坛ID，且有locatiion信息，就用location_信息来获取论坛
        $forum_id = $check->param('forum_id',0);
        if(!$forum_id && $check->has('location_name') && $check->param('location_name','')){
            //1.获取对应的市或省或全球总会
            $location = model('location','service')->read($check->param('location_name'), $check->param('location_value'));
            if($location){
                if($location['c_code']){
                    $forum->where('location_name','c_code')->where('location_value',$location['c_code']);
                }elseif ($location['p_code']){
                    $forum->where('location_name','p_code')->where('location_value',$location['p_code']);
                }else{
                    $forum->where('id',0);
                }


            }
        }elseif ($forum_id){
            $forum->where('id',$forum_id);
        }else{
            $forum->where('id',0);
        }

        $forum = $forum->find();
        if(!$forum){
            $forum = model('forum','service')->read(0);
        }

        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);
        if($forum && $now_user){
            model('user')->where('id',$now_user['id'])->update([
                'now_forum_id'  => $forum['id']
            ]);
        }

        $this->assign('index_id',$check->param('index',1));
        $this->assign('forum',$forum);
        $this->assign('forum_id',$forum_id);
        $this->assign('title',isset($forum['title'])?$forum['title']:'论坛');
        return $this->fetch();
    }

    //文章和服务列表
    public function table(){
        $check          = checkModel($this->param);
        $type           = $check->param('type',103);
        $sub_type       = $check->param('sub_type',101);
        $location_name  = $check->param('location_name','');
        $location_value = $check->param('location_value','');
        $lat            = $check->param('lat','0');
        $lng            = $check->param('lng','0');
        $coordtype      = $check->param('coordtype','gcj02ll');
        $location_str   = '当地';

        $location_type = 0;
        $geo = model('geo', 'service');
        if(!$location_name && $lat && $lng) {
            $location_type = 1;
            $geo->sl([
                'lat'   => $lat,
                'lng'   => $lng
            ]);
        }elseif ($location_name && 't_code' == $location_name) {
            $location_type =2;
            $geo->sl([
                'location_name' => $location_name,
                'location_value'=> $location_value
            ]);
        }
        $location_info = $geo->gl();
        if($location_info && isset($location_info['code']) && (200 == $location_info['code'])){
            $l_data = current($location_info['data']);
            if($location_type){
                if(isset($l_data['c_code'])){
                    $location_name  = 'c_code';
                    $location_value = $l_data['c_code'];
                    $location_str   = $l_data['c_name'];
                }
            }
        }

//        if(!$location_name && $lat && $lng){
//            $location_info = model('address','service')->getLocationV2($lat,$lng,$coordtype);
//            if(200 == $location_info['code']){
//                $location_name  = $location_info['data']['location_name'];
//                $location_value = $location_info['data']['location_value'];
//                if('t_code' == $location_name && $location_info['data']['c_code']){
//                    $location_name  = 'c_code';
//                    $location_value = $location_info['data']['c_code'];
//                    $location_str   = $location_info['data']['city_title'];
//                }
//            }
//        }elseif ($location_name){
//            //
//            $lo = model('location','service')->read($location_name, $location_value);
//            if($lo){
//                $location_str = $lo['location_str'];
//            }
//        }

        $address = model('Address','service')->index(103);
        $tag     = model('ArtTag','service')->index();
        $order   = model('ArtTag','service')->order();
        $chanel  = model('Chanel','service')->index( 103);

        //根据type和sub_type获取当前的
        $type_title = '';
        foreach ($chanel['data'] as $c){
            if(isset($c['jump']) && isset($c['jump']['type']) && isset($c['jump']['sub_type']) && $c['jump']['type'] == $type && $c['jump']['sub_type'] == $sub_type){
                $type_title = $c['title'];
            }
        }

        $this->assign('type_title',$type_title);
        $this->assign('type',$type);
        $this->assign('sub_type',$sub_type);
        $this->assign('location_name',$location_name);
        $this->assign('location_value',$location_value);
        $this->assign('location_str',$location_str);
        $this->assign('lat',$lat);
        $this->assign('lng',$lng);
        $this->assign('chanel',$chanel['data']);
        $this->assign('tag',$tag['data']);
        $this->assign('order',$order['data']);
        $this->assign('address',json_encode($address['data']));
        $this->assign('title','服务列表');
        return $this->fetch();
    }

    //文章详情
    public function art(){
        $check = checkModel($this->param);
        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);
        $res = model('art','service')->read($now_user,$check->param('id',0));
        if(!$res || !isset($res['code']) || 200 != $res['code']){
            return $this->errorHtml($res['desc']);
        }

        $this->assign('content',array_map(function ($vo){
            if(102 == $vo['para_type'] || 107 == $vo['para_type']){
                if(!isset($vo['img_url']))$vo['img_url'] = '';
            }
            return $vo;
        },$res['data']['content']));
        unset($res['data']['content']);
        $this->assign('data',$res['data']);
        if($now_user && ($now_user['id'] == $res['data']['user_id'])){
            $is_my = '101';
        }else{
            $is_my = '102';
        }
        $this->assign('is_my',$is_my);
        $this->assign('is_editor',$check->param('editor',102));


        if(102 == $check->param('need_delete_table_cache',101)){
            $this->assign('need_delete_table_cache',102);
        }else{
            $this->assign('need_delete_table_cache',101);
        }

        $this->assign('is_ex',$check->param('is_ex',102));
        if(102 == $res['data']['type']){
            return $this->fetch('activity');
        }
        $this->assign('index',0);
        $this->assign('title',isset($res['data']['title'])?$res['data']['title']:'');
        return $this->fetch();
    }

    //地点详情
    public function place(){
        $check = checkModel($this->param);
        $from  = $check->param('from',0);
        $from_user  = $check->param('from_user',0);

        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);

        $res = model('Place','service')->read($now_user,$check->param('id',0),$from,$from_user);
        if(200 != $res['code']){
            return $this->errorHtml('没找到该服务页面！');
        }

        if($res['data']['img']){
            array_unshift($res['data']['album'],$res['data']['img']);
        }

        if(102 == $check->param('need_delete_table_cache',101)){
            $this->assign('need_delete_table_cache',102);
        }else{
            $this->assign('need_delete_table_cache',101);
        }

        $this->assign('data',$res['data']);
        $this->assign('title',isset($res['data']['title'])?$res['data']['title']:'');
        return $this->fetch();
    }

    //服务/商品详情
    public function services(){
        $check = checkModel($this->param);

        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);

        $res = model('Services','service')->read($now_user,$check->param('id',0));
        if(200 != $res['code']){
            return $this->errorHtml('没找到该服务页面！');
        }

        $this->assign('data',$res['data']);

        $this->assign('title',isset($res['data']['title'])?$res['data']['title']:'');
        return $this->fetch();
    }

    //精彩影像的地址选择
    public function addimgselect(){
        $check = checkModel($this->param);

        $res = model('Address','service')->index(101);

        $this->assign('data',$res['data']);
        $this->assign('title','精彩影像');
        return $this->fetch();
    }

    public function addimgselectsub(){
        $check = checkModel($this->param);

        //根据
        $location_name  = $check->param('location_name','');
        $location_value = $check->param('location_value','');

        $res = model('Address','service')->getSub($location_name,$location_value,1);

        $this->assign('data',$res);
        $this->assign('title','精彩影像');
        return $this->fetch();
    }

    //精彩影像
    public function photo(){
        $check = checkModel($this->param);

        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);
        $now_user_id = isset($now_user['id'])?$now_user['id']:-1;

        $location_name  = $check->param('location_name','');
        $location_value = $check->param('location_value','');
        $p          = $check->param('page',1);
        $ps         = $check->param('page_size',10);

//        $data = model('WonderfulImg')->with(['art','img','user','like'=>function($query)use($now_user_id){
//            return $query->where('user_id',$now_user_id);
//        },])->where(function ($query)use($location_name,$location_value){
//            return $query->where($location_name,$location_value)->whereOr(function ($query)use($location_name,$location_value){
//                $query->where('location_name',$location_name)->where('location_value',$location_value);
//            });
//        })->where('status',101)->page($p,$ps)->select();
//
//        $data = array_map(function($d){
//            if(!isset($d['img_url']))$d['img_url'] = '';
//            if('' == $d['location_str'])$d['location_str'] = $this->getAddressFullName($d['location_name'], $d['location_value']);
//            return $d;
//        },$data);

        $data = model('photo','service')->index($now_user_id,$location_name,$location_value,$p,$ps);

        $this->assign('location_name',$location_name);
        $this->assign('location_value',$location_value);
        $this->assign('data',$data['data']);
        $this->assign('title','精彩影像');
        return $this->fetch();
    }

    //我的积分
    public function points(){
        $check = checkModel($this->param);

        $this->assign('title','积分说明');
        return $this->fetch();
    }

    //注册条款
    public function registerPage(){

        $this->assign('data','注册协议内容');
        $this->assign('title','注册协议');
        return $this->fetch();
    }

    //关于我们
    public function aboutus(){
        $check = checkModel($this->param);
        $version = $check->param('version','');
        $res = '无论你在哪里 只要你想要 就一定能实现FreeGo自由行是打造自驾旅游全域生态圈的互联网运营公司,以自驾旅游为载体，联动当地景区、酒店、营地、山庄、民宿客栈，乡村旅游，房车露营地等，以赛事，活动等方式，打造全域生态经济圈，拉动税收，助力自驾扶贫宗旨。发展汽车服务全产业链，围绕汽车配套服务全产业链，从汽车购买，销售，改装，美容，维修等，借力“车辆云数据”开发“后备箱集市”等配套产品，拉动本土旅游经济快速增长，从而实现共赢！';

        $this->assign('data',$res);
        $this->assign('title','关于我们');
        return $this->fetch();
    }

    //错误页面
    public function errorPage(){
        $check = checkModel($this->param);
        

        
        $this->assign('title','错误');
        return APP_PATH . 'index' . DS . 'view'.DS.'web'.DS.'errorPage.html';
        return $this->fetch();
    }

    public function test(){
        $check = checkModel($this->param);
        
        
        return $this->fetch();
    }


    //订单确认页面
    public function confirmorder(){
        $check = checkModel($this->param);
        $c = $this->param;
        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);
        if(!$now_user){
            return $this->errorHtml('请先登录，再进行下一步操作！');
        }
        if(!isset($c['type'])){
            return $this->errorHtml('错误的订单类型！');
        }
        if(103 == $c['type']){
            return $this->confirmrubcar($now_user,$c);
        }else if(101 == $c['type']){
            //商品
            $o = model('services','service')->read($now_user,$c['services_id']);
            if(200 != $o['code']){
                return $this->errorHtml($o['desc']);
            }
            $o = $o['data'];
        }else if(102 == $c['type']){
            //活动
            $o = model('art')->where('id',$check->param('art_id'))->with(['plan','act'])->find();
        }else{
            return $this->errorHtml('不支持该类型的订单！');
        }

        //1.是否至少选择了1个必选项
        $p = $o['plan'];
        $p = array_map(function ($plan_item)use($c){
            foreach ($c['plan'] as $v){
                if($v['id'] == $plan_item['id']){
                    //匹配到了
                    return array_merge($v,$plan_item->toArray());
                }
            }
            //空了
            $plan_item['num'] = 0;
            return $plan_item->toArray();
        },$p);

        $bixuan = 0;
        $bixaun_count = 0;
        $total_price = 0;
        foreach ($p as $k => $v){
            //99蹭车消费 100扫码消费 101普通套餐 102附加必买套餐 103附加选购套餐
            if(101 == $v['plan_type'] && $v['num']){
                $bixuan += $v['num'];
                $bixaun_count += 1;
            }elseif (102 == $v['plan_type']){
                if(0 >= $v['num']){
                    return $this->errorHtml('您至少需要选择1个'.$v['title'].'！');
                }
            }
            if(0 >= $v['num'])unset($p[$k]);
            //根据传入的参数，计算总价
            $total_price += $v['num'] * $v['fact_price'];
        }
        if(1 < $bixaun_count){
            return $this->errorHtml('您只能选择1种单选套餐！');
        }elseif(0 >= $bixuan) {
            return $this->errorHtml('您尚未选择套餐！');
        }

        $this->assign('total_price',$total_price);
        $this->assign('plan',$p);
        $this->assign('c',$c);
        $this->assign('title','支付');
        return $this->fetch();
    }

    //某活动的顺风车列表
    public function seatlist(){
        $check = checkModel($this->param);

        $p = $check->param('page',1);
        $ps = $check->param('page_size',10);

        $joined = false;
        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);
        if($now_user){
            if(model('artJoin')->where('user_id',$now_user['id'])->where('status',101)->find()){
                $joined = true;
            }
        }

        $this->assign('main_id',$check->param('main_id',0));
        $this->assign('now_user_id',$now_user['id']);
        $this->assign('joined',$joined?'101':'102');

        $this->assign('title','可蹭车辆');
        return $this->fetch();
    }

    //顺风车列表，数据
    public function seatdata(){
        $check = checkModel($this->param);

        $p = $check->param('page',1);
        $ps = $check->param('page_size',10);

        $res = model('artJoin')
            ->with(['user','carmodel'])
            ->where('main_id',$check->param('main_id',0))
            ->where('status',101)
//            ->where('car_model_id','<>','0')
            ->page($p,$ps)->select();

        $res = array_map(function($vo){
            $vo = $vo->toArray();
            $vo['car_name'] = (isset($vo['pre_name'])?$vo['pre_name']:'').$vo['car_name'];
            return $vo;
        },$res);
        return success($res);
    }

    //蹭车订单确认页面
    public function confirmrubcar($user,$input){
        if(!isset($input['join_id'])){
            return $this->errorHtml('错误的订单，请稍后再试！');
        }

        $join = model('artJoin')->where('id',$input['join_id'])->where('status',101)->find();
        if(!$join){
            return $this->errorHtml('未找到该记录！');
        }
        if(!isset($input['num']))$input['num'] = 1;
        if(!isset($input['art_id']))$input['art_id'] = $join['main_id'];

        if($input['num'] + $join['use_seats'] > $join['total_seats']){
            return $this->errorHtml('剩余座位数不足！');
        }
        //如果我已经申请蹭了别人的车，就拒绝
        //30分钟内，我是否已经预约了别人的车
        $my_rub = model('artRubCar')->where('art_id',$input['art_id'])->where('user_id',$user['id'])->where(function ($query) {
            $query->where(function ($sub_query){
                $sub_query->where('status',101);
            })->whereOr(function ($sub_query){
                $sub_query->where('status',102)->where('c_time','<',floor(THINK_START_TIME)-1800);
            });
        })->find();
        if($my_rub){
            if(101 == $my_rub['status']){
                if($my_rub['join_id'] == $input['join_id']){
                    $error = '您已经蹭了该车了，请不要重复申请！';
                }else{
                    $error = '您已经蹭了别人的车！';
                }
            }elseif (102 == $my_rub['status']){
                if($my_rub['join_id'] == $input['join_id']){
                    $error = '您已经提交了申请，请不要重复提交！';
                }else{
                    $error = '您已经申请了别人的车，要更换蹭车请先取消，或等待一段时间！';
                }
            }else{
                $error = '不满足蹭车条件，请稍后再试！';
            }
            return $this->errorHtml($error);
        }
        //现在，车主有位置，我也没有预约，可以显示蹭车页面了
        $this->assign('join',$join);
        $this->assign('user',$user);
        $this->assign('title','申请蹭车');
        return $this->fetch('confirmrubcar');
    }

    public function wallet(){
        $check = checkModel($this->param);

        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);
        if(!$now_user){
            $money = 0;
            $point = 0;
        }else{
            $money = $now_user['money'];
            $point = $now_user['point'];
        }

        $this->assign('money',$money);
        $this->assign('point',$point);
        $this->assign('title','钱包');
        return $this->fetch();
    }

    //订单详情
    public function order(){
        $check = checkModel($this->param);
        $now_user = model('User','service')->getUser([], ['token'=>$check->param('token')]);
        $res = model('order','service')->read($now_user,$check->param('id',0));

        $this->assign('user',$now_user);
        $this->assign('data',$res);
        $this->assign('title','订单详情');
        return $this->fetch();
    }

    public function errorHtml($content){
        
        $this->assign('content',$content);
        $this->assign('title','');
        return $this->fetch('errorHtml');
    }


    /************************ 私有的 **************************************/
    protected function getAddressFullName($name,$value){
        return model('Address','service')->getFullName($name, $value);
    }

    //返回文章类型
    protected function getArtType($type){
        //文章类型 101游记 102活动 103提问 104动态 105车主认证
        switch ($type){
            case '101':
                return '游记';
            break;
            case '102':
                return '活动';
                break;
            case '103':
                return '问答';
                break;
            case '104':
                return '动态';
                break;
            case '105':
                return '车主认证';
                break;
            default:
                return '文章';
        }
    }
//
//    //响应错误的web页面
//    protected function error( $content = ''){
//        $head = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no"><title>'.$content.'</title><style type="text/css">
//			body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td { margin:0; padding:0; }
//body { background:#fff; color:#555; font-size:14px; font-family: Verdana, Arial, Helvetica, sans-serif; }
//td,th,caption { font-size:14px; }
//h1, h2, h3, h4, h5, h6 { font-weight:normal; font-size:100%; }
//address, caption, cite, code, dfn, em, strong, th, var { font-style:normal; font-weight:normal;}
//a { color:#555; text-decoration:none; }
//a:hover { text-decoration:underline; }
//img { border:none; }
//ol,ul,li { list-style:none; }
//input, textarea, select, button { font:14px Verdana,Helvetica,Arial,sans-serif; }
//table { border-collapse:collapse; }
//html {overflow-y: scroll;}
///* css common */
//.clearfix:after {content: "."; display: block; height:0; clear:both; visibility: hidden;}
//.clearfix { *zoom:1; }
//			body,html{
//				background: white;
//			}
//			.row{
//				height: 163px;padding: 50px 0 0 0;text-align: center;
//			}
//			.row img{
//				width: 100%;
//			}
//			.row p{
//
//				margin-top: 25px;font-size: 15px;
//			}
//		</style></head><body>';
//        $foot = '</body></html>';
//        return $head.$content.$foot;
//    }

}