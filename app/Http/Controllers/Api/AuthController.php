<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
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
        try {
            $accountType = ! empty($request->business_type) ? 'business' : 'user';

            // Define validation rules
            $rules = [
                'full_name'    => 'required|string|max:100',
                'email'        => 'required|email|unique:users,email',
                'password'     => 'required|min:6',
                'phone_number' => 'required',
            ];

            // Add business-specific fields if it's a business account
            if ($accountType === 'business') {
                $rules['business_name'] = 'required|string|max:150';
                $rules['business_type'] = 'required|string|max:50';
                $rules['address'] = 'required';
            }

            // Validate input
            $validate = Validator::make($request->all(), $rules);

            if ($validate->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation Error',
                    'error'  => $validate->errors()->all(),
                ], 422);
            }

            // Check if email already exists
            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'status' => false,
                    'error'  => 'Email already exists',
                    'data'   => [],
                ], 409);
            }

            // Generate OTP
            $otp = rand(1000, 9999);

            // Create new user
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
                $user->address = $request->address;
            }
            // $token = $user->createToken('API Token')->plainTextToken;
            $user->save();
            Mail::to($user->email)->send(new OtpMail($otp));
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
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Otp Verification.
     */

     public function otpVerification(Request $request)
     {
         try {
             // Validate request
             $validator = Validator::make($request->all(), [
                 'otp' => 'required|digits:4', 
             ]);
     
             if ($validator->fails()) {
                 return response()->json([
                     'status' => false,
                     'message' => 'Validation failed',
                     'error' => $validator->errors(),
                 ], 422);
             }
     
             // Find user with the given OTP
             $otpCheck = User::where('otp', $request->otp)->first();
     
             if ($otpCheck) {
                $otpCheck->otp = Null;
                $otpCheck->status = 'active';
                $otpCheck->save();
                if($otpCheck){
                    return response()->json([
                        'status' => true,
                        'message' => 'OTP verified successfully.',
                        'user' => $otpCheck,
                    ]);
                }
              
             } else {
                 return response()->json([
                     'status' => false,
                     'message' => 'Invalid OTP Or Expire. Please check your email and try again.',
                 ], 404);
             }
         } catch (\Exception $e) {
             return response()->json([
                 'status' => false,
                 'message' => 'Something went wrong. Please try again later.',
                 'error' => $e->getMessage(),
             ], 500);
         }
     }
     

    /**
     * Login Form.
     */
    // public function signin(Request $request)
    // {

    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'email'    => 'required|email',
    //             'password' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'Validation failed',
    //                 'error'  => $validator->errors()->all(),
    //             ], 422);
    //         }

    //         $user = User::where('email', $request->email)->first();

    //         if (! $user || ! Hash::check($request->password, $user->password)) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'Pleae check Email or Password',
    //             ], 401);
    //         }

    //         $token = $user->createToken('API Token')->plainTextToken;

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Login successful',
    //             'user'    => [
    //                 'id'    => $user->id,
    //                 'name'  => $user->name,
    //                 'email' => $user->email,
    //                 'role'  => $user->role,
    //             ],
    //             'users'    => $user,
    //             'token'   => $token,
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => "Something went wrong",
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }




//     public function signin(Request $request)
// {
//     try {
//         // Validate request
//         $validator = Validator::make($request->all(), [
//             'email'    => 'required|email',
//             'password' => 'required',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Validation failed',
//                 'errors'  => $validator->errors()->all(),
//             ], 422);
//         }

//         // Find user by email
//         $user = User::where('email', $request->email)->first();

//         // Check if user exists and password matches
//         if (! $user || ! Hash::check($request->password, $user->password)) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Please check your Email or Password',
//             ], 401);
//         }

//         // Delete old tokens
//         $user->tokens()->delete();

//         // Generate new token
//         // $token = $user->createToken('API Token')->plainTextToken;

//         // Set the dashboard route based on role_id
//         $dashboard = $user->role_id == 1 ? 'admin/dashboard' : 'user/dashboard';

//         return response()->json([
//             'status'  => true,
//             'message' => 'Login successful',
//             'user'    => [
//                 'id'       => $user->id,
//                 'name'     => $user->name,
//                 'email'    => $user->email,
//                 'role'     => $user->role_id,
//                 'dashboard'=> $dashboard,
//             ],
//             // 'token'   => $token,
//         ], 200);

//     } catch (\Exception $e) {
//         return response()->json([
//             'status'  => false,
//             'message' => "Something went wrong",
//             'error'   => $e->getMessage(),
//         ], 500);
//     }
// }


public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors()->all(),
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json([
            'status'  => false,
            'message' => 'Invalid Email or Password',
        ], 401);
    }

    // 🔹 Ensure User is Authenticated
    // auth()->login($user);

    // 🔹 Create Token for the Authenticated User
    $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
        'status'  => true,
        'message' => 'Login successful',
        'user'    => [
            'id'    => $user->id,
            'name'  => $user->first_name,
            'email' => $user->email,
            'role'  => $user->role_id,
        ],
        'token'   => $token,
    ], 200);
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
        ], 200);
    }
}
