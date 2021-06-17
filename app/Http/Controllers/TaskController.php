<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use \Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;


/**
 * 
 */
class TaskController extends Controller
{
	
	public function getUser(Request $request) {

		$data = DB::table('users')->get();

		return response()->json($data);
	}

	public function task(Request $request) {

		$data = DB::table('db_task')->get();
		$user = $request->user();
		$array = [];

		foreach ($data as $key => $value) {
			
			$from 	= DB::table('users')->where('id', $value->id_from)->first()->username;
			$to		= DB::table('users')->where('id', $value->id_to)->first()->username;	

			if ( $value->id_from === $user->id ) {
				$text = 'Anda memberikan tugas '.$value->text.' kepada '. $to;
			} else {
				$text = 'Anda mendapatkan tugas '.$value->text.' dari '.$from;
			}

			if($value->id_from === $user->id OR $value->id_to === $user->id){	
				$array[] = [
					'text' => $text,
				];
			}

		
		}

		return  response()->json($array);

	}

	public function notifSend($token, $from, $isi){
		$url = 'https://fcm.googleapis.com/fcm/send';
		
		$ch = curl_init($url);

		$header = [
			'Content-Type: application/json',
			'Authorization: key=AAAAfGbzP9Q:APA91bGhdpR64LE-yzq8d4XPXQqH1aZQUQYjD1Jq_ltgIj7vvcrvzhJ45e1pukubcukge8L0Juq0DmMTZhNmDtG3ku11fjD14Ku6l1OeFL6HsO5cvZPa45Qyz4AIj1UJKg8Z28QDD1YI'
		];

		$body = [
					"to" 			=> $token,
					"priority" 		=> "high",
				    "soundName" 	=> "default",
					"notification" 	=> [
										"title"	=>"Job dari".$from,
										"body"=> $isi				
					]
		];

		$json = json_encode($body);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 

		$result=curl_exec ($ch);
		
		curl_close($ch);

		return true;
	}

	public function add(Request $request) {

		$this->validate($request, [
            'id_from' 	=> 'required',
            'id_to' 	=> 'required',
            'text'		=> 'required',
        ]);

        $data = [
        	'id_from' 	=> $request->id_from,
        	'id_to'		=> $request->id_to,
        	'text'		=> $request->text,
        ];

        $query = DB::table('db_task')->insert($data);

        if(!$query) {
        
        	return response()->json(['message' => false]);
        
        } else {

        	$from 	= DB::table('users')->where('id', $request->id_from)->first()->username;
			$to		= DB::table('users')->where('id', $request->id_to)->first()->username;
			$token  = DB::table('users')->where('id', $request->id_to)->first()->token;

			// if(empty($token)) {
				
				$send = $this->notifSend($token, $from, $request->text);

			// }

        	return response()->json(['message' => true]);
        	
        }

	}

	
}