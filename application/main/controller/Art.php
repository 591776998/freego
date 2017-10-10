<?php
namespace app\main\controller;
class Art extends \app\common\controller\MainBase
{
    public function read(){
        return $this->fetch();
    }

    public function create(){
        $art = model('art')->data([
            'user_id'           => $this->user['id'],
            'type'              => $this->param('type',101),
            'content_type'      => $this->getContentType($this->param('type',101)),
            'status'            => 103,
            'c_time'            => floor(THINK_START_TIME),
            'm_time'            => floor(THINK_START_TIME)
        ]);
        if(!$art->save()){
            return $this->errorPage('创建文章错误');
        }
        if(102 == $art->type)
        {
            $a = model('art_activity')->data([
                'id'    => $art->id,
                'tid'   => 0
            ])->save();
            if(!$a){
                $art->destroy($art->id);
                return $this->errorPage('创建失败');
            }
        }
        return $this->redirect('editor', ['id' => $art['id']]);
    }

    //编辑
    public function editor(){
        //如果没有ID，就新建一个文章，
        if(!$this->user){
            return $this->errorPage('请先登录再编辑文章');
        }
        if(!$this->check('id',['number','r','min:1'])){
            return $this->errorPage('错误的文章ID'.$this->param('id'));
        }
        $art_id = $this->param('id');

        $art_service = new \app\index\service\Art();
        $data = $art_service->read($this->user, $art_id);
        if($this->isError($data)){
            return $this->errorPage($data['desc']);
        }

        if((102 == $data['data']['type'] ) && (!isset($data['data']['art_activity']) || !$data['data']['art_activity'])){
            $data['data']['art_activity'] = model('art_activity')->data([
                'id'    => $data['data']['id'],
                'tid'   => 0
            ])->save()->toArray();
            if(!isset($data['data']['art_activity']['lead_head_img_url']))$data['data']['art_activity']['lead_head_img_url'] = '';
        }
//      $res = model('ArtPara')->with(['img','video'])->where('main_id',$art_id)->order('order_weight asc')->select();

        $res = [];

//      $plan = model('Plan')->where('art_id',$art_id)->where('status',101)->select();

        $this->assign('para_data',$data['data']['content']);
        if(isset($data['data']['activity_plan'])){
            $this->assign('plan',$data['data']['activity_plan']);
        }

//      debug($data['data']);
        $this->assign('data',$data['data']);
        return $this->fetch();
    }

    //传入文章类型，返回内容类型
    protected function getContentType($type){
        if(101 == $type || 102 == $type || 105 == $type){
            return 101;
        }elseif (103 == $type){
            return 102;
        }elseif (104 == $type){
            return 103;
        }else{
            return false;
        }
    }
}