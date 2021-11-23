<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\Client as chota;

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
        $query      = (new chota())->SocialAppMongo->User;
        $add_Friend = (new chota())->SocialAppMongo->Friends;

        $encode = json_encode($userID);
        $decode = json_decode($encode, true);
        $id_string = $decode['$oid'];
        
        //Reciever Existance Check
        $checkReciever = $query->findOne(['_id' => new \MongoDB\BSON\ObjectId($request->reciever_id)]);
        if(!isset($checkReciever)){
            return response([
                'message' => 'Reciver Does not exist'
            ]);
        }

        //Already Send Request Check
        $check_request = $add_Friend->findOne([
            'sender_id'  => $id_string,
            'reciver_id' => $request->reciver_id,
        ]);

        if($id_string == $request->reciever_id){
            return response([
                'message' => 'cannot send request to yourself'
            ]);
        }  

        if(isset($check_request)) {
            return response([
                "Message" => "You have already Sent the Friend Request to this User"
            ]);
        }
          
        //Send Friend Request
        if (isset($checkReciever)) {
            $add_Friend->insertOne([
                'sender_id'   => $id_string,
                'reciever_id' => $request->reciever_id,
                'status'      => 0
            ]);

            return response([
                "Message" => "You have Successfully Send Friend Request "
            ]);
        }else{
           return response([
                'message' => 'Already Send request'
            ]);
        }
    }

    public function acceptFriendRequest(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        $add_Friend = (new chota())->SocialAppMongo->Friends;
        $encode = json_encode($userID);
        $decode = json_decode($encode, true);
        $id_string = $decode['$oid'];

        $check_request = $add_Friend->findOne([
            'sender_id'  => $request->sender_id,
            'reciever_id' => $id_string,
        ]);
        if(isset($check_request)) {
            $update = $add_Friend->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectID($id_string)],
                ['$set' => [
                    'status' => 1
                ]]
            );
            return response([
                "Message" => "Friend Request Accepted"
            ]);
        }
        if($check_request->status == '1') {
            return response([
                    "Message" => "You are already Friend of this User"
                ]);
        } else{
            return response([
                'message' => 'Error'
            ]);
        }
    }
}
