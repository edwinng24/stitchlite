<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StitchLiteProduct extends Model
{
    //
    protected $primaryKey = 'stitch_lite_product_ids_id';
    public $incrementing = false;
    protected $fillable = ['stitch_lite_product_ids_id', 'sku', 'name', 'quantity', 'price', 'sales_channel_id'];

    public function sales_channel() {

    	return $this->belongsTo(SalesChannel::class);
    }

}
