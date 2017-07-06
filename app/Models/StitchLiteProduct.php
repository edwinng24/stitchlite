<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StitchLiteProduct extends Model
{
    //
    protected $fillable = ['sku', 'name', 'quantity', 'price', 'channel_id'];
}
