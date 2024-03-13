<?php

namespace App\Http\Controllers\buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $response = Http::get('https://test.trazoo.in/api/getCategoriesForBuyer/-1', []);
       if ($response->failed()) {
        return response()->json(['message' => 'Failed to retrieve data'], 500);
       }
       $data = $response->json('allCategories');
      
    //    $array = json_decode($data, true);
       return view('products.categories',['catData' => $data]);
    }

    public function subCategory($id)
    {
       $response = Http::get("https://test.trazoo.in/api/getCategoriesForBuyer/$id", []);
       if ($response->failed()) {
        return response()->json(['message' => 'Failed to retrieve data'], 500);
       }
       $data = $response->json('allCategories');
    //    $array = json_decode($data, true);
       return view('products.categories',['subcatData' => $data]);
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
