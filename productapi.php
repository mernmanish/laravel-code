<?php

namespace App\Http\Controllers\API\BuyerWeb;

use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    public function getCategoriesForBuyer($id=-1){

        log::info("$id");
        $sellerCategories =array();
        $parent_category_name = "All";

        if($id==-1){
            $ScreenCat = "1, 2, 3, 4, 70, 141, 88, 90, 151, 289";
            $array = explode(",", $ScreenCat);
            for ($i = 0; $i < count($array); $i++) {
                $categories = DB::table('fashions')
                    ->select('id', 'cat_alias as name', 'image_link', 'cat_slug')
                    ->where('id','=',$array[$i])
                    ->get();
                if(count($categories) == 1)
                {
                    array_push($sellerCategories,$categories[0]);
                }
            }
        }else{
            $category_id = DB::table('fashions') // get product detail
            ->select('id','cat_alias')
            ->where('cat_slug', $id)
            ->first();
            
            $parent_category_name = $category_id->cat_alias;
    
            $sellerCategories = DB::table('fashions')
            ->select('id', 'cat_alias as name', 'image_link', 'cat_slug')
            ->where('sub_id', $category_id->id)
            ->where('status', '=', '1')
            ->get();
        }

        foreach( $sellerCategories as $key => $value ) {
            /* fetching sub categories */
            $searchSubCategories = DB::table('fashions')
                ->select('id', 'cat_alias as name', 'image_link', 'cat_slug')
                ->where('sub_id', $value->id)
                ->where('status', '=', '1')
                ->get();

            if( count($searchSubCategories) > 0 ) {
                $sellerCategories[$key]->isSubCategories = true;
            } else {
                $sellerCategories[$key]->isSubCategories = false;
            }
        }

        return response()->json([
            'allCategories' => $sellerCategories,
            'parentCategoryName' => $parent_category_name
        ]);
    }

    public function getSubCategoriesForBuyer($id="-1")
    {
        $searchSubCategories = DB::table('fashions')
        ->select('id', 'cat_alias as name', 'image_link', 'cat_slug')
        ->where('sub_id', $id)
        ->where('status', '=', '1')
        ->get();
       
        foreach($searchSubCategories as $k => $v ) {
            /* fetching sub of sub category */
            $subOfSubCategories = DB::table('fashions')
                ->select('id', 'cat_alias as name', 'image_link', 'cat_slug')
                ->where('sub_id', $v->id)
                ->where('status', '=', '1')
                ->get();
            if ( count($subOfSubCategories) > 0 ) {
                $searchSubCategories[$k]->isSubCategories = true;
            } else {
                $searchSubCategories[$k]->isSubCategories = false;
            }            
        }
        return response()->json([
            'allSubCategories' => $searchSubCategories
        ]);
    }

    public function getBuyerWebProductsListsByPage(Request $request){
        log::info($request->all());

        $category_id = DB::table('fashions') // get product detail
        ->select('id','cat_alias')
        ->where('cat_slug', $request->categoryID)
        ->first();
        
        log::info("category id-".$category_id->id);

        $request->merge(['categoryID' => $category_id->id]);
        $productController = new ProductController();
        $response = $productController->getProductsListByPage($request);
        if($response){
            return $response;
        }else{
            return response()->json([
            'error' => 'internal server issue !'], 401);
        }
    }

    public function getBuyerWebProductDetails(Request $request){
        // log::info($request->all());

        $product_id = DB::table('products') // get product detail
        ->select('id')
        ->where('prod_slug', $request->productId)
        ->get();
        
        log::info("Product id- ".$product_id[0]->id);

        $request->merge(['productId' => $product_id[0]->id]);

        $productController = new ProductController();
        $response = $productController->getProductDetails($request);
        if($response){
            return $response;
        }else{
            return response()->json([
            'error' => 'internal server issue !'], 401);
        }
    }

}
