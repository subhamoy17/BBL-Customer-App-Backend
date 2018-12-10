<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
 
class ChangePasswordController extends Controller
{
	public function updateAdminPassword(Request $request)
	{	

		$password =$request->oldpassword;
		Log::debug ( "password ". $password);
		// $data=DB::table('customers')->select('password')
		// 	->where('id',Auth::guard('api')->user()->id)->get();
		$data= Auth::guard('api')->user()->password;
		Log::debug ( "data". $data);

		if(Hash::check($password, $data))
		{
			$validatedData = Validator::make($request->all(), [
				'new-password' => 'required|string|min:6|confirmed',
	        ]);
	        if($validatedData->fails())
	      	{
	       		 $errors = $validatedData->errors();
	        	 return response()->json(["message" => $errors->first(),"status" => false], 200);
	      	}
	      	try
	      	{
		        $user = Auth::guard('api')->user();
		        //Log::debug ( " error ". $user); 
		        $user->password = bcrypt($request->get('new-password'));
		        $user->save();
		        $email=Auth::guard('api')->user()->email;
		        $name=Auth::guard('api')->user()->name;
		 		$new_password=$request->get('new-password');

		 		 Mail::send('customer_change_password_message',['new_password' =>' ','email' => $email,'name'=>$name], function($message) {
		           $message->to(Auth::guard('api')->user()->email)->subject('Change Password');          
		            });
		 		 return response()->json(["message" => "Password Change Successfully","status" => true],200);
	 		}
	 		catch (Exception $e) 
			{
				return response()->json(["message" => "Something went wrong!","status" => false],404);
			}
 		}
 		else
 		{
 			return response()->json(["message" =>"You have entered wrong old password","status" => true],200);
 		}

	}
}
