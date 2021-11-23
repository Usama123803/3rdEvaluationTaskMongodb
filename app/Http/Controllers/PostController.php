<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\Client as chota;

class PostController extends Controller
{
    public function create(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        //Connection
        $query = (new chota())->SocialAppMongo->Posts;

        $encode = json_encode($userID);
        $decoded = json_decode($encode, true);
        $id = $decoded['$oid'];

        //Add post
        $add_post = $query->insertOne([
            'user_id' => $id,
            'title'   => $request->title,
            'description'  => $request->description,
            'digital_data'   => $request->file('digital_data')->store('folderForFile')
        ]);
        
        if($add_post){
            return response([
                'status'  => 200,
                'message' => 'Post Ceated'
            ]);
        }else{
            return response([
                'status'  => 404,
                'message' => 'Post not Ceated'
            ]);
        }
    }

    public function show(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        $encode = json_encode($userID);
        $decode = json_decode($encode, true);
        $id = $decode['$oid'];

        $query = (new chota())->SocialAppMongo->Posts;
        $show_post = $query->find(['user_id' => $id]);
        if ($show_post == null) {
            return response([
                'Status'  => '200',
                'message' => 'Posts Not Found',
            ], 200);
        } else {
            return response([
                'Status' => '200',
                'Data'   => $show_post->toArray(),
            ], 200);
        }
    }

    public function update(Request $request , $id)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        $encode = json_encode($userID);
        $decode = json_decode($encode, true);
        $id_string = $decode['$oid'];

        $query = (new chota())->SocialAppMongo->Posts;
        $check_post = $query->find(['_id' =>  new \MongoDB\BSON\ObjectID($id)]);
        if ($check_post->toArray() == null) {
            return response([
                'message' => 'Post Not Exits',
            ]);
        }

        $update_post = $query->findOne(['user_id' => $id_string]);
        if (isset($update_post)) {
            $update_fields = [];
            foreach ($request->all() as $key => $value) {
                if (in_array($key, ['title', 'description','digital_data'])) {
                    $update_fields[$key] = $value;
                    $update_post = $query->updateOne(
                        ['_id' => new \MongoDB\BSON\ObjectID($id)],
                        ['$set' => $update_fields]
                    );
                    return response([
                        'Status' => '200',
                        'message' => 'you have successfully Update Post',
                    ], 200);
                }
            }
        } else {
            return response([
                'Status' => '200',
                'message' => 'you are Authorize to Update other User Posts',
            ], 200);
        }
    }

    public function delete(Request $request , $id)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        $query = (new chota())->SocialAppMongo->Posts;
        $encode     = json_encode($userID);
        $decode     = json_decode($encode, true);
        $id_string  = $decode['$oid'];
        $find_post  = $query->findOne(['user_id' => $id_string]);
        $check_post = $query->find(['_id' =>  new \MongoDB\BSON\ObjectID($id)]);

        if ($check_post->toArray() == null) {
            return response([
                'message' => 'Post Not Exits',
            ]);
        }elseif(isset($find_post)){
            $query->deleteOne(['_id' => new \MongoDB\BSON\ObjectID($id)]);
            return response([
                'status'  => 200,
                'message' => 'Post deleted'
            ]);
        }else{
            return response([
                'status'  => 404,
                'message' => 'Post not deleted'
            ]);
        }
    }
}
