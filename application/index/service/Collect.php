<?php
namespace app\index\service;
class Collect extends \app\common\service\Base
{

    //某文章的特点段落
    public function add($user,$type,$sub_type,$target){
        //根据type值和target判断被收藏的目标是否存在
        switch ($type){
            case '103':
                $target_model = model('art');
                break;
            case '104':
                $target_model = model('place');
                break;
            case '106':
                $target_model = model('services');
                break;
            default:
                return $this->error(['desc'=>'不支持收藏该类型！']);
        }
        if(!$target_model->find($target)){
            return $this->error(['desc'=>'请选择您要收藏的内容！']);
        }elseif(model('collect')->where('user_id',$user['id'])->where('type',$type)->where('target_id',$target)->find()){
            return $this->error(['desc'=>'您已经收藏过该内容了！']);
        }

        $data = [
            'user_id'   => $user['id'],
            'type'      => $type,
            'target_id' => $target,
            'c_time'    => THINK_START_TIME,
            'del_time'  => '0'
        ];
        $model = model('collect')->data($data);
        if( $model->save()){
            return $this->success( $model);
        }else{
            return $this->error( ['desc'=> '收藏失败，请稍后再试']);
        }
    }

    public function delete($user, $id){
        $record = model('collect')->where('id',$id)->find();
        if($record && ($record['user_id'] == $user['id'])){
            if($record->destroy($record->id)){
                return $this->success($record);
            }else{
                return $this->error(['desc'=>'取消收藏失败！']);
            }
        }else{
            return $this->error(['desc'=>'您没有收藏该内容！']);
        }


    }

}