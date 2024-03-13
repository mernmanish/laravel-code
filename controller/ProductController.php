<?php

namespace App\Http\Controllers\buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function productList($var,$id)
    {
        //dd($id);
        // $ids = $decrypted = Crypt::decryptString($encryptedValue);
        // dd(MD5($id));
        $id = Crypt::decryptString(base64_decode($id));
        $response = Http::get("https://test.trazoo.in/api/getBuyerWebProductsListsByPage", [
            'rtype' => 'normal',
            'sorttype' => 'newestFirst', 
            'categoryID' => $id, 
            'page' => '1', 
            'keyword' => ''
        ]);
        if ($response->failed()) {
            return response()->json(['message' => 'Failed to retrieve data'], 500);
        }

        $data = $response->json('product');
        $CheckDomain = $_SERVER['HTTP_HOST'];
        if ($CheckDomain == 'test.trazoo.in' || $CheckDomain == 'test2.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
        if ($CheckDomain == 'seller.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
        $curPage = $response->json('current_page_num');
        $nextPage = $response->json('next_page_num');
        $hasMore = $response->json('has_more');
        $perCount = $response->json('per_page_count');
        $cat_ID = $id;
        $total_row_count = $response->json('total_results');
        return view('products.products-listing', ['productData' => $data,'catId' => $id,'currUrl'=>$URI,'curPage'=>$curPage,'nextPage'=>$nextPage,'hasMore'=>$hasMore,'var' => $var,'total_row_count' => $total_row_count,'cat_ID' => $cat_ID]);
    }

    public function loadMoreProducts(Request $request)
    {
        log::info('call controller');
        $nextPage = $request->nextPage;
        //dd($nextPage);
        // $hasMore = $request->hasMore;
        $catId = $request->catId;
        //dd($catId);
    
        $response = Http::get("https://test.trazoo.in/api/getBuyerWebProductsListsByPage", [
            'rtype' => 'normal',
            'sorttype' => 'newestFirst', 
            'categoryID' => $catId, 
            'page' => $nextPage, 
            'keyword' => ''
        ]);
        if ($response->failed()) {
            return response()->json(['message' => 'Failed to retrieve data'], 500);
        }
        // dd($response->json());
        $proData= $response->json();
        $CheckDomain = $_SERVER['HTTP_HOST'];
        if ($CheckDomain == 'test.trazoo.in' || $CheckDomain == 'test2.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
        if ($CheckDomain == 'seller.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
        $output = '';
        $nextPage = $response->json('next_page_num');
        $has_more = $response->json('has_more');    
        if(!empty($proData))
        {
            foreach($proData['product'] as $product)
            { 
                $output .= '<div class="row" onclick="window.location = "'.url('product').'/'.$product['id'].'">
                <div class="col-md-12 d-flex flex-row product-card">
                    <div class="col-md-4" style="width: 30%;">
                        <img class="" src="'.$URI.''.$product['product_images'].'" alt="" style="height:120px; width: 100%;">
                    </div>
                    <div class="col-md-8" style="width: 70%;">
                        <p class="product-card-title"><a href="'.url('product').'/'.$product['id'].'">'.$product['product_name'].'</a></p>
                        <p class="product-card-seller"><a href="'.url('user-profile').'/'.$product['user_id'].'">'.$product['user_businessname'].'</a></p>
                        <p class="product-card-moq">MOQ: '.$product['moq'].' '.$product['selling_format'].'</p>
                        <p class="product-card-price-margin"><span class="product-card-price">₹'.$product['whole_sale_price'].'/pc</span> <span class="product-card-margin">Margin '.(int)$product['margin'].'%</span></p>
                        <p class="product-card-variant">'.$product['variantStripText'].'</p>
                    </div>
                </div>
            </div>';
            }
        }
        $data = [
            'output' => $output,
            'nextPage' => $nextPage,
            'has_more' => $has_more
        ];
  
        return response()->json($data);
    }

    public function view_product($id)
    {
        // dd($var);
        $id = Crypt::decryptString(base64_decode($id));
       $response = Http::get("https://test.trazoo.in/api/getBuyerWebProductDetails/$id", []);
       if ($response->failed()) {
        return response()->json(['message' => 'Failed to retrieve data'], 500);
       }
       $proData = $response->json();
       $CheckDomain = $_SERVER['HTTP_HOST'];
        if ($CheckDomain == 'test.trazoo.in' || $CheckDomain == 'test2.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
        if ($CheckDomain == 'seller.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
    //    $array = json_decode($proData);
    //    dd($array);
     
        return view('products.product-view',['product' => $proData,'currUrl'=>$URI]);
    }

    public function view_product_test()
    {
        return view('products.product-view-test');
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
