<?php
namespace app\branch\controller;
class Best extends \app\common\controller\BranchBase
{

    public function save(){
        if(!$this->branch)return $this->errorLogin();
        if(!$this->check('id',['number','r','min:0'])){
            return error($this->checkError());
        }


        $art_id = $this->param('id',0);

        $location_name = $this->branch['location_name'];
        $location_value = $this->branch['location_value'];


        $record = model('forumBest')->where('art_id',$art_id)->where('location_name',$location_name)->where('location_value',$location_value)->find();
        if($record){
            return error(['desc'=>'已经加精']);
        }
        $save = [
            'art_id'            => $art_id,
            'location_name'     => $location_name,
            'location_value'    => $location_value,
        ];
        if(model('forumBest')->save($save)){
            return success([]);
        }else{
            return error(['desc'=>'没有做任何修改']);
        }
    }

}