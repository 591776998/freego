<?php
namespace app\index\controller;
class Plan extends \app\common\controller\MainBase
{

    //新建
    public function save(){
        if(!$this->check('art_id',['number','r','min:1'])){
            return error(['错误的活动ID']);
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
        if(isset($data['has_max_num']) && (in_array($data['has_max_num'],['on','101']))){
            $data['has_max_num'] = '101';
        }else{
            $data['has_max_num'] = '102';
        }
        if(isset($data['has_pre_num']) && (in_array($data['has_pre_num'],['on','101']))){
            $data['has_pre_num'] = '101';
        }else{
            $data['has_pre_num'] = '102';
        }
        $data['need_book']   = isset($data['need_book'])?$data['need_book']:'102';
        $data['ftof']        = isset($data['ftof'])?$data['ftof']:'102';
        $data['verify']      = isset($data['verify'])?$data['verify']:'102';
        $data['price']       *= 100;
        $data['fact_price']  *= 100;

        if(101 == $data['has_max_num']){
            $data['stock'] = $data['total_num'];
        }

        $model = model('Plan')->data($data);
        if($model->allowField(true)->save()){
            return success($model);
        }else{
            return error(['desc'=>'创建失败']);
        }
    }

    //更新
    public function update(){
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
        if(isset($data['has_max_num']) && (in_array($data['has_max_num'],['on','101']))){
            $data['has_max_num'] = '101';
        }else{
            $data['has_max_num'] = '102';
        }
        if(isset($data['has_pre_num']) && (in_array($data['has_pre_num'],['on','101']))){
            $data['has_pre_num'] = '101';
        }else{
            $data['has_pre_num'] = '102';
        }
        $data['need_book']   = isset($data['need_book'])?$data['need_book']:'102';
        $data['ftof']        = isset($data['ftof'])?$data['ftof']:'102';
        $data['verify']      = isset($data['verify'])?$data['verify']:'102';
        $data['price']      *= 100;
        $data['fact_price'] *= 100;

        if(101 == $data['has_max_num']){
            $data['stock'] = $data['total_num'];
        }

        if(model('Plan')->allowField(true)->save($data,['id'=>$this->param('id','0')])){
            return success(model('Plan')->where('id',$this->param('id','0'))->find());
        }else{
            return error(['desc'=>'没有做任何修改']);
        }
    }

    //删除
    public function delete(){
        if(model('Plan')->destroy($this->param('id','0'))){
            return success(['id'=>$this->param('id','0')]);
        }else{
            return error(['desc'=>'未找到该记录']);
        }
    }
}