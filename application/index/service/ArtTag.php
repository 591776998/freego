<?php
namespace app\index\service;
class ArtTag extends \app\common\service\Base
{

    //文章标签列表
    public function index($type = 0, $tag_type = 0){
        $model = model('ArtTag');
        if($type)$model->where('type',$type);
        if($tag_type)$model->where('sub_type',$tag_type);
        $data = $model->select();
        return $this->success($data);
    }

    //根据类型返回不同的排序条件
    public function order($type = 0, $tag_type = 0){
        $model = model('ListOrder');
        if($type)$model->where('type',$type);
        if($tag_type)$model->where('sub_type',$tag_type);
        $data = $model->select();
        return $this->success($data);
    }


    //标签分类
    protected function arr_merge( $data = [], $field1 = '', $field2 = ''){
        $result = [];
        foreach ($data as $tag){
            $result[$tag[$field1]][$tag[$field2]][] = $tag;
        }
        return $result;
    }


}