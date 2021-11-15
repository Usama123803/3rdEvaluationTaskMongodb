<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Token;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //Check User With Token
        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key('kk', 'HS256'));
        $userID = $decoded->data;
        $var = Token::where('user_id', $userID)->first();
        
        //Find User From With ID
        if(isset($var)) {
            $profile = User::find($userID);
            return response([ 
                'Status'   => '200',
                'email'    => $profile->email,
                'password' => $profile->password
            ], 200);
        } else {
            return response([
                'Status' => '400',
                'message' => 'Bad Request',
                'Error' => 'Incorrect userID = '.$userID
            ], 400); 
        }
    }
}
