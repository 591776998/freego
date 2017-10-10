<?php
namespace app\index\service;
class Art extends \app\common\service\Base
{

    //文章列表
    public function index($now_user, $art_type,$tag_id = 0, $user_id, $forum_id, $key_word = '',$place_id = 0, $place_type = 0, $order = 'id', $is_my_follow = 0, $p = 1, $ps = 10, $is_best = 0, $status = 101, $is_collect = 0, $is_joined = 0,$location_name='',$location_value = ''){
        $now_user_id = isset($now_user['id'])?$now_user['id']:'0';

        $with = [
            'user'=>function($query){
                return $query->with(['honour','club']);
            },
            'img',
            'content',
            'line',
            'collect'=>function($query)use($now_user_id){
                return $query->where('user_id',$now_user_id?$now_user_id:'-1');
            },
            'forum',
            'car',
            'artJoin'=>function($query){
                return $query->where('status',101);
            }
        ];

        if($forum_id){
            if('all' != $forum_id){
                $forum_data = model('forum')->where('id',$forum_id)->find();
                if($forum_data){
                    $location_name = $forum_data['location_name'];
                    $location_value = $forum_data['location_value'];
                }
            }else{
                $location_name  = 'all';
                $location_value = '';
            }

            $with['is_top'] = function($query)use($location_name,$location_value){
                return $query->where('location_name',$location_name)->where('location_value',$location_value);
            };

            $with['is_best'] = function($query)use($location_name,$location_value){
                return $query->where('location_name',$location_name)->where('location_value',$location_value);
            };
        }

        if((101 == $is_collect) && $now_user_id){
            $art_model = model('ArtN');

        }else{
            $art_model = model('Art');
        }

        $art_model->with($with)->withCount(['companion']);
        if(100 == $art_type){
            $art_model = $this->main_index($now_user,$location_name,$location_value,$art_model);
            $art_model->where('art.type','IN',[101,102,103]);
        }else{
            if('hot' == $order){
                $weight1 = 1;
                $weight2 = 1;
                $weight3 = 1;
                $weight4 = 1;
                $art_model->field("(`read_count`*($weight1/20) + `comments_count`*($weight2*2) + `like_count`*$weight3 + (select count(*) from collect as c_c where (c_c.target_id=`art`.id and c_c.type=103 and del_time=0))*$weight4 ) as hot");
            }

            if((101 == $is_collect) && $now_user_id){
                //添加1个条件
                $sub_sql = model('collect')->field('target_id')->where('user_id',$now_user_id)->where('type',103)->select(false);
                $art_model->where('art.id','exp','in('.$sub_sql.')');
            }

            if((101 == $is_joined)&& $now_user_id){
                //添加筛选
                $sub_sql = model('ArtJoin')->field('main_id')->where('user_id',$now_user_id)->where('status',101)->select(false);
                $art_model->where('art.id','exp','in('.$sub_sql.')');
            }

            if($place_id){
                $art_model->where('art.id','in',function($query)use($place_id,$place_type){
                    $query->table('art_para')->where('place_id',$place_id);
                    if($place_type){
                        $query->where('para_type',$place_type);
                    }else{
                        $query->where('para_type','in',[103,104]);
                    }
                    $query->where('del_time',0)->field('main_id');
                });
            }

            if($art_type){
                if(101 == $art_type){
                    $art_model->where('art.type','IN',[101,105]);
                }else{
                    $art_model->where('art.type',$art_type);
                }
            }
            if($tag_id){
                if(is_array($tag_id)){
                    $sub_tag_sql = model('artTagRelation')->field('art_id')->where('tag_id','in',$tag_id)->select(false);
                }else{
                    $sub_tag_sql = model('artTagRelation')->field('art_id')->where('tag_id',$tag_id)->select(false);
                }
                $art_model->where('art.id','exp','in('.$sub_tag_sql.')');
            }

            if($user_id){
                $art_model->where('art.user_id',$user_id);
            }

            //传入位置
            if($location_name){
                if('all' == $location_name){
                    $art_id_sql = model('locationArtRelation')->field('art_id')->where('location_name',$location_name)->select(false);
                }else{
                    //获取子集和当前的位置的
                    $art_id_sql = model('locationArtRelation')->field('art_id')
                        ->where($location_name,$location_value)
                        ->select(false);
                }
                $art_model->where('art.id','exp','in('.$art_id_sql.')');
            }

            //在当前位置被加精的
            if(101 == $is_best){
                $art_id_sql2 = model('forumBest')->field('art_id');
                if($location_name){
                    $art_id_sql2->where('location_name',$location_name)->where('location_value',$location_value);
                }
                $art_id_sql2 = $art_id_sql2->select(false);
                $art_model->where('art.id','exp','in('.$art_id_sql2.')');
            }

            if($is_my_follow){
                if(!$now_user){
                    return [];
                }
                $my_id = $now_user['id'];
                $art_model->where('user_id',(101 == $is_my_follow)?'IN':'NOT IN',function($query) use($my_id){
                    $query->table('user_follow')->where('user_id',$my_id)->field('b_user_id');
                });
            }

            //传入论坛ID
            if($forum_id){
                $art_model
                    ->alias('art')
                    ->field('art.*')
                    ->join('forum_top top',"art.id = top.art_id and top.status=101 and top.del_time=0 and (top.exp_time=0 or top.exp_time > UNIX_TIMESTAMP()) and (top.location_name='$location_name') and (top.location_value='$location_value')",'LEFT')
                    ->field('top.order_weight as tor')
                    ->order('tor desc');
            }
        }

        if($key_word)$art_model->where('art.title','like', '%'.$key_word.'%');

        if($order){
            //1.排序条件
            switch ($order){
                case 'id':
                case 'default':
                    $art_model->order( 'art.id desc');
                    break;
                case 'has_car':
                    $art_model->where('art.car_count','>','0')->order( 'art.car_count desc');
                    break;
                case 'end':
                    $art_model->where('art.end_time','<',floor(THINK_START_TIME))->order('art.end_time desc');
                    break;
                case 'hot':
                    $art_model->order('hot desc');
                    break;
                default:
                    $art_model->order( $order.' desc');
            }
        }else{
            $art_model->order('art.id desc');
        }

        $data = $art_model->where('art.status',$status)->page( $p, $ps)->select();

        if($data){
            $artTagRelation = model('artTagRelation')->with(['tag'])->where('art_id','in',array_map(function($d){return $d['id'];},$data))->select();
        }else{
            $artTagRelation = [];
        }

        $address = model('Address','service');
        return array_map(function($d)use($address,$artTagRelation){
            $d = $d->toArray();
            $d['likes_count'] = $d['like_count'];
            if(!isset($d['img_url']))$d['img_url'] = '';
            $d['content_text']   = $this->getContentText( $d['id'], $d['title'], $d['content']);
            $d['answer']         = [];
            $d['comments']       = [];
            $d['content']        = [];//注销掉内容，内容可能会比较大
            $d['companion']      = [];
            $d['line_str']       = $this->getLineStr($d['line']);
            if(!$d['line_str']){
                //截取正文的一部分
                $d['line_str'] = mb_substr( $d['content_text'], 0, 120 );
            }
            // todo 临时改的，等前端改了再改回来
            $d['line_str']       = $d['content_text'];
            $d['location_str']   = $address->getFullName($d['location_name'],$d['location_value']);
            $d['forum_title']    = isset($d['forum_title'])?$d['forum_title']:'';
            $d['tag'] = [];
            foreach ($artTagRelation as $k => $v){
                if($v['art_id'] == $d['id'] && $v['tag']){
                    $d['tag'][] = $v['tag'];
                }
            }
            if(!isset($d['user']['honor']))$d['user']['honor'] = '';
            if(!isset($d['user']['club_title']))$d['user']['club_title'] = '';

            $d['car_count'] = $this->getSeats($d['art_join']);
            return $d;
        },$data);
    }

    //统计顺飞车座位数
    protected function getSeats($data){
        $num = 0;
        foreach ($data as $vo){
            if(('101' == $vo['status']) && ($vo['total_seats'] > 0) && ($vo['total_seats'] > $vo['use_seats'])){
                $num += $vo['total_seats'] - $vo['use_seats'];
            }
        }
        return $num;
    }

    //主页列表
    public function main_index($now_user, $location_name,$location_value,$art_model){
        $forum_top_sql = '';
        //1.获关联的3个论坛
        if($location_name){
            $t_code = '';
            $c_code = '';
            $p_code = '';
            $s_code = '';
            $city   = '';

            $lo = model('address','service')->getFather($location_name,$location_value);
            if($lo){
                $forum_top = model('forumTop');
                if('c_code' == $location_name){
                    $c_code = $location_value;
                    if(isset($lo['father']['p_code'])){
                        $p_code = $lo['father']['p_code'];
//                        if(isset($lo['father']['father']['s_code'])){
//                            $s_code = $lo['father']['father']['s_code'];
//                        }
                    }
                }elseif ('city' == $location_name){
                    $city = $location_value;
                    if(isset($lo['father']['s_code'])){
                        $s_code = $lo['father']['s_code'];
                    }
                }elseif ('t_code' == $location_name){
//                    $t_code = $location_name;
                    if(isset($lo['father']['c_code'])){
                        $c_code = $lo['father']['c_code'];
                        if(isset($lo['father']['father']['p_code'])){
                            $p_code = $lo['father']['father']['p_code'];
//                            if(isset($lo['father']['father']['father']['s_code'])){
//                                $s_code = $lo['father']['father']['s_code'];
//                            }
                        }
                    }
                }

                $forum_top->whereOr('location_name','all');
                if($c_code){
                    $forum_top->whereOr('c_code',$c_code);
                }
                if($city){
                    $forum_top->whereOr('city',$city);
                }
                if($p_code){
                    $forum_top->whereOr(function ($query)use($p_code){
                        return $query->where('location_name','p_code')->where('location_value',$p_code);
                    });
                }
                if($s_code){
                    $forum_top->whereOr(function ($query)use($s_code){
                        return $query->where('location_name','s_code')->where('location_value',$s_code);
                    });
                }

                $forum_top_sql = $forum_top->field('art_id')->select(false);
            }
        }
        if($forum_top_sql){
            $art_model->where('id','exp','in('.$forum_top_sql.')');
        }
        return $art_model;
    }

    //动态类型的列表
    public function dynamic_index($now_user, $user_id, $is_my_follow = 0, $key_word = '', $order = 'id', $p = 1, $ps = 10){
        $now_user_id = $now_user?$now_user['id']:'-1';
        $art_model = model('Art')->with([
            'user',
            'content',
            'place',
            'likes'=>function($query){
                return $query->order('id desc')->limit(30);
            },
            'like'=>function($query)use($now_user){
                return $query->where('user_id',isset($now_user['id'])?$now_user['id']:'-1');
            },
        ]);
//      ])->withCount(['comments']);

        if($user_id){
            $art_model->where('user_id',$user_id);
        }

        if($is_my_follow){
            if(!$now_user){
                return [];
            }
            $my_id = $now_user['id'];
            if($my_id){
                $art_model->where(function ($query)use($is_my_follow,$my_id){
                    $query->where('user_id',(101 == $is_my_follow)?'IN':'NOT IN',function($query) use($my_id){
                        $query->table('user_follow')->where('user_id',$my_id)->field('b_user_id');
                    })->whereOr('user_id',$my_id);
                });
            }else{
                $art_model->where('user_id',(101 == $is_my_follow)?'IN':'NOT IN',function($query) use($my_id){
                    $query->table('user_follow')->where('user_id',$my_id)->field('b_user_id');
                });
            }
            $art_model->where('from','<>','107');
        }
        if($key_word)$art_model->where('title','like', '%'.$key_word.'%');
        $art_model->where('type',104);
        if($order){
            //1.排序条件
            switch ($order){
                case 'id':
                case 'default':
                    $art_model->order( 'id desc');
                    break;
                case 'show_time':
                    $art_model->order('show_time desc')->order( 'id desc');
                    break;
                default:
                    $art_model->order( $order.' desc');
            }
        }

        $data = $art_model->where('status',101)->page( $p, $ps)->select();

        //查询评论
        if($data){
            $sub_sql_comment = model('comment')->where('main_id','in',array_map(function($d){return $d['id'];},$data))->where('type',103)->select(false);
            $comments_temp =  model('comment')
                ->table('('.$sub_sql_comment.') as `comment`')
                ->with([
                    'user',
                    'reply_user',
                    'imgs',
                    'like'=>function($query)use($now_user_id){
                        return $query->where('user_id',$now_user_id?$now_user_id:'-1');
                    }])
                ->where('(SELECT count(*) FROM `comment` as temp_c2 WHERE `temp_c2`.`main_id` = `comment`.`main_id`  AND `temp_c2`.`id` > `comment`.`id`) < 5')
                ->order('id desc')
                ->select();
            $comments = [];
            foreach ($comments_temp as $key => $v){
                $v['desc_str'] = $this->commentStr($v);
                $comments[$v['main_id']][] = $v;
            }
        }else{
            $comments = [];
        }

        //
        if($is_my_follow && (101 == $is_my_follow) && $now_user){
            model('user')->where('id',$now_user['id'])->update(['friend_read_time'=>floor(THINK_START_TIME)]);
        }

        return array_map(function ($item)use($comments){

            $item['likes_count'] = $item['like_count'];
            $item = $item->toArray();
            $item['content_text'] = $this->getContentText( $item['id'], $item['title'], $item['content']);
            $item['imgs']         = $this->getContentImg($item['content']);
            $item['video_url']    = $this->getContentVideo($item['content']);
            $item['companion']    = [];
            $item['comments']     = isset($comments[$item['id']])?$comments[$item['id']]:[];
            $item['location_str'] = model('Address','service')->getFullName($item['location_name'],$item['location_value']);

            //添加定位的content
            $item['content'][] = $this->createPara([
                'content'          => $item['title'],
                'para_type'        => ($item['place_id'])?103:107,
                'main_id'          => $item['id'],
                'place_id'         => $item['place_id'],
                'lat'              => $item['lat'],
                'lng'              => $item['lng'],
                'location_name'    => $item['location_name'],
                'location_value'   => $item['location_value'],
                'dynamic_id'       => $item['id'],
            ]);
            return $item;
        },$data);
    }

    //文章详情
    public function read($now_user,$id){
        $user_id = isset($now_user['id'])?$now_user['id']:'0';
        $with = [
            'user',
            'answer',
            'img',
            'tag',
            'content',
            'line',
            'collect'=>function($query)use($user_id){
                return $query->where('user_id',$user_id?$user_id:-1);
            },
            'like'=>function($query)use($user_id){
                return $query->where('user_id',$user_id?$user_id:-1);
            },
            'forums',
            'companion',
            'comments'=>function($query)use($user_id){
                return $query->limit(3);
            }
        ];

        $art_model = model('Art')->with($with)->withCount(['answer','companion']);
        $data = $art_model->where('id',$id)->find();
        if($data){
            $data = $data->toArray();
            $data['likes_count'] = $data['like_count'];
            $data['content_text'] = $this->getContentText( $id, $data['title'], $data['content']);
            if(!isset($data['img_url']))$data['img_url'] = '';
            if(!isset($data['forum_title']))$data['forum_title'] = '';
            if(isset($data['comments'])){
                $data['comments'] = array_map(function ($comments){
                    $comments['desc_str'] = $this->commentStr($comments);
                    return $comments;
                },$data['comments']);
            }else{
                $data['comments'] = [];
            }

            if(102 == $data['type']){
                $total_seats = model('art_join')->where('main_id',$data['id'])->where('status',101)->where('total_seats','>','0')->where('total_seats','>','`use_seats`')->sum('`total_seats`-`use_seats`');
                if($total_seats){
                    $seats_arr = model('art_join')
                        ->with(['carmodel'])
                        ->where('main_id',$data['id'])
                        ->where('status',101)
                        ->where('total_seats','>','0')
                        ->where('total_seats','>','`use_seats`')
                        ->limit(2)
                        ->select();
                    $t = implode('、',array_map(function($vo){
                        return (isset($vo['pre_name'])?$vo['pre_name']:'').$vo['car_name'];
                    },$seats_arr));
                    $data['car_desc'] = $t.'等提供'.$total_seats.'个顺风车位';
                }else{
                    $data['car_desc'] = '暂无可蹭车辆';
                }
            }else{
                $data['car_desc'] = '';
            }

            //给没有img_url的赋一个空值
            array_map(function (&$c){
                if(!isset($c['img_url']))$c['img_url'] = '';
                if(!isset($c['video_url']))$c['video_url'] = '';
                return $c;
            },$data['content']);

            $address_service = new \app\index\service\Address();
            $data['location_str'] = $address_service->getFullName($data['location_name'], $data['location_value']);

            //截取标题生成目录
            $data['nav'] = $this->getNav($data['content']);

            //添加浏览量积分
            if(0 == $data['read_count']%100){
                $add_point = model('system','service')->get('point_read',1);
                model('point', 'service')->add($add_point, $data['user_id']);
            }

            //添加1个阅读数
            model('Art')->where('id',$id)->setInc('read_count');

            //查询活动的套餐
            if(102 == $data['type']){
                $data['art_activity']  = $this->artActivity($data['id']);
                $data['activity_plan'] = $this->activityPlan($data['id']);
                $data['relation_art']  = $this->relationArt($data['id']);
            }

            $data['share']    = $this->getShare($data);
            $linemap= model('artLineNav')->where('art_id',$data['id'])->find();
            if($linemap){
                $data['linenav'] = $linemap['data'];
            }else{
                $data['linenav'] = [];
            }

            $temp_content = [];
            foreach ($data['content'] as $vo){
                if(103 == $vo['para_type'] || 107 == $vo['para_type']){
                    $temp_content[] = $vo;
                }
            }

            $line = [];
            if($data['line']){
                if(isset($data['line']['point']))$line = $data['line']['point'];
            }

            $getFullLine = $this->getFullLine($line,$temp_content);
            $data['line_str'] = implode('-',array_column($getFullLine,'title'));
            if('' == $data['line_str'])$data['line_str'] = '暂无路线信息';

            return $this->success($data);
        }else{
            return $this->error(['desc'=>'没找到该文章，可能已经被删除']);
        }
    }




    //获取完整的路线
    protected function getFullLine($line,$para){
        $temp = [];
        foreach ($line as $k => $v){
            if(!$this->isDiff($v,$temp)){
                $temp[] = [
                    'title'         => $v['title'],
                    'location_name' => $v['location_name'],
                    'location_value'=> $v['location_value'],
                ];
            }
        }

        foreach ($para as $k => $v){
            if(!$this->isDiff($v,$temp)){
                $temp[] = [
                    'title'         => $v['city_title'],
                    'location_name' => $v['location_name'],
                    'location_value'=> $v['location_value'],
                ];
            }
        }
        return $temp;
    }

    //判断是否已经被除重
    protected function isDiff($a,$arr){
        $temp = array_map(function($vo){
            return $vo['location_name'].$vo['location_value'];
        },$arr);
        if(in_array($a['location_name'].$a['location_value'],$temp)){
            return true;
        }
        return false;
    }

    //获取动态格式的文章详情
    public function dynamic_read($now_user,$id){
        $user_id = isset($now_user['id'])?$now_user['id']:'-1';
        $with = [
            'user',
            'content',
            'place',
            'likes'=>function($query){
                return $query->order('id desc')->limit(30);
            },
            'like'=>function($query)use($user_id){
                return $query->where('user_id',$user_id);
            }
        ];
//        $art_model = model('Art')->with($with)->withCount(['comments']);
        $art_model = model('Art')->with($with);
        $item = $art_model->where('id',$id)->find();
        if($item){
            $item = $item->toArray();

            $item['likes_count'] = $item['like_count'];
            $item['content_text'] = $this->getContentText( $item['id'], $item['title'], $item['content']);
            $item['content'][] = $this->createPara([
                'content'          => $item['title'],
                'para_type'        => ($item['place_id'])?103:107,
                'main_id'          => $item['id'],
                'place_id'         => $item['place_id'],
                'lat'              => $item['lat'],
                'lng'              => $item['lng'],
                'location_name'    => $item['location_name'],
                'location_value'   => $item['location_value'],
                'dynamic_id'       => $item['id'],
            ]);
            $item['imgs']         = $this->getContentImg($item['content']);
            $item['video_url']    = $this->getContentVideo($item['content']);
            $item['companion']    = [];
            $item['location_str'] = model('Address','service')->getFullName($item['location_name'],$item['location_value']);

            $sub_sql_comment = model('comment')->where('main_id',$item['id'])->where('type',103)->select(false);
            $comments_temp =  model('comment')
                ->table('('.$sub_sql_comment.') as `comment`')
                ->with([
                    'user',
                    'reply_user',
                    'imgs'])
                ->order('id desc')
                ->select();
            foreach ($comments_temp as $key => $v){
                $comments_temp[$key]['desc_str'] = $this->commentStr($v);
            }
            $item['comments'] = $comments_temp;
            return $this->success($item);
        }else{
            return $this->error(['desc'=>'没找到该文章']);
        }
    }

    //获取文章的html格式的内容
    public function artContent( $id){
        $art = model('Art')->where('id',$id)->find();
        if(!$art){
            return $this->error(['desc'=>'该文章不存在或已被删除']);
        }

        if(config('cache_art_content')){
            $cache = model('Cache','service')->file('art_content_'.$id);
            if($cache){
                return $cache;
            }
        }

        $data = model('ArtPara')->with('img')->where('main_id',$id)->order('order_weight desc')->select();
        //1.判断是否是文本类型的
        if($data){
            switch ($art['content_type']){
                case '101':
                    $text = $this->getContentHtml( $art['id'], $art['title'], $data);
                    break;
                case '102':
                    $text = $this->getContentText( $art['id'], $art['title'], $data);
                    break;
                default:
                    return $this->error(['desc'=>'不支持的文章内容类型']);
            }
            if(config('cache_art_content')){
                model('Cache','service')->file('art_content_'.$id,$this->success(['content'=>$text]),86400);
            }
            return $this->success(['content'=>$text]);
        }else{
            return $this->success(['content'=>'']);
        }
    }

    //创建一个文章的主体
    public function addMain( $user_id, $data = []){
        //1.动态 的 经纬度
//        if(isset($data['lat']) && isset($data['lng']) && ($data['lat'] || $data['lng']) && !isset($data['location_name']) && !isset($data['location_value'])){
//            $location_data = model('address','service')->getLocation($data['lat'],$data['lng'],$data['coordtype']);
//            if(!$this->isError($location_data)){
//                $data['location_name']  = $location_data['data']['location_name'];
//                $data['location_value'] = $location_data['data']['location_value'];
//            }else{
//                $data['location_name']  = '';
//                $data['location_value'] = '';
//            }
//        }
//        debug($data,'$data');
        $geo = model('geo','service');
        if(isset($data['location_name']) && isset($data['location_value'])){
            $geo->sl([
                $data['location_name'],
                $data['location_value'],
            ]);
        }elseif(isset($data['lat']) && isset($data['lng'])){
            $geo->sl([
                $data['lat'],
                $data['lng'],
            ]);
        }
        $geo = $geo->gl();
//        debug($geo,'$geo');
        if(!$this->isError($geo)){
            $geo = current($geo['data']);
            $data['location_name']  = $geo['location_name'];
            $data['location_value'] = $geo['location_value'];
            if(isset($data['lat']))$geo['lat'] = $data['lat'];
            if(isset($data['lng']))$geo['lng']  = $data['lng'];
            if(isset($data['place_id']))$geo['place_id'] = $data['place_id'];

        }else{
            $geo = false;
            if(!isset($data['location_name']))$data['location_name'] = '';
            if(!isset($data['location_value']))$data['location_value'] = '';
            if(!isset($data['lat']))$data['lat'] = '0';
            if(!isset($data['lng']))$data['lng'] = '0';
            if(!isset($data['place_id']))$data['place_id'] = '0';
        }

        if(!isset($data['car_full_name'])){
            $data['car_full_name'] = $this->carFullName($data);
        }

        $line_id = isset($data['line_id'])?$data['line_id']:'0';

        $art = model('Art');
        $art_data = [
            'title'         => isset($data['title'])?$data['title']:'',
            'type'          => $data['art_type'],
            'car_auth_id'   => isset($data['car_auth_id'])?$data['car_auth_id']:'0',
            'content_type'  => (101 == $data['art_type']|| 102 ==$data['art_type'] || 105 == $data['art_type'])?'101':(103 == $data['art_type']?102:103),
            'c_time'        => THINK_START_TIME,
            'm_time'        => THINK_START_TIME,
            'comment_time'  => THINK_START_TIME,
            'show_time'     => isset($data['show_time'])?floor($data['show_time']):floor(THINK_START_TIME),
            'del_time'      => '0',
            'status'        => isset($data['status'])?$data['status']:'101',
            'user_id'       => $user_id,
            'read_count'    => '0',
            'img_id'        => isset($data['img_id'])?$data['img_id']:'0',
            'is_rec'        => '102',
            'total_number'  => isset($data['total_number'])?$data['total_number']:'0',
            'line_id'       => $line_id,
            'place_id'      => isset($data['place_id'])?$data['place_id']:'0',
            'lat'           => isset($data['lat'])?$data['lat']:'',
            'lng'           => isset($data['lng'])?$data['lng']:'',
            'location_name' => isset($data['location_name'])?$data['location_name']:'',
            'location_value'=> isset($data['location_value'])?$data['location_value']:'',
            'car_model_id'  => isset($data['car_model_id'])?$data['car_model_id']:'0',
            'car_name'      => isset($data['car_name'])?$data['car_name']:'',
            'car_full_name' => isset($data['car_full_name'])?$data['car_full_name']:'',
            'from'          => isset($data['from'])?$data['from']:'0',
        ];
        if($art->data($art_data)->save()){
            if(isset($data['car_auth_id']) && $data['car_auth_id']){
                model('carAuth')->where('id',$data['car_auth_id'])->update(['art_id'=>$art->id]);
            }

            if(isset($data['tag_id']) && $data['tag_id']){
                if(!is_array($data['tag_id'])){
                    $data['tag_id'] = [$data['tag_id']];
                }
                $art_tag_relation = [];
                foreach ($data['tag_id'] as $v){
                    $art_tag_relation[] = [
                        'art_id'    => $art->id,
                        'tag_id'    => $v
                    ];
                }
                model('ArtTagRelation')->saveAll($art_tag_relation);
            }

            $u_f_p = [];

            //该文章的图片数组
            $imgs = [];
            if($art_data['img_id']){
                $imgs[] = $art_data['img_id'];
            }

            //创建成功
            $c = [];
            if(isset($data['content']) && is_array($data['content'])){
                $dynamic_id = '0';
                if(104 == $art_data['type']){
                    $dynamic_id = $art->id;
                }
                foreach ($data['content'] as $content){
                    if(103 == $content['para_type'] || 107 == $content['para_type']){
                        $u_f_p[] = [
                            'location_name' => isset($content['location_name'])?$content['location_name']:'',
                            'location_value'=> isset($content['location_value'])?$content['location_value']:'',
                            'place_id'      => isset($content['place_id'])?$content['place_id']:'',
                            't_code'        => isset($content['t_code'])?$content['t_code']:'',
                            'c_code'        => isset($content['c_code'])?$content['c_code']:'',
                            'p_code'        => isset($content['p_code'])?$content['p_code']:'',
                            's_code'        => isset($content['s_code'])?$content['s_code']:'',
                            'city'          => isset($content['city'])?$content['city']:'',
                        ];
                    }
                    if(102 == $content['para_type']){
                        $imgs[] = isset($content['img_id'])?$content['img_id']:'0';
                    }
                    $c[] = [
                        'main_id'       => $art->id,
                        'place_id'      => isset($content['place_id'])?$content['place_id']:'0',
                        'para_type'     => $content['para_type'],
                        'content'       => $content['content'],
                        'location_name' => isset($content['location_name'])?$content['location_name']:'',
                        'location_value'=> isset($content['location_value'])?$content['location_value']:'',

                        't_code'        => isset($content['t_code'])?$content['t_code']:'',
                        'c_code'        => isset($content['c_code'])?$content['c_code']:'',
                        'p_code'        => isset($content['p_code'])?$content['p_code']:'',
                        's_code'        => isset($content['s_code'])?$content['s_code']:'',
                        'city'          => isset($content['city'])?$content['city']:'',

                        'order_weight'  => '100',
                        'status'        => '101',
                        'del_time'      => '0',
                        'img_id'        => isset($content['img_id'])?$content['img_id']:'0',
                        'video_id'      => isset($content['video_id'])?$content['video_id']:'0',
                        'c_time'        => THINK_START_TIME,
                        'dynamic_id'    => $dynamic_id
                    ];
                }
            }
            if($c){
                $this->addArtPara($c);
            }

            //判断是否同时上传到精彩影像
            if(isset($data['add_photo']) && 101 == $data['add_photo']){
                //只有动态
                if(104 == $art_data['type']){
                    $photo_data = [
                        'location_name'     => $art_data['location_name'],
                        'location_value'    => $art_data['location_value'],
                        'location_str'      => $art_data['title'],

//                        't_code'            => $art_data['t_code'],
//                        'c_code'            => $art_data['c_code'],
//                        'p_code'            => $art_data['p_code'],
//                        's_code'            => $art_data['s_code'],
//                        'city'              => $art_data['city'],

                        'img_id'            => $imgs,
                        'source_id'         => $user_id,
                        'source_type'       => '99',
                        'art_id'            => $art->id,
                        'place_id'          => isset($art_data['place_id'])?($art_data['place_id']?:'0'):'0',
                    ];
                    if($geo){
                        $photo_data['t_code'] = $geo['t_code'];
                        $photo_data['c_code'] = $geo['c_code'];
                        $photo_data['p_code'] = $geo['p_code'];
                        $photo_data['s_code'] = $geo['s_code'];
                        $photo_data['city']   = $geo['city'];
                        $photo_data['lat']   = isset($geo['lat'])?$geo['lat']:'0';
                        $photo_data['lng']   = isset($geo['lng'])?$geo['lng']:'0';
                    }

                    model('photo','service')->save($user_id,$photo_data);
                }
            }

            //保存段落里面的用户足迹
            if(104 == $art_data['type']){
                if($geo){
                    $geo['place_id'] = $art_data['place_id'];
                    $u_f_p[] = $geo;
                }
            }

            if($u_f_p){
                model('UserFootprint','service')->adds($user_id,$u_f_p);
            }

            //添加同行的伙伴
            if(isset($data['companion']) && $data['companion']){
                $companion = [];
                foreach ($data['companion'] as $k => $v){
                    $companion[] = [
                        'main_id'   => $art->id,
                        'user_id'   => $v,
                        'del_time'  => '0',
                        'c_time'    => floor(THINK_START_TIME)
                    ];
                }
                if($companion){
                    model('art_companion')->saveAll($companion);
                }
            }

            //保存导航信息
            $this->saveLineNavV2($art->id,model('line_Item')->where('line_id',$line_id)->count());

            //保存位置
            if(isset($data['forum_id']) && $data['forum_id']){
                $forum = model('forum')->where('id',$data['forum_id'])->find();
                if($forum){
                    $this->saveLocation($art->id,$forum['location_name'],$forum['location_value'],false);
                }
            }

            return $this->success(['id'=>$art->id]);
        }else{
            return $this->error(['desc'=>'创建文章失败，请稍后再试！']);
        }
    }

    //批量添加文章段落
    public function addArtPara($data){
        $result = model('ArtPara')->saveAll($data);
        if($result){
            return $this->success($result);
        }else{
            return $this->error(['desc'=>'文章段落保存失败，请稍后再试！']);
        }
    }

    //获取文章类型的字符
    public function showArtType($type){
        switch ($type){
            //文章类型 101游记 102活动 103提问 104动态 105车主认证
            case '101':
                return '游记';
            case '102':
                return '活动';
            case '103':
                return '问答';
            case '104':
                return '足迹';
            case '105':
                return '游记';
        }
        return '文章';
    }

    //车型信息
    public function carFullName($data){
        if(isset($data['car_model_id'])){
            if(!isset($data['car_full_name']) || !$data['car_full_name']){
                $car = model('carModel')->with(['parent'=>function($query){
                    return $query->with(['parent']);
                }])->where('id',$data['car_model_id'])->find();
                if($car){
                    $data['car_full_name'] = $car['title'].(isset($data['parent']['title'])?$data['parent']['title'].(isset($data['parent']['parent']['title'])?$data['parent']['parent']['title']:''):'').(isset($data['car_name'])?$data['car_name']:'');
                }
            }
        }else{
            if(!isset($data['car_full_name']) || !$data['car_full_name']){
                $data['car_full_name'] = isset($data['car_name'])?$data['car_name']:'';
            }
        }
        return isset($data['car_full_name'])?$data['car_full_name']:'';
    }

    //复制评论
    /********************************* 私有方法 *********************************************/

    //获取分享属性
    protected function getShare($data){
        return [
            'title'     => $data['title'],
            'content'   => mb_substr($data['content_text'],0,50),
            'img_url'   => web_url('/image/read',['id'=>$data['img_id'],'width'=>80,'height'=>80],true,2),
            'url'       => web_url('art',['id'=>$data['id'],'is_app'=>'102'],true,2)
        ];
    }

    //查活动的附加信息
    protected function artActivity($art_id){
        $data = model('ArtActivity')->with(['lead_head_img'])->where('id',$art_id)->find();
        if($data && !isset($data['lead_head_img_url'])){
            $data['lead_head_img_url'] = '';
        }
        return $data;
    }

    //查询某活动的套餐
    protected function activityPlan($art_id){
        return model('Plan')->where('art_id',$art_id)->select();
    }

    //查询关联文章
    protected function relationArt($id){
        $sub_sql = model('relationArt')->field('art_id')->where('main_id',$id)->select(false);
        return model('Art')->with(['img','user'])->where('id','exp','in('.$sub_sql.')')->select();
    }

    //虚构一个位置段落
    protected function createPara($data = []){
        $empty = [
            'id'            => '0',
            'main_id'       => '0',
            'para_type'     => '101',
            'content'       => '',
            'img_id'        => '0',
            'video_id'      => '0',
            'like_count'    => '0',
            'status'        => '101',
            'order_weight'  => '100',
            'c_time'        => floor(THINK_START_TIME),
            'm_time'        => '0',
            'del_time'      => '0',
            'place_id'      => '0',
            'lat'           => '0',
            'lng'           => '0',
            'location_name' => '',
            'location_value'=> '',
            'dynamic_id'    => '0',
            'img_url'       => '',
            'video_url'     => '',
            'img'           => null,
            'video'         => null
        ];
        if($data){
            $empty = array_merge($empty,$data);
        }
        return $empty;
    }

    //拼接文本的内容
    protected function getContentText($id, $title, $content = []){
        return implode('',array_map(function($value){return $value['content'];},$content));
    }

    //获取内容中的全部图片
    protected function getContentImg($content){
        $img = [];
        foreach ($content as $k => $c){
            if((102 == $c['para_type']) || (106 == $c['para_type'])){
               $img[] = isset($c['img_url'])?$c['img_url']:'';
            }
        }
        return $img;
    }

    //获取内容中的视频
    protected function getContentVideo($content){
        foreach ($content as $k => $c){
            if(106 == $c['para_type']){
                return isset($c['video_url'])?$c['video_url']:'';
            }
        }
        return '';
    }

    //从内容里面生成目录
    protected function getNav($content){
        $temp = [];
        foreach ($content as $k => $v){
            if(105 == $v['para_type']){
                $v['content'] = mb_substr($v['content'],0,25);
                $temp[] = $v;
            }
        }
        return $temp;
    }

    //获取动态的标题
    protected function getDynamicTitle($data){
        if($data['place_id']){
            if($data['place_title']){
                return $data['place_title'];
            }else{
                return '';
            }
        }elseif($data['location_name'] && $data['location_value']){
            return model('address','service')->getFullName($data['location_name'], $data['location_value']);
        }else{
            return '';
        }
    }

    //拼接文章的html格式内容
    protected function getContentHtml( $id, $title, $content = []){
        if($content){
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no"><title>'.$title.'</title><style type="text/css">
			body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td { margin:0; padding:0; }
body { background:#fff; color:#555; font-size:14px; font-family: Verdana, Arial, Helvetica, sans-serif; }
td,th,caption { font-size:14px; }
h1, h2, h3, h4, h5, h6 { font-weight:normal; font-size:100%; }
address, caption, cite, code, dfn, em, strong, th, var { font-style:normal; font-weight:normal;}
a { color:#555; text-decoration:none; }
a:hover { text-decoration:underline; }
img { border:none; }
ol,ul,li { list-style:none; }
input, textarea, select, button { font:14px Verdana,Helvetica,Arial,sans-serif; }
table { border-collapse:collapse; }
html {overflow-y: scroll;}
/* css common */
.clearfix:after {content: "."; display: block; height:0; clear:both; visibility: hidden;}
.clearfix { *zoom:1; }
			body,html{
				background: white;
			}
			.row{
				height: 163px;padding: 50px 0 0 0;text-align: center;
			}
			.row img{
				width: 100%;
			}
			.row p{
			
				margin-top: 25px;font-size: 15px;
			}
		</style></head><body>';

            //1.获取全部的图片类型的段落，一次性获取全部的图片
            foreach ($content as $v){
                if(101 == $v['para_type']){
                    $html .= $this->getTextHtml($v['content']);
                }elseif (102 == $v['para_type']){
                    $html .= $this->getImgHtml(isset($v['img_url'])?$v['img_url']:$v['img_id']);
                }
            }

            return $html.'</body></html>';
        }
        return '';
    }

    //解析文本形式的html
    protected function getTextHtml( $text){
        return '<div class="row"><p>'.$text.'</p></div>';
    }

    //解析图片类型的html
    protected function getImgHtml( $url){
        return '<div class="row"><img src="'.$url.'"/></div>';
    }

    //拼接路线
    protected function getLineStr($line_arr){
        if($line_arr && (is_array($line_arr)||is_object($line_arr)) && isset($line_arr['point'])){
            return implode('-',array_map(function ($v){return $v['title'];},$line_arr['point']));
        }else{
            return '';
        }
    }

    //拼接评论的文字
    protected function commentStr($comments){
        if(!$comments){
            return '';
        }
        if($comments['reply_id']){
            //回复
            return (isset($comments['user']['nick_name'])?$comments['user']['nick_name']:'').' 回复 '.(isset($comments['reply_user']['nick_name'])?$comments['reply_user']['nick_name']:'').'：'.$comments['content'];
        }else{
            //评论
            return (isset($comments['user']['nick_name'])?$comments['user']['nick_name']:'').'：'.$comments['content'];
        }
    }

    //第二版的文章段落路线导航保存 和足迹的保存
    public function saveLineNavV2($id,$line){
        if($id){
            $line_data = model('artPara')->where('main_id',$id)->where('para_type','in',[103,107])->order('order_weight asc')->select();
            $format_line_nav_res = model('address','service')->formatParaNav($line_data);
            if($this->isError($format_line_nav_res)){
                return $this->error($format_line_nav_res);
            }

            //加一步修改数据
            $city_arr = [];
            $place_arr = [];
            foreach ($line_data as $k => $v){
                if (107 == $v['para_type']){
                    $city_arr[] = $v['location_name'].$v['location_value'];
                }

                if((103 == $v['para_type']) || (107 == $v['para_type'])){
                    $place_arr[] = uniqueSign([
                        $v['main_id'],
                        $v['place_id'],
                        $v['para_type'],
                        $v['content'],
                        $v['lat'],
                        $v['lng'],
                        $v['location_name'],
                        $v['location_value']
                    ]);
                }
            }
            $city_num = count(array_unique($city_arr));
            $place_num = count(array_unique($place_arr));
            //去重再统计
            //array_unique

            if(is_numeric($line)){
                $city_num += $line;
            }elseif(is_array($line)){
                $temp_city = array_map(function ($vo){
                    return $vo['location_name'].$vo['location_value'];
                },$line);
                $city_num +=  count(array_unique($temp_city));
            }
            $footprint = $city_num.'个城市，'.$place_num.'个地点';
            model('art')->where('id',$id)->update(['footprint'=>$footprint]);
            //保存
            $art_line_nav = model('artLineNav')->where('art_id',$id)->find();
            if($art_line_nav){
                $art_line_nav->data = $format_line_nav_res['data'];
                if($art_line_nav->save()){
                    return $this->success($art_line_nav);
                }else{
                    return $this->error(['desc'=>'没有做任何修改'],'error');
                }
            }else{
                return model('artLineNav')->save([
                    'art_id'    => $id,
                    'data'      => $format_line_nav_res['data']
                ]);
            }
        }else{
            return $this->error(['desc'=>'没有ID信息']);
        }
    }

    //传入文章ID，重新保存路线导航信息
    public function saveLineNav($id){
        if($id){
            $line_data = model('artPara')->where('main_id',$id)->where('para_type','in',[103,107])->order('order_weight asc')->select();
            //反地理编码，获取location信息
            $new = model('address','service')->getLocationArr(array_map(function ($item){
                return [
                    'lat'       => $item['lat'],
                    'lng'       => $item['lng'],
                    'title'     => $item['content'],
                    'para_id'   => $item['id']
                ];
            },$line_data));
            if($this->isError($new)){
                return $this->error($new);
            }
            //获取格式化的二级城市信息
            $format_line_nav_res = model('address','service')->formatLineNav($new['data']);
            if($this->isError($format_line_nav_res)){
                return $this->error($format_line_nav_res);
            }
            $art_line_nax_data = $format_line_nav_res['data'];
            //保存
            $art_line_nav = model('artLineNav')->where('art_id',$id)->find();
            if($art_line_nav){
                $art_line_nav->data = $art_line_nax_data;
                return $art_line_nav->save();
            }else{
                return model('artLineNav')->save([
                    'art_id'    => $id,
                    'data'      => $art_line_nax_data
                ]);
            }
        }else{
            return $this->error(['desc'=>'没有ID信息']);
        }
    }

    //增量保存文章与地理位置的关系
    public function saveLocation($id,$location_name,$location_value,$can_del = true){
        if(!model('locationArtRelation')->where('art_id',$id)->where('location_name',$location_name)->where('location_value',$location_value)->find()){
            if('all' == $location_name){}
            $temp = model('location','service')->read($location_name,$location_value);
            if($temp){
                $temp['art_id']     = $id;
                $temp['can_del']    = $can_del?101:102;
                if(model('locationArtRelation')->isUpdate(false)->allowField(true)->data($temp)->save()){
                    return true;
                }
            }
        }
        return false;
    }

    //根据文章ID和路线ID保存位置信息
    public function saveLineLocation($art_id,$line_id){
        if($art_id){
            if(is_array($line_id)){
                $this->saveLocationArr($art_id,$line_id);
            }else{
                $this->saveLocationArr($art_id,model('line_item')->where('line_id',$line_id)->select());
            }
        }
        return true;
    }

    //批量添加位置关系
    public function saveLocationArr($id,$data){
        if($data){
            $old = model('locationArtRelation')->where('art_id',$id)->where('location_value','in',array_column($data,'location_value'))->select();
            if($old){
                $old_arr = array_map(function ($vo){
                    return md5($vo['location_name'].$vo['location_value']);
                },$old);
                foreach ($data as $k => $v){
                    if( (!isset($v['location_name']) || !$v['location_name']) && ( (!isset($v['lat']) || !$v['lat']) || (!isset($v['lng']) || !$v['lng'])) ){
                        unset($data[$k]);
                    }
                    if(in_array(md5($v['location_name'].$v['location_value']),$old_arr)){
                        unset($data[$k]);
                    }
                    if(!isset($v['s_code'])||!isset($v['p_code'])||!isset($v['c_code'])||!isset($v['t_code'])||!isset($v['city'])||!$v['location_name']){
                        $data['field_full'] = false;
                    }else{
                        $data['field_full'] = true;
                    }
                }
            }
            if($data){
                $data = array_unique_arr($data);
                if($data){
                    $save_data = [];
                    $geo = model('geo','service');
                    foreach ($data as $vo){
                        if(isset($vo['field_full'])&&!$vo['field_full']){
                            if(isset($vo['location_name'])&&$vo['location_name']){
                                $geo->sl($vo['location_name'],$vo['location_value']);
                            }elseif(isset($vo['lat'])&&isset($vo['lng'])&&$vo['lat']&&$vo['lng']){
                                $geo->sl($vo['lat'],$vo['lng']);
                            }
                        }else{
                            $save_data[] = $vo;
                        }
                    }

                    $save_data = array_merge($save_data,$geo->gl()['data']);
                    $data = [];
                    foreach ($save_data as $vo){
                        if($vo['location_name']){
                            $data[] = [
                                'art_id'            => $id,
                                't_code'            => $vo['t_code'],
                                'c_code'            => $vo['c_code'],
                                'p_code'            => $vo['p_code'],
                                's_code'            => $vo['s_code'],
                                'city'              => $vo['city'],
                                'location_name'     => $vo['location_name'],
                                'location_value'    => $vo['location_value'],
                                'del_time'          => '0',
                                'can_del'           => isset($vo['can_del'])?$vo['can_del']:101
                            ];
                        }
                    }

                    if($data){
                        model('locationArtRelation')->isUpdate(false)->allowField(true)->saveAll($data,false);

                        $art = model('art')->where('id',$id)->find();
                        $user_id = isset($art['user_id'])?$art['user_id']:'0';
                        if($user_id){
                            model('UserFootprint','service')->adds($user_id,array_map(function($vo){
                                return [
                                    'location_name' => isset($vo['location_name'])?$vo['location_name']:'',
                                    'location_value'=> isset($vo['location_value'])?$vo['location_value']:'',

                                    't_code'        => isset($vo['t_code'])?$vo['t_code']:'',
                                    'c_code'        => isset($vo['c_code'])?$vo['c_code']:'',
                                    'p_code'        => isset($vo['p_code'])?$vo['p_code']:'',
                                    's_code'        => isset($vo['s_code'])?$vo['s_code']:'',
                                    'city'          => isset($vo['city'])?$vo['city']:'',
                                    'place_id'      => isset($vo['place_id'])?$vo['place_id']:'',
                                ];
                            },$data));
                        }

                        //建立文章和地点的关联
                        foreach ($data as $k => $vo){
                            if(isset($vo['place_id']) && $vo['place_id']){
                                $sql = 'insert ignore into `place_art_relation`(`art_id`,`place_id`,`del_time`) values ('.$id.','.$vo['place_id'].',0)';
                                \Think\Db::execute($sql);
                            }
                        }

                    }

                }
            }
        }
        return true;
    }

}
















