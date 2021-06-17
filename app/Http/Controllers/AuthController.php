<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use \Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function login(Request $request) {
        
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'token'    => 'required'
        ]);

        
        $user = $this->users($request->all());
        
        
        if (! $user) {
           
            return response()->json(['message' => false, 'data' => 'Username dan password tidak cocok !!!']);
        
        } else {

            $this->token($request->all(), $user);
            
            return response()->json(['message' => true, 'data' => $user]);
        }
        

    
    
    }

    public function token($user, $data) {

        if($data->token !== $user['token']) {
            $update = ['token' => $user['token']];
            DB::table('users')->where('id', $data->id)->update($update);

        }

    }
    
    public function users($user) {
        
        $data  = DB::table('users')
                    ->where([
                        ['username','=', $user['username']], 
                        ['password', '=', $user['password']]
                    ])->first();
                
        return $data;
        
    }

    public function editUser(Request $request){

        $data = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        $query = DB::table('users')->where('id', $request->id)->update($data);

        if(! $query) {
            return response()->json(['message' => false]);
        }

        return response()->json(['message' => true]);

    }


    public function register(Request $request){

        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
            'token'    => 'required',
        ]);

        $data = [
            'username' => $request->username,
            'password' => $request->password,
            'token'    => $request->token,  
        ];

        $query = DB::table('users')->insert($data);

        if(! $query) {
            return response()->json(['message' => false]);
        }

        return response()->json(['message' => true]);

    }
    
}
