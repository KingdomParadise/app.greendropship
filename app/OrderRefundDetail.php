<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderRefundDetail extends Model
{
    protected $table = 'order_refund_details';
	public $timestamps = true;

    protected $fillable = [
        'id_order', 'sku', 'quantity'
    ];
}
