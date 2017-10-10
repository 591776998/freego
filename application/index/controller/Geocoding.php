<?php
namespace app\index\controller;
use think\Request;
class Geocoding extends \app\common\controller\Base
{

    //接收经纬度，返回反地理编码的结果
    public function read(){
        if(
            !$this->check('lat',['number','r','min:-90','max:90'])||
            !$this->check('lng',['number','r','min:-180','max:180'])
        ){
            return error($this->checkError());
        }
        $lat  = $this->param('lat','');
        $lng  = $this->param('lng','');
        $type = $this->param('coordtype','gcj02ll');
        $res = model('address','service')->getLocationV2($lat,$lng,$type);
        if($this->isError($res)){
            return error($res);
        }else{
            $res['data']['full_title'] = $res['data']['desc_title'];

            return success($res);
        }
    }


}