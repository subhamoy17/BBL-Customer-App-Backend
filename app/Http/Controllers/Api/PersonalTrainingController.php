<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\DateTime;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Customer;
use App\User;
use App\Notifications\PlanPurchasedNotification;


class PersonalTrainingController extends Controller
{
	//// Strip payment for personal training session ////
	public function pt_stripe_payment(Request $request)
  	{
  	  	try
  	  	{
			$payment_history_data=[];
	    	$order_data=[];

		  	$package_details=DB::table('products')
			    ->join('training_type','training_type.id','products.training_type_id')
			    ->join('payment_type','payment_type.id','products.payment_type_id')
			    ->select('training_type.training_name as product_name','payment_type.payment_type_name as payment_type_name','products.total_sessions as total_sessions','products.id as product_id',(DB::raw('products.validity_value * products.validity_duration  as validity')),'products.total_price as total_price','products.price_session_or_month as price_session_or_month','products.validity_value as validity_value','products.validity_duration as validity_duration','products.contract as contract','products.notice_period_value as notice_period_value','products.notice_period_duration as notice_period_duration')
			    ->where('products.id',$request->product_id)
				->first();
			Log::debug ( " Package Details ". print_r($package_details,true));

			if(!empty($package_details))
	      	{
	          $customer_details=Customer::find(Auth::guard('api')->user()->id);
	        
	          \Stripe\Stripe::setApiKey ( 'sk_test_oBDW3aKMIoUchBs9TKSQ8TwF' );

	          $customer =  \Stripe\Customer::create([
	            'email' =>Auth::guard('api')->user()->email,
	          ]);
	        
	          $payment_details = \Stripe\Charge::create ( array (
	            "amount" => intval($package_details->total_price)*100,
	            "currency" => "gbp",
	            "source" => $request->input ( 'stripeToken' ), // obtained with Stripe.js
	            "description" =>$package_details->product_name
	          ) );

	          $payment_history_data['payment_id']=$payment_details->id;
	          $payment_history_data['currency']='GBP';
	          $payment_history_data['amount']=$package_details->total_price;
	          $payment_history_data['payment_mode']='Stripe';
	          $payment_history_data['status']='Success';

	          $payment_history=DB::table('payment_history')->insert($payment_history_data);

	          $order_data['customer_id']=Auth::guard('api')->user()->id;
	          $order_data['product_id']=$request->product_id;
	          $order_data['training_type']=$package_details->product_name;
	          $order_data['payment_type']=$package_details->payment_type_name;
	          $order_data['order_purchase_date']=Carbon::now()->toDateString();
	          if($package_details->validity!='')
	          {
	            $order_data['order_validity_date']=Carbon::now()->addDay($package_details->validity);
	          }
	          else
	          {
	            $order_data['order_validity_date']='2099-12-31';
	          }	          
	          $order_data['payment_option']='Stripe';
	          $order_data['status']=1;
	          $order_data['no_of_sessions']=$package_details->total_sessions;
	          $order_data['remaining_sessions']=$package_details->total_sessions;
	          $order_data['price_session_or_month']=$package_details->price_session_or_month;
	          $order_data['total_price']=$package_details->total_price;
	          $order_data['validity_value']=$package_details->validity_value;
	          $order_data['validity_duration']=$package_details->validity_duration;
	          $order_data['contract']=$package_details->contract;
	          $order_data['notice_period_value']=$package_details->notice_period_value;
	          $order_data['notice_period_duration']=$package_details->notice_period_duration;
	          $order_data['payment_id']=DB::getPdo()->lastInsertId();
	          Log::debug ( " Order data ". print_r($order_data,true));

	          $order_history=DB::table('order_details')->insert($order_data);
	          Log::debug ( " Order inserted ". print_r($order_history,true));

	          $notifydata['product_name'] =$package_details->product_name;
	          $notifydata['no_of_sessions'] =$package_details->total_sessions;
	          $notifydata['product_validity'] =$order_data['order_validity_date'];
	          $notifydata['product_purchase_date'] =$order_data['order_purchase_date'];
	          $notifydata['product_amount'] =$package_details->total_price;
	          $notifydata['order_id'] =$payment_details->id;
	          $notifydata['payment_mode'] ='Stripe';
	          $notifydata['url'] = '/customer/purchased-history';
	          $notifydata['customer_name']=$customer_details->name;
	          $notifydata['customer_email']=$customer_details->email;
	          $notifydata['customer_phone']=$customer_details->ph_no;
	          $notifydata['status']='Payment Success';

	          $customer_details->notify(new PlanPurchasedNotification($notifydata));

	          return response()->json(["status" => true, 'order_ref_id' =>$payment_details->id, 'message' => 'Payment Success'], 200);
	  		}
	  		 else
	      	{
	          return response()->json(["status" => false, 'message' => 'Enter correct product_id'], 200);
	      	}

      	}
      	catch (\Exception $e) 
	    {
		  Log::debug ( " Package Details in catch ". print_r($package_details,true));
		  $payment_history_data['payment_id']='';
		  $payment_history_data['currency']='GBP';
		  $payment_history_data['amount']=$package_details->total_price;
		  $payment_history_data['payment_mode']='Stripe';
		  $payment_history_data['status']='Failed';
		  $payment_history=DB::table('payment_history')->insert($payment_history_data);

		  $order_data['customer_id']=Auth::guard('api')->user()->id;
		  $order_data['product_id']=$request->product_id;
		  $order_data['training_type']=$package_details->product_name;
		  $order_data['payment_type']=$package_details->payment_type_name;
		  $order_data['order_purchase_date']=Carbon::now()->toDateString();
		  Log::debug ( " Exception ". print_r ($e->getMessage(), true));
		  if($package_details->validity!='')
		  {
		    $order_data['order_validity_date']=Carbon::now()->addDay($package_details->validity);
		  }
		  else
		  {
		    $order_data['order_validity_date']='2099-12-30';
		  }
		  $order_data['payment_option']='Stripe';
		  $order_data['status']=0;
		  $order_data['no_of_sessions']=$package_details->total_sessions;
		  $order_data['remaining_sessions']=0;
		  $order_data['price_session_or_month']=$package_details->price_session_or_month;
		  $order_data['total_price']=$package_details->total_price;
		  $order_data['validity_value']=$package_details->validity_value;
		  $order_data['validity_duration']=$package_details->validity_duration;
		  $order_data['contract']=$package_details->contract;
		  $order_data['notice_period_value']=$package_details->notice_period_value;
		  $order_data['notice_period_duration']=$package_details->notice_period_duration;

		  $order_data['payment_id']=DB::getPdo()->lastInsertId();

		  $order_history=DB::table('order_details')->insert($order_data);

		  $notifydata['product_name'] =$package_details->product_name;
		  $notifydata['no_of_sessions'] =$package_details->total_sessions;
		  $notifydata['product_validity'] =$order_data['order_validity_date'];
		  $notifydata['product_purchase_date'] =$order_data['order_purchase_date'];
		  $notifydata['product_amount'] =$package_details->total_price;
		  $notifydata['order_id'] =' ';
		  $notifydata['payment_mode'] ='Stripe';
		  $notifydata['url'] = '/customer/purchased-history';
		  $notifydata['customer_name']=$customer_details->name;
		  $notifydata['customer_email']=$customer_details->email;
		  $notifydata['customer_phone']=$customer_details->ph_no;
		  $notifydata['status']='Payment Failed';

		  $customer_details->notify(new PlanPurchasedNotification($notifydata));

		  return response()->json(["status" => false, 'message' => 'Error! Please Try again.'], 400);
	    }
	}

	//// Bank payment for personal training session ////
	public function pt_bank_payment(Request $request)
  	{
  		DB::beginTransaction();
  		try
  		{
	  		$payment_history_data=[];
		    $order_data=[];

		    $package_details=DB::table('products')
		      ->join('training_type','training_type.id','products.training_type_id')
		      ->join('payment_type','payment_type.id','products.payment_type_id')
		      ->select('training_type.training_name as product_name','payment_type.payment_type_name as payment_type_name','products.total_sessions as total_sessions','products.id as product_id',(DB::raw('products.validity_value * products.validity_duration  as validity')),'products.total_price as total_price','products.price_session_or_month as price_session_or_month','products.validity_value as validity_value','products.validity_duration as validity_duration','products.contract as contract','products.notice_period_value as notice_period_value','products.notice_period_duration as notice_period_duration')
		      ->where('products.id',$request->product_id)
		      ->first();
		    Log::debug ( " Package Details ". print_r($package_details,true));

		    if(!empty($package_details))
	        {
	          $user = Auth::guard('api')->user();
	          $payment_history_data['purchase_history_id']= 0;
	          $payment_history_data['payment_id']='BBL'.time();
	          $payment_history_data['amount']=$package_details->total_price;
	          $payment_history_data['payment_mode']='Bank Transfer';
	          $payment_history_data['status']='Inprogress';
	          $payment_history_data['description']=$request->package_description;
	          if($request->hasFile('image'))
	          {
	            $myimage=$request->image;
	            $folder="backend/bankpay_images/"; 
	            $extension=$myimage->getClientOriginalExtension(); 
	            $image_name=time()."_bankdocimg.".$extension; 
	            $upload=$myimage->move($folder,$image_name); 
	            $payment_history_data['image']=$image_name;
	          }
	          else
	          {
	            $payment_history_data['image']='null';
	          }
	          Log::debug ( " payment history ". print_r($payment_history_data,true));

	          $payment_history=DB::table('payment_history')->insert($payment_history_data);
	          Log::debug ( " Payment inserted ".print_r($payment_history,true));

	          $order_data['payment_id']=DB::getPdo()->lastInsertId();
	          $order_data['customer_id']=Auth::guard('api')->user()->id;
	          $order_data['product_id']=$request->product_id;
	          $order_data['training_type']=$package_details->product_name;
	          $order_data['payment_type']=$package_details->payment_type_name;
	          $order_data['order_purchase_date']=Carbon::now()->toDateString();
	          if($package_details->validity!='')
	          {
	            $order_data['order_validity_date']=Carbon::now()->addDay($package_details->validity);
	          }
	          else
	          {
	            $order_data['order_validity_date']='2099-12-31';
	          } 
	          $order_data['payment_option']='Bank Transfer';
	          $order_data['status']=1;
	          $order_data['no_of_sessions']=$package_details->total_sessions;
	          $order_data['remaining_sessions']=$package_details->total_sessions;
	          $order_data['price_session_or_month']=$package_details->price_session_or_month;
	          $order_data['total_price']=$package_details->total_price;
	          $order_data['validity_value']=$package_details->validity_value;
	          $order_data['validity_duration']=$package_details->validity_duration;
	          $order_data['contract']=$package_details->contract;
	          $order_data['notice_period_value']=$package_details->notice_period_value;
	          $order_data['notice_period_duration']=$package_details->notice_period_duration;
	          Log::debug ( " Order data ". print_r($order_data,true));

	          $order_history=DB::table('order_details')->insert($order_data);
	          Log::debug ( " Order inserted ". print_r($order_history,true));

	          DB::commit();

	          return response()->json(["status" => true, 'user'=>$order_data,'message' => 'Bank Payment Success'], 200);
      		}
		    else
		    {
		      return response()->json(["status" => false, 'message' => 'Enter correct product_id'], 200);
		    }   

  		}
  		catch (Exception $e) 
	    {
	      DB::rollback();
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }

  	}
}