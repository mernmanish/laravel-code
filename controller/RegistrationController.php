<?php

namespace App\Http\Controllers\buyer;

use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Validator;


// use Illuminate\Foundation\Auth\RegistersUsers;

// use App\Models\User;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('auth.registration');
    }

    public function registered(Request $request)
    {
        
        $this->validator($request->all())->validate();
        log::info($request->all());
        $fullname = $request->firstname." ".$request->lastname;
        $response = Http::post('https://test.trazoo.in/api/buyerWebRegisteration', [
            'grant_type' => 'password',
            'client_id' => '',
            'client_secret' => '',
            "fullname" => $fullname,
            'mobile_number' => $request->number, //$request->input('mobile_number_or_email'), // replace with the input field name for mobile number
            'password' => $request->password,//$request->input('password'), // replace with the input field name for password
            'scope' => '',
        ]);
        $reg_response = json_decode((string) $response->getBody());
        
        if($response->ok()){
            if($reg_response->status==1122108){
                session(['user_number' =>$request->number]);
                return view('auth.registration',['number'=>$request->number]);
            }
            elseif($reg_response->status==1122107)
            {
                 return redirect('login')->with('message','Already Registered! Please Login to Continue');
            }
            else{
               session(['user_number' =>$request->number]);
               return view('auth.registration-otp');
            }
        }
        else
        {
            return redirect()->back()->with('message', 'Please Try Again !');
        }
        // event(new Registered($user = $this->create($request->all())));

        // $this->guard()->login($user);

        /**return $this->registered($request, $user)
        ?: redirect('registered');*/

    }

    protected function validator(array $data)
    {
        
        error_log('$data[number is : ' . $data['number']);
        //  $messages = "Please Fill Details";
        return Validator::make($data, [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'number' => 'required|digits:10|max:10',
            'password' => 'required|string|min:6',
        ]);
    }

    public function registrationOtp()
    {
        return view('auth.registration-otp');
    }

    public function otpVerification(Request $request)
    {
        $this->otpvalidator($request->all())->validate();
        log::info($request->all());
        $response = Http::post('https://test.trazoo.in/api/validateBuyerWebRegisterationOtp', [
            'grant_type' => 'password',
            'client_id' => '',
            'client_secret' => '',
            'number' => $request->number, //$request->input('mobile_number_or_email'), // replace with the input field name for mobile number
            'otp' => $request->otp,//$request->input('password'), // replace with the input field name for password
            'scope' => '',
        ]);
        $otp_response = json_decode((string) $response->getBody());
        if($response->ok()){
            if($otp_response->status==404){
                return redirect()->back()->with('message', 'Please Enter Valid OTP !');
            }
            else
            {
                return redirect('login')->with('message','Login to continue');
            }
        }
        else
        {
            return redirect()->back()->with('message', 'Please try again !');
        }

    }
    protected function otpvalidator(array $data)
    {
        error_log('$data[otp is : ' . $data['otp']);
        //  $messages = "Please Fill Details";
        return Validator::make($data, [
            'otp' => 'required|digits:4|max:4'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function create(array $data)
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
