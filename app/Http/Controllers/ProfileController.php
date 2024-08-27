<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function edit(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => ['required','string'],
            'address' => ['required', 'string'],
        ], [
            'firstname.required' => 'Firstname field is required',
            'lastname.required' => 'Lastname field is required',
            'address.required' => 'Address field is required',
        ]);
        $userExist  = User::firstWhere('cruxId', $request->uuid);
        if (!$userExist) {
            return response()->json([
                'meta' => [
                    'code' => 201,
                    'message' => 'Invalid account details!'
                ],
                'data' => []
            ]);
        }

        $userExist->firstname = $request->firstname;
        $userExist->lastname = $request->lastname;
        $userExist->address   =  [
            'street'  =>  $request->address,
            'town'    =>  "",
            'city'    =>  "",
            'state'   =>  "",
            'country' =>  "",
        ];
        $userExist->save();
        //
        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'Profile details updated!'
            ],
            'data' => [
                'user' => new UserResource($userExist)
            ]
        ]);

    }
}
