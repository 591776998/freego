<?php
namespace app\index\service;
class User extends \app\common\service\Base
{
    protected $default_head_img_id = 1;
    protected $default_head_img_url = 'static/img/logo.png';

    //创建api会话 101手机号登录 102微信 103微博
    public function login( $type, $login_info){

        //1.检查登录信息
        $result = $this->loginCheck( $type, $login_info);
        if($this->isError( $result)){
            return $this->error( $result['data'], $result['desc']);
        }

        //2.生成新token
        $result_token = $this->flushToken($result['data']['id']);
        if($this->isError( $result_token)){
            return $this->error( $result_token['data'], $result_token['desc']);
        }

        //2.返回数据
        return $this->success(['id'=>$result['data']['id'],'token'=>$result_token['data']['token']]);
    }

    //注销API会话
    public function logout($user_data,$id){
        //取消用户的token字段
        if(!$user_data){
            return $this->error(['desc'=>'您尚未登录！']);
        }
        if($user_data['id'] != $id){
            return $this->error(['desc'=>'注销登录失败！']);
        }
        $result_token = $this->flushToken($id);
        if($this->isError( $result_token)){
            return $this->error(['desc'=>'注销登录会话失败！']);
        }
        return $this->success(['id'=>$id]);
    }

    //检查某手机号是否被用户注册
    public function isRegister( $phone,$pre = '+86'){
        if(model('userLogin')->where('phone',$phone)->where('pre',$pre)->find()){
            return true;
        }
        return false;
    }

    //获取用户模型
    public function getUser($now_user = [], $where, $order = false){
        return $this->model($now_user, $where, $order);
    }

    //获取详细用户模型
    public function fullModel($id, $my_id = 0){
        $user_model = model('User')->with([
            'headImg',
            'coverImg',
            'footprintNum',
            'car',
            'club',
            'uimUser',
            'honour'
        ]);
        $need_follow_info = ($my_id != $id)?true:false;
        if($need_follow_info){
            $user_model->with(['follow'=>function($query)use($my_id){
                return $query->where('user_id',$my_id);
            },'isfollow'=>$isfollow = function ($query)use($my_id){
                return $query->where('b_user_id',$my_id);
            }]);
        }

        $user = $user_model->where('id',$id)->find();
        if($user){
            $user = $user->toArray();
            if(!$need_follow_info){
                $user['follow'] = (object)null;
                $user['isfollow'] = (object)null;
            }

//            $user['footprintDesc'] = '去过'.($user['footprint_num']['s_num']?:0).'个国家，'.($user['footprint_num']['c_num']?:0).'个城市，'.($user['footprint_num']['place_num']?:0).'个地点';
            $user['footprintDesc'] = '去过'.($user['footprint_num']['s_num']?:0).'个国家，'.($user['footprint_num']['c_num']?:0).'个城市，'.($user['dynamic_count']?:0).'个地点';

            $user['footprintCountry'] = $this->footprintCountry($id,1,10);
            $user['publishDesc'] = '发布'.$user['travels_count'].'篇游记，'.$user['dynamic_count'].'足迹';

            //给可能不存在的字段赋值
            if(!isset($user['head_img_url']))$user['head_img_url'] = img_show($this->default_head_img_url);
            if(!isset($user['cover_img_url']))$user['cover_img_url'] = img_show('static/img/beijing.png');
            if(isset($user['car'])){
                $car = $user['car'];
                if(count($car) < 3){
                    $temp = [(object)null,(object)null,(object)null];
                    foreach ($car as $k => $v){
                        $temp[$v['order_weight']] = $v;
                    }
                    unset($user['car']);
                    $car = $temp;
                }
                $user['car'] = $car;
            }
            if(!$user['follow'] || !$user['isfollow']){
                $user['is_friend'] = '102';
            }else{
                $user['is_friend'] = '101';
            }

            if(!$user['uim_user'] || empty($user['uim_user'])){
                $user['uim_user'] = $this->getUmuser($user);
            }
            if(!isset($user['honor']))$user['honor'] = '';
            if(!isset($user['show_home_page'])){
                if(1 < $user['level']){
                    $user['show_home_page'] = '101';
                }else{
                    $user['show_home_page'] = '102';
                }
            }
            unset($user['footprint_num']);
        }
        return $user;
    }

    //create um token
    protected function createUmToken($id){
        return md5('shidian_'.$id.'_'.mt_rand(1,9999999).floor(THINK_START_TIME));
    }

    //添加一个用户模型
    public function addUser( $data){
        //1.添加用户 2.添加登录信息
        $user_model = new \app\index\model\User();
        if($user_model->save(
            [
            'nick_name'     => isset($data['nick_name'])?$data['nick_name']:'',
            'head_img_id'   => isset($data['head_img_id'])?$data['head_img_id']:'86',
            'cover_img_id'  => '10',
            'e_mail'        => isset($data['e_mail'])?$data['e_mail']:'',
            'status'        => '103',
            'level'         => '1',
            'c_time'        => THINK_START_TIME,
            'login_time'    => '0',
            'del_time'      => '0',
            'login_phone'  => isset($data['phone'])?$data['phone']:'',
            'login_pre_phone'  => isset($data['pre'])?$data['pre']:'+86',
            ]
        )){
            $userLogin_model = new \app\index\model\UserLogin();
            if($userLogin_model->save(
                [
                    'id'            => $user_model->id,
                    'pre'           => isset($data['pre'])?$data['pre']:'+86',
                    'phone'         => isset($data['phone'])?$data['phone']:'',
                    'pwd'           => isset($data['pwd'])?$data['pwd']:'',
                    'login_type'    => isset($data['login_type'])?$data['login_type']:'101',
                    'del_time'      => '0',
                    'token'         => $this->createToken( THINK_START_TIME.mt_rand(1,99999999)),
                ]
            )){
                $user_model['uim_user'] = $this->getUmuser($user_model);

                return $this->success( ['id'=>$user_model->id]);
            }else{
                return $this->error( ['desc'=>'保存用户信息失败！']);
            }
        }else{
            return $this->error( ['desc'=>'注册用户失败！']);
        }
    }

    //创建并获取云信模型
    public function getUmuser($user){
        if(!isset($user['uim_user']) || !$user['uim_user'] || empty($user['uim_user'])){
            if(!isset($user['uim_user']))$user['uim_user'] = (object)null;
            $um = $this->localUm($user['id']);
            if(!$um){
                $save_data_uim = [
                    'accid'     => $user['id'],
                    'token'     => $this->createUmToken($user['id']),
                    'name'      => $user['nick_name']
                ];
                if(isset($user['head_img_url']) && $user['head_img_url']){
                    $save_data_uim['icon'] = $user['head_img_url'];
                }else{
                    $header_img_id = isset($user['head_img_id'])?$user['head_img_id']:'86';
                    $header_img_id = $header_img_id?:'86';
                    $icon = model('Image','service')->getUrl($header_img_id);
                    $save_data_uim['icon'] = $icon->url;
                }
                $user_uim = model('netease','service')->request('create',$save_data_uim);
                if($this->isError($user_uim)){
                    //创建云信用户失败
                    if((414 == $user_uim['code']) && ('already register' == $user_uim['desc'])){
                        //更新云信token
                        $net3 = model('netease','service')->request('update',['accid'=>$user['id'],'token'=>$save_data_uim['token']]);
                        if($this->isError($net3)){
                            return $user['uim_user'];
                        }
                    }else{
                        return $user['uim_user'];
                    }
                }

                $save_data_uim['user_id']   = $user['id'];
                $user_uim_res = model('userUim')->data($save_data_uim);
                if(!$user_uim_res->save()){
                    $temp_uim = $this->localUm($user['id']);
                    if($temp_uim){
                        $temp_uim->where('user_id',$user['id'])->update($save_data_uim);
                    }else{
                        return $user['uim_user'];
                    }
                }
                return $save_data_uim;
            }else{
                return $um;
            }
        }else{
            return $user['uim_user'];
        }
    }

    //返回本地云信模型
    protected function localUm($id){
        return model('userUim')->where('user_id',$id)->find();
    }

    //附近的用户
    public function nearby($now_user, $sex, $lat, $lng, $range = 1000000, $page = 1, $pagesize = 10){
        $now_user_id = isset($now_user['id'])?$now_user['id']:'-1';
        $is_lat_lng = 0;
        if($range && $lat && $lng){
            $is_lat_lng = 2;
        }elseif ($lat && $lng){
            $is_lat_lng = 1;
        }

        $model = model('userLocation')->field(true);
        if($is_lat_lng){
            $model ->field("getDistance('$lat','$lng',`lat`,`lng`) as distance");
        }
        if($sex){
            $model->where('sex',$sex);
        }
        if($now_user){
            $model->where('id','<>',$now_user_id);
        }

        if($is_lat_lng){
            //经纬度的限制，需要根据当前经纬度和范围来计算
            $lat_lng_limit = getAround($lat,$lng,$range);
            $model->where('lat','BETWEEN',[$lat_lng_limit['minlat'],$lat_lng_limit['maxlat']]);
            //限制经度
            if($lat_lng_limit['maxlng'] > 180){
                $model->where(function ($query)use($lat_lng_limit){
                    $query->where('lng','>',$lat_lng_limit['minlng'])->whereOr('lng','<',($lat_lng_limit['maxlng']-360));
                });
            }elseif ($lat_lng_limit['minlng'] < -180){
                $model->where(function ($query)use($lat_lng_limit){
                    $query->where('lng','>',($lat_lng_limit['minlng']+360))->whereOr('lng','<',$lat_lng_limit['maxlng']);
                });
            }else{
                $model->where('lng','BETWEEN',[$lat_lng_limit['minlng'],$lat_lng_limit['maxlng']]);
            }

            $model->having('distance < '.$range)->order('distance asc')->order('id asc');
        }

        $user_location = $model->page($page,$pagesize)->select();

        $user_id_arr = array_map(function ($v){return $v['id'];},$user_location);
        if(!$user_id_arr){
            return [];
        }
        $data = $this->getUser($now_user, $user_id_arr, ['id'=>$user_id_arr]);
        $data = array_map(function ($d)use($user_location){
            $d = $d->toArray();
            foreach ($user_location as $key => $val) {
                if ($d['id'] == $val['id']) {
                    if (isset($val['distance'])) {
                        $d['distance_str'] = humanizedDistance($val['distance']);
                    } else {
                        $d['distance_str'] = '';
                    }
                    break;
                }
            }

            return $d;
        },$data);
        return $data;
    }

    //统计某区域的用户数
    public function count($now_user,$lat,$lng,$range){
        $now_user_id = isset($now_user['id'])?$now_user['id']:'-1';
        $model = model('userLocation')->field(["count(id) as count_num","getDistance('$lat','$lng',lat,lng) as distance"]);

        $is_lat_lng = 0;
        if($range && $lat && $lng){
            $is_lat_lng = 2;
        }elseif ($lat && $lng){
            $is_lat_lng = 1;
        }
        if($is_lat_lng){
            //经纬度的限制，需要根据当前经纬度和范围来计算
            $lat_lng_limit = getAround($lat,$lng,$range);
            $model->where('lat','BETWEEN',[$lat_lng_limit['minlat'],$lat_lng_limit['maxlat']]);
            //限制经度
            if($lat_lng_limit['maxlng'] > 180){
                $model->where(function ($query)use($lat_lng_limit){
                    $query->where('lng','>',$lat_lng_limit['minlng'])->whereOr('lng','<',($lat_lng_limit['maxlng']-360));
                });
            }elseif ($lat_lng_limit['minlng'] < -180){
                $model->where(function ($query)use($lat_lng_limit){
                    $query->where('lng','>',($lat_lng_limit['minlng']+360))->whereOr('lng','<',$lat_lng_limit['maxlng']);
                });
            }else{
                $model->where('lng','BETWEEN',[$lat_lng_limit['minlng'],$lat_lng_limit['maxlng']]);
            }

            $model->having('distance < '.$range)->order('distance asc');
        }

        if($now_user){
            $model->where('id','<>',$now_user_id);
        }
        if($range){
            $model->having('distance < '.$range);
        }
        $data = $model->find();
        return $data['count_num']?$data['count_num']:'0';
    }

    //用户列表
    public function index($now_user, $type = 104,$key = '',$page = 1,$pagesize = 10,$art_id = 0){
        if(105 == $type){
            $user_model = model('UserVisit');
        }elseif(106 == $type) {
            $user_model = model('ArtCompanion');
        }elseif (107 == $type){
            $user_model = model('User');
        }elseif(108 == $type){
            $user_model = model('artJoin');
        }else{
            $user_model = model('UserFollow');
        }
        $user_id = isset($now_user['id'])?$now_user['id']:'0';
        $key = trim($key);

        switch ($type.''){
            case '102':
                $user_id_arr = $user_model
                    ->where('user_id',$user_id)
                    ->order('id desc')
                    ->page($page,$pagesize)
                    ->column('b_user_id');
                break;
            case '103':
                $user_id_arr = $user_model
                    ->where('b_user_id',$user_id)
                    ->order('id desc')
                    ->page($page,$pagesize)
                    ->column('user_id');
                break;
            case '104':
                $user_id_arr = $user_model
                    ->alias('a')
                    ->join('user_follow b','`a`.user_id = `b`.`b_user_id`','LEFT')
                    ->where('a.user_id',$user_id)
                    ->where('`b`.del_time',0)
                    ->where('a.b_user_id','exp','=`b`.`user_id`')
                    ->having('a.b_user_id != '.$user_id)
                    ->order('a.id desc')
                    ->page($page,$pagesize)
                    ->column('a.b_user_id');
                if($key){
                    $user_id_arr = model('User')->where('id','in',$user_id_arr)->where('nick_name|login_phone','like','%'.$key.'%')->column('id');
                }
                break;
            case '105':
                $user_id_arr = $user_model
                    ->distinct(true)
                    ->field('user_id')
                    ->where('target_id',$user_id)
                    ->where('type',101)
                    ->page($page,$pagesize)
                    ->order('id desc')
                    ->column('user_id');
                break;
            case '106':
                $user_id_arr = $user_model
                    ->field('user_id')
                    ->where('main_id',$art_id)
                    ->page($page,$pagesize)
                    ->order('id desc')
                    ->column('user_id');
                break;
            case '107':
                $user_id_arr = $user_model
                    ->field('id')
                    ->where('nick_name|login_phone|live_location_str','like','%'.$key.'%')
                    ->page($page,$pagesize)
                    ->order('id desc')
                    ->column('id');
                break;
            case '108':
                $user_id_arr = $user_model
                    ->field('distinct user_id')
                    ->where('main_id',$art_id)
                    ->where('status',101)
                    ->page($page,$pagesize)
                    ->order('id desc')
                    ->column('user_id');
                break;
            default:
                $user_id_arr = [];
        }

        if($user_id_arr){
            return $this->getUser($now_user, $user_id_arr, ['id'=>$user_id_arr]);
        }else{
            return [];
        }
    }

    //更新
    public function update($id,$data = []){
        if($data) {
            $net_field = [
                'nick_name'     => 'name',
                'head_img_id'   => 'icon',
                'sign'          => 'sign',
                'e_mail'        => 'email',
                'sex'           => 'gender',
                'phone'         => 'mobile',
            ];
            $net_field_keys = array_keys($net_field);

            $net_save_data = [];
            foreach ($data as $k => $v) {
                if (in_array($k, $net_field_keys)) {
                    if ('head_img_id' == $k) {
                        $icon = model('Image', 'service')->getUrl($v);
                        $v = isset($icon->url) ? ($icon->url) : (img_show($this->default_head_img_url));
                    } elseif ('sex' == $k) {
                        if (101 == $v) {
                            $v = '1';
                        } elseif (102 == $v) {
                            $v = '2';
                        } else {
                            $v = '0';
                        }
                    }
                    $net_save_data[$net_field[$k]] = $v;
                }
            }
            if ($net_save_data) {
                $net_save_data['accid'] = $id;
                $net = model('netease', 'service')->request('updateUinfo', $net_save_data);
                if ($this->isError($net)) {
//                    return $this->error(['desc' => '修改失败，请稍后再试！']);
                }
            }

            model('User')->save($data, ['id' => $id]);
        }
        return $this->success(['id'=>$id]);
    }

    //某用户去过的国家数组
    public function footprintCountry($user_id, $p = 1, $ps = 10){
        $sub_sql = model('userFootprint')->field('distinct `s_code`')->where('user_id', $user_id)->buildSql();
        return model('AddressCountry')->with('img')->where('s_code','exp','in '.$sub_sql)->page($p,$ps)->select();
    }

    /************************* 非public ***************************************/
    //登录验证
    public function loginCheck( $type, $data){
        switch ( $type){
            case 101:
                $result = $this->loginCheckByPhone( $data['phone'],isset($data['pre'])?$data['pre']:'+86', $data['pwd']);
                break;
            case 102:
            case 103:
                $result = $this->loginCheckByOnlySign( $data['only_sign']);
                break;
            default:
                return $this->error( ['desc'=>'type error'], '错误的登录类型');
        }
        if($this->isError( $result)){
            return $this->error( $result['data'], $result['desc']);
        }
        return $this->success( ['id'=>$result['data']['id']]);
    }

    //获取用户对象
    protected function model($now_user = [], $where, $order = false){
        $user_id = isset($now_user['id'])?$now_user['id']:'-1';
        $user = model('User')->with([
            'headImg',
            'coverImg',
            'footprintNum',
            'follow'=>(isset($now_user['id'])?(function($query)use($user_id){
                return $query->where('user_id',$user_id);
            }):[]),
            'isfollow'=>(isset($now_user['id'])?(function ($query)use($user_id){
                return $query->where('b_user_id',$user_id);
            }):[]),
            'honour',
            'club'
        ]);
        if($where instanceof \Closure){
            $data = array_map(function($r){
                if(!isset($r['honor']))$r['honor'] = '';
                if(!isset($r['club_title']))$r['club_title'] = '';
                return $r;
            },$user->where($where)->select());

            return $this->setFootprintDesc($data);
        }elseif(is_array($where) && !is_assoc($where)){
            if($order){
                if(is_array($order)){
                    foreach ($order as $k => $v){
                        if(is_array($v)){
                            $user->order('field('.$k.','.implode(',',$v).')');
                        }else{
                            $user->order($k,$v);
                        }
                    }
                }else{
                    $user->order($order.' desc');
                }
            }
            $data = array_map(function($r){
                if(!isset($r['honor']))$r['honor'] = '';
                if(!isset($r['club_title']))$r['club_title'] = '';
                return $r;
            },$user->select( $where));

            return $this->setFootprintDesc($data);
        }else{
            if(is_array($where)){
                foreach ($where as $k => $v){
                    if('token' == $k){
                        //查询出对应的id
                        $user->where('id','exp','='.model('UserLogin')->where($k,$v)->field('id')->limit(1)->buildSql());
                    }else{
                        if(is_array($v)){
                            $user->where($k,$v[0],$v[1]);
                        }else{
                            $user->where($k,$v);
                        }
                    }
                }
                $data = $user->find();

            }else{
                $data = $user->find( $where);
            }
            if($data){
                if(!isset($data['honor']))$data['honor'] = '';
                if(!isset($data['club_title']))$data['club_title'] = '';
            }
            return $this->setFootprintDesc($data);
        }
    }

    //组合用户的足迹和是否是好友
    protected function setFootprintDesc($data){
        if($data){
            if(is_array($data)){
                foreach ($data as $k => $v){
                    $data[$k] = $this->_setFootprintDesc($v);
                }
            }else{
                $data = $this->_setFootprintDesc($data);
            }
        }
        return $data;

    }

    protected function _setFootprintDesc($data){


//        $fp = [];
//        if(is_array($data['footprint_num'])){
//            foreach ($data['footprint_num'] as $k => $v){
//                $fp[$v['location_name']] = $v['num'];
//            }
//        }
//        $data['footprintDesc'] = '去过'.(isset($fp['s_code'])?$fp['s_code']:'0').'个国家，'.(isset($fp['c_code'])?$fp['c_code']:'0').'个城市，'.(isset($fp['place'])?$fp['place']:'0').'个地点';

//        $data['footprintDesc'] = '去过'.($data['footprint_num']['s_num']?:0).'个国家，'.($data['footprint_num']['c_num']?:0).'个城市，'.($data['footprint_num']['place_num']?:0).'个地点';
        $user['footprintDesc'] = '去过'.($data['footprint_num']['s_num']?:0).'个国家，'.($data['footprint_num']['c_num']?:0).'个城市，'.($data['dynamic_count']?:0).'个地点';

        $data['footprintCountry'] = [];

        if(isset($data['follow'])){
            if(!$data['follow'] || !$data['isfollow']){
                $data['is_friend'] = '102';
            }else{
                $data['is_friend'] = '101';
            }
        }else{
            $data['is_friend'] = 102;
        }
        return $data;
    }

    //1.手机号登录验证
    protected function loginCheckByPhone( $phone,$pre = '+86', $pwd){
        if(!$phone || !is_phone( $phone)){
            return $this->error(['desc'=>'错误的手机号']);
        }
        $userLogin = model('userLogin')->where('phone',$phone)->where('pre',$pre)->find();
        if($userLogin){
            if($userLogin['pwd'] != $pwd){
                return $this->error(['desc'=>'手机号或者密码错误']);
            }else{
                return $this->success(['id'=>$userLogin['id']]);
            }
        }else{
            return $this->error(['desc'=>'该手机号未注册']);
        }
    }

    //第三方唯一标志符验证登录
    protected function loginCheckByOnlySign($only_sign = ''){
        if(!$only_sign){
            return $this->error(['desc'=>'登录信息异常！']);
        }
        $userLogin = model('userLogin')->where('only_sign',$only_sign)->find();
        if($userLogin){
            return $this->success(['id'=>$userLogin['id']]);
        }else{
            return $this->error(['desc'=>'该用户未注册']);
        }
    }

    //2.给某用户生成新token 递归限制10次
    protected function flushToken( $id = false , $depth = 10){
        if(!$id)return $this->error(['desc'=>'id不存在'],'id不存在');
        if($depth <= 0)return $this->error(['desc'=>'token更新失败'],'token更新失败');

        $token = $this->createToken($id);
        if(model('userLogin')->where('id',$id)->update(['token'=>$token])){
            return $this->success(['token'=>$token]);
        }else{
            return $this->error(['desc'=>'token更新失败']);//$this->flushToken( $id, --$depth);
        }
    }

    //检查token是否重复
    protected function isRepeatedToken( $token){
        if(model('userLogin')->where('token',$token)->find()){
            return true;
        }
        return false;
    }

    //创建一个token
    public function createToken( $id){
        return md5('ShiDian' .'_'. $id .'_'. time().'_'.mt_rand(1,99999999));
    }
}