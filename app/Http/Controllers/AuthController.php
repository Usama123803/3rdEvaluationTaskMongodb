<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Token;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVarify;

class AuthController extends Controller
{
    function register(Request $request){
        //Validate data
        $request->validate([
            'email' => 'required|string|unique:users',
            'password' => 'required|string'
        ]);

        //Request is valid, create new user
        $token    = $this->createToken($request->email);
        $url = 'http://127.0.0.1:8000/api/emailVarification/'.$token.'/'.$request->email;
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'url' => $url,
        ]);

        //Mail Send To Mail Trap Acc
        Mail::to($request->email)->send(new EmailVarify($user->url));
        return $user;

    }

    function emailVarification($token,$email){
        // dd($email);
        $emailVerify = User::where('email',$email)->first();
        // dd($emailVerify->id);
        if($emailVerify->email_verified_at != null){
            return response([
                'message'=>'Already Verified'
            ]);
        }elseif ($emailVerify) {
            $emailVerify->email_verified_at = date('Y-m-d h:i:s');
            $emailVerify->save();
            return response([
                'message'=>'Eamil Verified'
            ]);
        }else{
            return response([
                'message'=>'Error'
            ]);
        }
    }

    function createToken($data) {
        $key = "kk";
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            "data" => $data,
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    function login(Request $request) {
        //Validate data
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        //Check Eamil
        $data = [
            'email'    => $request->email,
            'password' => $request->password
        ];

        $user = User::where('email', $request->email)->first();
        // dd($user);
        //check if user already has token
        $var = Token::where('user_id', $user->id)->first();

        if(isset($var)){
            return response([
                'message' => 'user already login'
            ]);
        }
        //Create User Token
        if(Auth::attempt($data)) {
            $token    = $this->createToken($user->id);
            $var      = Token::create([
            'user_id' => $user->id,
            'token'   => $token
        ]);
            return response([ 
                'Status'  => '200',
                'Message' => 'Successfully Login',
                'Email'   => $request->email,
                'token'   => $token
            ], 200);
        } else {
            return response([
                'Status'  => '400',
                'message' => 'Bad Request',
                'Error'   => 'Email or Password does not match'
            ], 400); 
        }
    } 

    function logout(Request $request){
        //Decode Token
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;
        //Check If Token Exits
        $userExist = Token::where("user_id",$userID)->first();
        if($userExist){
            $userExist->delete();
        }else{
            return response([
                "message" => "This user is already logged out"
            ], 404);
        }
            return response([
                "message" => "logout successfull"
            ], 200);
    }
}