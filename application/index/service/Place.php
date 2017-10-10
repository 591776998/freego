<?php
namespace app\index\service;
use think\Db;
class Place extends \app\common\service\Base
{

    //返回地点列表
    public function index($now_user, $key_word = '',$type = 100, $order = '', $lat = 0, $lng = 0, $range = 0, $page = 0, $page_size = 0,$is_collect = 0,$location_name ='',$location_value= '',$tag_id = 0){
        $now_user_id = isset($now_user['id'])?$now_user['id']:'0';

        $is_lat_lng = 0;
        if($range && $lat && $lng){
            $is_lat_lng = 2;
        }elseif ($lat && $lng){
            $is_lat_lng = 1;
        }

        if(98 == $type){
            //101景点 102美食 103酒店 104娱乐 105购物 106租车
            $type_arr = [
                '101'   => '景点',
                '102'   => '美食',
                '103'   => '酒店',
                '104'   => '娱乐',
                '105'   => '购物',
                '106'   => '租车',
            ];
                $data = model('place');
                $key_word = trim($key_word);

                $is_lat_lng = 0;
                if(1 < $is_lat_lng){
                    $data->field("*,getDistance('$lat','$lng',`lat`,`lng`) as `distance`");
                    $lat_lng_limit = getAround($lat,$lng,$range);
                    $sql_arr = [];

                    foreach ($type_arr as $type_id => $title){
                        $model = model('place')
                            ->field("id,getDistance('$lat','$lng',`lat`,`lng`) as `distance`")
                            ->where('type',$type_id)
                            ->where('lat','BETWEEN',[$lat_lng_limit['minlat'],$lat_lng_limit['maxlat']]);
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

                        if($key_word) {
                            $model->where('title|content|address', 'like', '%' . $key_word . '%');
                        }
                        $sql_arr[] = '('.$model->order('distance asc')->limit(200)->select(false).')';
                    }

                    $sub = Db::query(implode(' union all ',$sql_arr));
                    $id_arr = array_column($sub,'id');
                    $data->order('distance asc');
                }else{
                    if($lat && $lng){
//                    if($lat && $lng && !$location_name){
                        $lo = model('Geo','service')->sl([$lat,$lng])->gl();

                        if('200' == $lo['code']){
                            $lo = current($lo['data']);
                            if(isset($lo['city']) && $lo['city']){
                                $location_name  = 'city';
                                $location_value = $lo['city'];
                            }elseif ( isset($lo['c_code']) && $lo['c_code']){
                                $location_name  = 'c_code';
                                $location_value = $lo['c_code'];
                            }elseif ( isset($lo['p_code']) && $lo['p_code']){
                                $location_name  = 'p_code';
                                $location_value = $lo['p_code'];
                            }elseif ( isset($lo['s_code']) && $lo['s_code']){
                                $location_name  = 's_code';
                                $location_value = $lo['s_code'];
                            }
                        }
                    }
                    $sql_arr = [];
                    foreach ($type_arr as $type_id => $title){
                        $temp = model('place');
                        if($key_word)$temp->where('title|content|address','like','%'.$key_word.'%');

                        if($location_name && $location_value){
                            $temp->where($location_name,$location_value);
//                            $temp->where(function ($query)use($location_name,$location_value){
//                                return $query->where(function ($query)use($location_name,$location_value){
//                                    return $query->where('location_name',$location_name)->where('location_value',$location_value);
//                                })->whereOr($location_name,$location_value);
//                            });

                        }
                        $sql_arr[] = '('.$temp->where('type',$type_id)->select(false).')';
                    }
                    $sub = Db::query(implode(' union all ',$sql_arr));
                    $id_arr = array_column($sub,'id');
                }

            $data = $data->with(['img','tag'])->where('id','in',$id_arr)->select();
            //根据某字段分成多个数组
            $res = [];
            foreach ($type_arr as $type_id => $title){
                $the_sub = [];
                foreach ($data as $k => $v){
                    //组合城市名
                    //
                    //
                    $v['full_title'] = $v['pre_city_title'].$v['title'];
//                    if($v['country'])$v['full_title'] .= isset($v['country']['name'])?$v['country']['name']:'';
//                    if($v['c_code']){
//                        if($v['cityl'])$v['full_title'] .= isset($v['cityl']['name'])?$v['cityl']['name']:'';
//                    }elseif($v['city']){
//                        if($v['cityo'])$v['full_title'] .= isset($v['cityo']['name'])?$v['cityo']['name']:'';
//                    }
//                    $v['full_title'] .= $v['title'];

                    if($type_id == $v['type']){
                        $the_sub[] = $v;
                    }
                }
                $res[] = [
                    'title' => $title,
                    'sub'   => $the_sub,
                ];
            }

            return $res;
        }else{

            if((101 == $is_collect)&&$now_user_id){
                $model = model('placeN');
            }else{
                $model = model('place');
            }

            $model->field(true)->with([
                'img',
                'tag',
                'collect'=>function($query)use($now_user_id){
                    return $query->where('user_id',$now_user_id?$now_user_id:'-1');
                }
            ])->withCount([
                'comments',
                'art'
            ]);

            if('hot' == $order){
                $weight1 = 1;
                $weight2 = 1;
                $weight3 = 1;
                $weight4 = 1;
                $model->field("( `comments_count`*$weight2 + (select count(*) from collect as c_c where (c_c.target_id=id and c_c.type=104 and del_time=0))*$weight4 ) as hot");
            }

            if((101 == $is_collect)&&$now_user_id){
                $sub_sql = model('collect')->field('target_id')->where('user_id',$now_user_id)->where('type',104)->select(false);
                $model->where('id','exp','in('.$sub_sql.')');
            }
            if($is_lat_lng){
                $model->field("getDistance('$lat','$lng',`lat`,`lng`) as distance");
            }
            if($type && 100 != $type){
                $model->where('type',$type);
            }

            if(1 < $is_lat_lng){
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
                $model->having('distance < '.$range);
            }

            if(trim($key_word)){
                $model->where('title|content|address','like','%'.trim($key_word).'%');
            }

            if($location_name && $location_value){
                $model->where(function ($query)use($location_name,$location_value){
                    return $query->where(function ($query)use($location_name,$location_value){
                            return $query->where('location_name',$location_name)->where('location_value',$location_value);
                    })->whereOr($location_name,$location_value);
                });
            }

            if($order){
                if($is_lat_lng && 'distance' == $order){
                    $model->order('distance asc');
                }else{
                    // todo 排序还需要处理
                    switch ($order){
                        case 'running':
                            $now_t = floor(THINK_START_TIME)-strtotime(date('Y-m-d'));
                            $model->where('run_s_time','<',$now_t)->where('run_e_time','>',$now_t);
                            break;
                        case 'hot':
                            $model->order('hot desc');
                            break;
                        case 'distance':

                            break;
                        default:
                            $model->order($order.' desc');
                    }

                }
            }
            if($tag_id){
                if(is_array($tag_id)){
                    $sub_tag_sql = model('placeTagRelation')->field('place_id')->where('tag_id','in',$tag_id)->select(false);
                }else{
                    $sub_tag_sql = model('placeTagRelation')->field('place_id')->where('tag_id',$tag_id)->select(false);
                }
                $model->where('id','exp','in('.$sub_tag_sql.')');
            }
            $res = $model->order('order_weight desc')->order('id desc')->page( $page, $page_size)->select();
        }
        return array_map(function ($r){
            $r['art']      = [];
            $r['services'] = [];
            $r['share']    = (object)null;//$this->getShare($r);
            if(!isset($r['img_url']))$r['img_url'] = '';
            if(!isset($r['distance']))$r['distance'] = '0';
            $r['distance_str'] = humanizedDistance($r['distance']);
            $r['full_title'] = $r['pre_city_title'].$r['title'];
            return $r;
        },$res);
    }

    //返回周边有多少家地点
    public function count($type,$location_name = '', $location_value = ''){
        $model = model('place')->where('type',$type);
        if($location_name && $location_value){
            $model->where(function ($query)use($location_name,$location_value){
                return $query->where(function ($query)use($location_name,$location_value){
                    return $query->where('location_name',$location_name)->where('location_value',$location_value);
                })->whereOr($location_name,$location_value);
            });
        }
        return $model->count();
    }

    //地点详情
    public function read($now_user, $id, $from=0, $from_user = 0){
        if($now_user){
            $collect_func = function($query)use($now_user){
                $query->where('user_id',$now_user['id']);
            };
        }else{
            $collect_func = [];
        }
        $data = model('place')->with([
            'collect'=>$collect_func,
            'img',
            'park',
            'album',
            'services'=>function($query){
                return $query->where('status','<>','99');
            },
            'art'=>function($query){
                return $query->with([
                    'img',
                    'user'=>function($query){
                        return $query->with(['head_img']);
                    },
                ])->page(1,8);
            }
        ])->where('id',$id)->find();
        if(!$data){
            return $this->error(['desc'=>'未找到该地点！']);
        }

        $data = $data->toArray();
        $data['share'] = $this->getShare($data);

//        $data['art'] = array_map(function ($art){
//            if(!isset($art['img_url']))$art['img_url'] = '';
//            return $art;
//        },$this->relationArt($data['id'],1,8));

        //把当前位置添加到park
        array_unshift($data['park'],[
            'title' => $data['title'],
            'lat'   => $data['lat'].'',
            'lng'   => $data['lng'].''
        ]);
        $data['park'] = array_map(function($park){
            return [
                'title'     => isset($park['title'])?($park['title'].''):'',
                'lat'       => isset($park['lat'])?($park['lat'].''):'',
                'lng'       => isset($park['lng'])?($park['lng'].''):'',
                'touring'   => isset($park['touring'])?($park['touring'].''):'102'
            ];
        },$data['park']);

        if($from_user && $now_user && $from_user != $now_user['id']){
            //给来自的用户加积分
            $add_point1 = model('system','service')->get('point_place_from',1);
            model('point','service')->add($add_point1, $from_user);
        }


         return $this->success($data);
    }

    //某地点的关联文章
    public function relationArt($id,$p,$ps){
        //这里应该筛选出段落里面有该地点的游记和发布的place_id是该地点的文章
        $sub_sql_line_sub = model('lineItem')->distinct(true)->field('line_id')->where('place_id',$id)->select(false);
        $sub_sql_line = model('line')->distinct(true)->field('main_id')->where('id','exp','in('.$sub_sql_line_sub.')')->select(false);

        //查询段落
        $sub_sql_art = model('artPara')->distinct(true)->field('main_id')->where('place_id',$id)->select(false);

        return model('PlaceArtRelation')->with(['img','user'])->where(function ($query)use($id,$sub_sql_line,$sub_sql_art){
            $query->where('place_id',$id)->whereOr('id','exp','in('.$sub_sql_line.' union all '.$sub_sql_art.')');
        })->where('type',101)->order('id desc')->page($p,$ps)->select();

    }

    //获取地图的分类
    public function map(){
        return model('placeMap')->with('img')->select();
    }

    /********************************************** 私有 ********************************************************/
    protected function getShare($data){
        return [
            'title'     => $data['title'],
            'content'   => $data['content'],
            'img_url'   => web_url('/image/read',['id'=>$data['img_id'],'width'=>80,'height'=>80],true,2),//这里要给个默认值
            'url'       => web_url('place',['id'=>$data['id'],'is_app'=>'102'],true,2)//'http://www.baidu.com'//"http://baidu.com"
        ];
    }

}