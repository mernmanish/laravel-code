<?php

namespace App\Http\Controllers\API\BuyerWeb;

use App\Http\Controllers\API\HomeController;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;
use App\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public $successStatus = 200;
    public $failStatus = 404;

    public function login()
    {
        /* first if condition for if user enter mobile number as first input */
        Log::info(request('mobile_number_or_email')."---".request('password'));
        if (Auth::attempt([
            'number' => request('mobile_number_or_email'), 'password' => request('password'), 'role' => 'user', 'is_otp_entered' => 1, 'isBlocked' => 0
        ])) {
            $user = Auth::user();
            $userInfo = DB::table('users')
                ->join('businesses', 'users.id', '=', 'businesses.user_id')
                ->select(
                    'users.id',
                    'users.fullname',
                    'users.is_seller',
                    'businesses.businessname',
                    'businesses.business_photo'
                )
                ->where('users.id', $user->id)
                ->get();
            if ($userInfo[0]->is_seller == true) {
                $userInfo[0]->is_seller = true;
            } else {
                $userInfo[0]->is_seller = false;
            }

            $success['token'] = $user->createToken('MyApp')->accessToken;
            return response()->json([
                'status' => $this->successStatus, 'success' => $success,
                'userInfo' => $userInfo
            ]);
        } // else if condition for if user enter email as first input
        else if (Auth::attempt([
            'emailId' => request('mobile_number_or_email'), 'password' => request('password'),
            'role' => 'user', 'is_otp_entered' => 1, 'is_email_verified' => 1, 'isBlocked' => 0
        ])) {
            $user = Auth::user();
            $userInfo = DB::table('users')
                ->join('businesses', 'users.id', '=', 'businesses.user_id')
                ->select(
                    'users.id',
                    'users.fullname',
                    'users.is_seller',
                    'businesses.businessname',
                    'businesses.business_photo'
                )
                ->where('users.id', $user->id)
                ->get();
            if ($userInfo[0]->is_seller == true) {
                $userInfo[0]->is_seller = true;
            } else {
                $userInfo[0]->is_seller = false;
            }
            $success['token'] = $user->createToken('MyApp')->accessToken;
            return response()->json([
                'status' => $this->successStatus, 'success' => $success,
                'userInfo' => $userInfo
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorised'], 401);
        }
    }

    public function register(Request $request){
        log::info($request->all());
        $homeController = new HomeController();
        $response = $homeController->register($request);
        if($response){
            return $response;
        }else{
            return response()->json([
            'error' => 'internal server issue !'], 401);
        }
    }

    public function validateOtp(Request $request){
        log::info($request->all());
        $homeController = new HomeController();
        $response = $homeController->validateOtp($request);
        if($response){
            return $response;
        }else{
            return response()->json([
            'error' => 'internal server issue !'], 401);
        }
    }

}
