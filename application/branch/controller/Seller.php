<?php
namespace app\branch\controller;
class Seller extends \app\common\controller\BranchBase
{

    //返回地点的数组
    public function index(){
        if(!$this->branch)return $this->errorLogin();
        //查询出该代理商的直接商家
        if($this->has('branch_id')){
            $b = model('branch')->where('branch_id',$this->param('branch_id',0))->find();
            if($b){
                $location_name  = $b['location_name'];
                $location_value = $b['location_value'];
            }else{
                return success([]);
            }
        }else{
            if(!$this->has('location_name')){
                $location_name  = $this->branch['location_name'];
                $location_value = $this->branch['location_value'];
            }else{
                $location_name  = $this->param('location_name','');
                $location_value = $this->param('location_value','');
            }
        }



        $data = model('place')->with(['img'])->where($location_name,$location_value);
        if($this->has('key_word')){
            $data->where('title|boss|phone','like','%'.trim($this->param('key_word','')).'%');
        }
        $data = $data->order('id desc')->page($this->param('page',1),$this->param('page_size',10))->select();
        $data = array_map(function($d){
            if(!isset($d['img_url']))$d['img_url'] = '';
            return $d;
        },$data);
        return success($data);
    }

    public function update(){
        if(!$this->branch)return $this->errorLogin();
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }
        $seller_id = $this->param('id',0);
        $info_id   = $this->param('info_id',0);
        $seller_model = new \app\index\model\Seller();
        $s = $seller_model->where('id',$seller_id)->find();
        if(!$s){
            return error(['desc'=>'该商家不存在']);
        }

        $save = [];
        $info_save = [];
        if($this->has('info_status') && $this->param('info_status')){
            $info = model('sellerInfo')->where('id',$info_id)->find();
            if(!$info){
                return error(['desc'=>'未找到该资质信息']);
            }

            $status = $this->param('info_status');

            //如果有修改
            if($status != $s['info_status']){
                $save['info_status'] = $status;

                $info_save['id']     = $info_id;
                $info_save['status'] = $status;

                if(102 == $s['info_status']){
                    if(101 == $status){
                        //同意

                        if($info['place_id']){
                            $p =  model('place')->where('id',$info['place_id'])->find();
                            if($p){
                                if($p['seller_id'] && ($p['seller_id'] != $s['id'])){
                                    return error(['desc'=>'资质内的地点ID已绑定了商家，请重新选择地点或新建一个']);
                                }else{
                                    //更新地点的所属商家
                                    $p->where('id',$p->id)->update(['seller_id'=>$s['id']]);
                                    //更新商家的地点
                                    $save['place_id'] = $info['place_id'];
                                }
                            }else{
                                return error(['desc'=>'资质内的地点ID对应的地点未找到，请重新选择地点或新建一个']);
                            }
                        }
                        else {
                            $location_name = '';
                            $location_value = '';
                            if($info['t_code']){
                                $location_name = 't_code';
                                $location_value = $info['t_code'];
                            }elseif($info['city']){
                                $location_name = 'city';
                                $location_value = $info['city'];
                            }elseif ($info['c_code']){
                                $location_name = 'c_code';
                                $location_value = $info['c_code'];
                            }elseif ($info['p_code']){
                                $location_name = 'p_code';
                                $location_value = $info['p_code'];
                            }elseif ($info['s_code']){
                                $location_name = 's_code';
                                $location_value = $info['s_code'];
                            }
                            $place_data = [
                                'seller_id' => $seller_id,
                                'title'     => $info['place_title'],
                                'run_s_time'=> '28800',
                                'run_e_time'=> '64800',
                                'run_time'  => '9:00-17:00',
                                'phone'     => $info['place_phone'],
                                'address'   => $info['address'],
                                'boss'      => $info['boss_name'],
                                't_code'    => $info['t_code'],
                                'c_code'    => $info['c_code'],
                                'p_code'    => $info['p_code'],
                                's_code'    => $info['s_code'],
                                'city'      => $info['city'],
                                'location_name'=>$location_name,
                                'location_value'=>$location_value
                            ];
                            $place = model('place');
                            $place->data($place_data);
                            if(!$place->isUpdate(false)->save()){
                                return error(['desc'=>'新建地点失败，请稍后再试']);
                            }
                            //更新商家的地点
                            $save['place_id'] = $place['id'];
                        }

                    }elseif(100 == $status){
                        //拒绝
                    }else{
                        return error(['desc'=>'错误的status']);
                    }
                }elseif(101 == $s['info_status']){
                    //正常的商家
                    if(99 == $status){
                        $save['info_status'] = $status;
                    }else{
                        return error(['desc'=>'该商家已通过审核']);
                    }
                }elseif(100 == $s['info_status']){
                    //被拒绝的商家
                    if(99 == $status){
                        $save['info_status'] = $status;
                    }else{
                        return error(['desc'=>'该商家资质已经被拒绝']);
                    }
                }else{
                    return error(['desc'=>'不能改变状态']);
                }
            }
        }
        if($save){
            if($seller_model->where('id',$s['id'])->update($save)){

                if($info_save){
                    model('sellerInfo')->allowField(true)->where('id',$info_save['id'])->update($info_save);
                }

                return success($seller_model->where('id',$s['id'])->find());
            }else{
                return error(['desc'=>'修改失败']);
            }
        }else{
            return error(['desc'=>'没有做任何修改']);
        }
    }

    //测试接口
    public function test(){
        $ps = $this->param('page_size',10);
        $data = [];
        for ($i = 0;$i< $ps;$i++){
            $data[] =       [
                'id'          => 1,
                'info_status' => 101,
                'place'       => (object)null,
                'sellerinfo'  => (object)null,
                'name'        => 'test',
                'boss'        => '测试',
                'img_url'     => 'http://www.tp5.local/uploads/static/img/base/xiaolongxia.jpg?v=1'
            ];
        }
        return success($data);
    }

    public function main(){

        $sub_city =  model('branch')->where('p_id',$this->branch['branch_id'])->select();

        $this->assign('branch',$this->branch);
        $this->assign('sub_city',$sub_city);
        return $this->fetch();
    }

    //查询下级城市
    public function subcity($location_name,$location_value){
        ///branch/branch/index

    }

    public function main2(){
        return $this->fetch();
    }

    //商家资质列表
    public function infolist(){
        return $this->fetch();

    }

    //返回商家和请求列表
    public function sellerarr(){
        if(!$this->branch)return $this->errorLogin();
        //查询出该代理商的直接商家
        $branch_code = $this->branch['code'];
        //属于该分会或者是申请该分会的商家
        $data = model('seller')->with(['img','place','info'])->where(function ($query)use($branch_code){
            return $query->where('branch_id',$this->branch['id'])->whereOr(function ($query)use($branch_code){
                return $query->where('branch_id',0)->where('branch_code',$branch_code);
            });
        });

        if($this->has('key_word')){
            $data->where('name|boss|phone|login_phone','like','%'.trim($this->param('key_word','')).'%');
        }

        $data = $data->where('info_status','<>','100')->order('id desc')->page($this->param('page',1),$this->param('page_size',10))->select();

        $data = array_map(function($d){
            if(!isset($d['img_url']))$d['img_url'] = '';
            return $d;
        },$data);
        return success($data);
    }

    //查看商家资质
    public function sellerinfo(){
        $id = $this->param('id',0);
        $data = model('SellerInfo')->with(['cardHoldImg','cardFrontImg','cardBackImg','blImg'])->where('id',$id)->find();
        if(!$data){
            return $this->errorPage('未找到该资质');
        }

        if(!isset($data['card_hold_img_url']))$data['card_hold_img_url'] = '';
        if(!isset($data['card_front_img_url']))$data['card_front_img_url'] = '';
        if(!isset($data['card_back_img_url']))$data['card_back_img_url'] = '';
        if(!isset($data['bl_img_url']))$data['bl_img_url'] = '';



        $p = model('addressProvince')->where('p_code',$data['p_code'])->find();
        $c = model('addressCity')->where('c_code',$data['c_code'])->find();
        $t = model('addressTown')->where('t_code',$data['t_code'])->find();

        $this->assign('p',$p);
        $this->assign('c',$c);
        $this->assign('t',$t);

        $this->assign('data',$data);
        return $this->fetch();
    }
}