<?php

namespace App\Http\Controllers\buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $response = Http::get("https://test.trazoo.in/api/getBuyerWebUserProfile/$id", []);
        if ($response->failed()) {
          return response()->json(['message' => 'Failed to retrieve data'], 500);
        }
        $profileData= $response->json();
        $CheckDomain = $_SERVER['HTTP_HOST'];
        if ($CheckDomain == 'test.trazoo.in' || $CheckDomain == 'test2.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
        if ($CheckDomain == 'seller.trazoo.in') {
            $URI = 'https://seller.trazoo.in';
        }
        $Proresponse = Http::get("https://test.trazoo.in/api/getBuyerWebUserProducts/$id", []);
        if ($Proresponse->failed()) {
          return response()->json(['message' => 'Failed to retrieve data'], 500);
        }
        $proData= $Proresponse->json();
        return view('users.user-profile',['profile'=>$profileData,'cURI' => $URI,'product' => $proData]);
    }

    public function view_product()
    {
        return view('products.product-view');
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