<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PostController extends Controller
{
    public function create(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        $post = new Post;
        $post->user_id      = $userID;
        $post->title        = $request->title;
        $post->description  = $request->description;
        $post->digital_data = $request->file('digital_data')->store('folderForFile');
        $create = $post->save();
        
        if($create){
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

        return Post::all()->where('user_id',$userID);
    }

    public function update(Request $request , $id)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;

        $post = Post::where('user_id',$userID)->where('id',$id)->first();
        // $post->id           = $request->id;
        // $post->user_id      = $userID;
        // $post->title        = $request->title;
        // $post->description  = $request->description;
        // $post->digital_data = $request->file('digital_data')->store('folderForFile');

        $update = $post->update($request->all());
        // $update = $post->save();

        if($update){
            return response([
                'status'  => 200,
                'message' => 'Post Updated'
        ]);
        }else{
            return response([
                'status'  => 404,
                'message' => 'Post not Updated'
            ]);
        }
    }

    public function delete(Request $request , $id)
    {
        $jwt = $request->bearerToken();
        $key = 'kk';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $userID = $decoded->data;
        // dd($userID);
        $post = Post::find($id);
        // dd($post);
        $delete = $post->delete();

        if($delete){
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
