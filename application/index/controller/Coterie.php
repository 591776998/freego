<?php
namespace app\index\controller;
use think\Request;
class Coterie extends \app\common\controller\Base
{
    //
    public function index(){
        if(!$this->check('lat',['number'])||
            !$this->check('lng',['number']))
        {
            return error(['desc'=>'请上传位置信息']);
        }
        $lat = $this->param('lat',0);
        $lng = $this->param('lng',0);
        $range = 50000;
        $location_name = $this->param('location_name','');
        $location_value = $this->param('location_value','');

        //返回市一级的信息
        if(!$location_name && $lat && $lng){
            $location_data = model('address','service')->getLocation($lat,$lng,'gcj02ll');
            if($this->isError($location_data)){
                return error(['desc'=>'错误的位置信息']);
            }
            $location_name = $location_data['data']['location_name'];
            $location_value = $location_data['data']['location_value'];
            if('t_code' == $location_name){
                $location_info = model('addressTown')->where('t_code',$location_value)->find();
                if($location_info){
                    $location_name = 'c_code';
                    $location_value = $location_info['c_code'];
                }
            }
        }


        //要查看的用户的id
        //1.附近驾友
        $user_result        = model('User','service')->nearby( $this->user, 0, $lat, $lng, $range, 1, 5);

        //附近的好友数
        $user_result_count  = model('User','service')->count( $this->user, $lat, $lng, $range);

        //2.好友圈动态
        $friends_art = model('Art','service')->index($this->user, 104, 0, 0, 0, '', 0, 0, '', 1, 1, 10, 0);

        //论坛
        if($this->user && -1 != $this->user['now_forum_id']){
            $forum_result = model('forum','service')->read($this->user['now_forum_id']);
        }else{
            //获取
            $forum_result = model('forum','service')->getForumByLocation($location_name,$location_value);
        }

        //4.联系协会
        $association_result = [];

        //5.周边汽服
        $car_services = model('Place','service')->count( 110,$location_name, $location_value);

        //6.道路救援
        $rescue_services = model('Place','service')->count( 111,$location_name, $location_value);

        //未读动态数
        if($this->user){
            $last_time = $this->user['friend_read_time'];
            $my_id = $this->user['id'];
            $count_unread = model('art')->where('type',104)->where('user_id','IN',function($query) use($my_id){
                return $query->table('user_follow')->where('user_id',$my_id)->field('b_user_id');
            })->where('c_time','>',$last_time)->count();
        }else{
            $count_unread = '0';
        }

        return success([
            'nearby_user'       => $user_result,
            'nearby_user_count' => $user_result_count,
            'count_unread'      => $count_unread,
            'friends_info'      => $friends_art,
            'forum'             => $forum_result,
            'association'       => $association_result,
            'car_services'      => $car_services,
            'rescue_services'   => $rescue_services,
        ]);
    }

//
//    public function read(){
//        if(!$this->check('lat',['number'])||
//            !$this->check('lng',['number']))
//        {
//            return error(['desc'=>'请上传位置信息']);
//        }
//        $lat = $this->param('lat',100);
//        $lng = $this->param('lng',100);
//        $range = 50000;
//
//        //把该经纬度反地理编码
//        $location_data = model('address','service')->getLocation($lat,$lng,'gcj02ll');
//        if($this->isError($location_data)){
//            return error(['desc'=>'错误的位置信息']);
//        }
//
//        //要查看的用户的id
//        //1.附近驾友
//        $user_result = model('User','service')->nearby( $this->user, 0, $lat, $lng, $range, 1, 5);
//
//        $user_result_count  = model('User','service')->count( $this->user, $lat, $lng, $range);
//
//        //2.好友圈动态
//        $friends_art = model('Art','service')->index($this->user, 104, 0, 0, 0, '', 0, 0, '', 1, 1, 10, 0);
//
//
//
//        //3.联盟论坛
//        $forum_result = model('Forum','service')->index( 0,0);
//
//        //4.联系协会
////        $association_result = model('Association','service')->index(0);
////        if($this->isError($association_result)){
////            $association_result = [];
////        }else{
////            $association_result = $association_result['data'];
////        }
//        $association_result = [];
//        //5.周边汽服
//        $car_services = model('Place','service')->count( 110,$location_data['data']['location_name'], $location_data['data']['location_value']);
//
//        //6.道路救援
//        $rescue_services = model('Place','service')->count( 111,$location_data['data']['location_name'], $location_data['data']['location_value']);
//
//        //未读动态数
//        if($this->user){
//            $last_time = $this->user['friend_read_time'];
//            $my_id = $this->user['id'];
//            $count_unread = model('art')->where('type',104)->where('user_id','IN',function($query) use($my_id){
//                return $query->table('user_follow')->where('user_id',$my_id)->field('b_user_id');
//            })->where('c_time','>',$last_time)->count();
//        }else{
//            $count_unread = '0';
//        }
//
//        return success([
//            'nearby_user'       => $user_result,
//            'nearby_user_count' => $user_result_count,
//            'count_unread'      => $count_unread,
//            'friends_info'      => $friends_art,
//            'forum'             => $forum_result,
//            'association'       => $association_result,
//            'car_services'      => $car_services,
//            'rescue_services'   => $rescue_services,
//        ]);
//    }
}