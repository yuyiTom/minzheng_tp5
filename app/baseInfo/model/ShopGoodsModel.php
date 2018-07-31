<?php


namespace app\baseInfo\model;

use think\Model;
use think\Db;

class ShopGoodsModel extends Model
{
	protected $type = [
        'more' => 'array',
    ];

	protected $table = 'mz_user';

	public function getYuyi()
	{
		return "yuyi";
	}

}