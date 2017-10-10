<?php
//模型父类
namespace app\common\model;
class BaseNodelete extends \think\Model{

//    use SoftDelete;

    //protected $deleteTime = 'del_time';
    //protected $updateTime = 'm_time';
    //protected $createTime = 'c_time';

    // 读取器
    protected function getStatusStrAttr($status, $data)
    {
        if ( $data['del_time']){
            return '已删除';
        }
        else if ( $data['status']){
            $status_arr = array(
                '99'    => '已删除',
                '100'   => '禁用',
                '101'   => '正常',
                '102'   => '待审核',
            );
            return $status_arr[$status];
        }
        else {
            return '正常';
        }
    }

}