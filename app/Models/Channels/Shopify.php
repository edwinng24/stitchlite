<?php

namespace App\Models\Channels;

use App\Models\SalesChannel;
use App\Models\StitchLiteProduct;

class Shopify extends SalesChannel
{
	
	public function getProducts($ch) {

		$status = self::SYNC_OK;
		$message = "OK";

      	$apikey = env('SHOPIFY_APIKEY','');
      	$apipasswd = env('SHOPIFY_PASSWORD', '');
      	if ($apikey == '' || $apipasswd == "") {
      		$status = self::SYNC_ERROR;
      		$message = "Missing Shopify Credentials";
      	}
      	else {
      		$url = "https://".$apikey.":".$apipasswd."@edwin-php-store.myshopify.com/admin/products.json?fields=title,variants";
      		curl_setopt($ch, CURLOPT_URL, $url);
      		$content = curl_exec($ch);
      		if ($content) {
                        $res = json_decode($content, true);
                        if ($res == null || ($res['errors'] ?? "") != "") {
                              $status = self::SYNC_ERROR;
                              $message = $res == null ? "Invalid Shopify Server Response" : $res['errors'];
                        }
                        else {
                              foreach ($res['products'] as $product) {
                                    $title = $product['title'];
                                    foreach ($product['variants'] as $variant) {
                                          $name = $title." / ".$variant['title'];
                                          $sku = $variant['sku'];
                                          $quantity = $variant['inventory_quantity'];
                                          $price = $variant['price'];
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

      	}

	
		return array('status' => $status, 'message' => $message);
	}


}