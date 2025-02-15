<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * SignUp User.
     */
    public function signup(Request $request)
    {
        try {
            $accountType = ! empty($request->business_type) ? 'business' : 'user';

            $rules = [
                'full_name'    => 'required|string|max:100',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|min:6',
                'phone_number' => 'required',
            ];

            if ($accountType === 'business') {
                $rules['business_name'] = 'required|string|max:150';
                $rules['business_type'] = 'required|string|max:50';
                $rules['address']       = 'required';
            }

         
            $validate = Validator::make($request->all(), $rules);

            if ($validate->fails()) {
                return response()->json([
                    'status'  => false,
                   'data'=>[],
                    'error'   => $validate->errors()->all(),
                ]);
            }

        
            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'status' => false,
                    'data'   => [],
                    'error'  => 'Email already exists',
                ]);
            }

         
           $otp_number = rand(1000, 9999);
           $otp        = str_pad((string) $otp_number, 4, '0', STR_PAD_LEFT);

            $user               = new User();
            $user->role_id      = 3;
            $user->first_name   = $request->full_name;
            $user->email        = $request->email;
            $user->password     = Hash::make($request->password);
            $user->phone_number = $request->phone_number;
            $user->otp          = $otp;

            if ($accountType === 'business') {
                $user->role_id       = 2;
                $user->business_name = $request->business_name;
                $user->business_type = $request->business_type;
                $user->address       = $request->address;
            }
            // $token = $user->createToken('API Token')->plainTextToken;
            $user->save();
            // Mail::to($user->email)->send(new OtpMail($otp));
            // Mail::to($request->email)->send(new OtpMail, $user);

            return response()->json([
                'status'  => true,
                'message' => 'Record Inserted Successfully',
                'data'    => $user,
                // 'token'=>$token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'data'=>[],
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Otp Verification.
     */

    public function otpVerification(Request $request)
    {
        try {
          
            $validator = Validator::make($request->all(), [
                'otp' => 'required|digits:4',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'data'=>[],
                    'message' => 'Validation failed',
                    'error'   => $validator->errors(),
                ]);
            }

           
            $otpCheck = User::where('otp', $request->otp)->first();

            if ($otpCheck) {
                $otpCheck->otp    = null;
                $otpCheck->status = 'active';
                $otpCheck->save();
                if ($otpCheck) {
                    return response()->json([
                        'status'  => true,
                        'data'=>[],
                        'message' => 'OTP verified successfully.',
                        'user'    => $otpCheck,
                    ]);
                }

            } else {
                return response()->json([
                    'status'  => false,
                    'data'=>[],
                    'message' => 'Invalid OTP Or Expire. Please check your email and try again.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'data'=>[],
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function signin(Request $request)
    {
        try {
      
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required',
                'web_type' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'data'=>[],
                    'errors'  => $validator->errors()->all(),
                ]);
            }

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json([
                    'status'  => false,
                    'data'=>[],
                    'message' => 'Invalid Email or Password',
                ]);
            }

        
            if ($user->otp !== null) {
                return response()->json([
                    'status'  => false,
                    'data'=>[],
                    'message' => 'Your OTP is not verified. Please verify your OTP first.',
                ]);
            }

          
            if (! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'data'=>[],
                    'message' => 'Invalid Email or Password',
                ]);
            }

       
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'status'     => true,
                'token'      => $token,
                'data'=>[],
                'token_type' => 'Bearer',
                'message'    => 'Login successful',
                'user'       => $user,
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'status'  => false,
                'data'=>[],
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout successful',
        ]);
    }
}
