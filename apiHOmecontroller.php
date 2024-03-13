<?php

namespace App\Http\Controllers\API;

use App\OTP;
use App\Avatar;
use Auth;
use App\User;
use App\Product;
use App\fashion;
use App\Cart;
use File;
use App\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\API\BusinessDiscoveryController;
use Illuminate\Support\Facades\Mail;
use Psy\Util\Str;
use App\SMSModel;
use App\Employees;
use App\Events\NewUserRegistrationEV;
use App\Http\Controllers\AdminUtillity;
use Carbon\Carbon;
use App\Events\UserProfileViewedEV;
use App\Events\ProductImpressionEV;
use App\Http\Controllers\kwiqreply\KwiqreplyController;
use App\Jobs\ApproveSellerApplicationLSJob;
use App\Jobs\EditProfileJob;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //    public function __construct()
    //    {
    //        $this->middleware('auth');
    //    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public $successStatus = 200;
    public $failStatus = 404;

    /*--------------------------------------------------------------------
       used to validate  
      --------------------------------------------------------------------*/
    protected function validator(array $data)
    {
        $rules = [
            'fullname' => 'required|string|max:255',
            //            'email' => 'required_without:phone|nullable|string|email|max:30|unique:users,email',
            'number' => 'required|digits:10|max:10|unique:users',
            'password' => 'required|string|min:6',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            //Log::info('API Registration : $validator failed');

            return new JsonResponse([
                'data' => $validator->errors()
            ], 422);
        } else {
            //Log::info('API Registration : $validator passed');

            return true;
        }
    }
    /* ---- END validator() ---- */


    /*--------------------------------------------------------------------
       used to create account of user  
      --------------------------------------------------------------------*/
    protected function create(array $data)
    {
        $data['password'] = bcrypt($data['password']);

        return User::create([
            'firstname' => ucwords($data['firstname']),
            'lastname' => ucwords($data['lastname']),
            'fullname' => ucwords($data['fullname']),
            'number' => $data['number'],
            'password' => bcrypt($data['password']),
        ]);
    }
    /* ---- END create() ---- */


    /*--------------------------------------------------------------------
       used to buyer login  
      --------------------------------------------------------------------*/
    public function login()
    {
        /* first if condition for if user enter mobile number as first input */
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
            ]);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }
    /* ---- END login() ---- */


    /*--------------------------------------------------------------------
       used to validate Otp
      --------------------------------------------------------------------*/
    public function validateOtp(Request $request)
    {
        $user = new User();
        // log::info($request->all());
        log::info("validateOTP activate");
        $otp = DB::table('otps')
            ->select('id', 'mobile_number', 'otp')
            ->where([['mobile_number', '=', $request->number], ['type', '=', 'mr'],])
            ->orderBy('sent_at', 'desc')
            ->take(1)
            ->get();

        // log::info($otp[0]->otp."==". $request->otp);

        if (count($otp) > 0) {
            if ($otp[0]->otp == $request->otp) {
                DB::table('users')->where('number', $request->number)->update(['is_otp_entered' => 1]);
                $userInfo = DB::table('users')
                    ->join('businesses', 'users.id', '=', 'businesses.user_id')
                    ->select('users.id', 'users.firstname', 'users.fullname', 'users.number', 'businesses.businessname', 'businesses.business_photo')
                    ->where('users.number', $otp[0]->mobile_number)
                    ->get();
                if (count($userInfo) > 0) {
                    $user->firstName = $userInfo[0]->firstname;
                    $user->fullName = $userInfo[0]->fullname;
                    $user->contactNum = $userInfo[0]->number;
                    /* sending message to user */
                    $contactNumForSMS = $user->contactNum;
                    // $messageBody = 'Registration Completed: Welcome to Trazoo. Enjoy seamless wholesale selling/buying. visit our website seller.trazoo.in if you are a seller.';
                    // $sms->sendSms($messageBody, $contactNumForSMS);

                    $Message = "Welcome to Trazoo! You are now a part of Trazoo's business community. Visit Website www.trazoo.in";
                    if (SMSModel::GupShupTransactionalAPI($Message, $contactNumForSMS) != 'success') {
                        // Log::info('#TRSMSLOG mobile validateOtp fail to send welcome message.');
                    }

                    event(new NewUserRegistrationEV($userInfo[0]->id));
                }

                return response()->json([
                    'status' => $this->successStatus,
                    'userInfo' => $userInfo, 'message' => 'Otp has been verified successfully'
                ]);
            }
        }
        return response()->json([
            'status' => $this->failStatus,
            'message' => 'Please enter valid OTP'
        ]);
    }
    /* ---- END validateOtp() ---- */


    /*--------------------------------------------------------------------
       used to edit buyer profile
      --------------------------------------------------------------------*/
    public function editProfile(Request $request)
    {
        log::info("edit main profile");
        $user = Auth::user();
        $GetSellerDetails = DB::table('users')
            ->join('businesses', 'businesses.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.isApproved',
                'users.is_seller',
                'users.number',
                'users.fullname',
                'businesses.industry',
                'businesses.gstin'
            )
            ->where([
                ['user_id', $user->id],
            ])
            ->first();

        if ($request->email != '') {
            $validator = Validator::make(
                ['email' => $request->email],
                ['email' => ['required', 'email']]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status' => $this->failStatus,
                    'message' => 'Please enter valid Email address and try again.'
                ]);
            }
        }

        $alreadyInDB = DB::table('users')
            ->select('id', 'number', 'emailId')
            ->where([
                ['emailId', '=', $request->email],
                ['id', '!=', $user->id]
            ])
            ->get();
        if (count($alreadyInDB) > 0 && $request->email != '') {
            return response()->json([
                'status' => $this->failStatus,
                'message' => 'Sorry email already in use'
            ]);
        }

        $FirstName_inCaseEmptyNameComingInAPI = $user->number;
        $LastName_inCaseEmptyNameComingInAPI = 'User';

        $array = explode(' ', $request->userName);

        $RemoveAllEmptySpaces = array_values(array_filter($array));

        if (count($RemoveAllEmptySpaces) == 0) {
            $newUser['firstname'] = $FirstName_inCaseEmptyNameComingInAPI;
            $newUser['lastname'] = $LastName_inCaseEmptyNameComingInAPI;
        } elseif (count($RemoveAllEmptySpaces) == 1) {
            $newUser['firstname'] = $RemoveAllEmptySpaces[0];
            $newUser['lastname'] = $LastName_inCaseEmptyNameComingInAPI;
        } elseif (count($RemoveAllEmptySpaces) == 2) {
            $newUser['firstname'] = $RemoveAllEmptySpaces[0];
            $newUser['lastname'] = $RemoveAllEmptySpaces[1];
        } elseif (count($RemoveAllEmptySpaces) > 2) {
            $newUser['firstname'] = $RemoveAllEmptySpaces[0];
            $newUser['lastname'] = $RemoveAllEmptySpaces[1] . " " . $RemoveAllEmptySpaces[2];
        }

        $CreateFullName = $newUser['firstname'] . " " . $newUser['lastname'];

        if ($user->emailId != $request->email && $request->email != '') {
            $user->email_verification_token = str_random(25);
            $user->emailId = $request->email;
            /* sending an email to user's new email */
            Mail::send('mail.userEmailVerification', ['user' => $user], function ($message) use ($user) {
                $message->from('noreply@trazoo.in', 'Trazoo');
                $message->subject('E-mail verification - Trazoo');
                $message->to($user->emailId);
            });
        }
        DB::table('users')->where('id', $user->id)->update([
            'firstname' => ucwords($newUser['firstname']),
            'lastname' => ucwords($newUser['lastname']),
            'fullname' => ucwords($CreateFullName),
            'emailId' => $request->email,
            // 'last_active' => date("Y-m-d H:i:s") 
        ]);
        $user->is_email_verified = 0;
        $user->update();
        $this->userId = $user->id;

        if ($request->gstCompliance != null && $request->gstIn != null) {
            DB::table('businesses')
                ->where('user_id', function ($query) {
                    $query->select('id')
                        ->from(with(new User)->getTable())
                        ->where('id', $this->userId);
                })->update([
                    'gstin' => strtoupper($request->gstIn),
                    'gstcompliance' => $request->gstCompliance, //GSTAPI EDITPROFILE
                ]);
        }
        if ($request->gstCompliance != null) {
            DB::table('businesses')
                ->where('user_id', function ($query) {
                    $query->select('id')
                        ->from(with(new User)->getTable())
                        ->where('id', $this->userId);
                })->update(['gstcompliance' => $request->gstCompliance]); //GSTAPI EDITPROFILE
        }

        if ($request->gstIn != null) {
            DB::table('businesses')
                ->where('user_id', function ($query) {
                    $query->select('id')
                        ->from(with(new User)->getTable())
                        ->where('id', $this->userId);
                })->update(['gstin' => strtoupper($request->gstIn)]); //GSTAPI EDITPROFILE
        }

        if($request->gstIn != $GetSellerDetails->gstin){
            //for buyers only GST details will be updated
            Log::info("ApproveSellerApplicationLSJob from Main edit profile");
            dispatch(
                (new ApproveSellerApplicationLSJob($user->id))->onQueue('QE_APPROVE_SELLER')->delay(Carbon::now()->addMinutes(30))
            );
        }

        //#wholeseller_to_wholesaler_change
        $BusinessType = $request->businessType;
        // #wholeseller_to_wholesaler_change_end
        DB::table('businesses')
            ->where('user_id', function ($query) {
                $query->select('id')
                    ->from(with(new User)->getTable())
                    ->where('id', $this->userId);
            })->update([
                'businessname' => ucwords($request->companyName),
                'businesstype' => $BusinessType,
                'industry' => $request->industry,
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
        // here is mechanism to upload pic
        if ($request->image != "") {
            $filename = 'images/' . $user->id;
            if (file_exists($filename)) {
                $companyLogoDir = 'images/' . $user->id . '/companyLogo';
                if (file_exists($companyLogoDir)) {

                    $size = 0;
                    foreach (glob(rtrim($companyLogoDir, '/') . '/*', GLOB_NOSORT) as $each) {
                        $size += is_file($each) ? filesize($each) : folderSize($each);
                    }
                    // here is to remove already exists pic of user
                    if ($size > 0) {
                        $dirPath = $companyLogoDir;
                        if (!is_dir($dirPath)) {
                            throw new InvalidArgumentException("$dirPath must be a directory");
                        }
                        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                            $dirPath .= '/';
                        }
                        $files = glob($dirPath . '*', GLOB_MARK);
                        foreach ($files as $file) {
                            if (is_dir($file)) {
                                self::deleteDir($file);
                            } else {
                                unlink($file);
                            }
                        }
                        rmdir($dirPath);
                        File::makeDirectory('images/' . $user->id . '/companyLogo', 0775, true);
                    }
                } else {
                    File::makeDirectory('images/' . $user->id . '/companyLogo', 0775, true);
                }
            } else {
                File::makeDirectory('images/' . $user->id . '/companyLogo', 0775, true);
            }
            // convert base64 to image and save in database
            $data = base64_decode($request->image);
            $imageName = rand(11111111111111, 99999999999999);
            $file = 'images/' . $user->id . '/companyLogo/' . $imageName . '.png';
            $success = file_put_contents($file, $data);
            if ($success) {
                \DB::table('businesses')
                ->where('user_id', $user->id)
                ->update([
                    'business_photo' => "/" . $file,
                    'business_photo_original' => "/" . $file,
                ]);
                dispatch(
                    (new EditProfileJob($user->id,"profile_image_update"))->onQueue('Q_EDIT_PROFILE')->delay(Carbon::now()->addSeconds(2))
                );
            }
        }
        $businessInfo = Business::where('user_id', $user->id)->first();

        return response()->json(['imagePath' => $businessInfo->business_photo, 'status' => $this->successStatus, 'message' => 'Profile has been edited successfully']);
    }
    /* ---- END editProfile() ---- */




    /*--------------------------------------------------------------------
         used to get master data after home screen
        --------------------------------------------------------------------*/
    public function getMasterData()
    {
        $masterData = DB::table('masterdata')
            ->select('android_version', 'maintanance', 'message', 'relogin')
            ->get();
        return response()->json($masterData);
    }

    /*--------------------------------------------------------------------
       used to register buyer by phone
      --------------------------------------------------------------------*/
    public function register(Request $request)
    {
        // log::info($request->all());
        $requestData = $request->all();
        $requestData['number'] = $request->mobile_number;
        $validator = Validator::make($requestData, [
            'fullname' => 'required',
            'number' => 'required|digits:10|max:10',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            //Log::info("Mobile Validation Failed in register function line 368");
            return response()->json(['error' => $validator->errors()], 401);
        }

        /**
         * 
         * reg phone
         * otp send not entered
         * 
         */
        $userData = User::where('number', $request->mobile_number)->get();
        if (count($userData) > 0) {
            if ($userData[0]->is_otp_entered == 1) {
                return response()->json([
                    'status' => 1122107,
                    'message' => 'Sorry! given number already exists.'
                ]);
            } else {
                $otp = rand(1111, 9999);

                $Message = $otp . " is your OTP for Trazoo registration. Please don't share this OTP with anyone.";
                if (SMSModel::GupShupOTPAPI($Message, $request->mobile_number) != 'success') {
                    //Log::info('#OTPLOG Mobile Register OTP failed for '.$request->mobile_number);
                }

                DB::table('otps')
                    ->insert(['otp' => $otp, 'mobile_number' => $request->mobile_number, 'type' => 'mr']);

                try {
                    $wamsg = (new KwiqreplyController())->kwiqReplyRegisterationOTP($request->mobile_number, $otp);
                    if (!$wamsg) {
                        error_log('#OTPLOG Whatsapp msg Registration OTP failed for ' . $request->mobile_number);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    error_log('#OTPLOG exception Whatsapp msg Registration OTP failed for ' . $request->mobile_number);
                }

                return response()->json([
                    'status' => 1122108,
                    'message' => 'Sorry! your account is not varified.'
                ]);
            }
        }

        $FirstName_inCaseEmptyNameComingInAPI = $request->mobile_number;
        $LastName_inCaseEmptyNameComingInAPI = 'User';

        $array = explode(' ', $request->fullname);

        $RemoveAllEmptySpaces = array_values(array_filter($array));

        if (count($RemoveAllEmptySpaces) == 0) {
            $newUser['firstname'] = $FirstName_inCaseEmptyNameComingInAPI;
            $newUser['lastname'] = $LastName_inCaseEmptyNameComingInAPI;
        } elseif (count($RemoveAllEmptySpaces) == 1) {
            $newUser['firstname'] = $RemoveAllEmptySpaces[0];
            $newUser['lastname'] = $LastName_inCaseEmptyNameComingInAPI;
        } elseif (count($RemoveAllEmptySpaces) == 2) {
            $newUser['firstname'] = $RemoveAllEmptySpaces[0];
            $newUser['lastname'] = $RemoveAllEmptySpaces[1];
        } elseif (count($RemoveAllEmptySpaces) > 2) {
            $newUser['firstname'] = $RemoveAllEmptySpaces[0];
            $newUser['lastname'] = $RemoveAllEmptySpaces[1] . " " . $RemoveAllEmptySpaces[2];
        }

        $CreateFullName = $newUser['firstname'] . " " . $newUser['lastname'];

        $newUser['number'] = $request->mobile_number;
        $newUser['fullname'] = $request->fullname;
        $newUser['password'] = $request->password;
        $user =
            User::create([
                'firstname' => ucwords($newUser['firstname']),
                'lastname' => ucwords($newUser['lastname']),
                'fullname' => ucwords($request->fullname),
                'number' => $newUser['number'],
                'is_buyer' => 1,
                'is_otp_entered' => 0,  // OTP_CHECK
                'password' => bcrypt($newUser['password']),
                'registered_from' => 'App'
            ]);
        // to send otp on user phones
        $otp = rand(1111, 9999);




        DB::table('otps')
            ->insert(['otp' => $otp, 'mobile_number' => $request->mobile_number, 'type' => 'mr']);
        // end of the otp procedure

        if (is_numeric(substr($newUser['firstname'], 0, 1))) {
            $avatar = "/images/trazoo-letter-avatar/mystery.png";
        } else {
            $firstLetter = substr($newUser['firstname'], 0, 1);
            $firstLetter = strtolower($firstLetter);
            $avatar = "/images/trazoo-letter-avatar/" . $firstLetter . ".png";
        }

        /* get rendom row of avatar */
        //        $avatar = Avatar::inRandomOrder()->first();
        DB::table('businesses')
            ->insert([
                'user_id' => $user->id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
                'business_photo' => $avatar,
                'business_photo_original' => $avatar
            ]);

        //adding business_gst entry for new users
        DB::table('businesses_gst')
            ->insert([
                'user_id' => $user->id
            ]);

        // Adding entry for new users right after new registration.
        DB::table('users_data_points')
            ->insert([
                'user_id' => $user->id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]);

        DB::table('da_user_data_points')
            ->insert([
                'user_id' => $user->id
            ]);

        $AdminUtillityController = new AdminUtillity();

        $UserID = $user->id;
        $Title = ($request->fullname);
        $Description = '-';
        $ImageLink = 'https://seller.trazoo.in' . $avatar;
        $AdminUtillityController->SaveFirebaseProfileUrlToBusinesstable($UserID, $Title, $Description, $ImageLink);

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
        // SMS
        $Message = $otp . " is your OTP for Trazoo registration. Please don't share this OTP with anyone.";
        if (SMSModel::GupShupOTPAPI($Message, $request->mobile_number) != 'success') {
            // Log::info('#OTPLOG Mobile Register OTP failed for '.$request->mobile_number);
        }
        //Log::info("User ID".$user->id);

        try {
            $wamsg = (new KwiqreplyController())->kwiqReplyRegisterationOTP($request->mobile_number, $otp);
            if (!$wamsg) {
                error_log('#OTPLOG Whatsapp msg Registration OTP failed for ' . $request->mobile_number);
            }
        } catch (\Throwable $th) {
            //throw $th;
            error_log('#OTPLOG exception Whatsapp msg Registration OTP failed for ' . $request->mobile_number);
        }

        return response()->json([
            'status' => $this->successStatus, 'success' => $success,
            'userInfo' => $userInfo
        ]);
    }
    /* ---- END register() ---- */


    /*--------------------------------------------------------------------
       used to get detail of buyer
      --------------------------------------------------------------------*/
    public function getDetails()
    {
        $user = Auth::user();
        $userData = DB::table('users')
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->select('users.id', 'users.firstname', 'users.lastname', 'users.fullname', 'users.number', 'users.emailId', 'businesses.businessname', 'businesses.business_photo', 'businesses.gstin', 'businesses.businesstype', 'businesses.industry')
            ->where('users.id', $user->id)
            ->get();
        $GetBankDetails = DB::table('businesses')->select('accountnumber', 'ifsccode', 'bank', 'accountholdername')->where('user_id', $user->id)->get();
        if ($GetBankDetails[0]->accountnumber) {
            $BankDetails = array(
                'AccountHoldersName' => $GetBankDetails[0]->accountholdername,
                'BankAccountNumber' => $GetBankDetails[0]->accountnumber,
                'BankIFSC' => $GetBankDetails[0]->ifsccode
            );
            $isBankAdded = true;
        } else {
            $BankDetails = array(
                'AccountHoldersName' => '',
                'BankAccountNumber' => '',
                'BankIFSC' => '',
            );
            $isBankAdded = false;
        }
        //#wholeseller_to_wholesaler_change
        $NewBusinessType = $userData[0]->businesstype;
        //#wholeseller_to_wholesaler_change_end
        $MakeUserArrayNew = array(
            array(
                "id" => $userData[0]->id,
                "firstname" => $userData[0]->firstname,
                "lastname" => $userData[0]->lastname,
                "fullname" => $userData[0]->fullname,
                "number" => $userData[0]->number,
                "emailId" => $userData[0]->emailId,
                "businessname" => $userData[0]->businessname,
                "business_photo" => $userData[0]->business_photo,
                "gstin" => $userData[0]->gstin,
                "businesstype" => $NewBusinessType,
                "industry" => $userData[0]->industry,
                "isBankAdded" => $isBankAdded,
                "BankDetails" => $BankDetails
            )
        );
        return response()->json(['userData' => $MakeUserArrayNew, 'Status' => $this->successStatus, 'message' => 'Data of user has been fetched successfully']);
    }
    /* ---- END getDetails() ---- */

    /*--------------------------------------------------------------------
       used to forgot password
      --------------------------------------------------------------------*/
    public function forgotPassword(Request $request)
    {
        if (strpos($request->mobile_numberOrEmail, '@') !== false) {
            $data = DB::table('users')
                ->select('firstname', 'lastname', 'emailId')
                ->where('emailId', $request->mobile_numberOrEmail)
                ->take(1)
                ->get();
            if (count($data) == 0) {
                return response()->json([
                    'message' => 'Email does not exists.',
                    'status' => 'Failed'
                ], 401);
            }
            $otp = rand(1111, 9999);
            $query = DB::table('otps')
                ->insert([
                    'otp' => $otp,
                    'mobile_number' => $request->mobile_numberOrEmail,
                    'type' => 'mfp'
                ]);
            $firstname = $data[0]->firstname;
            $emailId = $data[0]->emailId;
            Mail::send('mail.forgotPassword', [
                "firstname" => $firstname, "emailId" => $emailId,
                "otp" => $otp
            ], function ($message) use (
                $firstname,
                $emailId,
                $otp
            ) {
                $message->from('noreply@trazoo.in', "Trazoo");
                $message->subject("Trazoo - Reset Password");
                $message->to($emailId);
            });
            return response()->json([
                'message' => 'Please enter the OTP sent your email.',
                'success' => 'Success',
                'success_code' => 200
            ], 200);
        } else {
            $requestData = $request->all();
            $requestData['number'] = $request->mobile_numberOrEmail;

            $validator = Validator::make($requestData, [
                'number' => 'required|digits:10|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }

            if (User::where('number', $request->mobile_numberOrEmail)->exists()) {
                $phoneNumber = $request->mobile_numberOrEmail;
                $otp = rand(1111, 9999);

                $new_user = new OTP();
                $new_user->otp = $otp;
                $new_user->mobile_number = $phoneNumber;
                // mfp = mobile forgot password in database.
                $new_user->type = "mfp";
                $new_user->save();

                $otp_message = "Please+enter+this+OTP+'" . $otp . "'+to+reset+your+password.";

                // Curl::to('http://tsms.my-reminders.in/api/sendmsg.php?user=trazoo&pass=trazoosofto2018&sender=TRAZOO&phone=' . $phoneNumber . '&text=' . $otp_message . '&priority=ndnd&stype=normal')
                //     ->get();
                $Message = $otp . " is your OTP for Trazoo password reset. Please don't share this OTP with anyone.";
                if (SMSModel::GupShupOTPAPI($Message, $phoneNumber) != 'success') {
                    error_log('#OTPLOG Mobile Reset Pass OTP failed for ' . $phoneNumber);
                }

                try {
                    $wamsg = (new KwiqreplyController())->kwiqReplyPasswordReset($phoneNumber, $otp);
                    if (!$wamsg) {
                        error_log('#OTPLOG Whatsapp msg kwiqReplyPasswordReset OTP failed for ' . $phoneNumber);
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                    error_log('#OTPLOG exception Whatsapp msg kwiqReplyPasswordReset OTP failed for ' . $phoneNumber);
                }

                return response()->json([
                    'message' => 'Please enter the OTP sent your mobile.',
                    'success' => 'Success',
                    'success_code' => 200
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Number does not exists.',
                    'status' => 'Failed'
                ], 401);
            }
        }
    }
    /* ---- END forgotPassword() ---- */


    /*--------------------------------------------------------------------
       used to validate forgot password
      --------------------------------------------------------------------*/
    public function validateFpOtp(Request $request)
    {
        $query = DB::table('otps')
            ->where([
                ['mobile_number', '=', $request->mobile_numberOrEmail],
                ['type', '=', 'mfp'],
                ['otp', '=', $request->otp],
            ])
            ->orderBy('sent_at', 'DESC')
            ->get();
        if (count($query) > 0) {
            return response()->json(['status' => $this->successStatus, 'message' => 'Otp has been verified successfully']);
        } else {
            return response()->json(['status' => $this->failStatus, 'message' => 'Invalid OTP']);
        }
    }
    /* ---- END validateFpOtp() ---- */


    /*--------------------------------------------------------------------
       used to change password after forgot password
      --------------------------------------------------------------------*/
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $getOTP = DB::table('otps')
            ->select('otp')
            ->where([
                ['mobile_number', '=', $request->mobile_numberOrEmail],
                ['otp', '=', $request->otp],
                ['type', '=', 'mfp']
            ])
            ->orderBy('sent_at', 'DESC')
            ->get();
        if (count($getOTP) > 0) {
            $hashed = Hash::make($request->password);
            \DB::table('users')->where([['number', '=', $request->mobile_numberOrEmail]])
                ->orWhere([['emailId', '=', $request->mobile_numberOrEmail]])
                ->update(['password' => $hashed, 'is_otp_entered' => 1]);
            DB::table('otps')
                ->where([
                    ['mobile_number', '=', $request->mobile_numberOrEmail],
                    ['otp', '=', $request->otp],
                    ['type', '=', 'mfp']
                ])
                ->delete();
            return response()->json([
                'message' => 'Password changed Successfully.',
                'success' => 'Success',
                'success_code' => 200
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid data.',
                'status' => 'Failed',
                'error_code' => 403
            ], 401);
        }
    }
    /* ---- END changePassword() ---- */


    /*--------------------------------------------------------------------
       used to get mobile banner
      --------------------------------------------------------------------*/
    public function getMobileBanner()
    {
        $banners = DB::table('mobile_banners') // to get data from mobile_banners table for android app
            ->select('id', 'image_link')
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();
        $fashions = DB::table('fashions') // to get data from fashions table for shop by category section in app
            ->select('name', 'image_link', 'id')
            ->whereIn('sub_id', function ($query) {
                $query->select('id')
                    ->from(with(new fashion)->getTable())
                    ->whereNull('sub_id');
            })
            ->where('status', '=', '1')
            ->get();

        return response()->json(['mobile_banners' => $banners, 'categories' => $fashions, 'status' => $this->successStatus, 'message' => 'Data have been fetched successfully'], $this->successStatus);
    }
    /* ---- END getMobileBanners() ---- */


    /*--------------------------------------------------------------------
       used to get detail of a particular product
      --------------------------------------------------------------------*/
    public function getProductDetail(Request $request)
    {
        $product = Product::select('product_name', 'product_code', 'product_images', 'mrp')
            ->where('id', $request->productID)
            ->get();
        // update increase view_count + 1
        $productViewCount = Product::select('view_count')
            ->where('id', $request->productID)
            ->get();
        DB::table('products')->where('id', $request->productID)->update(['view_count' => $productViewCount[0]->view_count + 1]);
        if (count($product) == 0) {   // if requested product is not available in database
            return response()->json(['status' => $this->failStatus, 'message' => 'Requested product not found']);
        }
        return response()->json(['product' => $product, 'status' => $this->successStatus, 'message' => 'Requested product have been successfully fetched']);
    }
    /* ---- END getProductDetail() ---- */


    /*--------------------------------------------------------------------
       used to send favorite categories id in database
      --------------------------------------------------------------------*/
    public function sendCategories(Request $request)
    {

        $user = Auth::user();
        $selectedCat = DB::table('trending_categories')
            ->where('user_id', $user->id)
            ->get();

        if (count($selectedCat) > 0) {
            $data = DB::table('trending_categories')
                ->where('user_id', $user->id)
                ->update(['categories_id' => $request->categories_id]);
        } else {
            DB::table('trending_categories')->insert(
                ['user_id' => $user->id, 'categories_id' => $request->categories_id]
            );
        }
        return response()->json(['status' => $this->successStatus, 'message' => 'Requested categories have been added as favourite']);
    }
    /* ---- END sendCategories() ---- */


    public function getNewSellers($take)
    {
        //         $getNewSellersIds = DB::table('newSellers')
        //             ->Select('seller_id');
        //         if ($take == "StatusIs1") {
        //             $getNewSellersIds = $getNewSellersIds->where('status', 1);
        //         }
        //         $getNewSellersIds = $getNewSellersIds->orderBy('sequence', 'asc')->get();

        //         $newSellerIdarrays = array();
        //         for ($i = 0; $i < count($getNewSellersIds); $i++) {
        // //            array_push($newSellerIdarrays, $getNewSellersIds[$i]->seller_id);
        //             $newSellerIdarrays[$i] = $getNewSellersIds[$i]->seller_id;
        //         }

        $newSellersInfo = DB::table('users') // get seller detail
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->join('addresses', 'users.id', '=', 'addresses.user_id')
            ->join('newSellers', 'newSellers.seller_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.fullname',
                'users.chat_id',
                'businesses.businessname',
                'addresses.landmark',
                'addresses.city',
                'addresses.state',
                'addresses.pincode',
                'businesses.business_photo',
                'users.cumulative_rating as rating'
            )
            // ->whereIn('users.id', $newSellerIdarrays)
            // ->whereIn('addresses.user_id', $newSellerIdarrays)
            ->where('addresses.address_name', '=', 'office');
        if ($take == "StatusIs1") {
            $newSellersInfo = $newSellersInfo->where('status', 1);
        }
        $newSellersInfo =  $newSellersInfo->groupBy('addresses.user_id')->orderBy('newSellers.sequence', 'asc')->get();

        //        return response()->json([ 'newSellers' => $newSellersInfo,
        //            'status' => $this->successStatus,
        //            'message' => 'Requested seller information have been successfully fetched']);

        return $newSellersInfo;
    }

    public function getNewTopSellers($take)
    {
        //         $getNewTopSellersIds = DB::table('newSellers')
        //             ->Select('seller_id')
        //             ->where('location_category_id','=',0);
        //         if ($take == "StatusIs1") {
        //             $getNewTopSellersIds = $getNewTopSellersIds->orderBy('sequence', 'asc')->where('status', 1);
        //         }
        //         $getNewTopSellersIds = $getNewTopSellersIds->get();
        //        // Log::info("top seller ------ ".$getNewTopSellersIds);

        //         $newSellerIdarrays = array();
        //         for ($i = 0; $i < count($getNewTopSellersIds); $i++) {
        // //            array_push($newSellerIdarrays, $getNewSellersIds[$i]->seller_id);
        //             $newSellerIdarrays[$i] = $getNewTopSellersIds[$i]->seller_id;
        //         }

        $newTopSellersInfo = DB::table('users') // get seller detail
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->join('addresses', 'users.id', '=', 'addresses.user_id')
            ->join('newSellers', 'newSellers.seller_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.fullname',
                'users.chat_id',
                'businesses.businessname',
                'businesses.business_photo',
                'businesses.industry',
                'newSellers.image_url',
                'addresses.landmark',
                'addresses.city',
                'addresses.state',
                'addresses.pincode',
                'users.cumulative_rating as rating'
            )
            //->whereIn('users.id', $newSellerIdarrays)
            ->where('addresses.address_name', '=', 'office')->groupBy('users.id')
            ->where('newSellers.location_category_id', '=', 0);
        if ($take == "StatusIs1") {
            $newTopSellersInfo =     $newTopSellersInfo->where('newSellers.status', 1);
        }
        $newTopSellersInfo =  $newTopSellersInfo->orderBy('newSellers.sequence', 'asc')->get();

        foreach ($newTopSellersInfo as $Key => $Value) {
            $newTopSellersInfo[$Key]->active_score = "--"; //hardcoded for the time being
            $newTopSellersInfo[$Key]->trade_score = "--"; //hardcpoded for the time being
            $newTopSellersInfo[$Key]->active_score_color = "#0A0A14"; //hardcpoded for the time being
            $newTopSellersInfo[$Key]->trade_score_color = "#0A0A14"; //hardcpoded for the time being
            if ($take == "StatusIs1") {
                unset($newTopSellersInfo[$Key]->business_photo);
                $newTopSellersInfo[$Key]->business_photo = $newTopSellersInfo[$Key]->image_url;
                unset($newTopSellersInfo[$Key]->image_url);
            } else {
                //$topsellers[$Key]->business_photo = $topsellers[$Key]->image_url;
                unset($newTopSellersInfo[$Key]->image_url);
            }
        }

        //        return response()->json([ 'newSellers' => $newSellersInfo,
        //            'status' => $this->successStatus,
        //            'message' => 'Requested seller information have been successfully fetched']);

        return $newTopSellersInfo;
    }
    //Might be not in use. #notinuse
    public function getNewProductsSeeAll()
    {
        $newProducts = $this->getNewBestSellers("All");

        foreach ($newProducts as $productKey => $productValue) {
            $Mv = DB::table('variants')
                ->select('color', 'size')
                ->where('product_id', $productValue->id)
                ->first();
            $data = (array)$Mv;
            //Log::info($data);
            if (count($data) > 0) {
                $productValue->variantStripText = null;
                $productValue->isVariant = true;
                if ($data['color'] != null) {
                    $productValue->variantStripText = "Colors Available";
                }

                if ($data['size'] != null) {
                    if ($productValue->product_type1 == 70) {
                        if ($productValue->variantStripText == null) {
                            $productValue->variantStripText = "Mobile Models Available";
                        } else {
                            $productValue->variantStripText = "Colors Available | Mobile Models Available";
                        }
                    } else {
                        if ($productValue->variantStripText == null) {
                            $productValue->variantStripText = "Sizes Available";
                        } else {
                            $productValue->variantStripText = "Colors Available | Sizes Available";
                        }
                    }
                }
            } else {
                $productValue->isVariant = false;
                $productValue->variantStripText = " ";
            }
        }

        return response()->json([
            'newProducts' => $newProducts
        ]);
    }
    //Might be not in use. #notinuse
    public function getNewSellersSeeAll()
    {
        $newSellers = $this->getNewTopSellers("All");

        return response()->json([
            'newSellers' => $newSellers
        ]);
    }

    public function getNewProducts($take)
    {

        $newProducts = DB::table('products')
            ->join('users', 'users.id', '=', 'products.user_id')
            ->join('newProducts', 'products.id', '=', 'newProducts.product_id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_images',
                'products.moq',
                'products.mrp',
                'products.selling_format',
                'products.selling_pcs',
                'products.whole_sale_price',
                'products.margin',
                'products.status',
                'products.user_id',
                'products.selling_format'
            )
            ->where([
                ['products.isApproved', '=', 1],
                ['products.status', '!=', 'Hidden'],
                ['users.isApproved', '=', 1],
                ['users.isBlocked', '=', 0],
                ['products.deleted_at', '=', null]
            ]);
        if ($take == "StatusIs1") {
            $newProducts =  $newProducts->where('newProducts.status', 1);
        }
        $newProducts = $newProducts->whereNotNull('products.shipping_resp')->orderBy('newProducts.sequence', 'asc')->get();


        foreach ($newProducts as $product) {
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs, 2);
        }

        return $newProducts;
    }


    public function getNewBestSellers($take)
    {
        $getNewBestProductIds = DB::table('newProducts')
            ->Select('product_id')
            ->where('location_category_id', '=', 0);
        if ($take == "StatusIs1") {
            $getNewBestProductIds = $getNewBestProductIds->where('status', 1);
        }

        $getNewBestProductIds = $getNewBestProductIds->orderBy('sequence', 'asc')->get();

        $newProductIdarrays = array();
        for ($i = 0; $i < count($getNewBestProductIds); $i++) {
            //            array_push($newSellerIdarrays, $getNewSellersIds[$i]->seller_id);
            $newProductIdarrays[$i] = $getNewBestProductIds[$i]->product_id;
        }

        $newBestProducts = DB::table('products')
            ->join('users', 'users.id', '=', 'products.user_id')
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->join('newProducts', 'products.id', '=', 'newProducts.product_id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_type1',
                'products.product_images',
                'products.moq',
                'products.selling_pcs',
                'products.mrp',
                'products.gst',
                'products.selling_format',
                'products.whole_sale_price',
                'products.margin',
                'products.status',
                'products.user_id',
                'businesses.businessname as user_businessname',
                'users.fullname as user_fullname',
                'products.selling_format'
            )
            ->where([
                ['products.isApproved', '=', 1],
                ['products.status', '!=', 'Hidden'],
                ['users.isApproved', '=', 1],
                ['users.isBlocked', '=', 0],
                ['products.deleted_at', '=', null]
            ])
            ->where('newProducts.location_category_id', '=', 0)
            //->whereIn('products.id', $newProductIdarrays)
            ->whereNotNull('products.shipping_resp');
        if ($take == "StatusIs1") {
            $newBestProducts = $newBestProducts->where('newProducts.status', 1);
        }
        //            ->take(6)
        $newBestProducts = $newBestProducts->orderBy('newProducts.sequence', 'asc')->get();
        foreach ($newBestProducts as $product) {
            $product->mrp = round($product->mrp + ($product->mrp * ($product->gst / 100)));
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
            $product->mrp = round($product->mrp / $product->selling_pcs);
            $imagesData = explode(',', $product->product_images);
            $product->product_images = $imagesData[0];
        }
        //        return response()->json([ 'newSellers' => $newSellersInfo,
        //            'status' => $this->successStatus,
        //            'message' => 'Requested seller information have been successfully fetched']);

        return $newBestProducts;
    }






    /*--------------------------------------------------------------------
       used to get home screen
      --------------------------------------------------------------------*/
    public function getHomeScreen()
    {
        $user = Auth::user();
        $userBlockStatus = Auth::user()->isBlocked;
        // To be removed in future
        // if($userBlockStatus == 1)
        //     {
        //         return response()->json(['status' => $this->failStatus,
        //                                 'message' => 'Invalid request- Home.'
        //                                     ]);
        //     }
        // To be removed in future
        $recentlyVP = new Product();
        $youMayAlsoLike = new Product();
        $BusinessPrimaryInfo = new BusinessDiscoveryController();
        $recentlyPids = array();
        $test = array();
        $test1 = array();
        $categories = array();
        $searchCategories = array();
        $productsInCart = 0;
        $trendingProducts = new Product();
        // uses to get banner
        $banners = DB::table('mobile_banners') // to get data from mobile_banners table for android app
            ->select('id', 'action_id', 'image_link', 'banner_action')
            ->where('banner_type', 'mb')
            ->where('location', '=', 0)
            ->orderBy('id', 'desc')
            ->get();
        // end of get banner
        $brandBanner = DB::table('mobile_banners') // to get data from mobile_banners table for android app
            ->select('id', 'action_id', 'image_link', 'banner_action')
            ->orderBy('id', 'desc')
            ->where('banner_type', 'bb')
            ->where('location', 0)
            ->take(1)
            ->get();
        // uses to get recently views product
        $recentlyPid = DB::table('recently_seen_products')
            ->select('product_id')
            ->where('user_id', Auth::User()->id)
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();
        // to store in array for execute query
        if (count($recentlyPid) > 0) {
            for ($i = 0; $i < count($recentlyPid); $i++) {
                array_push($recentlyPids, $recentlyPid[$i]->product_id);
            }
            // fetch products behalf of recently seen product id
            $tempRecentlyVP = DB::table('products')
                ->join('users', 'users.id', '=', 'products.user_id')
                ->select(
                    'products.id',
                    'products.product_name',
                    'products.selling_format',
                    'products.product_images',
                    'products.moq',
                    'products.mrp',
                    'products.selling_format',
                    'products.whole_sale_price',
                    'products.selling_pcs',
                    'products.margin',
                    'products.user_id',
                    'products.status',
                    'products.user_id'
                )
                ->where([
                    ['products.isApproved', '=', 1],
                    ['products.status', '!=', 'Hidden'],
                    ['users.isApproved', '=', 1],
                    ['users.isBlocked', '=', 0],
                    ['products.deleted_at', '=', null]
                ])
                ->whereIn('products.id', $recentlyPids)
                ->whereNotNull('products.shipping_resp')
                ->orderByRaw("field(products.id," . implode(',', $recentlyPids) . ")")
                ->get();
            foreach ($tempRecentlyVP as $product) {
                if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                    $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
            }
            $recentlyVP = $this->arrangeProducts($tempRecentlyVP);
        }
        // uses to get trending product
        $productIds = DB::table('trending_products')
            ->select('product_id')
            ->where('category_id', 0)
            ->get();

        // get data from trending_products table behalf of user id

        $productIdsArray = array();
        for ($i = 0; $i < count($productIds); $i++) {
            $productIdsArray[$i] = $productIds[$i]->product_id;
        }
        //        return $productIdsArray;

        $tempTrendingProducts = DB::table('products')
            ->join('users', 'users.id', '=', 'products.user_id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_images',
                'products.moq',
                'products.mrp',
                'products.selling_format',
                'products.selling_pcs',
                'products.whole_sale_price',
                'products.margin',
                'products.status',
                'products.user_id',
                'products.selling_format'
            )
            ->where([
                ['products.isApproved', '=', 1],
                ['products.status', '!=', 'Hidden'],
                ['users.isApproved', '=', 1],
                ['users.isBlocked', '=', 0],
                ['products.deleted_at', '=', null]
            ])
            ->whereIn('products.id', $productIdsArray)
            //                ->orWhereIn('products.product_type2', $productIds)
            //                ->orWhereIn('products.product_type3', $productIds)
            //                ->orWhereIn('products.product_type4', $productIds)
            ->whereNotNull('products.shipping_resp')
            ->take(4)
            ->get();
        foreach ($tempTrendingProducts as $product) {
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
        }

        for ($i = 0; $i < count($tempTrendingProducts); $i++) {
            if ($tempTrendingProducts[$i]->selling_format == 'Piece') {
                $tempTrendingProducts[$i]->selling_format = 'pc';
            } else {
                $tempTrendingProducts[$i]->selling_format = strtolower($tempTrendingProducts[$i]->selling_format);
            }
        }

        $trendingProducts = $this->arrangeProducts($tempTrendingProducts);
        $flag = 0;
        //        $homeScreenCat = "4,71,88,89,90";
        //$homeScreenCat = "1,2,3,4,70,141,90";
        $homeScreenCat = "71,70,151,141,189,88,90,283";
        $array = explode(",", $homeScreenCat);
        for ($i = 0; $i < count($array); $i++) {
            $test = DB::table('fashions')
                ->select('id', 'name', 'image_link')
                ->where('status', '=', '1')
                ->where('id', '=', $array[$i])
                ->get();                       // get data from fashions table behalf of selected categories id
            if (count($test) == 1) {
                array_push($categories, $test[0]);
            }
        }

        for ($i = 0; $i < count($categories); $i++) {
            $query = DB::table('fashions')
                ->where('sub_id', $categories[$i]->id)
                ->where('status', '=', '1')
                ->get();
            for ($j = 0; $j < count($query); $j++) {
                if ($query[$j]->id == 4) {
                    $flag = 1;
                }
            }
            // hard coded for not show fashion accessories in fashion.
            if (count($query) - 1 > 0) {
                $categories[$i]->isSubCategories = true;
            } else {
                $categories[$i]->isSubCategories = false;
            }
            if ($categories[$i]->id == 90) {
                $categories[$i]->isSubCategories = false;
            }
            if ($categories[$i]->id == 71 or $categories[$i]->id == 70) {
                $categories[$i]->isSubHomeScreen = true;
            } else {
                $categories[$i]->isSubHomeScreen = false;
            }
        }

        $categoriesId = DB::table('you_may_also_like')
            ->select('product_id')
            ->where('category_id', 0)
            ->get();                        // get data from trending_categories table behalf of user id

        $arrayForRows = array();
        for ($i = 0; $i < count($categoriesId); $i++) {
            $arrayForRows[$i] = $categoriesId[$i]->product_id;
        }


        $tempYouMayAlsoLike = DB::table('products')
            ->join('users', 'users.id', '=', 'products.user_id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_images',
                'products.moq',
                'products.mrp',
                'products.selling_format',
                'products.selling_pcs',
                'products.whole_sale_price',
                'products.margin',
                'products.status',
                'products.user_id',
                'products.selling_format'
            )
            ->where([
                ['products.isApproved', '=', 1],
                ['products.status', '!=', 'Hidden'],
                ['users.isApproved', '=', 1],
                ['users.isBlocked', '=', 0],
                ['products.deleted_at', '=', null]
            ])
            ->whereIn('products.id', $arrayForRows)
            ->whereNotNull('products.shipping_resp')
            ->take(3)
            ->get();
        foreach ($tempYouMayAlsoLike as $product) {
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
        }
        $youMayAlsoLike = $this->arrangeProducts($tempYouMayAlsoLike);

        // $carts = DB::table('carts')
        //     ->where('buyer_id', Auth::user()->id)
        //     ->get();
        // if (count($carts) > 0) {
        //     $totalProductInCart = DB::table('cart_details')
        //         ->select(DB::raw('count(*) as count'))
        //         ->where('cart_id', $carts[0]->cart_id)
        //         ->get();
        //     $productsInCart = $totalProductInCart[0]->count;
        // }

        //dkcrptesting
        $carts = DB::table('carts')
            ->join('cart_details as cd', 'cd.cart_id', '=', 'carts.cart_id')
            ->where('buyer_id', Auth::user()->id)
            ->select(DB::raw("count(cd.id) as product_count"))
            ->get();
        if (count($carts) > 0) {
            $productsInCart = $carts[0]->product_count;
            //     $totalProductInCart = DB::table('cart_details')
            //         ->select(DB::raw('count(*) as count'))
            //         ->where('cart_id', $carts[0]->cart_id)
            //         ->get();
        }

        $tatalConnectNotify = DB::table('users')
            ->select('connect_notify')
            ->where('id', Auth::user()->id)
            ->get();


        if ($tatalConnectNotify[0]->connect_notify != null && $tatalConnectNotify[0]->connect_notify != '0') {
            $connectNotify = 1;
        } else {
            $connectNotify = 0;
        }

        /* fetching heirarchy of required all categories */
        $homeScreenSearchCat = "1,2,3,4,70,141,189,151,88,90 ";
        $array1 = explode(",", $homeScreenSearchCat);
        for ($i = 0; $i < count($array1); $i++) {
            $test1 = DB::table('fashions')
                ->select('id', 'name')
                ->where('status', '=', '1')
                ->where('id', '=', $array1[$i])
                ->get();                       // get data from fashions table behalf of selected categories id
            if (count($test1) == 1) {
                array_push($searchCategories, $test1[0]);
            }
        }
        foreach ($searchCategories as $key => $value) {
            /* fetching sub categories */
            $searchSubCategories = DB::table('fashions')
                ->select('id', 'name')
                ->where('sub_id', $value->id)
                ->where('status', '=', '1')
                ->get();
            foreach ($searchSubCategories as $k => $v) {
                /* fetching sub of sub category */
                $subOfSubCategories = DB::table('fashions')
                    ->select('id', 'name')
                    ->where('sub_id', $v->id)
                    ->where('status', '=', '1')
                    ->get();
                foreach ($subOfSubCategories as $k1 => $v1) {
                    /* fetching final categories if in added in future*/
                    $finalCategories = DB::table('fashions')
                        ->select('id', 'name')
                        ->where('sub_id', $v1->id)
                        ->where('status', '=', '1')
                        ->get();
                    if (count($finalCategories) > 0) {
                        $subOfSubCategories[$k1]->isSubCategories = true;
                    } else {
                        $subOfSubCategories[$k1]->isSubCategories = false;
                    }
                    $subOfSubCategories[$k1]->subCategories = $finalCategories;
                }
                if (count($subOfSubCategories) > 0) {
                    $searchSubCategories[$k]->isSubCategories = true;
                } else {
                    $searchSubCategories[$k]->isSubCategories = false;
                }
                $searchSubCategories[$k]->subCategories = $subOfSubCategories;
            }
            if (count($searchSubCategories) > 0) {
                $searchCategories[$key]->isSubCategories = true;
            } else {
                $searchCategories[$key]->isSubCategories = false;
            }
            $searchCategories[$key]->subCategories = $searchSubCategories;
        }


        if (!$recentlyVP) {

            for ($i = 0; $i < count($recentlyVP); $i++) {
                if ($recentlyVP[$i]->selling_format == 'Piece') {
                    $recentlyVP[$i]->selling_format = 'pc';
                } else {
                    $recentlyVP[$i]->selling_format = strtolower($recentlyVP[$i]->selling_format);
                }
            }
        }
        for ($i = 0; $i < count($youMayAlsoLike); $i++) {
            if ($youMayAlsoLike[$i]->selling_format == 'Piece') {
                $youMayAlsoLike[$i]->selling_format = 'pc';
            } else {
                $youMayAlsoLike[$i]->selling_format = strtolower($youMayAlsoLike[$i]->selling_format);
            }
        }
        $popularAroundYou = DB::table('popular_around_you') // to get data from popular_around_you table for android app
            ->select('category_id', 'title', 'subtitle', 'image_url')
            ->where('location_category_id', '=', 0)
            ->get();


        //$newSellers =  $this->getNewSellers("StatusIs1");
        //$newProducts = $this->getNewProducts("StatusIs1");

        $bestsellers = $this->getNewBestSellers("StatusIs1"); //PRODUCTS
        $topsellers = $this->getNewTopSellers("StatusIs1"); //SELLERS
        $today = Carbon::today();
        DB::table('users')->where('id', Auth::User()->id)->update(['visit_count' => DB::raw('visit_count + 1'), 'last_active' => Carbon::now()->toDateTimeString()]);

        $LogoutEvent = $userBlockStatus;

        return response()->json([
            'totalProductsInCart' => $productsInCart,
            'connect_notify' => $connectNotify,
            'is_blocked' => $userBlockStatus,
            'logout_event' => $LogoutEvent,
            'primary_business_info' => $BusinessPrimaryInfo->checkBusinessPrimaryInfo()->original,
            'Banners' => $banners,
            'brand_banner' => $brandBanner,
            'popularAroundYou' => $popularAroundYou,
            'bestSellers' => $bestsellers, //
            'topSellers' => $topsellers, //
            //'newSellers' => $newSellers,
            //'newProducts' => $newProducts,
            'homeCategories' => $categories,
            'trendingProducts' => $trendingProducts,
            'youMayAlsoLIke' => $youMayAlsoLike,
            'recentlyVP' => $recentlyVP,
            'allCategories' => $searchCategories
        ]);
    }
    /* ---- END getHomeScreen() ---- */


    /*--------------------------------------------------------------------
       used to get sub home screen after home screen
      --------------------------------------------------------------------*/
    public function getSubHomeScreen(Request $request)
    {
        $user = Auth::user();
        $recentlyVP = new Product();
        $youMayAlsoLike = new Product();
        $recentlyPids = array();
        $productsInCart = 0;
        $trendingProducts = new Product();
        // uses to get banner
        $banners = DB::table('mobile_banners') // to get data from mobile_banners table for android app
            ->select('id', 'action_id', 'image_link', 'banner_action')
            ->where('banner_type', 'mb')
            ->where('location', $request->categoryId)
            ->orderBy('id', 'desc')
            ->get();
        // end of get banner
        $brandBanner = DB::table('mobile_banners') // to get data from mobile_banners table for android app
            ->select('id', 'action_id', 'image_link', 'banner_action')
            ->orderBy('id', 'desc')
            ->where('banner_type', 'bb')
            ->where('location', $request->categoryId)
            ->take(1)
            ->get();
        // uses to get recently views product
        $recentlyPid = DB::table('recently_seen_products')
            ->select('product_id')
            ->where('user_id', Auth::User()->id)
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();
        // to store in array for execute query
        if (count($recentlyPid) > 0) {
            for ($i = 0; $i < count($recentlyPid); $i++) {
                array_push($recentlyPids, $recentlyPid[$i]->product_id);
            }
            // fetch products behalf of recently seen product id
            $tempRecentlyVP = DB::table('products')
                ->join('users', 'users.id', '=', 'products.user_id')
                ->select(
                    'products.id',
                    'products.product_name',
                    'products.selling_format',
                    'products.product_images',
                    'products.moq',
                    'products.mrp',
                    'products.selling_format',
                    'products.whole_sale_price',
                    'products.selling_pcs',
                    'products.margin',
                    'products.user_id',
                    'products.status',
                    'products.user_id'
                )
                ->where([
                    ['products.isApproved', '=', 1],
                    ['products.status', '!=', 'Hidden'],
                    ['users.isApproved', '=', 1],
                    ['users.isBlocked', '=', 0],
                    ['products.deleted_at', '=', null]
                ])
                ->whereIn('products.id', $recentlyPids)
                ->whereNotNull('products.shipping_resp')
                ->orderByRaw("field(products.id," . implode(',', $recentlyPids) . ")")
                ->get();
            foreach ($tempRecentlyVP as $product) {
                if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                    $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
            }
            $recentlyVP = $this->arrangeProducts($tempRecentlyVP);
        }
        // uses to get trending product

        $productIds = DB::table('trending_products')
            ->select('product_id')
            ->where('category_id', $request->categoryId)
            ->get();

        // get data from trending_products table behalf of user id

        $productIdsArray = array();
        for ($i = 0; $i < count($productIds); $i++) {
            $productIdsArray[$i] = $productIds[$i]->product_id;
        }
        //        return $productIdsArray;

        $tempTrendingProducts = DB::table('products')
            ->join('users', 'users.id', '=', 'products.user_id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_images',
                'products.moq',
                'products.mrp',
                'products.selling_format',
                'products.selling_pcs',
                'products.whole_sale_price',
                'products.margin',
                'products.status',
                'products.user_id',
                'products.selling_format'
            )
            ->where([
                ['products.isApproved', '=', 1],
                ['products.status', '!=', 'Hidden'],
                ['users.isApproved', '=', 1],
                ['users.isBlocked', '=', 0],
                ['products.deleted_at', '=', null]
            ])
            ->whereIn('products.id', $productIdsArray)
            //                ->orWhereIn('products.product_type2', $productIds)
            //                ->orWhereIn('products.product_type3', $productIds)
            //                ->orWhereIn('products.product_type4', $productIds)
            ->whereNotNull('products.shipping_resp')
            ->take(4)
            ->get();
        foreach ($tempTrendingProducts as $product) {
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
        }

        $trendingProducts = $this->arrangeProducts($tempTrendingProducts);
        $flag = 0;
        // $homeScreenCat = "4,71,88,89,70";
        // $array = explode(",", $homeScreenCat);

        $categories = DB::table('fashions')
            ->select('id', 'name', 'image_link')
            ->where('sub_id', $request->categoryId)
            ->where('status', '=', '1')
            ->get();                       // get data from fashions table behalf of selected categories id
        //return $categories[3]->id;
        for ($i = 0; $i < count($categories); $i++) {
            $query = DB::table('fashions')
                ->where('sub_id', $categories[$i]->id)
                ->where('status', '=', '1')
                ->get();
            if (count($query) > 0) {
                $categories[$i]->isSubCategories = true;
            } else {
                $categories[$i]->isSubCategories = false;
            }
            // if ($categories[$i]->id == 4) {
            //     //unset($categories[$i]); //was to seperate fashion accc. from clothing. this caused wrong array format. will revise category structure.
            // } else {


            // }
        }
        $categoriesId = DB::table('you_may_also_like')
            ->select('product_id')
            ->where('category_id', $request->categoryId)
            ->get();                        // get data from trending_categories table behalf of user id

        $arrayForRows = array();
        for ($i = 0; $i < count($categoriesId); $i++) {
            $arrayForRows[$i] = $categoriesId[$i]->product_id;
        }

        $tempYouMayAlsoLike = DB::table('products')
            ->join('users', 'users.id', '=', 'products.user_id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_images',
                'products.moq',
                'products.mrp',
                'products.selling_format',
                'products.selling_pcs',
                'products.whole_sale_price',
                'products.margin',
                'products.status',
                'products.user_id',
                'products.selling_format'
            )
            ->where([
                ['products.isApproved', '=', 1],
                ['products.status', '!=', 'Hidden'],
                ['users.isApproved', '=', 1],
                ['users.isBlocked', '=', 0],
                ['products.deleted_at', '=', null]
            ])
            ->whereIn('products.id', $arrayForRows)
            ->whereNotNull('products.shipping_resp')
            ->take(3)
            ->get();
        foreach ($tempYouMayAlsoLike as $product) {
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
        }
        $youMayAlsoLike = $this->arrangeProducts($tempYouMayAlsoLike);


        for ($i = 0; $i < count($trendingProducts); $i++) {
            if ($trendingProducts[$i]->selling_format == 'Piece') {
                $trendingProducts[$i]->selling_format = 'pc';
            } else {
                $trendingProducts[$i]->selling_format = strtolower($trendingProducts[$i]->selling_format);
            }
        }

        if (!$recentlyVP) {
            for ($i = 0; $i < count($recentlyVP); $i++) {
                if ($recentlyVP[$i]->selling_format == 'Piece') {
                    $recentlyVP[$i]->selling_format = 'pc';
                } else {
                    $recentlyVP[$i]->selling_format = strtolower($recentlyVP[$i]->selling_format);
                }
            }
        }
        for ($i = 0; $i < count($youMayAlsoLike); $i++) {
            if ($youMayAlsoLike[$i]->selling_format == 'Piece') {
                $youMayAlsoLike[$i]->selling_format = 'pc';
            } else {
                $youMayAlsoLike[$i]->selling_format = strtolower($youMayAlsoLike[$i]->selling_format);
            }
        }


        $popularAroundYou = DB::table('popular_around_you') // to get data from popular_around_you table for android app
            ->select('category_id', 'title', 'subtitle', 'image_url')
            ->where('location_category_id', $request->categoryId)
            ->get();


        $bestsellers = $this->getSubNewBestSellers("StatusIs1", $request->categoryId);
        $topsellers = $this->getSubNewTopSellers("StatusIs1", $request->categoryId);


        return response()->json([
            'Banners' => $banners,
            'brand_banner' => $brandBanner,
            'subHomeCategories' => $categories,
            'popularAroundYou' => $popularAroundYou,
            'bestSellers' => $bestsellers,
            'topSellers' => $topsellers,
            'trendingProducts' => $trendingProducts,
            'youMayAlsoLike' => $youMayAlsoLike,
            'recentlyVP' => $recentlyVP
        ]);
    }
    /* ---- END getSubHomeScreen() ---- */

    public function getSubNewTopSellers($take, $id)
    {
        //Log::info($id);
        //         $getNewTopSellersIds = DB::table('newSellers')
        //             ->Select('seller_id')
        //             ->where('location_category_id',$id);

        //         if ($take == "StatusIs1") {
        //         $getNewTopSellersIds = $getNewTopSellersIds->where('status', 1);
        //         }
        //         $getNewTopSellersIds = $getNewTopSellersIds->orderBy('sequence', 'asc')->get();

        //         $newSellerIdarrays = array();
        //         for ($i = 0; $i < count($getNewTopSellersIds); $i++) {
        // //            array_push($newSellerIdarrays, $getNewSellersIds[$i]->seller_id);
        //             $newSellerIdarrays[$i] = $getNewTopSellersIds[$i]->seller_id;
        //         }

        $topsellers = DB::table('users') // get seller detail
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->join('addresses', 'users.id', '=', 'addresses.user_id')
            ->join('newSellers', 'newSellers.seller_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.fullname',
                'users.chat_id',
                'businesses.businessname',
                'businesses.industry',
                'businesses.business_photo',
                'newSellers.image_url',
                'addresses.landmark',
                'addresses.city',
                'addresses.state',
                'addresses.pincode',
                'users.cumulative_rating as rating'
            )
            // ->whereIn('users.id', $newSellerIdarrays)
            ->where('addresses.address_name', '=', 'office')->groupBy('users.id')
            ->where('newSellers.location_category_id', $id);
        if ($take == "StatusIs1") {
            $topsellers = $topsellers->where('status', 1);
        }
        // ->orderBy('users.id','asc')
        $topsellers = $topsellers->orderBy('newSellers.sequence', 'asc')->get();
        foreach ($topsellers as $Key => $Value) {
            $topsellers[$Key]->active_score = "--"; //hardcoded for the time being
            $topsellers[$Key]->trade_score = "--"; //hardcpoded for the time being
            $topsellers[$Key]->active_score_color = "#0A0A14"; //hardcpoded for the time being
            $topsellers[$Key]->trade_score_color = "#0A0A14"; //hardcpoded for the time being

            if ($take == "StatusIs1") {
                unset($topsellers[$Key]->business_photo);
                $topsellers[$Key]->business_photo = $topsellers[$Key]->image_url;
                unset($topsellers[$Key]->image_url);
            } else {
                //$topsellers[$Key]->business_photo = $topsellers[$Key]->image_url;
                unset($topsellers[$Key]->image_url);
            }
        }
        return $topsellers;
    }


    public function getSubNewBestSellers($take, $id)
    {

        //         $getNewBestProductIds = DB::table('newProducts')
        //             ->Select('product_id')
        //             ->where('location_category_id',$id);
        //         if ($take == "StatusIs1") {
        //             $getNewBestProductIds = $getNewBestProductIds->where('status', 1);
        //         }
        //         $getNewBestProductIds = $getNewBestProductIds->orderBy('sequence', 'asc')->get();
        //         $newProductIdarrays = array();
        //         for ($i = 0; $i < count($getNewBestProductIds); $i++) {
        // //            array_push($newSellerIdarrays, $getNewSellersIds[$i]->seller_id);
        //             $newProductIdarrays[$i] = $getNewBestProductIds[$i]->product_id;
        //         } 

        $bestsellers = DB::table('products')
            ->join('users', 'users.id', '=', 'products.user_id')
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->join('newProducts', 'products.id', '=', 'newProducts.product_id')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_images',
                'products.moq',
                'products.selling_pcs',
                'products.mrp',
                'products.gst',
                'products.selling_format',
                'products.whole_sale_price',
                'products.margin',
                'products.status',
                'products.user_id',
                'businesses.businessname as user_businessname',
                'users.fullname as user_fullname',
                'products.selling_format'
            )
            ->where([
                ['products.isApproved', '=', 1],
                ['products.status', '!=', 'Hidden'],
                ['users.isApproved', '=', 1],
                ['users.isBlocked', '=', 0],
                ['products.deleted_at', '=', null]
            ])
            //->whereIn('products.id', $newProductIdarrays)
            ->where('newProducts.location_category_id', $id)
            ->whereNotNull('products.shipping_resp');
        if ($take == "StatusIs1") {
            $bestsellers = $bestsellers->where('newProducts.status', 1);
        }
        //            ->take(6)
        $bestsellers = $bestsellers->orderBy('newProducts.sequence', 'asc')->get();
        foreach ($bestsellers as $product) {
            $images = explode(",", $product->product_images);
            $product->product_images = $images[0];

            $product->mrp = round($product->mrp + ($product->mrp * ($product->gst / 100)));
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
            $product->mrp = round($product->mrp / $product->selling_pcs);
        }
        foreach ($bestsellers as $Key => $Value) {
            $bestsellers[$Key]->active_score = "--"; //hardcoded for the time being
            $bestsellers[$Key]->trade_score = "--"; //hardcpoded for the time being
            $bestsellers[$Key]->active_score_color = "#0A0A14"; //hardcpoded for the time being
            $bestsellers[$Key]->trade_score_color = "#0A0A14"; //hardcpoded for the time being
        }
        return $bestsellers;
    }


    public function getSubNewProductsSeeAll(Request $request)
    {
        $newProducts = $this->getSubNewBestSellers("All", $request->categoryId);

        // Log::info("getSubNewProductsSeeAll->>>".$request->categoryId);

        foreach ($newProducts as $productKey => $productValue) {
            $Mv = DB::table('variants')
                ->select('color', 'size')
                ->where('product_id', $productValue->id)
                ->first();
            $data = (array)$Mv;
            //Log::info($data);

            // Log::info("getSubNewProductsSeeAll->>>ProductID".$productValue->id);

            if (count($data) > 0) {
                $productValue->variantStripText = null;
                $productValue->isVariant = true;
                if ($data['color'] != null) {
                    $productValue->variantStripText = "Colors Available";
                }
                if ($data['size'] != null) {
                    if ($productValue->product_type1 == 70) {
                        if ($productValue->variantStripText == null) {
                            $productValue->variantStripText = "Mobile Models Available";
                        } else {
                            $productValue->variantStripText = "Colors Available | Mobile Models Available";
                        }
                    } else {
                        if ($productValue->variantStripText == null) {
                            $productValue->variantStripText = "Sizes Available";
                        } else {
                            $productValue->variantStripText = "Colors Available | Sizes Available";
                        }
                    }
                }
            } else {
                $productValue->isVariant = false;
                $productValue->variantStripText = " ";
            }
        }

        return response()->json([
            'newProducts' => $newProducts
        ]);
    }


    public function getSubNewSellersSeeAll(Request $request)
    {
        $newSellers = $this->getSubNewTopSellers("All", $request->categoryId);

        return response()->json([
            'newSellers' => $newSellers
        ]);
    }

    /*--------------------------------------------------------------------
       used to get sub categories after sub home screen
      --------------------------------------------------------------------*/
    function getSubCategories(Request $request)
    {

        $products = new Product();
        $subCategories = DB::table('fashions')
            ->select('id', 'name', 'image_link')
            ->where('sub_id', $request->categoryId)
            ->where('status', '=', '1')
            ->get();
        for ($i = 0; $i < count($subCategories); $i++) {
            $query = DB::table('fashions')
                ->select('id', 'name', 'image_link')
                ->where('sub_id', $subCategories[$i]->id)
                ->where('status', '=', '1')
                ->get();
            if (count($query) > 0) {
                $subCategories[$i]->subCategory = $query;
                for ($j = 0; $j < count($query); $j++) {
                    $isSubCategory = DB::table('fashions')
                        ->where('sub_id', $query[$j]->id)
                        ->where('status', '=', '1')
                        ->get();
                    if (count($isSubCategory) > 0) {
                        $subCategories[$i]->subCategory[$j]->isSubCategory = true;
                    } else {
                        $subCategories[$i]->subCategory[$j]->isSubCategory = false;
                    }
                }
            } else {

                // Change was made due to private and public product status #STATUSCHANGE

                $tempProducts = DB::table('products')
                    ->join('users', 'products.user_id', '=', 'users.id')
                    ->select(DB::raw('count(*) as count, products.id,products.selling_pcs,
                    products.product_name, products.product_images, products.moq, 
                    products.mrp, products.whole_sale_price, products.selling_format,
                    products.margin, products.status, products.user_id'))
                    ->where([
                        ['products.isApproved', '=', 1],
                        ['products.status', '=', 'Public'],
                        ['users.isApproved', '=', 1],
                        ['users.isBlocked', '=', 0],
                        ['products.product_type2', '=', $subCategories[$i]->id],
                        ['products.deleted_at', '=', null]
                    ])
                    ->orWhere([
                        ['products.isApproved', '=', 1],
                        ['products.status', '=', 'Public'],
                        ['users.isApproved', '=', 1],
                        ['users.isBlocked', '=', 0],
                        ['products.product_type3', '=', $subCategories[$i]->id],
                        ['products.deleted_at', '=', null]
                    ])
                    ->orWhere([
                        ['products.isApproved', '=', 1],
                        ['products.status', '=', 'Public'],
                        ['users.isApproved', '=', 1],
                        ['users.isBlocked', '=', 0],
                        ['products.product_type4', '=', $subCategories[$i]->id],
                        ['products.deleted_at', '=', null]
                    ])
                    ->whereNotNull('products.shipping_resp')
                    //->orderBy(DB::raw('count(*)'), 'desc')
                    ->orderBy('products.created_at', 'desc')
                    ->take(5)
                    ->get();
                foreach ($tempProducts as $product) {
                    if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                        $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
                }

                $products = $this->arrangeProducts($tempProducts);
                if (count($products) >= 5) {

                    $subCategories[$i]->subCategory = $products;
                } else {
                    $tempProducts = DB::table('products')
                        ->join('users', 'products.user_id', '=', 'users.id')
                        ->select(
                            'products.id',
                            'products.product_name',
                            'products.product_images',
                            'products.moq',
                            'products.mrp',
                            'products.whole_sale_price',
                            'products.selling_pcs',
                            'products.selling_format',
                            'products.margin',
                            'products.status',
                            'products.user_id'
                        )
                        ->where([
                            ['products.isApproved', '=', 1],
                            ['products.status', '=', 'Public'],
                            ['users.isApproved', '=', 1],
                            ['users.isBlocked', '=', 0],
                            ['products.product_type2', '=', $subCategories[$i]->id],
                            ['products.deleted_at', '=', null]
                        ])
                        ->orWhere([
                            ['products.isApproved', '=', 1],
                            ['products.status', '=', 'Public'],
                            ['users.isApproved', '=', 1],
                            ['users.isBlocked', '=', 0],
                            ['products.product_type3', '=', $subCategories[$i]->id],
                            ['products.deleted_at', '=', null]
                        ])
                        ->orWhere([
                            ['products.isApproved', '=', 1],
                            ['products.status', '=', 'Public'],
                            ['users.isApproved', '=', 1],
                            ['users.isBlocked', '=', 0],
                            ['products.product_type4', '=', $subCategories[$i]->id],
                            ['products.deleted_at', '=', null]
                        ])
                        ->whereNotNull('products.shipping_resp')
                        ->orderBy('products.created_at', 'desc')
                        ->take(5)
                        ->get();
                    foreach ($tempProducts as $product) {
                        if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                            $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
                    }
                    $products = $this->arrangeProducts($tempProducts);
                    $subCategories[$i]->proudcts = $products;
                }
            }
        }
        return $subCategories;
    }
    /* ---- END getSubCategories() ---- */

    /*--------------------------------------------------------------------
       used to get user details. it called when click on user profile
      --------------------------------------------------------------------*/
    public function getSellerDetail(Request $request)
    {
        $userCon = "";
        $queries = "not found";
        $sellerInfo = DB::table('users')
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->select(
                'users.id',
                'users.is_seller',
                'users.fullname',
                'users.chat_id',
                'businesses.businessname',
                'businesses.business_photo',
                'businesses.businesstype',
                'businesses.industry',
                'businesses.business_photo'
            )
            ->where([
                ['users.id', '=', $request->userId]
            ])
            ->take(1)
            ->get();
        $userVisitCount = DB::table('users')
            ->select('visit_count')
            ->where('id', $request->userId)
            ->get();

        DB::table('users')
            ->where('id', $request->userId)
            ->update(['visit_count' => $userVisitCount[0]->visit_count + 1]);

        $isSellers = true;
        if ($sellerInfo[0]->is_seller == false)
            $isSellers = false;

        /* if request already sent */
        $sentReq = DB::table('connections')
            ->select('status')
            ->where([
                ['from_user_id', '=', Auth::User()->id],
                ['to_user_id', '=', $request->userId]
            ])
            ->take(1)
            ->get();
        /* if request already received */
        $receiveReq = DB::table('connections')
            ->select('status')
            ->where([
                ['from_user_id', '=', $request->userId],
                ['to_user_id', '=', Auth::User()->id]
            ])
            ->take(1)
            ->get();
        /* sent request */
        if (count($sentReq) > 0) {
            $userCon = $sentReq[0]->status;
        }
        /* received request */
        if (count($receiveReq) > 0) {
            if ($receiveReq[0]->status == 'sent') {
                $userCon = 'received';
            } else {
                $userCon = $receiveReq[0]->status;
            }
        }

        if (count($sellerInfo) > 0) {
            /* separate address from above table because this api is uses on two places
            one is for sellerInfo (seller info ) and connection ( user info ) */
            $products = array();
            $address = DB::table('addresses')
                ->where([
                    ['user_id', '=', $request->userId]
                ])
                ->orderBy('id', 'desc')
                ->take(1)
                ->get();
            /* if address is available */
            if (count($address) > 0) {
                $sellerInfo[0]->landmark = $address[0]->landmark;
                $sellerInfo[0]->city = $address[0]->city;
                $sellerInfo[0]->state = $address[0]->state;
                $sellerInfo[0]->pincode = $address[0]->pincode;
            } else {
                /* if address is not available */
                $sellerInfo[0]->landmark = null;
                $sellerInfo[0]->city = null;
                $sellerInfo[0]->state = null;
                $sellerInfo[0]->pincode = null;
            }
            /* fetch products of seller */
            $tempProducts = DB::table('products')
                ->join('users', 'users.id', '=', 'products.user_id')
                ->select(
                    'products.id',
                    'products.product_name',
                    'products.product_images',
                    'products.moq',
                    'products.product_type1',
                    'products.mrp',
                    'products.whole_sale_price',
                    'products.selling_pcs',
                    'products.selling_format',
                    'products.margin',
                    'products.user_id',
                    'products.status',
                    'products.user_id'
                )
                ->where([
                    ['products.isApproved', '=', 1],
                    ['products.status', '!=', 'Hidden'],
                    ['products.user_id', '=', $sellerInfo[0]->id],
                    ['users.isApproved', '=', 1],
                    ['users.isBlocked', '=', 0],
                    ['products.deleted_at', '=', null]
                ])
                ->whereNotNull('products.shipping_resp')
                ->get();
            foreach ($tempProducts as $product) {
                if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                    $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
            }

            $products = $this->arrangeProducts($tempProducts);
            foreach ($products as $productKey => $productValue) {
                $Mv = DB::table('variants')
                    ->select('color', 'size')
                    ->where('product_id', $productValue->id)
                    ->first();
                $data = (array)$Mv;

                if (count($data) > 0) {
                    $productValue->variantStripText = null;
                    $productValue->isVariant = true;
                    if ($data['color'] != null) {
                        $productValue->variantStripText = "Colors Available";
                    }

                    if ($data['size'] != null) {
                        if ($productValue->product_type1 == 70) {
                            if ($productValue->variantStripText == null) {
                                $productValue->variantStripText = "Mobile Models Available";
                            } else {
                                $productValue->variantStripText = "Colors Available | Mobile Models Available";
                            }
                        } else {
                            if ($productValue->variantStripText == null) {
                                $productValue->variantStripText = "Sizes Available";
                            } else {
                                $productValue->variantStripText = "Colors Available | Sizes Available";
                            }
                        }
                    }
                } else {
                    $productValue->isVariant = false;
                    $productValue->variantStripText = " ";
                }


                //                if (count($data)>0 ){
                //                    $productValue->isVariant = true;
                //                    $productValue->variantStripText = null;
                //                    if ($data['color'] != null) {
                //                        $productValue->variantStripText = "Color";
                //                    }
                //                    if ($data['size'] != null) {
                //                        if ($productValue->variantStripText == null) {
                //                            $productValue->variantStripText = "Size Options Available!";
                //                        } else {
                //                            $productValue->variantStripText ="Colors & Sizes Available!";
                //                        }
                //                    }else
                //                    if($data['color'] == null && $data['size'] == null)
                //                    {
                //                        $productValue->isVariant = false;
                //                        $productValue->variantStripText = "";
                //                    }else
                //                        $productValue->variantStripText = $productValue->variantStripText . " Options Available!";
                //                } else {
                //                    $productValue->isVariant = false;
                //                    $productValue->variantStripText = "";
                //                }
            }

            /* fetching rating of user */
            $users = DB::table('users')
                ->select(
                    'cumulative_rating',
                    'product_q_rating',
                    'service_q_rating',
                    'packaging_rating',
                    'margin_rating',
                    'rating_count'
                )
                ->where('id', $request->userId)
                ->get();
            /* assigning rating in object */
            $sellerInfo[0]->totalRating = $users[0]->cumulative_rating;
            $sellerInfo[0]->totalPR = $users[0]->product_q_rating;
            $sellerInfo[0]->totalS = $users[0]->service_q_rating;
            $sellerInfo[0]->totalP = $users[0]->packaging_rating;
            $sellerInfo[0]->totalM = $users[0]->margin_rating;
            $sellerInfo[0]->status = $userCon;

            if ($isSellers == false) {
                $sellerInfo[0]->totalRating = 0.0;

                $sellerInfo[0]->city = "N/A";
                $sellerInfo[0]->state = "N/A";
                $sellerInfo[0]->businessname = "N/A";
                $sellerInfo[0]->totalPR = 0.0;
                $sellerInfo[0]->totalS = 0.0;
                $sellerInfo[0]->totalP = 0.0;
                $sellerInfo[0]->totalM = 0.0;
                $queries = "This feature will be available soon";
            }
            for ($i = 0; $i < count($products); $i++) {
                if ($products[$i]->selling_format == 'Piece') {
                    $products[$i]->selling_format = 'pc';
                } else {
                    $products[$i]->selling_format = strtolower($products[$i]->selling_format);
                }
            }
            if (count($sellerInfo) > 0) {
                return response()->json(['sellerInfo' => $sellerInfo[0], 'products' => $products, 'queries' => $queries, 'status' => $this->successStatus, 'message' => 'requested seller information has been fetched successfully']);
            } else {
                return response()->json(['sellerInfo' => $sellerInfo[0], 'queries' => $queries, 'status' => $this->successStatus, 'message' => 'requested seller information has been fetched successfully']);
            }
        }

        return response()->json(['status' => $this->failStatus, 'message' => 'Requested product seller and his or her product not found']);
    }
    /* ---- END getSellerDetail() ---- */


    public function getUserProfileCatalogue(Request $request)
    {
        log::info("getUserProfileCatalogue");

        if (auth()->check()) {
            $user_id = Auth::user()->id;
            // Do something with $userId
        } else {
            $user_id = 2;
        }

        $products = array();
        $CategoriesArray = array();

        $CheckConnectionStatus = DB::table('connections')
            ->select('status')
            ->where([['from_user_id', $user_id], ['to_user_id', $request->userId]])
            ->orWhere([['to_user_id', $user_id], ['from_user_id', $request->userId]])
            ->get();
        if (count($CheckConnectionStatus) > 0 && $CheckConnectionStatus[0]->status == 'accepted') {
            $tempProducts = DB::table('products')
                ->join('users', 'users.id', '=', 'products.user_id')
                ->join('fashions', 'products.product_type1', '=', 'fashions.id')
                ->select(
                    'products.id',
                    'products.product_name',
                    'products.product_images',
                    'products.moq',
                    'products.product_type1',
                    'products.mrp',
                    'products.whole_sale_price',
                    'products.selling_pcs',
                    'products.selling_format',
                    'products.margin',
                    'products.user_id',
                    'products.status',
                    'products.user_id'
                )
                ->where([
                    ['products.isApproved', '=', 1],
                    ['products.status', '!=', 'Hidden'],
                    ['products.user_id', '=', $request->userId],
                    ['users.isApproved', '=', 1],
                    ['users.isBlocked', '=', 0],
                    ['products.deleted_at', '=', null]
                ])
                ->whereNotNull('products.shipping_resp')
                ->orderBy('products.created_at', 'desc')
                ->limit(300)
                ->get();
        } else {
            $tempProducts = DB::table('products')
                ->join('users', 'users.id', '=', 'products.user_id')
                ->join('fashions', 'products.product_type1', '=', 'fashions.id')
                ->select(
                    'products.id',
                    'products.product_name',
                    'products.product_images',
                    'products.moq',
                    'products.product_type1',
                    'products.mrp',
                    'products.whole_sale_price',
                    'products.selling_pcs',
                    'products.selling_format',
                    'products.margin',
                    'products.user_id',
                    'products.status',
                    'products.user_id'
                )
                ->where([
                    ['products.isApproved', '=', 1],
                    ['products.status', '=', 'Public'],
                    ['products.user_id', '=', $request->userId],
                    ['users.isApproved', '=', 1],
                    ['users.isBlocked', '=', 0],
                    ['products.deleted_at', '=', null]
                ])
                ->whereNotNull('products.shipping_resp')
                ->orderBy('products.created_at', 'desc')
                ->limit(300)
                ->get();
        }
        /* fetch products of seller */

        foreach ($tempProducts as $product) {
            if ($product->selling_format == 'Set' || $product->selling_format == 'Box')
                $product->whole_sale_price = round($product->whole_sale_price / $product->selling_pcs);
        }

        $products = $this->arrangeProducts($tempProducts);
        $CategoriesArray[] = array('category_name' => 'All', 'category_id' => 0);
        $VatID = array();
        $GrabAllProductID = array();
        foreach ($products as $productKey => $productValue) {
            $productValue->category_id = $this->getProductLastCategory($productValue->id)['category_id'];
            $productValue->category_name = $this->getProductLastCategory($productValue->id)['category_name'];
            if (array_search($productValue->category_id, array_column($CategoriesArray, 'category_id'))) {
            } else {
                $CategoriesArray[] = $this->getProductLastCategory($productValue->id);
            }


            $Mv = DB::table('variants')
                ->select('color', 'size')
                ->where('product_id', $productValue->id)
                ->first();
            $data = (array)$Mv;

            if (count($data) > 0) {
                $productValue->variantStripText = null;
                $productValue->isVariant = true;
                if ($data['color'] != null) {
                    $productValue->variantStripText = "Colors Available";
                }

                if ($data['size'] != null) {
                    if ($productValue->product_type1 == 70) {
                        if ($productValue->variantStripText == null) {
                            $productValue->variantStripText = "Mobile Models Available";
                        } else {
                            $productValue->variantStripText = "Colors Available | Mobile Models Available";
                        }
                    } else {
                        if ($productValue->variantStripText == null) {
                            $productValue->variantStripText = "Sizes Available";
                        } else {
                            $productValue->variantStripText = "Colors Available | Sizes Available";
                        }
                    }
                }
            } else {
                $productValue->isVariant = false;
                $productValue->variantStripText = " ";
            }
        }

        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]->selling_format == 'Piece') {
                $products[$i]->selling_format = 'pc';
            } else {
                $products[$i]->selling_format = strtolower($products[$i]->selling_format);
            }
        }

        if (count($products) > 0) {
            return response()->json(['total_products' => count($products), 'products' => $products, 'categories' => $CategoriesArray, 'status' => $this->successStatus, 'message' => 'requested seller information has been fetched successfully']);
        }

        return response()->json(['total_products' => count($products), 'products' => $products, 'categories' => [], 'status' => $this->failStatus, 'message' => 'No products found for this user.']);
    }

    public function getProductLastCategory($ProductID)
    {

        $CatArray = array();
        $CatID = '';
        $GoToEnd = false; //This param is to break if condition, after the if get satisfy at any line.
        $CheckLastCat = DB::table('products')
            ->where('id', $ProductID)
            ->select('product_type1', 'product_type2', 'product_type3', 'product_type4')
            ->get();
        if ($GoToEnd == false && ($CheckLastCat[0]->product_type4 != null || $CheckLastCat[0]->product_type4 != '')) {
            $CatID = $CheckLastCat[0]->product_type4;
            $CatName = DB::table('fashions')->select('name')->where('id', $CheckLastCat[0]->product_type4)->get()[0]->name;
            $GoToEnd = true;
        }
        if ($GoToEnd == false && ($CheckLastCat[0]->product_type3 != null || $CheckLastCat[0]->product_type3 != '')) {
            $CatID = $CheckLastCat[0]->product_type3;
            $CatName = DB::table('fashions')->select('name')->where('id', $CheckLastCat[0]->product_type3)->get()[0]->name;
            $GoToEnd = true;
        }
        if ($GoToEnd == false && ($CheckLastCat[0]->product_type2 != null || $CheckLastCat[0]->product_type2 != '')) {
            $CatID = $CheckLastCat[0]->product_type2;
            $CatName = DB::table('fashions')->select('name')->where('id', $CheckLastCat[0]->product_type2)->get()[0]->name;
            $GoToEnd = true;
        }
        if ($GoToEnd == false && ($CheckLastCat[0]->product_type1 != null || $CheckLastCat[0]->product_type1 != '')) {
            $CatID = $CheckLastCat[0]->product_type1;
            $CatName = DB::table('fashions')->select('name')->where('id', $CheckLastCat[0]->product_type1)->get()[0]->name;
            $GoToEnd = true;
        }

        $CatArray = array('category_name' => $CatName, 'category_id' => (int) $CatID);

        return $CatArray;
    }

    //// Profile data api

    public function getUserProfileDetail(Request $request)
    {
        log::info("getUserProfileDetail");
        if (auth()->check()) {
            $user_id = Auth::user()->id;
            // Do something with $userId
        } else {
            $user_id = 2;
        }
        $userCon = "";
        $userInfo = DB::table('users')
            ->join('businesses', 'users.id', '=', 'businesses.user_id')
            ->select(
                'users.user_city',
                'users.user_state',
                'users.user_pincode',
                'users.id',
                'users.isApproved',
                'users.isBlocked',
                'users.hidden',
                'users.fullname',
                'users.chat_id',
                'businesses.businessname',
                'businesses.business_photo',
                'businesses.businesstype',
                'businesses.industry',
                'businesses.gstin',
                'businesses.business_photo',
                'businesses.profile_url'
            )
            ->where([
                ['users.id', '=', $request->userId]
            ])
            ->take(1)
            ->get();
        if (count($userInfo) > 0) {
            $userVisitCount = DB::table('users')
                ->select('profile_view_count')
                ->where('id', $request->userId)
                ->get();

            DB::table('users')
                ->where('id', $request->userId)
                ->update(['profile_view_count' => $userVisitCount[0]->profile_view_count + 1]);

            $isSellers = true;
            $isArroved = true;
            $isBlocked = true;
            $isProfileVisibility = true;
            if ($userInfo[0]->isApproved == 0) {
                $isSellers = false;
                $isApproved = false;
            }

            if ($userInfo[0]->isBlocked == 1 || $userInfo[0]->hidden == 1) {
                $isProfileVisibility = false;
                $isBlocked = false;
            }

            /* if request already sent */
            $sentReq = DB::table('connections')
                ->select('status')
                ->where([
                    ['from_user_id', '=', $user_id],
                    ['to_user_id', '=', $request->userId]
                ])
                ->take(1)
                ->get();
            /* if request already received */
            $receiveReq = DB::table('connections')
                ->select('status')
                ->where([
                    ['from_user_id', '=', $request->userId],
                    ['to_user_id', '=', $user_id]
                ])
                ->take(1)
                ->get();
            /* sent request */
            if (count($sentReq) > 0) {
                $userCon = $sentReq[0]->status;
            }
            /* received request */
            if (count($receiveReq) > 0) {
                if ($receiveReq[0]->status == 'sent') {
                    $userCon = 'received';
                } else {
                    $userCon = $receiveReq[0]->status;
                }
            }


            /* separate address from above table because this api is uses on two places
            one is for sellerInfo (seller info ) and connection ( user info ) */
            $products = array();
            $userInfo[0]->city = $userInfo[0]->user_city;
            $userInfo[0]->state = $userInfo[0]->user_state;
            $userInfo[0]->pincode = $userInfo[0]->user_pincode;

            /* fetching rating of user */
            $users = DB::table('users')
                ->select(
                    'cumulative_rating',
                    'product_q_rating',
                    'service_q_rating',
                    'packaging_rating',
                    'margin_rating',
                    'rating_count'
                )
                ->where('id', $request->userId)
                ->get();
            /* assigning rating in object */
            if ($userInfo[0]->gstin != '' || $userInfo[0]->gstin != null) {
                $userInfo[0]->GST = 'Available';
            } else {
                $userInfo[0]->GST = 'Unavailable';
            }

            $userInfo[0]->active_score = "--"; //hardcoded for the time being
            $userInfo[0]->trade_score = "--"; //hardcpoded for the time being
            $userInfo[0]->active_score_color = "#0A0A14"; //hardcpoded for the time being
            $userInfo[0]->trade_score_color = "#0A0A14"; //hardcpoded for the time being
            $userInfo[0]->totalPro_R = $users[0]->product_q_rating;
            $userInfo[0]->totalSR = $users[0]->service_q_rating;
            $userInfo[0]->totalPR = $users[0]->packaging_rating;
            $userInfo[0]->totalMR = $users[0]->margin_rating;
            $userInfo[0]->conStatus = $userCon;
            $userInfo[0]->is_seller = $isArroved;
            $userInfo[0]->profile_visibility = $isProfileVisibility;
            //$userInfo[0]->rep_score = 50; //obsolete

            unset($userInfo[0]->isApproved);
            unset($userInfo[0]->gstin);
            unset($userInfo[0]->user_city);
            unset($userInfo[0]->user_state);
            unset($userInfo[0]->user_pincode);
            unset($userInfo[0]->isBlocked);
            unset($userInfo[0]->hidden);

            foreach ($userInfo[0] as $Key => $Value) {
                if (($Key == 'chat_id' || $Key == 'conStatus'  || $Key == 'totalPro_R'  || $Key == 'totalPR' || $Key == 'totalSR' || $Key == 'totalMR'  || $Key == 'profile_visibility')) {
                } elseif ($Value == '' || $Value == null) {
                    $userInfo[0]->$Key = 'Unavailable';
                }
            }

            if (count($userInfo) > 0) {
                if ($user_id != $request->userId) {
                    event(new UserProfileViewedEV($request->userId, $user_id));
                }


                return response()->json(['userInfo' => $userInfo[0],  'status' => $this->successStatus,  'message' => 'requested user information has been fetched successfully']);
            } else {
                return response()->json(['userInfo' => $userInfo[0], 'status' => $this->failStatus,  'message' => 'requested user information has not been found.']);
            }
        }

        return response()->json(['status' => $this->failStatus, 'message' => 'Requested product seller and his or her product not found']);
    }


    /*--------------------------------------------------------------------
       used to change payment method on order page
      --------------------------------------------------------------------*/
    public function changePaymentMethod(Request $request)
    {
        $user = Auth::user();
        $data = DB::table('orders')
            ->where([
                ['buyer_id', '=', $user->id],
                ['order_no', '=', $request->orderNo],
            ])
            ->update(['mop' => $request->paymentMethod]);
        if ($data) {
            return response()->json(['status' => $this->successStatus, 'message' => 'Payment method has been updated successfully']);
        }
        return response()->json(['status' => $this->failStatus, 'message' => 'Database error']);
    }
    /* ---- END changePaymentMethod() ---- */

    /*--------------------------------------------------------------------
       used to send contact number to Trazoo that a particular user wants get Application
      --------------------------------------------------------------------*/
    public function getApp(Request $request)
    {
        $mobileno = $request->mobile_no;
        $subject = $request->source;
        Mail::send('mail.getApp', ["request" => $request], function ($message) use ($request, $subject) {
            $message->from('noreply@trazoo.in', "trazoo");
            if ($subject == "1") {
                $message->subject(" New User - App Request ");
            } else {
                $message->subject(" New User - Demo Request");
            }
            $message->to("contact@trazoo.in");
        });
        return response()->json(['status' => '1']);
    }
    /* ---- END getApp() ---- */


    /*--------------------------------------------------------------------
       used to get detail of from form of Trazoo website
      --------------------------------------------------------------------*/
    public function getCFormOfWebsite(Request $request)
    {
        $fullName = $request->firstName . ' ' . $request->lastName;
        $contactNum = $request->contactNum;
        $messageType = $request->messageType;
        $messageBody = $request->messageBody;
        // to save data on database
        DB::table('inquiry_details')
            ->insert([
                'first_name' => $request->firstName, 'last_name' => $request->lastName,
                'contact_number' => $contactNum, 'message_type' => $messageType,
                'message' => $messageBody
            ]);
        // to send email to Trazoo
        Mail::send('mail.sendCFormToTrazoo', ["fullName" => $fullName, "contactNum" => $contactNum, "messageType" =>
        $messageType, "messageBody" => $messageBody], function ($message) use (
            $fullName,
            $contactNum,
            $messageType,
            $messageBody
        ) {
            $message->from('noreply@trazoo.in', "Trazoo");
            $message->subject(" E-mail from inquiry form of website ");
            $message->to("contact@trazoo.in");
        });
        return response()->json(['status' => $this->successStatus, 'message' => 'E-mail sent successfully']);
    }
    /* ---- END getCFormOfWebsite() ---- */

    // uses for testing...
    public function checkRazor(Request $request)
    {
        $key = "rzp_live_8UlA8y1cCpZ1ew";
        $secret = "iL5Q5zbOvisug6ZWXoQxSnab";
        $api = new Api($key, $secret);
        DB::table('Order_details')->where([['id', '=', 4]])->update(['Sku' => 'test@123']);
    }

    /*--------------------------------------------------------------------
       used to send varification email when user change his/her email address
      --------------------------------------------------------------------*/
    public function sendVerificationEmail(Request $request)
    {
        $user = Auth::User();
        $user->email_verification_token = str_random(25);
        $user->emailId = $request->email;
        /* sending an email to user's new email */
        Mail::send('mail.userEmailVerification', ['user' => $user], function ($message) use ($user) {
            $message->from('noreply@trazoo.in', 'Trazoo');
            $message->subject('E-mail verification - Trazoo');
            $message->to($user->emailId);
        });
        $user->is_email_verified = 0;
        $user->update();
        return response()->json([
            'status' => $this->successStatus,
            'message' => 'Success! Verification email has been sent successfully'
        ]);
    }
    /* ---- END sendVerificationEmail() ---- */


    /*--------------------------------------------------------------------
       used to update email address with user account
      --------------------------------------------------------------------*/
    public function updateEmailAddress($token)
    {
        $user = User::where('email_verification_token', $token)->first();
        if (!is_null($user)) {
            /* updating user's new email,reseting token and status */
            $user->is_email_verified = 1;
            $user->email_verification_token = null;
            $user->save();
            return response()->json([
                'status' => $this->successStatus,
                'message' => 'Success! Email has been successfully updated'
            ]);
        }
        return response()->json([
            'status' => $this->failStatus,
            'message' => 'Sorry! Something went wrong'
        ]);
    }
    /* ---- END updateEmailAddress() ---- */

    /*--------------------------------------------------------------------
       used to set wish list boolean, remove private product if user is not connected
       with seller, remove other images send only first image of product.
      --------------------------------------------------------------------*/
    public function arrangeProducts($tempObject)
    {
        if (auth()->check()) {
            $user_id = Auth::user()->id;
            // Do something with $userId
        } else {
            $user_id = 2;
        }
        $arrayOfObjects = array();
        /* fetching connections of user's */
        $connections = DB::table('connections')
            ->where([
                ['from_user_id', '=', $user_id],
                ['status', '=', 'accepted']
            ])
            ->orWhere([
                ['to_user_id', '=', $user_id],
                ['status', '=', 'accepted']
            ])
            ->get();
        /* fetching products which is in user's wish list */
        $wishLists = DB::table('wish_lists')
            ->select('user_id', 'product_id')
            ->where('user_id', $user_id)
            ->get();
        foreach ($tempObject as $productKey => $productVal) {
            $isConnection = false;
            $wishList = false;
            
            // $images = explode(",", $productVal->product_images); //now sending thumb image
            // $productVal->product_images = $images[0];
            
            $product = Product::find($productVal->id);

            if ($product) {
                $productVal->product_images = $product->prod_thumb_image;
                $productVal->prod_slug = $product->prod_slug;
            }

            foreach ($wishLists as $wishListKey => $wishListValue) {
                if ($productVal->id == $wishListValue->product_id) {
                    $wishList = true;
                    break;
                }
            }
            if ($wishList == true) {
                $productVal->isInWishList = true;
            } else {
                $productVal->isInWishList = false;
            }
            /* if product status is private */
            if ($productVal->status == 'Private') {
                foreach ($connections as $conKey => $conVal) {
                    if ($productVal->user_id == $conVal->to_user_id || $productVal->user_id == $conVal->from_user_id) {
                        $isConnection = true;
                        break;
                    }
                }
                if ($isConnection == true) {
                    array_push($arrayOfObjects, $productVal);
                    continue;
                }
            } else {
                array_push($arrayOfObjects, $productVal);
            }
        }
        return $arrayOfObjects;
    }
    /* --- END arrangeProducts() --- */

    /*--------------------------------------------------------------------
       used to insert token of user when he/she login first time in application.
      --------------------------------------------------------------------*/
    function updateNotificationToken(Request $request)
    {
        $user = Auth::user();
        $user->app_notification_token = $request->app_notification_token;
        $user->save();
        return response()->json(['status' => $this->successStatus, 'message' =>
        'Success! Token has been updated successfully.']);
    }
    /* --- END updateNotificationToken() --- */

    function resendRegistrationOtp(Request $request)
    {
        /**
         * Intention of this method to send otp to user's phone using 3rd party api.
         */
        $validator = Validator::make($request->all(), [
            'number' => 'required|size:10|string',
        ])->validate();

        /* if account has not been verified */
        $phoneNumber = $request->number;
        $otp = rand(1111, 9999);
        $new_user = new OTP();
        $new_user->otp = $otp;
        $new_user->mobile_number = $phoneNumber;
        $new_user->type = "mr";
        $new_user->save();
        // $otp_message = "Please enter this OTP '".$otp."' for registration";
        // Curl::to('http://tsms.my-reminders.in/api/sendmsg.php?user=trazoo&pass=trazoosofto2018&sender=TRAZOO&phone='.$phoneNumber.'&text=Please+enter+this+otp+'.$otp.'+for+registration&priority=ndnd&stype=normal')
        //     ->get();
        $Message = $otp . " is your OTP for Trazoo password reset. Please don't share this OTP with anyone.";
        if (SMSModel::GupShupOTPAPI($Message, $phoneNumber) != 'success') {
            error_log('#OTPLOG Mobile Reset OTP failed for ' . $phoneNumber);
        }

        try {
            $wamsg = (new KwiqreplyController())->kwiqReplyPasswordReset($phoneNumber, $otp);
            if (!$wamsg) {
                error_log('#OTPLOG Whatsapp msg kwiqReplyPasswordReset OTP failed for ' . $phoneNumber);
            }
        } catch (\Throwable $th) {
            //throw $th;
            error_log('#OTPLOG exception Whatsapp msg kwiqReplyPasswordReset OTP failed for ' . $phoneNumber);
        }


        return response()->json([
            'status' => $this->successStatus,
            'message' => 'Success! otp has been sent successfully.'
        ]);
    }
    /* --- END resendRegistrationOtp() --- */

    public function checkBankDetailsStatus()
    {
        $user = Auth::user();
        $UserID = $user->id;
        $GetBankDetails = DB::table('businesses')->select('accountnumber', 'ifsccode', 'bank', 'accountholdername')->where('user_id', $user->id)->get();
        if ($GetBankDetails[0]->accountnumber) {

            $isBankAdded = true;
            return response()->json(['isBankAdded' => $isBankAdded, 'status' => $this->successStatus, 'message' => 'Bank is added in this account.']);
        } else {

            $isBankAdded = false;
            return response()->json(['isBankAdded' => $isBankAdded, 'status' => $this->failStatus, 'message' => 'Bank details are missing in your account.']);
        }
    }

    /*
        Update Bank Details in Database from Android APP.
    */

    public function UpdateBankDetails(Request $request)
    {
        $user = Auth::user();
        $UserID = $user->id;

        if ($request->AccountHoldersName != '' ||  $request->BankAccountNumber  != '' || $request->BankIFSC  != '') {
            $GetBusinessDetails = DB::table('businesses')->where('user_id', $user->id)->get();
            $Values = array(
                "businessname" => $GetBusinessDetails[0]->businessname,
                "businesstype" => $GetBusinessDetails[0]->businesstype,
                "business_photo" => $GetBusinessDetails[0]->business_photo,
                "industry" => $GetBusinessDetails[0]->industry,
                "addressline1" => $GetBusinessDetails[0]->addressline1,
                "addressline2" => $GetBusinessDetails[0]->addressline2,
                "landmark" => $GetBusinessDetails[0]->landmark,
                "city" => $GetBusinessDetails[0]->city,
                "pincode" => $GetBusinessDetails[0]->pincode,
                "state" => $GetBusinessDetails[0]->state,
                "bank" => $GetBusinessDetails[0]->bank,
                "accountholdername" => $GetBusinessDetails[0]->accountholdername,
                "accountnumber" => $GetBusinessDetails[0]->accountnumber,
                "ifsccode" => $GetBusinessDetails[0]->ifsccode,
                "pannumber" => $GetBusinessDetails[0]->pannumber,
                "gstcompliance" => $GetBusinessDetails[0]->gstcompliance,
                "gstin" => $GetBusinessDetails[0]->gstin,
                "vat" => $GetBusinessDetails[0]->vat,
                "user_id" => $GetBusinessDetails[0]->user_id,
                "updated_at" => date('Y-m-d H:i:s'),
                "created_at" => date('Y-m-d H:i:s'),
            );
            if (!DB::table('business_data_history')->insert($Values)) {
                //Log::info('Business histroy table update not working. HomeController API');
            }
            $BankAcntNmbr = $request->BankAccountNumber;
            $BankIFSC = $request->BankIFSC;
            $AccntHolderName = $request->AccountHoldersName;
            DB::table('businesses')->where('user_id', $UserID)->update([
                'accountholdername' => $AccntHolderName,
                'accountnumber' => $BankAcntNmbr,
                'ifsccode' => $BankIFSC,
            ]);
            return response()->json(['status' => $this->successStatus, 'message' => 'Bank details updated successfully.']);
        } else {
            return response()->json(['status' => $this->failStatus, 'message' => 'Some fields are missing. Please check again.']);
        }
    }

    public function updateDelhiveryWarehouse()
    {
        return view('Admin.updateDelhiveryWarehouse');
    }
    /* Shipping Card Details Function */
    public function GetShippingRateCard()
    {
        $RateCardQuery = DB::table('shipping_rate_card')
            ->where([['app_visibility', '=', true], ['shipping_avail', '=', true]])
            ->get();

        $Data = array();
        foreach ($RateCardQuery as $Key) {
            $Data['category'][] = array(
                'name' => $Key->label_name,
                'image' => $Key->label_image,
                'rates' => $this->GetShippingRates($Key->id)
            );
        }
        return $Data;
    }
    /* Shipping Card Details Function */
    public function GetShippingRates($ID)
    {
        $GetShippingCharges = DB::table('shipping_rate_card_range')
            ->where([['rate_card_id', '=', $ID]])
            ->get();
        $Text = array();
        foreach ($GetShippingCharges as $Key) {
            if ($Key->charge_from == '0') {
                $Text[] = array(
                    'order_value' => 'Under ₹ ' . number_format($Key->charge_to),
                    'rate' => "₹ " . number_format($Key->shipping_charge)
                );
            } elseif ($Key->charge_to > '9999999') {
                $Text[] = array(
                    'order_value' => 'More than ₹ ' . number_format($Key->charge_from),
                    'rate' => "₹ " . number_format($Key->shipping_charge)
                );
            } else {
                $Text[] = array(
                    'order_value' => '₹ ' . number_format($Key->charge_from) . ' - ₹ ' . number_format($Key->charge_to),
                    'rate' => "₹ " . number_format($Key->shipping_charge)
                );
            }
        }
        return $Text;
    }

    public function getContactUsDetails(Request $request)
    {
        $Query = DB::table('contact_us')->where('status', 'Active')->get();

        foreach ($Query as $Key => $Value) {
            if ($Value->contact_type == 1) {
                $ContactTypeText = 'Call';
            }
            if ($Value->contact_type == 2) {
                $ContactTypeText = 'WhatsApp';
            }
            if ($Value->contact_type == 3) {
                $ContactTypeText = 'Call_WhatsApp';
            }

            $Query[$Key]->contact_type_text = $ContactTypeText;

            unset($Query[$Key]->id);
            unset($Query[$Key]->status);
        }

        if (!$Query->isEmpty()) {
            return response()->json(['contact_details' => $Query, 'status' => 200, 'message' => 'Contact details fetched.']);
        } else {
            return response()->json(['contact_details' => (object)[], 'status' => 404, 'message' => 'Contact details not found.']);
        }
    }
}
