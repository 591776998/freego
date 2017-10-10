<?php
namespace app\seller\controller;
class Sellerinfo extends \app\common\controller\SellerBase
{

    public function editor(){
        $data = model('sellerInfo');
        if($this->has('id')){
            $data->where('id',$this->param('id'));
        }elseif($this->has('seller_id')){
            $data->where('seller_id',$this->param('seller_id'));

        }
        $data->where('status','in',[100,101,102])->find();

        $p = model('addressProvince')->with(['sub'=>function($query){
            return $query->with('sub');
        }])->select();

        $this->assign('data',$data);
        $this->assign('p',$p);
        return $this->fetch();
    }

    public function index(){
        return 'seller';
    }

    public function save(){
        if(!$this->seller){
            return $this->errorLogin();
        }
        $save_seller = [];
        $save_data = $this->param;
        $file = $this->request->file();

        if(!$save_data['boss_name']){
            return error(['desc'=>'请填写负责人姓名']);
        }
        if(!isset($save_data['boss_id_card']) || !isIdCard($save_data['boss_id_card'])){
            return error(['desc'=>'请输入正确的身份证号码']);
        }
        if(!$save_data['place_title']){
            return error(['desc'=>'请填写门店名称']);
        }
        if(!$save_data['place_phone']){
            return error(['desc'=>'请填写门店电话']);
        }
        if(!$save_data['p_code']){
            return error(['desc'=>'请选择省份']);
        }
        if(!$save_data['c_code']){
            return error(['desc'=>'请选择市']);
        }
        if(!$save_data['t_code']){
            return error(['desc'=>'请选择县']);
        }
        if(!$save_data['address']){
            return error(['desc'=>'请填写详情地址']);
        }
        if(!$save_data['bl_code']){
            return error(['desc'=>'请填写营业执照注册号']);
        }
        if(!$save_data['bl_title']){
            return error(['desc'=>'请填写营业执照名称']);
        }

        if(isset($save_data['status']))unset($save_data['status']);
        if(isset($save_data['place_id']) && $save_data['place_id']){
            if(model('place')->where('id',$save_data['place_id'])->where('seller_id','<>','0')->find()){
                return error(['desc'=>'该地点已经被其他商家绑定，如有疑问请联系客服']);
            }
        }
        if(isset($save_data['branch_code']) && $save_data['branch_code']){
            if(!model('branch')->where('code',$save_data['branch_code'])->find()){
                return error(['desc'=>'请填写正确的审核机构代码']);
            }
            $save_seller['branch_code'] = $save_data['branch_code'];
        }

        if(!isset($file['card_front_img']) || !$file['card_front_img']){
            return error(['desc'=>'请上传身份证正面照片']);
        }
        if(!isset($file['card_back_img']) || !$file['card_back_img']){
            return error(['desc'=>'请上传身份证背面照片']);
        }
        if(!isset($file['card_hold_img']) || !$file['card_hold_img']){
            return error(['desc'=>'请上传手持身份证照片']);
        }
        if(!isset($file['bl_img']) || !$file['bl_img']){
            return error(['desc'=>'请上传营业执照照片']);
        }

        $img_result = model('image','service')->upload($file,115);
        if(!$img_result){
            return error(['desc'=>'上传图片失败']);
        }
        foreach ($img_result as $k => $img){
            if(!$img['id']){
                return error(['desc'=>isset($img['desc'])?$img['desc']:'不支持该格式的图片']);
            }
            if(!strpos($k,'_id')){
                $k .= '_id';
            }
            $save_data[$k] = $img['id'];
        }

        $save_data['seller_id'] = $this->seller['id'];
        if($this->param('id',0)){
            $data = model('sellerInfo')->where('id',$this->param('id',0))->find();
            if(!$data){
                return error(['desc'=>'该记录不存在！']);
            }
            if($data->data($save_data)->allowField(true)->update()){
                $save_seller['info_status'] = 102;
                if($save_seller){
                    model('seller')->where('id',$this->seller['id'])->update($save_seller);
                }
                return success($data);
            }else{
                return error(['desc'=>'未做任何修改！']);
            }
        }else{
            //查询是否有未审核的资质
            if(model('sellerInfo')->where('seller_id',$this->seller['id'])->where('status','in',[101,102])->find()){
                return error(['desc'=>'您有审核中或已通过的申请，请不要重复提交']);
            }else{
                $seller_id = $this->seller['id'];
                model('sellerInfo')->destroy(function ($query)use($seller_id){
                    return $query->where('seller_id',$seller_id);
                });
            }

            $data = model('sellerInfo');
            if($data->data($save_data)->allowField(true)->save()){
                $save_seller['info_status'] = 102;
                if($save_seller){
                    model('seller')->where('id',$this->seller['id'])->update($save_seller);
                }
                return success($data);
            }else{
                return error(['desc'=>'新建失败']);
            }
        }
    }

    public function res(){
        if(!$this->seller){
            return $this->errorLogin();
        }
        $place = [];
        $data = model('sellerInfo')->where('seller_id',$this->seller['id'])->where('status','in',[100,101,102])->order("FIELD(status,101,102,100)")->find();
        if($data){
            if($data['status'] == 101){
                $place = model('place')->where('seller_id',$this->seller['id'])->find();
            }
        }

        $this->assign('has_place',$place?101:102);
        $this->assign('place',$place);
        $this->assign('data',$data);
        return $this->fetch();
    }
}