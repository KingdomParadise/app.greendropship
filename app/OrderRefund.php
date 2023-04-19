<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
	protected $table = 'order_refunds';
	public $timestamps = true;

    protected $fillable = [
        'order_id', 'refund_amount', 'refund_reason', 'refund_status', 'request_amount'
    ];
}
