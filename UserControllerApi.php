<?php

namespace App\Http\Controllers\API\BuyerWeb;

use App\Http\Controllers\API\HomeController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{

    public function getBuyerWebUserProfile(Request $request){
        // Log::info($request->userId);

        $user_id = DB::table('businesses') // get product detail
        ->select('user_id')
        ->where('profile_slug', $request->userId)
        ->get();
        
        log::info($user_id[0]->user_id);

        $request->merge(['userId' => $user_id[0]->user_id]);
        
        $homeController = new HomeController();
        $response = $homeController->getUserProfileDetail($request);
        if($response){
            return $response;
        }else{
            return response()->json([
            'error' => 'internal server issue !'], 401);
        }
    }
    
    public function getBuyerWebUserProducts(Request $request){
        // Log::info($request->all());

        $user_id = DB::table('businesses') // get product detail
        ->select('user_id')
        ->where('profile_slug', $request->userId)
        ->get();
        
        // log::info($user_id[0]->user_id);

        $request->merge(['userId' => $user_id[0]->user_id]);

        $homeController = new HomeController();
        $response = $homeController->getUserProfileCatalogue($request);
        if($response){
            return $response;
        }else{
            return response()->json([
            'error' => 'internal server issue !'], 401);
        }
    }
}
