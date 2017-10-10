<?php
namespace app\cron\controller;
use think\Controller;
use think\Db;

class Uim extends Controller{

    public function index(){
        //1.
        foreach (model('User')->select() as $vo){
            $model = new \app\index\service\User();
            $res = $model->fullModel($vo['id'],0);
            imma('go');

        }

    }

    public function addUim(){


    }

    public function delUim(){
        $sql = 'SELECT id FROM	user_uim u WHERE id IN ( SELECT	min(id)	FROM user_uim where del_time=0 GROUP BY (user_id + accid) ORDER BY id ASC ) AND ( SELECT count(*) FROM user_uim	WHERE del_time=0 and user_id = u.user_id AND accid = u.accid) > 1 and del_time=0';
        $res = Db::query($sql);
        chunk(function ($vo){
            $id = array_map(function($i){
                return $i['id'];
            },$vo);
            if($id){
                $now = floor(THINK_START_TIME);
                $sql = 'update user_uim set `del_time`='.$now.' where id in('.implode(',',$id).')';
                Db::execute($sql);
            }
        },$res,500);
        return success([]);
    }

    protected function filter($data){
        foreach ($data as $k => $v){

        }

    }

}