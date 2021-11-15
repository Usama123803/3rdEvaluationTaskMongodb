<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use\App\Models\addFriend;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// use App\Http\Controllers\AuthController;
use App\Models\User;

class AddFriendController extends Controller
{
    public function addFriend(Request $request)
    {
        //Token Generate
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;
        //Query
        $addFriend = new addFriend;
        $var = addFriend::all()->where('sender_id', $userID)->where('reciever_id',$request->reciever_id)->first();
        $var2= addFriend::all()->where('sender_id',$request->reciever_id)->where('reciever_id', $userID)->first();
        //Send Request To Yourself Check
        if($userID == $request->reciever_id)
        {
            return response([
                'message' => 'You can not send request to yourself'
            ]);
        }
        //Reciever Existance Check
        $checkReciever = User::all()->where('id', $request->reciever_id)->first();
        if(!isset($checkReciever)){
            return response([
                'message' => 'Reciver Does not exist'
            ]);
        }
        //Send Friend Request
        if($var==null && $var2==null)
        {
            $addFriend->sender_id    = $userID;
            $addFriend->reciever_id  = $request->reciever_id;
            $addFriend->status       = $request->status;
            $create = $addFriend->save();
            return response([
                'message' => 'Friend Request Send'
            ]);
        }elseif($userID == $request->reciever_id){
            return response([
                'message' => 'cannot send request to yourself'
            ]);
        }else{
           return response([
                'message' => 'Already Send request'
            ]);
        }
    }

    public function acceptFriendRequest(Request $request , $id)
    {
        // $friend_Request = addFriend::where('id',$id)->first();
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        $friend_Request = addFriend::all()->where('sender_id',$id)->where('reciever_id',$userID)->first();
        // dd($friend_Request);

        if($friend_Request->status = null){
            return response([
                'message'=>'Friend Request Not Accepted'
            ]);
        }elseif ($friend_Request) {
            $friend_Request->status = '1';
            $friend_Request->save();
            return response([
                'message'=>'Friend Request Accepted'
            ]);
        }else{
            return response([
                'message'=>'Error'
            ]);
        }
    }
}
