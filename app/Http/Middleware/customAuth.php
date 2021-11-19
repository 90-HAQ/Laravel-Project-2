<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DataBaseConnection;

class customAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $req, Closure $next)
    {
        $token = $req->token;

        if(!empty($token))
        {
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
                return $next($req);
            }
            else
            {
                return response(['Message' => 'Your are not Authenticated User.']);
            }
        }
        else
        {
            return response(['Message' => 'Your Token is Empty.']);
        }
        
    }
}
