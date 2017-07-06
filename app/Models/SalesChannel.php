<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

	            $res = $salesChannel->getProducts($ch);
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
	

	abstract public function getProducts($ch);
}
