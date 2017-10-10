<?php
namespace app\index\service;
class ArtPlan extends \app\common\service\Base
{

    //文章标签列表
    public function save($main_id = 0, $data = []){
        $c = [];
        if($data){
            foreach ($data as $d){
                $c[] = [
                    'main_id'   => $main_id?$main_id:0,
                    'title'     => $d['title'],
                    'content'   => $d['content'],
                    'price'     => $d['price'],
                    'del_time'  => '0'

                ];
            }
            $result = model('ArtPlan')->saveAll($c);
            if($result){
                return $this->success(['id'=>$result]);
            }else{
                return $this->error(['desc'=>'付费信息保存失败！']);
            }
        }else{
            return $this->error(['desc'=>'你没有上传任何信息！']);
        }
    }


}