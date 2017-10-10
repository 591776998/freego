<?php
namespace app\seller\controller;
class Index extends \app\common\controller\SellerBase
{

    public function index(){

        return $this->fetch();
    }

}