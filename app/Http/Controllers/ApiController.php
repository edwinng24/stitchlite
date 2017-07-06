<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesChannel;
use App\Models\StitchLiteProduct;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{

    public function sync() {


        return SalesChannel::sync();
    }


    public function products() {

        /*$products = StitchLiteProduct::all();
        foreach($products as $idx => $product) {
            $sc = $product->sales_channel;
            $products[$idx]->sales_channel_name = $sc->name;
        }
        return $products;*/
        $ps = DB::table('stitch_lite_products')
                        ->leftJoin('sales_channels', 'sales_channel_id', '=', 'sales_channels.id')
                        //->orderBy('stitch_lite_products.updated_at', 'desc')
                        ->select('stitch_lite_products.stitch_lite_product_ids_id as id', 'stitch_lite_products.name', 'sku', 'quantity', 'price', 'sales_channels.name as channel')
                        ->get();
        return $this->_genResults($ps);

    }

    public function product($id) {
        $ps = DB::table('stitch_lite_products')
                        ->leftJoin('sales_channels', 'sales_channel_id', '=', 'sales_channels.id')
                        ->where('stitch_lite_product_ids_id', '=', $id)
                        ->select('stitch_lite_product_ids_id as id', 'stitch_lite_products.name', 'sku', 'quantity', 'price', 'sales_channels.name as channel')
                        ->get();

        return $this->_genResults($ps);

    }

    protected function _genResults($ps) {

        $r = []; $res = [];
        foreach ($ps as $p) {
            $r[$p->id][] = 
                                [
                                'sku' => $p->sku,
                                'name' => $p->name,
                                'quantity' => $p->quantity,
                                'price' => $p->price,
                                'channel' => $p->channel
                                ];
        }
        foreach ($r as $id => $channels){
            $res[] = [
            'id' => $id,
            'channels' => $channels ];
        }
        return $res;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
