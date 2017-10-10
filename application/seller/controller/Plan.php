<?php
namespace app\seller\controller;
class Plan extends \app\common\controller\SellerBase
{
    //创建
    public function create(){

        $this->assign('services_id',$this->param('services_id'));
        return $this->fetch();
    }

    public function editor(){
        $data = model('plan')->with(['img'])->where('id',$this->param('id',0))->find();


        $this->assign('data',$data);
        return $this->fetch();
    }

    //新建
    public function save(){
        $file = $this->request->file();
        if(!$this->check('services_id',['number','r','min:1'])){
            return error(['错误的服务ID']);
        }
        if(!$this->check('title',['string','r'])){
            return error(['desc'=>'请输入套餐名称']);
        }
//        if(!$this->check('discount',['number','r'])){
//            return error(['desc'=>'请输入正确的折扣']);
//        }else{
//            if(0 > $this->param['discount']){
//                return error(['desc'=>'折扣应该大于等于0']);
//            }elseif (10 < $this->param['discount']){
//                return error(['desc'=>'折扣应该小于等于10']);
//            }
//        }
        if(!$this->check('price',['number','r'])){
            return error(['desc'=>'请输入正确的挂牌价']);
        }else{
            if(0 > $this->param['price']){
                return error(['desc'=>'挂牌价应该大于等于0']);
            }
        }
        if(!$this->check('fact_price',['number','r'])){
            return error(['desc'=>'请输入正确的实售价']);
        }else{
            if(0 > $this->param['fact_price']){
                return error(['desc'=>'实售价应该大于等于0']);
            }
        }
        if(!$this->check('back_points',['number','r'])){
            return error(['desc'=>'请输入正确的积分']);
        }else{
            if(0 > $this->param['back_points']){
                return error(['desc'=>'积分应该大于等于0']);
            }
        }

        $data = $this->param;
        $data['has_max_num'] = isset($data['has_max_num'])?$data['has_max_num']:'102';
        $data['has_pre_num'] = isset($data['has_pre_num'])?$data['has_pre_num']:'102';
        $data['need_book']   = isset($data['need_book'])?$data['need_book']:'102';
        $data['ftof']        = isset($data['ftof'])?$data['ftof']:'102';
        $data['verify']      = isset($data['verify'])?$data['verify']:'102';
        $data['price']      *= 100;
        $data['fact_price'] *= 100;

        if(isset($file['img']) && $file['img']){
            //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图 110动态视频封面图 111群组封面图 112评论图片 113地点相册
            $img_res = model('image','service')->upload($file['img'],108);
            $data['img_id'] = $img_res['id'];
        }else{
            return error(['desc'=>'请选择1张图片']);
        }

        if(101 == $data['has_max_num']){
            $data['stock'] = $data['total_num'];
        }

        $model = model('Plan')->data($data);
        if($model->save()){
            return success($model);
        }else{
            return error(['desc'=>'创建失败']);
        }
    }

    //更新
    public function update(){
        $file = $this->request->file();
        if(!$this->check('id',['number','r','min:1'])){
            return error(['错误的ID']);
        }
        if(!$this->check('title',['string','r'])){
            return error(['desc'=>'请输入套餐名称']);
        }
        if(!$this->check('price',['number','r'])){
            return error(['desc'=>'请输入正确的挂牌价']);
        }else{
            if(0 > $this->param['price']){
                return error(['desc'=>'挂牌价应该大于等于0']);
            }
        }
        if(!$this->check('fact_price',['number','r'])){
            return error(['desc'=>'请输入正确的实售价']);
        }else{
            if(0 > $this->param['fact_price']){
                return error(['desc'=>'实售价应该大于等于0']);
            }
        }
        if(!$this->check('back_points',['number','r'])){
            return error(['desc'=>'请输入正确的积分']);
        }else{
            if(0 > $this->param['back_points']){
                return error(['desc'=>'积分应该大于等于0']);
            }
        }

        $data = $this->param;
        $data['has_max_num'] = isset($data['has_max_num'])?$data['has_max_num']:'102';
        $data['has_pre_num'] = isset($data['has_pre_num'])?$data['has_pre_num']:'102';
        $data['need_book']   = isset($data['need_book'])?$data['need_book']:'102';
        $data['ftof']        = isset($data['ftof'])?$data['ftof']:'102';
        $data['verify']      = isset($data['verify'])?$data['verify']:'102';
        $data['price']      *= 100;
        $data['fact_price'] *= 100;

        if(isset($file['img']) && $file['img']){
            //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图 110动态视频封面图 111群组封面图 112评论图片 113地点相册
            $img_res = model('image','service')->upload($file['img'],108);
            $data['img_id'] = $img_res['id'];
        }

        if(101 == $data['has_max_num']){
            $data['stock'] = $data['total_num'];
        }

        $model = model('Plan')->where('id',$this->param('id','0'));
        if($model->update($data)){
            return success(model('Plan')->where('id',$this->param('id','0'))->find());
        }else{
            return error(['desc'=>'保存失败']);
        }
    }

    //删除
    public function delete(){
        if(model('Plan')->where('id',$this->param('id','0'))->update(['status'=>'99'])){
            return success(['id'=>$this->param('id','0')]);
        }else{
            return error(['desc'=>'未找到该记录']);
        }
    }
}