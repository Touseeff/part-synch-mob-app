<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use App\Mail\ForgotPasswordMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * SignUp User.
     */
    public function signup(Request $request)
    {

        // return "...................######### SignUp is for maintenance ########## .......................";

        try {
            $accountType = ! empty($request->business_type) ? 'business' : 'user';

            $rules = [
                // 'full_name'    => 'required|string|max:100',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|min:6',
                'phone_number' => 'required',
            ];

            if ($accountType === 'business') {
                $rules['business_name'] = 'required|string|max:150';
                $rules['business_type'] = 'required';
                $rules['address']       = 'required';
            }

            $validate = Validator::make($request->all(), $rules);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'data'   => [],
                    'error'  => $validate->errors()->all(),
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

            $user          = new User();
            $user->role_id = 3;

            $user->first_name   = $request->full_name;
            $user->email        = $request->email;
            $user->password     = Hash::make($request->password);
            $user->phone_number = $request->phone_number;
            $user->otp          = $otp;

            if ($accountType === 'business') {
                $user->role_id       = 2; // Role for vendors
                $user->category_id   = $request->category_id;
                $user->business_name = $request->business_name;
                $user->business_type = $request->business_type;
                $user->address = $request->address;
            }

            $user->save();

            // Prepare data for admin email
            $data = [
                'full_name'    => $user->first_name,
                'email'        => $user->email,
                'phone_number' => $user->phone_number,
            ];

            if ($accountType === 'business') {
                $data['business_name'] = $user->business_name;
                $data['business_type']   =$user->business_type;
                $data['address']       = $user->address;
            }

            // Mail::to($user->email)->send(new OtpMail(
            //     ['otp' => $otp],
            //     'Mails.otp_generate',
            //     'Your OTP Code'
            // ));
            if ($accountType === 'business') {
                // Mail::to('tauseefdevelopment000@gmail.com')->send(new OtpMail(
                //     $data,
                //     'Mails.new_user_mail',
                //     'New Vedor Registered'
                // ));
            } else {
                // Mail::to('tauseefdevelopment000@gmail.com')->send(new OtpMail(
                //     $data,
                //     'Mails.new_user_mail',
                //     'New User Registered'
                // ));
            }

            return response()->json([
                'status'  => true,
                'data'    => $user,
                'message' => 'Record Inserted Successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data'   => [],
                'error'  => $e->getMessage(),
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
                    'data'    => [],
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
                        'data'    => [],
                        'message' => 'OTP verified successfully.',
                        'user'    => $otpCheck,
                    ]);
                }

            } else {
                return response()->json([
                    'status'  => false,
                    'data'    => [],
                    'message' => 'Invalid OTP Or Expire. Please check your email and try again.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data'   => [],
                'error'  => $e->getMessage(),
            ]);
        }
    }
    /**
     * Registration .
     */
    public function signin(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required',
                // 'web_token' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'data'   => [],
                    'errors' => $validator->errors()->all(),
                ]);
            }

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json([
                    'status' => false,
                    'data'   => [],
                    'error'  => 'Invalid Email or Password',
                ]);
            }

            if ($user->otp !== null) {
                return response()->json([
                    'status' => false,
                    'data'   => [],
                    'error'  => 'Your OTP is not verified. Please verify your OTP first.',
                ]);
            }

            if (! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'data'   => [],
                    'error'  => 'Invalid Email or Password',
                ]);
            }
            if ($user) {
                $user->status    = 'active';
                $user->web_token = $request->web_token;
                $user->save();
            }

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'status'    => true,
                'user'      => $user,
                'token'     => $token,
                'data'      => [],
                'web_token' => 'Bearer',
                'message'   => 'Login successful',
            ]);
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'status' => false,
                'data'   => [],
                'error'  => $e->getMessage(),
            ]);
        }
    }
    /**
     * Forgot Password Users.
     */
    public function forgotPassword(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);
    
            // Fetch user
            $user = User::where('email', $request->email)->first();
            
            // Ensure user is found
            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not found',
                ], 404);
            }
    
            // Ensure business_name and first_name are not null
            $nameForOTP = !empty($user->business_name) ? $user->business_name : ($user->first_name ?? 'User');
    
            // Generate OTP
            $new_password = $nameForOTP . rand(1000, 9999);
    
            // Update user password
            $user->password = Hash::make($new_password);
            $user->save();
    
            // Send email
            Mail::to($user->email)->send(new ForgotPasswordMail($user, $new_password));
    
            return response()->json([
                'status'  => true,
                'code'    => $new_password,
                'message' => 'New Password sent successfully to your email.',
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
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
