<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\testmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Requests\SignupValidation;
use App\Http\Requests\LoginValidation;
use App\Http\Requests\UserUpdateDetailsValidation;
use App\Http\Requests\UserForgetValidation;
use App\Http\Requests\UserChangePasswordValidation;
use App\Services\DataBaseConnection;



class UserCredentialsController extends Controller
{ 
    // mail sending function
    public function sendmail($sendto, $verify_token)
    {
        $details = [
            'title' =>  'Signup Verification.',
            'body'  =>  'Please Verify your Account. Please Click on this link to verify http://127.0.0.1:8000/api/welcome_login'.'/'.$sendto.'/'.$verify_token
        ];

        Mail::to($sendto)->send(new testmail($details));
        return response(['Message' => 'Email has been sent for Verification, Please verify your Account.']);
    }



    // user signup 
    public function signup(SignupValidation $req)
    {

        $req->validated();
    
        $name = $req->input('name');
        $email = $req->input('email');
        $password = Hash::make($req->input('password')); // return hashed password
        $gender = $req->input('gender');
        $status = 0;
        $verify_token = rand(10, 5000);


        // parameters for mail sending function.
        $sendto = $email;
        $verify_token = $verify_token;


        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->insertOne(
        [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'gender' => $gender,
            'status' => $status,
            'verif_token' => $verify_token,

        ]);

        if($insert)
        {
            $result = $this->sendmail($sendto, $verify_token);
            return response($result,200);
        }
        else
        {
            return response(['Message'=>'Something went wrong in Signup Api..!!!']);
        }       
    }



    // welcome api for user email verification and updation at backend
    public function welcome_to_login($email, $verify_token)
    {

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->find(
        [
            'email' => $email,
            'verif_token' => $verify_token,
        ]);

        if($insert)
        {
            $update = $coll2->$table->updateMany(array("email"=>$email),
            array('$set'=>array('email_verified_at' => now(), 'updated_at' => now())));
            
            return response(['Message'=>'Your Email has been Verified']);
        }
        else
        {
            return response(['Message' => 'Something went wrong in Welcome To Login Api..!!!']);
        }
    }


    // user login
    public function login(LoginValidation $req)
    {
        $pas = 0;
        $status = 0;
        $email_verified = 0;

        $req->validated();
        $email = $req->input('email');
        $password = $req->input('password');

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->findOne(
        [
            'email' => $email,
            // 'password' => $password,
        ]);

        $pas = $insert['password'];
        $status = $insert['status'];  
        $email_verified = $insert['email_verified_at']; 


        if(!empty($email_verified) && Hash::check($password, $pas))
        {
            if($status == 0)
            {
                // jwt token generate
                $key = "90HAQ";
                $payload = array(
                    "iss" => "localhost",
                    "aud" => "users",
                    "iat" => time(),
                    "nbf" => 1357000000
                );
                $jwt = JWT::encode($payload, $key, 'HS256');

                // check if jwt is generating or not.
                //echo $jwt;

                $coll2->$table->updateMany(array("email"=>$email),
                array('$set'=>array('remember_token' => $jwt, 'status' => '1')));

                return response(['Message' => 'Now you are logged In', 'access_token' => $jwt]);
            }
            else
            {
                return response(['Message' => 'You are Already Logged In..!!!']);
            }
        }
        else
        {
            return response(['Message' => 'Your email '.$email.' does not exists in our record '.'because your email is not verified. Please verify your email first.']);
            //return response(['Message' => 'Your email '.$user->email.' is not verified. Please verify your email first.']);
        }
    }


    // user forgets password after signup and can't login, so reset password.
    function userForgetPassword(UserForgetValidation $req)
    {
        // $req->validated();
        // $mail=$req->email;

        $req->validated();
        $user = new User;
        $mail = $user->email = $req->input('email');

        $data = DB::table('users')->where('email', $mail)->get();
        
        $num = count($data);
        
        if($num > 0)
        {
            foreach ($data as $key)
            {
                $verfiy =$key->email_verified_at;
            }
            if(!empty($verfiy))
            {
                $otp=rand(1000,9999);
                DB::table('users')->where('email', $mail)->update(['verify_token'=> $otp]);
                return response($this->sendMailForgetPassword($mail,$otp));
            }
            else{
                return response(['Message'=>'User not Exists']);
            }
        }
        else{
            return response(['Message'=>'User not Exists']);
        }
    }


    // send token as otp for resetting old password with new password,
    function sendMailForgetPassword($mail,$otp)
    {
        $details=[
            'title'=> 'Forget Password Verification',
            'body'=> 'Your OTP is '. $otp . ' Please copy and paste the change Password Api'
        ]; 
        Mail::to($mail)->send(new testmail($details));
        return response(['Message' => 'An OTP has been sent to '.$mail.' , Please verify and proceed further.']);
    }


    // get otp-token and veirfy then update the user new password.
    function userChangePassword(UserChangePasswordValidation $req)
    {
        $req->validated();
        $user = new User;
        $mail = $user->email = $req->input('email');
        $token = $user->otp = $req->input('otp');
        $pass=Hash::make($req->input('password'));

        $data = DB::table('users')->where('email', $mail)->get();
        $num = count($data);
        
        if($num > 0)
        {
            foreach ($data as $key)
            {
                $token1 =$key->verify_token;
            }
            if($token1==$token)
            {
                DB::table('users')->where('email', $mail)->update(['password'=> $pass]);
                return response(['Message'=>'Your Password has been updated so now you can login easily.. Thankyou..!!!!. ']);
            }
            else{
                return response(['Message'=>'Otp Does Not Match. ']);
            }
        }
        else{
            return response(['Message'=>'Please Enter Valid Mail. ']); 
        }
    }


    // user update details
    function user_update_details(UserUpdateDetailsValidation $req)
    {
        $req->validated();
        $user = new User;
        $token = $user->token = $req->input('token');
        $name = $req->name = $req->input('name');
        $password = Hash::make($req->input('password')); // return hashed password

        DB::table('users')->where('remember_token', $token)->update(['name' => $name, 'password' => $password]);
        return response(['Message' => 'User Credentials Updated']);    

    }


    // user view all data and posts as well
    public function user_details_and_posts_details(Request $req)
    {
        $token = $req->token;
        $data = DB::table('users')->where(['remember_token' => $token])->get();
        $uid = $data[0]->uid;
        $check = count($data);

        if($check > 0)
        {
            $data = User::with(['AllUserPost','AllUserPostComments'])->where('uid', $uid)->get();
            return response(['Message' => $data]);
        }
        else
        {
            return response(['Message' => 'This user does not exist...!!']);
        } 
    }


    // user logout
    public function user_logout(Request $req)
    {
        $token = $req->token;

        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection();

        $insert = $coll2->$table->findOne(
        [
            'remember_token' => $token,
            // 'password' => $password,
        ]);

        if($insert)
        {

            $coll2->$table->updateMany(array("remember_token"=>$token),
            array('$set'=>array('remember_token' => null, 'status' => '0')));

            // DB::table('users')->where(['remember_token' => $token])->update(['status'=> '0']);
            // DB::table('users')->where(['remember_token' => $token])->update(['remember_token' => null]);
            return response(['Message' => 'Logout Succeccfully..!!']);
        }
        else
        {
            return response(['Message' => 'Token not found or expired..!!']);
        } 
    }
}
