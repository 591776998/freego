<?php
namespace app\common\service;

class System extends \app\common\service\Base
{
    //取得单个系统设置的值
    public function get( $name, $default = false){
        //1.获取数据库配置，如果数据库没有配置，获取配置文件配置，否则取默认值
        $value = $this->getValue( 'db', $name);
        if($value)return $value;
        $value = $this->getValue( 'file', $name);
        if($value)return $value;
        if($default)return $default;
        //这里可以考虑抛出错误，或者其他处理
        return null;
    }

    /************************** 非public ***********************************************/

    //获取变量的配置
    protected function getValue( $type = 'db', $name = '', $default = false){
        switch ($type){
            case 'db':
                $data = $this->getDbConfig( $name);
                break;
            case 'file':
                $data = $this->getFileConfig( $name);
                break;
            default:
                return '';
        }
        if(!$data && $default){
            return $default;
        }
        return $data;
    }

    //db
    protected function getDbConfig( $name){
        $system = new \app\common\model\System();
        return $system->where('name', $name)->where('status', 101)->value('value');
    }

    //file
    protected function getFileConfig( $name){
        return config( $name);
    }


}