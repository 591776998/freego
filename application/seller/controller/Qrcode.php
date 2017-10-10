<?php
namespace app\seller\controller;
class Qrcode extends \app\common\controller\SellerBase
{
    //生成二维码页面
    public function show(){
        if(!$this->seller){
            return $this->errorPage('未登录，请先登录才能显示二维码');
        }
        if(!$this->seller['place_id']){
            return $this->errorPage('未绑定地点，请先联系管理员绑定地点');
        }

        $data = model('place')->with(['img'])->where('id',$this->seller['place_id'])->find();
        if(!$data){
            return $this->errorPage('未找到该地址');
        }

        //shidian://codepay?jp={"id":"1","title":"龙记夜市海鲜","img_url":"http://39.108.3.179/tp5/public/uploads/static/img/base/xiaolongxia.jpg"}
        $qrcode_data = [
            'id'        => $data['id'].''
//            'title'     => $data['title'],
//            'img_url'   => isset($data['img_url'])?$data['img_url']:''
        ];
        $this->assign('qrcode_data',$qrcode_data);
        return $this->fetch();
    }


}