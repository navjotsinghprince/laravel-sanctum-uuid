<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//Feel Free To Visit https://navjotsinghprince.com
class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = 'test@user.com';
        $password = '12345';

        if (Auth::attempt(['email' =>  $email, 'password' =>  $password])) {
            $user = Auth::user();
            $success['access_token'] =  $user->createToken('PrinceFerozepuria')->plainTextToken;
            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'unauthorized'], 401);
        }
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();
        $response = [
            "user" =>  $user,
            "message" => "success"
        ];
        return response()->json($response, 200);
    }
}
