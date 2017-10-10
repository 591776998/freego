<?php
namespace app\common\model;
use app\common\model;

class LineItem extends model\Base{

    public function Line()
    {
        return $this->belongsTo('Line','line_id','id',[],'LEFT');
    }
}

