<?php

namespace App\Models\Channels;

use App\Models\SalesChannel;
use App\Models\StitchLiteProduct;

class Shopify extends SalesChannel
{
	const DOMAIN = "edwin-php-store.myshopify.com";

	public function syncProducts($ch) {

		$status = self::SYNC_OK;
		$message = "OK";

      	$apikey = env('SHOPIFY_APIKEY','');
      	$apipasswd = env('SHOPIFY_PASSWORD', '');
      	if ($apikey == '' || $apipasswd == "") {
      		$status = self::SYNC_ERROR;
      		$message = "Missing Shopify Credentials";
      	}
      	else {
      		$url = "https://".$apikey.":".$apipasswd."@".self::DOMAIN."/admin/products.json?fields=title,variants";
      		curl_setopt($ch, CURLOPT_URL, $url);
      		$content = curl_exec($ch);
      		if ($content) {
                        $res = json_decode($content, true);
                        //dd($res);
                        if ($res == null || ($res['errors'] ?? "") != "") {
                              $status = self::SYNC_ERROR;
                              $message = $res == null ? "Invalid Shopify Server Response" : $res['errors'];
                        }
                        else {
                              foreach ($res['products'] as $product) {
                                    $title = $product['title'];
                                    foreach ($product['variants'] as $variant) {
                                          $data = [
                                                'name' => $title." / ".$variant['title'],
                                                'sku' => $variant['sku'],
                                                'quantity' => $variant['inventory_quantity'],
                                                'price' => $variant['price'],
                                                'channel_id' => $this->id, 
                                          ];
                                          $this->_createOrUpdateProduct($data);
                                    }
                                    
                              }
                        }
                  }

      	}

	
		return array('status' => $status, 'message' => $message);
	}


}