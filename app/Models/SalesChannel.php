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

	protected function _createOrUpdateProduct($data) {

		$prodId = $this->_getProductId($data['sku']);
		$prod = StitchLiteProduct::where([
				[ "stitch_lite_product_ids_id", '=', $prodId],
				[ "sku", '=', $data['sku']],
				[ "sales_channel_id", '=', $data['channel_id'] ]

			])->first();
		if (!is_null($prod)) {
			$prod->name = $data['name'];
			$prod->quantity = $data['quantity'];
			$prod->price = $data['price'];
			$prod->save();
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
