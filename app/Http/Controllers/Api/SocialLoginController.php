<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite; // socialite namespace
use App\Customer;
use Illuminate\Support\Facades\DB;

class SocialLoginController extends Controller
{
	public function handleProviderCallback(Request $request)
	{		
		try
		{
			// $user_social_signin = $request->all();
			Log::debug("social auth user" . print_r($request->all(),true));

			//check if social account exist
			$user_social_account=DB::table('social_accounts')
				->where('provider_id',$request->provider_id)
				->first(); 

			Log::debug("user_social_account" . print_r($user_social_account,true));

			// check if user already register
			$old_customer_data = Customer::where('email',$request->email)
				->first();

			Log::debug("old_customer_data" . print_r($old_customer_data,true));

			// if registered user with social account
			if(!empty($old_customer_data) && !empty($user_social_account))
			{
				Log::debug("block 1");
				Auth::login($old_customer_data,true);
					$data['name'] = Auth::user()->name;
		            $data['email'] = Auth::user()->email;
		            $data['ph_no'] = Auth::user()->ph_no;
		            $data['address'] = Auth::user()->address;

					$token = Auth()->user()->createToken('Body By Lekan Customer App')->accessToken;				

					return response()->json(['token' => $token,"status" => true, 'user'=>$data, 'message' => 'You are logged in.'], 200);
			}

			elseif(!empty($old_customer_data) && $old_customer_data->confirmed==1)
			{
				Log::debug("block 2");
				// registered and verified user with no social account
				$social_account_data['customers_id']=$old_customer_data->id;
				$social_account_data['provider_id']=$request->provider_id;
				$social_account_data['provider_name']=$request->provider_name;

				$customer_social_account = DB::table('social_accounts')->insert($social_account_data);

				//Link social account
				if($customer_social_account)
				{				
					Auth::login($old_customer_data,true);
					$data['name'] = Auth::user()->name;
		            $data['email'] = Auth::user()->email;
		            $data['ph_no'] = Auth::user()->ph_no;
		            $data['address'] = Auth::user()->address;

					$token = Auth()->user()->createToken('Body By Lekan Customer App')->accessToken;					

					return response()->json(['token' => $token,"status" => true, 'user'=>$data, 'message' => 'You are logged in.'], 200); 	            				
				}
			}

			elseif($old_customer_data && $old_customer_data->confirmed==0)
			{
				Log::debug("block 3");
				// registered user but not verified
				$social_account_data['customers_id']=$old_customer_data->id;
				$social_account_data['provider_id']=$request->provider_id;
				$social_account_data['provider_name']=$request->provider_name;

				$customer_social_account = DB::table('social_accounts')->insert($social_account_data);

				// link social account 
				$customer_data['confirmed']=1;
				$customer_data['confirmation_code']=NULL;

				$savedata=Customer::where('id',$old_customer_data->id)->update($customer_data);

				
				Auth::login($old_customer_data,true);
				$data['name'] = Auth::user()->name;
	            $data['email'] = Auth::user()->email;
	            $data['ph_no'] = Auth::user()->ph_no;
	            $data['address'] = Auth::user()->address;

				$token = Auth()->user()->createToken('Body By Lekan Customer App')->accessToken;

				return response()->json(['token' => $token,"status" => true, 'user'=>$data, 'message' => 'You are logged in.'], 200); 				
			}
			
			else
			{
				// Not a registered user 
				Log::debug("block 4");
				$customer_social_data['email']=$request->email;
				$customer_social_data['name']=$request->name;
				$customer_social_data['provider_id']=$request->provider_id;
				$customer_social_data['provider_name']=$request->provider_name;

				Log::debug("new_customer_data" . print_r($customer_social_data,true));
				return response()->json(['status' => false, 'user' => $customer_social_data], 200);
			}
		}
		catch(\Exception $e)
		{
			Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
			return response()->json(["message" => "Something went wrong!","status" => false],404);
		}
	}

}