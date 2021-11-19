<?php


namespace App\Http\Controllers;
use App\Services\DataBaseConnection;
use Illuminate\Http\Request;


class UserController extends Controller
{
    function insert_data()
    {
        $coll = new DatabaseConnection();
        $table = 'users';
        $coll2 = $coll->db_connection($table);

        $insert = $coll2->insertOne(
        [
            'name' => 'Umar',
        ]);
        dd($insert);
    }    
}