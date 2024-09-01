<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'phone' => ['required','min:11'],
            'password' => ['required', 'confirmed', 'min:6'],
        ], [
            'email.required' => 'Email field is required',
            'email.email' => 'The email should be a valid email',
            'phone.required' => 'Phone number field is required',
            'phone.min' => 'Phone number should be at least 11 characters or more',
            'password.min' => 'Password should be at least 6 characters or more',
            'password.required' => 'Password field is required',
            'password.confirmed' => 'Passwords do not matched each other'
        ]);

        $isExist  = User::firstWhere('username', $request->email);
        if ($isExist) {
            return response()->json([
                'meta' => [
                    'code' => 201,
                    'message' => 'Account already exist!'
                ],
                'data' => []
            ]);
        }

        $loginCode = mt_rand(1111, 9999); ///remove for sms
        $cruxId = 'CCI' . $loginCode . Str::upper(Str::random(2));

        $user = new User;
        $user->cruxId = $cruxId;
        $user->ver_code = $loginCode;
        $user->ver_code_send_at = Carbon::now();
        $user->username = request()->email;
        $user->email = request()->email;
        $user->contacts   =  [
            'email'    =>  @request()->email,
            'phone'    =>  @request()->phone,
            'mobile'   =>  @request()->phone,
        ];
        $user->address   =  [
            'street'  =>  "",
            'town'    =>  "",
            'city'    =>  "",
            'state'   =>  "",
            'country' =>  "",
        ];
        $user->password = bcrypt(request()->password);
        $user->role = "patient";
        $user->status = '0';
        $user->category_id = 0;
        $user->save();

        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'Successful!'
            ],
            'data' => [
                'uuid' => $user->cruxId
            ]
        ]);
    }

    public function verify(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => ['required'],
            'uuid' => ['required','string'],
            'val' => ['required','string','min:4','max:4'],
        ], [
            'email.required' => 'Email field is required',
            'email.email' => 'The email should be a valid email',
            'password.required' => 'Password field is required',
            'val.required' => 'Please enter the code sent to you'
        ]);
        $checkOtp = User::where('cruxId', $request->uuid)->where('ver_code', $request->val)->first();
        if (!$checkOtp) {
            return response()->json([
                'meta' => [
                    "code" => 201,
                    "status" => false,
                    "message" => "Invalid Code!"
                ],
                "data" => []
            ]);
        }
        // continue
        $user = User::where('cruxId', $request->uuid)->firstOrFail();
        $user->email_verified_at = Carbon::now();
        $user->ev = '1';
        $user->status = '1';
        $user->save();
        //
        $credentials = ["email" => $request->email, "password" => $request->password];
        $token = auth()->attempt($credentials);
        return response()->json([
            'meta' => [
                "code" => 200,
                "status" => true,
                "message" => "Account verified!"
            ],
            "data" => [
                "user" => new UserResource($user),
                "credentials" => [
                        "token" => $token,
                        "token_type" => "Bearer",
                        "expires_in" => auth()->factory()->getTTL()
                    ]
            ]
        ]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required|min:6"
        ],[
            'email.required' => 'Email field is required',
            'email.email' => 'The email should be a valid email',
            'password.required' => 'Password field is required',
            'password.min' => 'Password should be 6 character or above'
        ]);

        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        
        return response()->json([
            'user' => new UserResource(auth()->user()),
            'credentials' => $this->respondWithToken($token),
        ]);

        // return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json([
            'meta' => [
                "code" => 200,
                "status" => true,
                "message" => "You are logout!"
            ]
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
