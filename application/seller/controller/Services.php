<?php
namespace app\seller\controller;
class Services extends \app\common\controller\SellerBase
{
    //创建
    public function create(){

        $this->assign('place_id',$this->param('place_id'));
        return $this->fetch();
    }

    public function editor(){
        //1.

        $data = model('services')->with(['plan'=>function($query){
            $query->where('status','<>',99);
        },'img'])->where('id',$this->param('id',0))->find();

        $this->assign('data',$data);
        return $this->fetch();
    }

    //新建
    public function save(){
        if(!$this->check('place_id',['number','r'])){
            return error(['desc'=>'错误的服务地点，请返回服务地点页面！']);
        }
        if(!isset($this->param['title']) || !$this->param['title']){
            return error(['desc'=>'请填写服务名称！']);
        }
        if(!isset($this->param['discount']) || !$this->param['discount']){
            return error(['desc'=>'请填写折扣！']);
        }elseif( 0 > $this->param['discount'] ){
            return error(['desc'=>'折扣应该大于0折']);
        }elseif (10 < $this->param['discount'] ) {
            return error(['desc' => '折扣应该小于10折']);
        }

        $file = $this->request->file();
        $data = $this->param;
        //1.保存图片
        if(isset($file['img']) && $file['img']){
            //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图 110动态视频封面图 111群组封面图 112评论图片 113地点相册
            $img_res = model('image','service')->upload($file['img'],108);
            $data['img_id'] = $img_res['id'];
        }elseif ($this->has('img_id')){
            $data['img_id'] = $this->param('img_id');
        }else{
            return error(['desc'=>'请选择一张图片']);
        }
        $model = model('services');
        $model->data($data);
        if($model->allowField(true)->save()){
            return success($model);
        }else{
            return error(['desc'=>'保存失败！']);
        }
    }

    //更新
    public function update(){
        if(!$this->check('id',['number','r','min:1'])){
            return error(['desc'=>'错误的ID!']);
        }
        $data = $this->param;

        if(isset($this->param['discount'])){
            if( 0 > $this->param['discount'] ){
                return error(['desc'=>'折扣应该大于0折']);
            }elseif (10 < $this->param['discount'] ) {
                return error(['desc' => '折扣应该小于10折']);
            }
            $data['discount']   = $this->param('discount',10);
        }
        if($this->has('title')){
            if('' == trim($this->param['title'])){
                return error(['desc'=>'请填写标题']);
            }
            $data['title'] = $this->param['title'];
        }

        $file = $this->request->file();
        //1.保存图片
        if(isset($file['img']) ){
            if($file['img']){
                //图片类型 0未分类  101游记 102活动 103提问 104动态 105头像 106用户主页图片 107地点封面图 108服务封面图 109套餐封面图 110动态视频封面图 111群组封面图 112评论图片 113地点相册
                $img_res = model('image','service')->upload($file['img'],108);
                $data['img_id'] = $img_res['id'];
            }
        }elseif ($this->has('img_id')){
            $data['img_id'] = $this->param('img_id');
        }
        if(!$data){
            return error(['desc'=>'没有修改任何数据']);
        }

        $model = model('services');
        $model->where('id',$this->param['id']);
        if($model->allowField(true)->update($data)){
            return success($model->where('id',$this->param['id'])->find());
        }else{
            return error(['desc'=>'保存失败！']);
        }
    }

    //删除
    public function delete(){
        if(model('Services')->where('id',$this->param('id','0'))->update(['status'=>'99'])){
            return success(['id'=>$this->param('id','0')]);
        }else{
            return error(['desc'=>'未找到该记录']);
        }
    }
}