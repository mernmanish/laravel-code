<?php

namespace App\Http\Controllers\buyer;

use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
// use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('auth.login');
    }

    public function otp()
    {
        return view('auth.login-otp');
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

    public function login(Request $request)
    {
        
//        app()->call('App\Http\Controllers\buyer\LoginController@login');
         $this->validator($request->all())->validate();
         log::info($request->all());
        $response = Http::post('https://test.trazoo.in/api/buyerWebLogin', [
                'grant_type' => 'password',
                'client_id' => '',
                'client_secret' => '',
                'mobile_number_or_email' => $request->number, //$request->input('mobile_number_or_email'), // replace with the input field name for mobile number
                'password' => $request->password,//$request->input('password'), // replace with the input field name for password
                'scope' => '',
        ]);

        if($response->unauthorized()){
            log::info("response unauthorized");
            return redirect()->back()->with('message', 'Invalid Credentials');
        }

        $login_response = json_decode((string) $response->getBody());
        
        if($response->ok()){

            session(['buyerinfo' => $login_response->userInfo[0]]);
            session(['token' => $login_response->success->token ]);
            return redirect('shop');

        }else{

            return redirect()->back()->with('message', 'Invalid Credentials');

        }
    
        // Log::info(json_encode((string)$response->getBody(), JSON_PRETTY_PRINT));
        // dd(json_decode((string) $response->getBody())->userInfo[0]->id);
        // dd(json_decode((string) $response->getBody())->success->token);

        // $accessToken = json_decode((string) $response->getBody(), true)['access_token'];

        // // store the access token in a session variable or cookie
        // session(['access_token' => $accessToken]);

        // return redirect()->route('dashboard');
    }
    protected function validator(array $data)
    {
        error_log('$data[number is : ' . $data['number']);
        //  $messages = "Please Fill Details";
        return Validator::make($data, [
            'number' => 'required|digits:10|max:10',
            'password' => 'required|string|min:6'
        ]);
    }
}
