<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UserAddFriendValidation;
use App\Http\Requests\UserRemoveFriendValidation;
use App\Services\DataBaseConnection;

class UserMakeFriendsController extends Controller
{
    // user add friend
    function user_add_friends(UserAddFriendValidation $req)
    {

        $req->validated();
        
        $token = $req->input('token');
        $email = $req->input('email');
        $status = $req->input('status');

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();


        $insert1 = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);

        $insert2 = $coll2->$table->findOne(
        [
            'email' => $email,
        ]);

        // get id of user-1
        $uid1 = $insert1['_id']; 

        // get id of user-2
        $uid22 = $insert2['_id']; 
        // get name of user-2
        $name2 = $insert2['name'];

        // to check if user-2 is email-verified or not
        $user2_verify = $insert2['email_verified_at']; 

        // to check if friend user is email-verified or not
        if(!empty($insert1) && !empty($insert2))
        {
            // this if is for to check num of rows from user1 variable  
            // this if is for to check num of rows from user2 variable  
            if(!empty($user2_verify))
            {                    
                // user cannot add himself as friend.
                if($uid1 != $uid22)
                {                    
                    $coll = new DatabaseConnection();
                    $table = 'users';
                    $coll2 = $coll->db_connection();

                    $insert3 = $coll2->$table->findOne(
                    [
                        "friends.user_id2" => $uid22
                    ]);                    

                    if(empty($insert3))
                    {
                        $coll = new DatabaseConnection();
                        $table = 'users';
                        $coll2 = $coll->db_connection();
            
                        // this error will be always shown so ignore it.
                        $uuid = new \MongoDB\BSON\ObjectId($uid1);
            
                        // it will generate a random id as comment id
                        $friend_id = new \MongoDB\BSON\ObjectId();
            
                        $friends = array(
                            'friend_id'    =>      $friend_id,
                            'user_id2'      =>      $uid22,
                            'status'        =>      $status,
                        );
            
                        $update = $coll2->$table->updateOne(["_id"=>$uuid],['$push'=>["friends" => $friends]]);

                        if(!empty($update))
                        {
                            return response(['Message' => 'Congrats '.$name2.' is your friend now...!!!!']);
                        }
                        else
                        {
                            return response(['Message' => 'Something went wrong while adding friend...!!!!']);
                        }                                
                    }
                    else
                    {
                        return response(['Message' => 'Already your Friend.']);
                    }
                }
                else
                {
                    return response(['Message' => 'You cannot add yourself as a friend.']);   
                }                            
            }       
            else
            {
                return response(['Message' => 'Friend not Found / Friend is not verified']);                           
            } 
        }
        else
        {
            return response(['Message' => 'Friend not Found / Something went wrong with friend.']);
        }
    }


    // user remove / unfriend friend

    function user_remove_friends(UserRemoveFriendValidation $req)
    {

        $req->validated();
        
        $token = $req->input('token');
        $user_id2 = $req->input('user_id2');

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert1 = $coll2->$table->findOne(
        [
            'remember_token' => $token,
        ]);
        

        $user_id22 = new \MongoDB\BSON\ObjectId($user_id2);
        $insert2 = $coll2->$table->findOne(
        [
            '_id' => $user_id22,
        ]);

        // get id of user-1
        $uid1 = $insert1['_id']; 

        $name2 = $insert2['name'];

        // to check if friend user is email-verified or not
        if(!empty($insert1) && !empty($insert2))
        {
            // this if is for to check num of rows from user1 variable  
            // this if is for to check num of rows from user2 variable  
                // user cannot un-friend himself as friend.
            if($uid1 != $user_id2)
            {
                $coll = new DatabaseConnection();
                $table = 'users';
                $coll2 = $coll->db_connection();
    
                // this error will be always shown so ignore it.
                $uuid = new \MongoDB\BSON\ObjectId($uid1);
    
    
                $delete = $coll2->$table->updateOne(["_id" => $uuid, "friends.user_id2" => $user_id22], ['$pull' => ["friends" => ["user_id2" => $user_id22]]]);

                if(!empty($delete))
                {
                    return response(['Message' => 'Congrats '.$name2.' is removed now from your friend list...!!!!']);
                }
                else
                {
                    return response(['Message' => 'Not your Friend / Something went wrong while removing friend...!!!!']);
                } 
            }
            else
            {
                return response(['Message' => 'You cannot remove yourself as a friend.']);   
            }                            
        }
        else
        {
            return response(['Message' => 'Friend not Found / Something went wrong with friend.']);
        }
    }
}
