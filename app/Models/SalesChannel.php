<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\StitchLiteProductId;

abstract class SalesChannel extends Model
{
    //
    const SYNC_OK = 1;
	const SYNC_ERROR = 2; 
	protected $fillable = ['id'];


/*

      To sync Stitch lite product quantities to the various channels, we will probably 
      need do two passes to the data retrieved from the channels. The approach works well
      if we assume the quantity in the channels will always be smaller or equal to the 
      quantity in internal db.

      1. Scan the database to build a data structure like this

      $inv[$sku]['old_quantity'] = Quantity of the $sku in DB

      2. For each of the channel data fetched, update the above data structure so that

      $inv[$sku]['delta_quantity'] += $inv[$sku]['old_quantity'] - $channel_quantity

      This is the first pass.

      3. On the second pass, much of the operations is like the current sync implementation with
      the following difference:

       a) The quantity used in the db update will be

           $quantity = $inv[$sku]['old_quantity'] - $inv[$sku]['delta_quantity'];

       b) If the $quantity value from a) is different from the quantity fetched from channel,
          update the channel inventory # to the new value using the channel API

      */

	// if sku is changed on the channel, the old sku's will remain in the db
	static public function sync() {

		$syncok = self::SYNC_OK;
		$message = "";

		$ch =  curl_init();
		if ($ch) {
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$scs = config('app.saleschannels');
	        foreach ($scs as $sc) {

	        	$fsc = 'App\Models\Channels\\'.$sc;
	        	$scm = DB::table('sales_channels')->where('name', $sc)->first();
	            $salesChannel = new $fsc(['id' => $scm->id]);

	            $res = $salesChannel->syncProducts($ch);
	            if ($res['status'] == self::SYNC_ERROR) {
	            	$syncok = self::SYNC_ERROR;
	            	$message .= $res['message'];
	            }
	        }
	        if ($message == "") { $message = "OK"; }
	    }
	    else {
	    	$synok = self::SYNC_ERROR;
	    	$message = "Unable to initialize curl";
	    }


		$data = array("status" => $syncok,"message" => $message);
		return $data;

	}

	protected function _getProductId($sku) {

		$prodId = '';
		$prod = StitchLiteProduct::where('sku', '=', $sku)->first();
		if ($prod == null) {
			$prodIdO = StitchLiteProductId::create();
			$prodId = $prodIdO->id;
		}
		else {
			$prodId = $prod->stitch_lite_product_ids_id;
		}
		return $prodId;
	}

	/* if we want to minimize write to db, we can consider
       checking if the name, quantity, price, sku has changed 
       before updating the db */
	protected function _createOrUpdateProduct($data) {

		$prodId = $this->_getProductId($data['sku']);
		$prod = StitchLiteProduct::where([
				[ "stitch_lite_product_ids_id", '=', $prodId],
				[ "sku", '=', $data['sku']],
				[ "sales_channel_id", '=', $data['channel_id'] ]

			])->first();
		if (!is_null($prod)) {
			DB::table('stitch_lite_products')
			->where([
				'stitch_lite_product_ids_id' => $prodId,
				'sales_channel_id' => $data['channel_id']
			    ])
			->update([
				'name' => $data['name'],
				'quantity' => $data['quantity'],
				'price' => $data['price']
				]);
			/*$prod->name = $data['name'];
			$prod->quantity = $data['quantity'];
			$prod->price = $data['price'];
			$prod->save();*/
		}
		else {
          StitchLiteProduct::create(
                [
                "stitch_lite_product_ids_id" => $prodId,
                "sku" => $data['sku'], 
                "sales_channel_id" => $data['channel_id'],
                "name" => $data['name'], 
                "quantity" => $data['quantity'], 
                "price" => $data['price'], 
                ]
          );
        }  
	}

	public function products() {

		return $this->hasMany(StitchLiteProduct::class);
	}
	

	abstract function syncProducts($ch);
}
