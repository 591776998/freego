<?php
namespace app\seller\controller;
class Content extends \app\common\controller\SellerBase
{

    public function create(){

        $this->assign('id',$this->param('id'));
        $this->assign('type',$this->param('type'));
        return $this->fetch();
    }

    //创建
    public function editor(){
        //接收文章或者地点的id和type值，
        if(!$this->check('id',['number','r'])){
            return $this->errorPage('错误的id');
        }
        switch ($this->param('type',0)){
            case '103':
//                $res = model('')
                $res = model('ArtPara')->with(['img','video'])->where('main_id',$this->param['id'])->order('order_weight asc')->select();
                break;
            case '104':
                $res = model('ServicesPara')->with(['img','video'])->where('main_id',$this->param['id'])->order('order_weight asc')->select();
                break;
            default:
                //未知
                return '错误的类型';
        }

        $this->assign('data',isset($res)?$res:[]);
        $this->assign('id',$this->param('id'));
        $this->assign('type',$this->param('type'));
        return $this->fetch();
    }

}