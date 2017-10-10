<?php
namespace app\index\controller;
use think\Request;
class Search extends \app\common\controller\Base
{
    //地点列表
    public function index( ){
        if(!$this->check('type',['number','min:0'])||
            !$this->check('sub_type',['number','min:0'])||
            !$this->check('key_word','string')||
            !$this->check('page',['number','min:1'])||
            !$this->check('page_size',['number','min:1'])||
            !$this->check('lat',['number'])||
            !$this->check('lng',['number'])||
            !$this->check('range',['number']))
        {
            return error(['desc'=>'请上传位置信息']);
        }

        $lat = $this->param('lat',0);
        $lng = $this->param('lng',0);

        if(!$this->has('lat')||!$this->has('lng')){
            $user_id = isset($this->user['id'])?$this->user['id']:'0';
            if($user_id){
                $user_location = model('user_location')->where('id',$user_id)->find();
                if($user_location){
                    $lat = $user_location['lat'];
                    $lng = $user_location['lng'];
                }
            }
        }

        if($this->has('key_word')){
            $key_word = trim($this->param('key_word'));
        }else{
            $key_word = '';
        }
        $range = $this->param('range',0);

        $result = [];
        switch ($this->param('type')){
            case 0:
                //全部
                $result['address'][]     = [
                    'title'     => '搜索'.$key_word.'相关的地点',
                    'type'      => '0',
                    'sub'       => $this->searchCity( $key_word,$this->param('page',1),$this->param('page_size',100))
                ];
                $result['art'][]     = [
                    'title'     => '搜索'.$key_word.'相关的游记',
                    'type'      => '103',
                    'sub'       => $this->searchArt($this->user, 101, 0, $key_word, $this->param('page',1),$this->param('page_size',100))
                ];
                $result['art'][]     = [
                    'title'     => '搜索'.$key_word.'相关的活动',
                    'type'      => '103',
                    'sub'       => $this->searchArt($this->user, 102, 0, $key_word, $this->param('page',1),$this->param('page_size',100))
                ];
                $result['place'][]     = [
                    'title'     => '搜索'.$key_word.'相关的景点',
                    'type'      => '106',
                    'sub'       => $this->searchPlace($this->user, $key_word, 101, $lat, $lng,$range, $this->param('page',1),$this->param('page_size',100))
                ];
                $result['place'][]     = [
                    'title'     => '搜索'.$key_word.'相关的酒店',
                    'type'      => '106',
                    'sub'       => $this->searchPlace($this->user, $key_word, 103, $lat, $lng,$range, $this->param('page',1),$this->param('page_size',100))
                ];
                $result['place'][]     = [
                    'title'     => '搜索'.$key_word.'相关的美食',
                    'type'      => '106',
                    'sub'       => $this->searchPlace($this->user, $key_word, 102,$lat, $lng,$range, $this->param('page',1),$this->param('page_size',100))
                ];
                $result['place'][]     = [
                    'title'     => '搜索'.$key_word.'相关的娱乐',
                    'type'      => '106',
                    'sub'       => $this->searchPlace($this->user, $key_word, 104,$lat, $lng,$range, $this->param('page',1),$this->param('page_size',100))
                ];
                break;
            case 103:
                //文章
                $result = $this->searchArt($this->user,$this->param('sub_type'),0,$this->param('key_word'),$this->param('page',1),$this->param('page_size',10));
                break;
            case 104:
                //提供服务的地点
                $result = $this->searchPlace($this->user, $this->param('key_word'), $this->param('sub_type'), $lat, $lng,$range, $this->param('page',1),$this->param('page_size',10));
                break;
            default:
        }
        return success($result);
    }

    //1.搜索城市
    protected function searchCity($key_word = '',$page = 1,$page_size = 1){
        return model('Address','service')->search($key_word, $page, $page_size);
    }

    //2.搜索文章
    protected function searchArt( $now_user, $sub_type, $tag_id,$key_word, $page, $page_size){
        return model('Art', 'service')->index($now_user, $sub_type, $tag_id, 0, 0, $key_word, 0, 0, 'id', 0, $page, $page_size);
    }
    //3.搜索地点
    protected function searchPlace($user, $key_word, $type, $lat, $lng, $range, $page, $page_size){
        return model('Place', 'service')->index($user, $key_word , $type, '', $lat, $lng, $range, $page, $page_size);
    }

}