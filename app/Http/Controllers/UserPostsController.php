<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UserCreatePostValidation;
use App\Http\Requests\UserUpdatePostValidation;
use App\Http\Requests\UserDeletePostValidation;
use App\Services\DataBaseConnection;

class UserPostsController extends Controller
{
    // user create post
    function create_post(UserCreatePostValidation $req)
    {
        $req->validated();
        
        $token = $req->input('token');
        $file = $req->file('file')->store('post');
        $access  = $req->input('access');

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);

        
        if(!empty($insert))
        {
            $id = $insert['_id']; 

            $coll = new DatabaseConnection();
            $table = 'posts';
            $coll2 = $coll->db_connection();
    
            $insert = $coll2->$table->insertOne(
            [
                'user_id' => $id,
                'file' => $file,
                'access' => $access,
            ]);

            if(!empty($insert))
            {
                // $val=array('user_id'=>$id, 'file'=>$file, 'access'=>$access);
                // DB::table('posts')->insert($val);
                return response(['Message'=>'Post Successfull.']);
            }
            else
            {
                return response(['Message'=>'Post Not Successfull.']);
            }
        }
        else
        {
            return response(['Message'=>'Please login First / No Record Found']);
        }
    }


    // user view all his own posts
    function view_post(Request $req)
    {
        $token = $req->input('token');

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);

        if(!empty($insert))
        {
            // gets specfic data against uid
            $id = $insert['_id'];

            $coll = new DatabaseConnection();
            $table = 'posts';
            $coll2 = $coll->db_connection();
    
            $data = $coll2->$table->find(
            [
                'user_id' => $id,
            ]);

            // converts objects into json and returns 
            $objects = json_decode(json_encode($data->toArray(),true));

            if(!empty($objects))
            {
                return response(['Message'=> $objects]);
            }
            else
            {
                return response(['Message'=> 'Posts Data not found.']);
            }            
        }
        else
        {
            return response(['Message'=>'Please login First / Token Expired.']);
        }
    }


    // user updates post
    function update_post(UserUpdatePostValidation $req)
    {
        $req->validated();

        $token = $req->input('token');
        $pid = $req->input('pid');
        $file = $req->file('file')->store('post');
        $access = $req->input('access');

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);

        // gets specfic data against uid
        $id = $insert['_id'];


        if(!empty($insert))
        {
            $coll = new DatabaseConnection();
            $table = 'posts';
            $coll2 = $coll->db_connection();

            // this error will be always shown so ignore it.
            $ppid = new \MongoDB\BSON\ObjectId($pid);

            $update = $coll2->$table->updateOne(array("_id" => $ppid, "user_id" => $id),
            array('$set'=>array('file' => $file, 'access' => $access)));

            if(!empty($update))
            {
                return response(['Message'=>'Post Updated']);
            }
            else
            {
                return response(['Message'=>'Post Not Updated']);
            }   
        }
        else
        {
            return response(['Message'=>'Please login First / Token Expired.']);
        }
    }


    // user delete post
    function delete_post(UserDeletePostValidation $req)
    {
        $req->validated();

        $token =  $req->input('token');
        $pid =  $req->input('pid');

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);

        // gets specfic data against uid
        $uid = $insert['_id'];

        if(!empty($insert))
        {
            $coll = new DatabaseConnection();
            $table = 'posts';
            $coll2 = $coll->db_connection();

            // this error will be always shown so ignore it.
            $ppid = new \MongoDB\BSON\ObjectId($pid);

            $delete = $coll2->$table->deleteOne(array("user_id"=> $uid, "_id"=>$ppid));
            
            if(!empty($delete))
            {
                return response(['Message'=>'Post and Comments on that post deleted successfully.']);   
            }
            else
            {
                $check2 = "You are not allowed to delete this post, because this post belongs to someone else.";
                return response(['Message' => $check2]);                                 
            }                
        }
        else
        {
            return response(['Message'=>'Post Id does not exist.']);
        }
    }
}
