<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVarify;
use MongoDB\Client as chota;

// use MongoDB\BSON\ObjectId::__toString;
use Symfony\Component\Console\Input\Input;
use Throwable;

class AuthController extends Controller
{
    function register(Request $request) {
        
        //Validate data
        $validator = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string'
        ]);

        //Connection
        $query = (new chota())->SocialAppMongo->User;
        //create a link to varify email.
        $token = $this->createToken($request->email);
        $url   = 'http://127.0.0.1:8000/api/emailVarification/'.$token.'/'.$request->email;
        //Check User Existance
        $user_exist = $query->findOne(['email' => $request->email]);
        if(!isset($user_exist)){
        //create new User in DB
            $user = $query->insertOne([
                'name'              => $request->name,
                'email'             => $request->email,
                'password'          => bcrypt($request->password),
                'token'             => $token,
                'url'               => $url,
                'email_verified_at' => null,
            ]);
            // Mail Send To Mail Trap Acc
            Mail::to($request->email)->send(new EmailVarify($url));
            if ($user) {
                return response([
                    'message' => 'Link Recieved',
                ]);
            } else {
                return response([
                    'message' => 'Email Not Sent',
                ]);
            }
        }else{
            return response([
                'message' => 'Already registered'
            ]);
        }
    }

    function emailVarification($token,$email) {

        $query = (new chota())->SocialAppMongo->User;
        $emailVerifyOriginal = $query->findOne(['email' => $email]);
        $emailVerify = $query->findOne(['email' => $email]);

        if ($emailVerify['email'] != $email) {
            return response([
                'message' => 'User Does not Exits'
            ]);
        }

        $emailVerify = iterator_to_array($emailVerify);
        if ($emailVerify['token'] != $token) {
            return response([
                'message' => 'Un-authorized'
            ]);
        }

        if($emailVerifyOriginal->email_verified_at != null){
            return response([
                'message'=>'Already Verified'
            ]);
        }elseif ($emailVerifyOriginal) {
            $query->updateOne(
                ['email' => $email],
                ['$set'  => ['email_verified_at' => date('Y-m-d h:i:s')]
            ]);
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
            "iss"  => "http://127.0.0.1:8000",
            "aud"  => "http://127.0.0.1:8000/api",
            "iat"  => time(),
            "nbf"  => 1357000000,
            "data" => $data,
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    function login(Request $request) {
        try {
            $request->validate([
                'email'    => 'required|string',
                'password' => 'required|string'
            ]);

            //DB Connection
            $query = (new chota())->SocialAppMongo->User;
            $user  = $query->findOne(['email' => $request->email]);

            if ($user['email_verified_at'] == null) {
                return response([
                    'Status'  => '400',
                    'message' => 'Bad Request',
                    'Error'   => 'Please Verify your Email before login'
                ], 400);
            } elseif($request->email == $user['email'] and Hash::check($request->password, $user['password'])) {

                // check if user is already login and assigned token 
                $query = (new chota())->SocialAppMongo->User;
                $user = $query->findOne(['_id' => $user['_id']]);

                if (isset($user)) {
                    $new_token = $this->createToken($user->_id); 
                    $token_add =  $query->updateOne(
                        ['_id'  => $user['_id']],
                        ['$set' => ['token' => $new_token]]
                    );
                    return response([
                        'Status'  => '200',
                        'Message' => 'Successfully Login',
                        'user_id' => $user->_id,
                        'Email'   => $request->email,
                        'token'   => $new_token
                    ], 200);
                } else {
                    return response([
                        'Status'  => '400',
                        'message' => 'Bad Request',
                        'Error'   => 'Un-authorized User'
                    ], 400);
                }
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    function logout(Request $request){
        //Decode Token
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;
        //DB Connection
        $query = (new chota())->SocialAppMongo->User;
        //Check If Token Exits
        $encode = json_encode($userID);
        $decoded = json_decode($encode, true);
        $id = $decoded['$oid'];
        $userExist = $query->findOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);

        if($query->findOne(['token' => null])){
            return response([
                "message" => "This user is already logged out"
            ], 404);
        }else{
            $user = $query->updateOne(
                ['_id'  => $userExist['_id']],
                ['$set' => ['token' => null]]
            );
            return response([
                "message" => "logout successfully"
            ], 200);
        }
    }
}