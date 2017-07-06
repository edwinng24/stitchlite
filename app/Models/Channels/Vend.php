<?php

namespace App\Models\Channels;

use App\Models\SalesChannel;
use App\Models\StitchLiteProduct;

class Vend extends SalesChannel
{

	const DOMAIN = "https://edwinphpstore.vendhq.com";
	
	public function getProducts($ch) {

		curl_setopt($ch, CURLOPT_POST, false);
		$token = $this->_getAccessToken($ch);

		$url = self::DOMAIN."/api/products?active=true";
		curl_setopt($ch, CURLOPT_URL, $url);
		$headers = array(
					"Authorization: Bearer ".$token
			);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$content = curl_exec($ch);
		//dd(json_decode($content, true));
		if ($content) {
            $res = json_decode($content, true);
            if ($res == null || ($res['error'] ?? "") != "") {
                  $status = self::SYNC_ERROR;
                  $message = $res == null ? "Invalid Vend Server Response" : $res['errors'];
            }
            else {
                foreach ($res['products'] as $product) {
                	$handle = $product['handle'];
                	if ($handle == "vend-discount") {
                		continue;
                	}
                	$name = $product['name'];
                	$sku = $product['sku'];
                	$quantity = $product['inventory'][0]['count'];
                	$price = $product['price'];
                    StitchLiteProduct::updateOrCreate(
                        ["sku" => $sku, "channel_id" => $this->id],
                        [
                              "name" => $name, 
                              "quantity" => $quantity, 
                              "price" => $price, 
                        ]
                    );
                        
                }
            }
      }

	}

	protected function _getAccessToken($ch) {

		// issue a re
		$tokenfile = env('VEND_TOKENFILE','');
		//dd(json_decode(file_get_contents($tokenfile)));
		$tf = json_decode(file_get_contents($tokenfile), true);
		$expires = $tf['expires'];
		//dd([$expires, time()]);
		if ($expires-300 < time()) {
			// token is about to expire in 5 min, refresh it
			$tf_new = $this->_refreshToken($ch, $tf);
			if ($tf_new['refresh_token'] == ""){
				$tf_new['refresh_token'] = $tf['refresh_token'];
			}
			$tf = $tf_new;
			file_put_contents($tokenfile, json_encode($tf));
		}
		return $tf['access_token'];
		
	}

	protected function _refreshToken($ch, $tf) {
		$postStr = "refresh_token=".$tf['refresh_token']."&";
		$postStr .= "client_id=".env('VEND_CLIENTID','')."&";
		$postStr .= "client_secret=".env('VEND_CLIENTSECRET','')."&";
		$postStr .= "grant_type=refresh_token";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postStr);
		curl_setopt($ch, CURLOPT_POST, true);
		$url = self::DOMAIN."/api/1.0/token";
		curl_setopt($ch, CURLOPT_URL, $url);
      	$content = curl_exec($ch);
      	return $content ? json_decode($content, true) : $content;
	}



}