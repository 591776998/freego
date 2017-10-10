<?php
namespace app\common\service;
class Validate extends \app\common\service\Base
{
    protected $fail_desc  = '';
    protected $fail_param = '';
    protected $data       = [];

    public function __construct( $data = [])
    {
        if($data){
            $this->data($data);
        }
    }

    //参数赋值
    public function data( $data){
        if($data)$this->data = array_merge($this->data,$data);
        return $this;
    }

    //获取错误描述
    public function getError(){
        return $this->fail_desc;
    }

    //2.单独验证某参数
    public function index( $param, $rules = [], $param_name = false){
        if(!$rules){
            return true;
        }
        $this->fail_param = $param_name===false?$param:$param_name;
        $this->fail_desc  = '';
        if(!is_array($rules)){
            $rules = [$rules];
        }
        //检查是否是必须的参数
        if(!isset($this->data[$param])){
            if(in_array('r',$rules)|| in_array('require',$rules)) {
                $this->fail_desc = '缺少参数 '.$this->fail_param;
                return false;
            }
            return true;
        }
        //类型检查
        if(!$this->checkType( $this->data[$param],array_shift($rules))){
            return false;
        }
        //范围检查
        foreach ($rules as $rule) {
            if(!$this->checkRange( $this->data[$param],$rule)){
                return false;
            }
        }
        return true;
    }

    //批量验证
    public function check($param_rule = []){
        foreach ($param_rule as $r){
            if(!$this->index($r[0],isset($r[1])?$r[1]:[],isset($r[2])?$r[2]:false)){
                return false;
            }
        }
        return true;
    }

    //参数获取
    public function param( $param, $default = false){
        if(isset($this->data[$param])){
            return $this->data[$param];
        }
        if (false !== $default){
            return $default;
        }
        if('' == $param){
            return $this->data;
        }
        return '';
    }

    //判断某参数是否存在
    public function has( $param){
        if(isset($this->data[$param])){
            return true;
        }else{
            return false;
        }
    }

    /************************ 辅助方法 ******************************/
    //判断是什么类型的规则
    protected function checkType( $param, $rule){
        //如果是字符串且不是数字时，认为是类型
        if(false !== $rule){
            if(strpos($rule,'|') !== false){
                $rules = explode('|',$rule);
                foreach ($rules as $r){
                    if($r && $this->isType( $param, $r)){
                        return true;
                        break;
                    }
                }
                return false;
            }elseif (strpos($rule,'&') !== false){
                $rules = explode('&',$rule);
                foreach ($rules as $r){
                    if($r && !$this->isType( $param, $r)){
                        return false;
                    }
                }
                return true;
            }else{
                if(is_array($rule)){
                    foreach ($rule as $r){
                        if($r && $this->isType( $param, $r)){
                            return true;
                        }
                    }
                    return false;
                }else{
                    if($this->isType( $param, $rule)){
                        return true;
                    }else{
                        return false;
                    }
                }
            }
        }else{
            return true;
        }
    }

    //对类型进行检查
    protected function isType( $param, $rule){
        switch ($rule){
            case 'f':
            case 'file':
                return true;
                break;
            case 'n':
            case 'num':
            case 'number':
                if(is_numeric( $param)){
                    return true;
                }
                $this->fail_desc = $this->fail_param.' 应该是数字类型';
                return false;
                //return is_numeric($param)?true:false;
                break;
            case 'a':
            case 'arr':
            case 'array':
                if(is_array( $param)){
                    return true;
                }else{
                    $this->fail_desc = $this->fail_param.' 应该是数组类型';
                    return false;
                }
                break;
            case 's':
            case 'str':
            case 'string':
                if(is_string( $param)){
                    return true;
                }
                $this->fail_desc = $this->fail_param.' 应该是字符串类型';
                return false;
                break;
            case 'j':
            case 'json':
                if(json_decode($param)){
                    return true;
                }else{
                    $this->fail_desc = $this->fail_param.' 应该是json类型';
                    return false;
                }
            default :
                return true;
        }
    }

    //验证是否符合规则
    protected function checkRange( $param, $rule){
        // 1. 一个数字（视为长度） 2 一个范围
        if($rule){
            if( is_numeric($rule)){
                if( is_numeric($param) || is_string($param)){
                    if(mb_strlen( $param) == $rule){
                        return true;
                    }
                    $this->fail_desc = $this->fail_param.' 的长度应该为'.$rule;
                    return false;
                }elseif( is_array($param)){
                    if(count($param) == $rule){
                        return true;
                    }
                    $this->fail_desc = $this->fail_param.' 的长度应该为'.$rule;
                    return false;
                }else{
                    //比如上传文件的个数
                    return true;
                }
            }elseif ( is_array( $rule)){
                $min = isset($rule['min'])?$rule['min']:$rule[0];
                $max = isset($rule['max'])?$rule['max']:$rule[1];
                if( is_numeric($param) || is_string($param)){
                    if($min !== false && $param < $min){
                        $this->fail_desc = $this->fail_param.' 不能小于'.$min;
                        return false;
                    }elseif($max !== false && $param > $max){
                        $this->fail_desc = $this->fail_param.' 不能大于'.$max;
                        return false;
                    }
                    return true;
                }elseif( is_array($param)){
                    foreach ($param as $p){
                        if($min !== false && $p < $min){
                            $this->fail_desc = $this->fail_param.' 不能小于于'.$min;
                            return false;
                        }elseif ($max !== false && $p > $max){
                            $this->fail_desc = $this->fail_param.' 不能大于'.$max;
                            return false;
                        }
                    }
                    return true;
                }else{
                    return true;
                }
            }elseif ( is_string( $rule)){
                //1.  >xxx 或 <xxx 或min 2. 0,10

                if ( 'phone' == $rule){
                    if( is_phone($param)){
                        return true;
                    }else{
                        $param = str_replace(array("(",")","（","）"),"",$param);
                        if(is_numeric($param)){
                            return true;
                        }
                        $this->fail_desc = '请输入正确的 '.$this->fail_param;
                        return false;
                    }
                }elseif ( 'email' == $rule){
                    if( is_true_email($param)){
                        return true;
                    }else{
                        $this->fail_desc = '请输入正确的 '.$this->fail_param;
                        return false;
                    }
                }elseif(('r' == $rule) || ('require' == $rule)){
                    return true;
                }elseif('full' == $rule){
                    if('' !== $param){
                        return true;
                    }else{
                        $this->fail_desc = $this->fail_param.' 不能为空';
                        return false;
                    }
                }

                if( is_numeric( $param)){
                    if(false !== strpos( $rule, '>=')){
                        if(!( $param >= (substr($rule,2)))){
                            $this->fail_desc = $this->fail_param.' 应大于等于'.(substr($rule,2));
                            return false;
                        }
                        return true;
                    }elseif(false !== strpos( $rule, 'min:')){
                        if(!( $param >= (substr($rule,4)))){
                            $this->fail_desc = $this->fail_param.' 应大于等于'.(substr($rule,3));
                            return false;
                        }
                        return true;
                    }elseif(false !== strpos( $rule, '<=')){
                        if(!( $param <= (substr($rule,2)))){
                            $this->fail_desc = $this->fail_param.' 应小于等于'.(substr($rule,2));
                            return false;
                        }
                        return true;
                    }elseif(false !== strpos( $rule, 'max:')){
                        if(!( $param <= (substr($rule,4)))){
                            $this->fail_desc = $this->fail_param.' 应小于等于'.(substr($rule,3));
                            return false;
                        }
                        return true;
                    }elseif(false !== strpos( $rule, '>')){
                        if(!( $param > (substr($rule,1)))){
                            $this->fail_desc = $this->fail_param.' 应大于'.(substr($rule,1));
                            return false;
                        }
                        return true;
                    }elseif(false !== strpos( $rule, '<')){
                        if(!( $param < (substr($rule,1)))){
                            $this->fail_desc = $this->fail_param.' 应小于'.(substr($rule,1));
                            return false;
                        }
                        return true;
                    }
                    return true;
                }else{
                   //debug([$rule,$param]);
                    $this->fail_desc = $this->fail_param.' 是错误的类型';
                    return false;
                }
            }else{
                return true;
            }
        }else{
            return true;
        }
    }
}