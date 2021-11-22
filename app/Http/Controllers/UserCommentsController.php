<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UserCommentValidation;
use App\Http\Requests\UserCommentUpdateValidation;
use App\Http\Requests\UserCommentDeleteValidation;
use App\Services\DataBaseConnection;

class UserCommentsController extends Controller
{

    // user comments
    function user_comments(UserCommentValidation $req)
    {
        $req->validated();

        $token = $req->input('token');
        $pid = $req->input('pid');
        $comment = $req->input('comment');
        
        if($req->file != null)
        {
            $file = $req->file('file')->store('comments');
        }
        else
        {
            $file = null;
        }

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();


        $insert = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);

        if(!empty($insert))
        {
            // get user id from 
            $uid = $insert['_id']; 

            $coll = new DatabaseConnection();
            $table = 'posts';
            $coll2 = $coll->db_connection();

            // this error will be always shown so ignore it.
            $ppid = new \MongoDB\BSON\ObjectId($pid);

            // it will generate a random id as comment id
            $comment_id = new \MongoDB\BSON\ObjectId();

            $comments = array(
                'comment_id'    =>       $comment_id,
                'user_id2'      =>       $uid,
                'comment'       =>      $comment,
                'file'          =>      $file
            );

            $update = $coll2->$table->updateOne(["_id"=>$ppid],['$push'=>["comments" => $comments]]);

            if(!empty($update))
            {
                return response(['Message' => 'Comment Uploaded on Post...!!!']);
            }
            else
            {
                return response(['Message' => 'No Comment Uploaded on Post...!!!']);
            }   
        }
        else
        {
            return response(['Message' => 'User does not exist in database.']);
        }
    }

    
    // user updates comment
    function user_comments_update(UserCommentUpdateValidation $req)
    {

        $req->validated();
        

        $token =  $req->input('token');
        $comment_id = $req->input('cid');
        $comment = $req->input('comment');

        if($req->file != null)
        {
            $file = $req->file('file')->store('comments');
        }
        else
        {
            $file = null;
        }
        
        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();


        $insert = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);


        $coll = new DatabaseConnection();
        $table = 'posts';
        $coll2 = $coll->db_connection();

        $insert1 = $coll2->$table->findOne(
        [
            'comments' => $comment_id,
        ]);

        dd($insert1);
        
        // $data = DB::table('users')->where('remember_token', $token)->get();

        // $wordcount = count($data);

        if(!empty($insert))
        {
            // get user id from 
            $uid = $insert['_id']; 

            //DB::table('comments')->where(['cid' => $cid, 'user_id' => $uid])->update(['comments' => $comment, 'file' => $file]);

            return response(['Message' => 'Your Comment has been updated.']);
        }
        else
        {
            return response(['Message' => 'Something went wrong in while updating comment..!!!']);
        }
    }


    // user delete comment
    function user_comment_delete(UserCommentDeleteValidation $req)
    {
        $req->validated();
        $user = new Comment;

        $token = $user->name = $req->input('token');
        $cid = $user->pid = $req->input('cid');
        
        $data = DB::table('users')->where('remember_token', $token)->get();

        $wordcount = count($data);

        if($wordcount > 0)
        {
            // get user id from 
            $uid = $data[0]->uid;

            DB::table('comments')->where(['cid' => $cid, 'user_id' => $uid])->delete();

            return response(['Message' => 'Your Comment has been deleted.']);
        }
        else
        {
            return response(['Message' => 'Something went wrong in while deleted comment..!!!']);
        }     
    }
}
