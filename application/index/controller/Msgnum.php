<?php
namespace app\index\controller;
class Msgnum extends \app\common\controller\Base
{
    //
    public function read(){
        $save_read = [];
        $session_type_arr = [101,102];

        if(!$this->user){
            return error(['desc'=> '请先登录！'],'请先登录！','401');
        }
        $user_id = $this->user['id'];
        if($this->user['id'] != $this->param('id')){
            return error(['desc'=> '只能查看自己的消息数！']);
        }
        $msg_read = model('msg_read')->field('last_time,session_type')->where('user_id',$user_id)->select();
        $read_type = array_map(function($read){
            return $read['session_type'];
        },$msg_read);
        //检查缺少的类型
        foreach ($session_type_arr as $k => $v){
            if(!in_array($v,$read_type)){
                $save_read[] = [
                    'session_type'  => $v,
                    'user_id'       => $this->user['id'],
                    'last_time'     => '0',
                    'del_time'      => '0'
                ];
                $msg_read[] = ['session_type'=>'101','last_time'=>'0'];
            }
        }
        $m_r = [];
        foreach ($msg_read as $k => $v){
            $m_r[$v['session_type']] = $v['last_time'];
        }

        if($save_read){
            model('msg_read')->saveAll($save_read);
        }

        $msg_num = model('MsgSession')->field('count(*) as num,session_type')->where(function ($query)use($user_id){
            $query->where('target_type',100)->whereOr(function ($query)use($user_id){
                $query->where('target_id',$user_id)->where('target_type',101);
            });
        });
        if($m_r){
            $msg_num->where(function ($query)use($m_r){
                $first = true;
                foreach ($m_r as $k => $v){
                    if($first){
                        $query->where(function ($query)use($k,$v){
                            $query->where('session_type',$k)->where('m_time','>',$v);
                        });
                    }else{
                        $query->whereOr(function ($query)use($k,$v){
                            $query->where('session_type',$k)->where('m_time','>',$v);
                        });
                    }
                    $first= false;
                }
            });
        }
        $res = $msg_num->group('session_type')->select();
        $out = array_map(function($item){
            return $item->toArray();
        },$res);
        $out = array_column($out,'num','session_type');

        return success([
            'notice'    => isset($out[101])?$out[101]:'0',
            'apply'     => isset($out[102])?$out[102]:'0',
        ]);
    }


}