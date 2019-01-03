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
use App\Notifications\BootcampSessionNotification;


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


  	//// Personal Training slot booking using Trainer ////
  	public function booking_personal_training()
	{
		try
		{
			$pt_session_address=DB::table('bootcamp_plan_address')
			  ->join('bootcamp_plans','bootcamp_plans.address_id','bootcamp_plan_address.id')
			  ->select('bootcamp_plan_address.address_line1','bootcamp_plan_address.id','bootcamp_plans.address_id')
			  ->whereNull('bootcamp_plans.deleted_at')
			  ->distinct('bootcamp_plans.address_id')
			  ->first();

			$all_pt_trainer=DB::table('personal_training_plan_schedules')
			  ->join('users','users.id','personal_training_plan_schedules.trainer_id')
			  ->select('personal_training_plan_schedules.trainer_id as trainer_id','users.name as trainer_name')
			  ->whereNull('users.deleted_at')
			  ->where('is_active',1)
			  ->distinct('users.name as trainer_name')
			  ->get()->all();

    		$current_date=Carbon::now()->toDateString();
    		// Check customer product validity as well as available session 
    		$order_details=DB::table('order_details')
			  ->join('products','products.id','order_details.product_id')
			  ->join('training_type','training_type.id','products.training_type_id')
			  ->where('order_details.customer_id',Auth::guard('api')->user()->id)
			  ->where('order_details.status',1)
			  ->where('training_type.id',1)
			  ->where('order_details.order_validity_date','>=',$current_date)
			  ->where('order_details.remaining_sessions','>',0)
			  ->get()->all();

			$no_of_sessions=0;
		    if(count($order_details)>0)
		    {
		      foreach($order_details as $total)
		      {
		        $no_of_sessions=$no_of_sessions+$total->remaining_sessions; 
		      }
		      return response()->json(['status' => true, 'flag' => 1, 'pt_session_address'=>$pt_session_address,'all_pt_trainer'=>$all_pt_trainer, 'no_of_sessions'=>$no_of_sessions], 200);
		    }
		    else
		    {
		      $no_of_sessions=0;
		      return response()->json(['status' => true, 'flag' => 0], 200);
		    }
		}
		catch (Exception $e) 
	    {
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }
	}

	// Find date with respect to trainer //
	public function booking_pt_date(Request $request)
	{
		try
		{
			$current_date=Carbon::now()->toDateString();

			// get customer's product validity last end date
		    $customer_product_validity=DB::table('order_details')
		        ->join('payment_history','payment_history.id','order_details.payment_id')
		        ->where('payment_history.status','Success')
		        ->where('order_details.order_validity_date','>=',$current_date)
		        ->where('order_details.status',1)
		        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
		        ->where('order_details.training_type','=','Personal Training')
		        ->max('order_details.order_validity_date');
		    Log::debug ( " ::Customer product validity:: ". print_r ($customer_product_validity, true));

		    if(empty($customer_product_validity))
		    {
		       $customer_product_validity='';
		    }

			// check already booked shedules
		    $alredy_booked_shedule_id=DB::table('personal_training_booking')
		    	->where('customer_id',Auth::guard('api')->user()->id)
		        ->whereNull('deleted_at')
		        ->pluck('personal_training_plan_shedules_id');

			//check already booked date
		    $alredy_booked_date=DB::table('personal_training_plan_schedules')
		        ->whereIn('id',$alredy_booked_shedule_id)
		        ->pluck('plan_date');
		    Log::debug ( " ::Alredy booked date:: ". print_r ($alredy_booked_date, true));

			// get all available date to apply
		    $date_details=DB::table('personal_training_plan_schedules')
			    ->where('plan_date','<=',$customer_product_validity)
			    ->whereNull('deleted_at')
			    ->whereNotIn('plan_date',$alredy_booked_date)
			    //->where('plan_date','>',$current_date)
			    ->where('trainer_id',$request->trainer_id)
			    ->select('plan_date')
			    ->distinct('plan_date')
			    ->get()->all();
		    Log::debug ( " ::Date Details:: ". print_r ($date_details, true));

	        if(count($date_details)>0)
	        {
	        	return response()->json(['status' => true, 'date_details' =>$date_details], 200);
	        }
		    else
		    {
		      return response()->json(['status' => false, 'message' => 'No data found!'], 200);
		    }
		}
		catch (Exception $e) 
	    {
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }
	}

	// Find time with respect to date //
	public function booking_pt_time(Request $request)
	{		
		$arr=[];
		try
		{
			$get_slot_times=DB::table('personal_training_booking')
				->join('personal_training_plan_schedules','personal_training_plan_schedules.id','personal_training_booking.personal_training_plan_shedules_id')
  				->pluck('personal_training_booking.personal_training_plan_shedules_id');
  			Log::debug(" Check get_slot_times ".print_r($get_slot_times,true));

  			if(count($get_slot_times))
			{
				foreach($get_slot_times as $key=>$hour) { }

				  $length=$key+1;
				  $upto=$length*4;

				  for($i=$length;$i<$upto;$i++)
				{
				  $get_slot_times[$i]=$get_slot_times[$i-$length]+1;

				}

				foreach($get_slot_times as $key=>$hour) { }

				$length=$key+1;
				$upto=$length*4;

				  for($i=$length;$i<$upto;$i++)
				{
				  $get_slot_times[$i]=$get_slot_times[$i-$length]-1;

				}

			}
			Log::debug("get_slot_times ".print_r($get_slot_times,true));

			$time_details=DB::table('personal_training_plan_schedules')
		        ->where('plan_date',$request->pt_date)
		        ->where('trainer_id',$request->trainer_id)
		        ->whereNotIn('personal_training_plan_schedules.id',$get_slot_times)
		        ->whereNull('deleted_at')
		        ->get()->all();

		    //Log::debug ( " ::time_details:: ". print_r ($time_details, true));

		    for($i = 0;$i<count($time_details);$i++)
		    {
		    	$time=new \stdClass;
			    $st_time=DB::table('slot_times')
			    	->join('personal_training_plan_schedules','personal_training_plan_schedules.plan_st_time_id','slot_times.id')
			    	->where('plan_date',$request->pt_date)
			    	->where('slot_times.id',$time_details[$i]->plan_st_time_id)
			    	->value('slot_times.time');

			    $end_time=DB::table('slot_times')
			    	->join('personal_training_plan_schedules','personal_training_plan_schedules.plan_end_time_id','slot_times.id')	
			    	->where('plan_date',$request->pt_date)
			    	->where('slot_times.id',$time_details[$i]->plan_end_time_id)
			    	->value('slot_times.time');

			    $time->id = $time_details[$i]->id;
			    $time->all_time = date('h:i A', strtotime($st_time))." to ".date('h:i A', strtotime($end_time));
			    array_push($arr, $time);
			    Log::debug ( " ::time:: ". print_r ($time, true));			    			   
			}

			if(count($time_details)==0)
	      	{
	        	return response()->json(['status' => false, 'message' => 'No data found!'], 200);
	      	}
	      	else
	      	{
	      		return response()->json(['status' => true, 'time' => $arr], 200);
	      	}

		}
		catch (Exception $e) 
	    {
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }

	}

	// Personal Training booking submit with respect to trainer //
	public function personal_training_booking(Request $request)
	{
		DB::beginTransaction();
		try
		{
			$current_date=Carbon::now()->toDateString();

	    	$order_details=DB::table('order_details')
		        ->join('products','products.id','order_details.product_id')
		        ->join('training_type','training_type.id','products.training_type_id')
		        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
		        ->where('order_details.status',1)
		        ->where('training_type.id',1)
		        ->where('order_details.order_validity_date','>=',$current_date)
		        ->where('order_details.remaining_sessions','>',0)
		        ->get()->all();

		    if(count($order_details)>0)
    		{
    			$no_of_session_notunlimited=DB::table('order_details')
				    ->join('products','products.id','order_details.product_id')
				    ->join('training_type','training_type.id','products.training_type_id')
				    ->select('order_details.id as order_id','order_details.remaining_sessions as remaining_sessions')
				    ->where('order_details.customer_id',Auth::guard('api')->user()->id)
				    ->where('order_details.status',1)
				    ->where('training_type.id',1)
				    ->where('order_details.order_validity_date','>=',$current_date)
				    ->where('order_details.remaining_sessions','>',0)
				    ->orderBy('order_details.order_validity_date', 'ASC')->first();

				$pt_booking_data['personal_training_plan_shedules_id']=$request->availableTime;
			    $pt_booking_data['customer_id']=Auth::guard('api')->user()->id;
			    $pt_booking_data['order_details_id']=$no_of_session_notunlimited->order_id;

			    $check_abalible_session=DB::table('personal_training_booking')->where('personal_training_plan_shedules_id',$request->availableTime)->get()->all();

			    if(count($check_abalible_session)==0)
			    {
			      $pt_booking_insert=DB::table('personal_training_booking')->insert($pt_booking_data);

			      $decrease_remaining_session=DB::table('order_details')
				      ->where('id',$no_of_session_notunlimited->order_id)
				      ->where('remaining_sessions','>',0)
				      ->decrement('remaining_sessions',1);

			      $shedule_id=$request->availableTime;
			    }
			    else
			    {
			       return response()->json(["message" => "Already booked this personal training session request, please try again","status" => false],200);
			    }

		        $time_details=DB::table('personal_training_plan_schedules')
			        ->join('slot_times','slot_times.id','personal_training_plan_schedules.plan_st_time_id')
			        ->select('slot_times.id as plan_st_time_id','slot_times.time as plan_st_time','personal_training_plan_schedules.id as schedule_id')
			        ->where('personal_training_plan_schedules.plan_date',$request->availableDate)
			        ->where('personal_training_plan_schedules.id',$request->availableTime)
				    ->whereNull('deleted_at')
				    ->get()->all();
				Log::debug ( " time_details ". print_r ($time_details, true));

				foreach($time_details as $key=>$each_time)
		        {
		          $each_time->all_time=date('h:i A', strtotime($each_time->plan_st_time));      
		        }

				$all_data['address']=$request->address;
		        $all_data['date']=$request->availableDate;
		        $all_data['time']=$each_time->all_time;

		        Log::debug ( " all_data ". print_r ($all_data, true));

		        $pt_booking_data=DB::table('personal_training_booking') 
			        ->join('personal_training_plan_schedules','personal_training_plan_schedules.id','personal_training_booking.personal_training_plan_shedules_id')
			        ->where('personal_training_booking.customer_id',Auth::guard('api')->user()->id)
			        ->first();

    			$customer_details=Customer::find($pt_booking_data->customer_id);

    			$notifydata['url'] = '/customer/mybooking';
			    $notifydata['customer_name']=Auth::guard('api')->user()->name;
			    $notifydata['customer_email']=Auth::guard('api')->user()->email;
			    $notifydata['customer_phone']=Auth::guard('api')->user()->ph_no;
			    $notifydata['status']='Boocked PTSession by Customer';
			    $notifydata['session_booked_on']=$pt_booking_data->created_at;
			    $notifydata['all_data']=$all_data;

			    $customer_details->notify(new BootcampSessionNotification($notifydata));

			    $pt_schedule=DB::table('personal_training_plan_schedules')
				    ->where('personal_training_plan_schedules.id',$request->availableTime)
				    ->first();

				$trainer=DB::table('users')->where('users.id',$pt_schedule->trainer_id)->first();
				Log::debug(" no_of_session_notunlimited ".print_r($trainer,true));

				$notifydata['url'] = '/trainer/home';
			    $notifydata['customer_name']=Auth::guard('api')->user()->name;
			    $notifydata['trainer_name']=$trainer->name;
			    $notifydata['status']='Boocked PTSession by Customer send by Trainer';
			    $notifydata['session_booked_on']=$pt_booking_data->created_at;
			    $notifydata['all_data']=$all_data;

			    $customer_details->notify(new BootcampSessionNotification($notifydata));

			    DB::commit();

			    $remaining_sessions=DB::table('order_details')
		          ->join('products','products.id','order_details.product_id')
		          ->join('training_type','training_type.id','products.training_type_id')
		          ->where('order_details.customer_id',Auth::guard('api')->user()->id)
		          ->where('order_details.status',1)
		          ->where('training_type.id',1)
		          ->where('order_details.order_validity_date','>=',$current_date)
		          ->where('order_details.remaining_sessions','>',0)
		          ->where('order_details.total_price',0)
		          ->value('order_details.remaining_sessions');
		        Log::debug ( " remaining_sessions ". print_r ($remaining_sessions, true));

			    return response()->json(["status" => true, 'flag' =>1, 'remaining_sessions' =>$remaining_sessions, 'message' => 'You have successfully sent the below Personal training session request(s)!'], 200);
	        }
	        else
	        {
	        	return response()->json(["status" => true, "flag" => 0, "message" => "You don't have any Personal Training session"], 200);
	        }
		}
		catch (Exception $e) 
	    {
	    	DB::rollback();
	    	Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	    	return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }
	}


	//// Personal Training slot booking using Date ////
	public function booking_pt_by_date(Request $request)
  	{
  		try
  		{
  			$pt_session_address=DB::table('bootcamp_plan_address')
			  ->join('bootcamp_plans','bootcamp_plans.address_id','bootcamp_plan_address.id')
			  ->select('bootcamp_plan_address.address_line1','bootcamp_plan_address.id','bootcamp_plans.address_id')
			  ->whereNull('bootcamp_plans.deleted_at')
			  ->distinct('bootcamp_plans.address_id')
			  ->first();

			$current_date=Carbon::now()->toDateString();
    		// Check customer product validity as well as available session 
    		$order_details=DB::table('order_details')
			  ->join('products','products.id','order_details.product_id')
			  ->join('training_type','training_type.id','products.training_type_id')
			  ->where('order_details.customer_id',Auth::guard('api')->user()->id)
			  ->where('order_details.status',1)
			  ->where('training_type.id',1)
			  ->where('order_details.order_validity_date','>=',$current_date)
			  ->where('order_details.remaining_sessions','>',0)
			  ->get()->all();

			if(count($order_details)>0)
	        {

				$customer_product_validity=DB::table('order_details')
			        ->join('payment_history','payment_history.id','order_details.payment_id')
			        ->where('payment_history.status','Success')
			        ->where('order_details.order_validity_date','>=',$current_date)
			        ->where('order_details.status',1)
			        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
			        ->max('order_details.order_validity_date');
			    Log::debug ( " ::Customer product validity:: ". print_r ($customer_product_validity, true));

			    if(empty($customer_product_validity))
			    {
			        $customer_product_validity='';
			    }

				// check already booked shedules
			    $alredy_booked_shedule_id=DB::table('personal_training_booking')
			    	->where('customer_id',Auth::guard('api')->user()->id)
			        ->whereNull('deleted_at')
			        ->pluck('personal_training_plan_shedules_id');

				//check already booked date
			    $alredy_booked_date=DB::table('personal_training_plan_schedules')
			        ->whereIn('id',$alredy_booked_shedule_id)
			        ->pluck('plan_date');
			    Log::debug ( " ::Alredy booked date:: ". print_r ($alredy_booked_date, true));

				// get all available date to apply
			    $date_details=DB::table('personal_training_plan_schedules')
				    ->where('plan_date','<=',$customer_product_validity)
				    ->whereNull('deleted_at')
				    ->whereNotIn('plan_date',$alredy_booked_date)
				    //->where('plan_date','>',$current_date)
				    ->select('plan_date')
				    ->distinct('plan_date')
				    ->get()->all();
			    Log::debug ( " ::Date Details:: ". print_r ($date_details, true));

			    $no_of_sessions=0;

	        	foreach($order_details as $total)
		        {
		        	$no_of_sessions=$no_of_sessions+$total->remaining_sessions; 
		      	}

		        return response()->json(['status' => true, 'flag' =>1, 'pt_session_address' =>$pt_session_address, 'no_of_sessions'=>$no_of_sessions, 'date_details' =>$date_details], 200);
	       	}
		    else
		    {
		      $no_of_session=0;
		      return response()->json(['status' => true, 'flag' =>0, 'message' => 'No data found!'], 200);
		    }
  		}
  		catch (Exception $e) 
	    {
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["status" => false, "message" => "Something went wrong!"],404);
	    }

  	}

  	// Find time with respect to date //
	public function get_pt_time_using_date(Request $request)
	{		
		$arr=[];
		try
		{
			$get_slot_times=DB::table('personal_training_booking')
				->join('personal_training_plan_schedules','personal_training_plan_schedules.id','personal_training_booking.personal_training_plan_shedules_id')
  				->pluck('personal_training_booking.personal_training_plan_shedules_id');
  			Log::debug(" Check get_slot_times ".print_r($get_slot_times,true));

  			if(count($get_slot_times))
			{
				foreach($get_slot_times as $key=>$hour) {

				}

				  $length=$key+1;
				  $upto=$length*4;

				  for($i=$length;$i<$upto;$i++)
				{
				  $get_slot_times[$i]=$get_slot_times[$i-$length]+1;

				}

				foreach($get_slot_times as $key=>$hour) {

				}

				$length=$key+1;
				$upto=$length*4;

				  for($i=$length;$i<$upto;$i++)
				{
				  $get_slot_times[$i]=$get_slot_times[$i-$length]-1;

				}

			}
			Log::debug("get_slot_times ".print_r($get_slot_times,true));

			$time_details=DB::table('personal_training_plan_schedules')
		        ->where('plan_date',$request->pt_date)
		        ->whereNotIn('personal_training_plan_schedules.id',$get_slot_times)
		        ->whereNull('deleted_at')
		        ->get()->all();

		    //Log::debug ( " ::time_details:: ". print_r ($time_details, true));

		    for($i = 0;$i<count($time_details);$i++)
		    {
		    	$time=new \stdClass;
			    $st_time=DB::table('slot_times')
			    	->join('personal_training_plan_schedules','personal_training_plan_schedules.plan_st_time_id','slot_times.id')
			    	->where('plan_date',$request->pt_date)
			    	->where('slot_times.id',$time_details[$i]->plan_st_time_id)
			    	->value('slot_times.time');

			    $end_time=DB::table('slot_times')
			    	->join('personal_training_plan_schedules','personal_training_plan_schedules.plan_end_time_id','slot_times.id')	
			    	->where('plan_date',$request->pt_date)
			    	->where('slot_times.id',$time_details[$i]->plan_end_time_id)
			    	->value('slot_times.time');

			    $time->id = $time_details[$i]->id;
			    $time->all_time = date('h:i A', strtotime($st_time))." to ".date('h:i A', strtotime($end_time));
			    array_push($arr, $time);
			    Log::debug ( " ::time:: ". print_r ($time, true));			    			   
			}

			if(count($time_details)==0)
	      	{
	        	return response()->json(['status' => false, 'message' => 'No data found!'], 200);
	      	}
	      	else
	      	{
	      		return response()->json(['status' => true, 'time' => $arr], 200);
	      	}

		}
		catch (Exception $e) 
	    {
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }

	}

	// Find trainer with respect to time //
	public function get_pt_trainer_using_time(Request $request)
	{
		try
		{
			$all_pt_trainer=DB::table('personal_training_plan_schedules')
			  ->join('users','users.id','personal_training_plan_schedules.trainer_id')
			  ->select('users.id as trainer_id','users.name as trainer_name')
			  ->whereNull('users.deleted_at')
			  ->where('personal_training_plan_schedules.id',$request->pt_time_id)
			  ->where('is_active',1)
			  ->whereNull('personal_training_plan_schedules.deleted_at')
			  ->get()->all();


			if(count($all_pt_trainer)==0)
	      	{
	        	return response()->json(['status' => false, 'message' => 'No data found!'], 200);
	      	}
	      	else
	      	{
	      		return response()->json(['status' => true, 'all_pt_trainer' => $all_pt_trainer], 200);
	      	}

		}
		catch (Exception $e) 
	    {
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }

	}

	// Personal Training booking submit with respect to date //
	public function pt_booking_by_date(Request $request)
	{
		DB::beginTransaction();
		try
		{
			$current_date=Carbon::now()->toDateString();

	    	$order_details=DB::table('order_details')
		        ->join('products','products.id','order_details.product_id')
		        ->join('training_type','training_type.id','products.training_type_id')
		        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
		        ->where('order_details.status',1)
		        ->where('training_type.id',1)
		        ->where('order_details.order_validity_date','>=',$current_date)
		        ->where('order_details.remaining_sessions','>',0)
		        ->get()->all();

		    if(count($order_details)>0)
    		{
    			$no_of_session_notunlimited=DB::table('order_details')
				    ->join('products','products.id','order_details.product_id')
				    ->join('training_type','training_type.id','products.training_type_id')
				    ->select('order_details.id as order_id','order_details.remaining_sessions as remaining_sessions')
				    ->where('order_details.customer_id',Auth::guard('api')->user()->id)
				    ->where('order_details.status',1)
				    ->where('training_type.id',1)
				    ->where('order_details.order_validity_date','>=',$current_date)
				    ->where('order_details.remaining_sessions','>',0)
				    ->orderBy('order_details.order_validity_date', 'ASC')->first();

				$pt_booking_data['personal_training_plan_shedules_id']=$request->availableTime;
			    $pt_booking_data['customer_id']=Auth::guard('api')->user()->id;
			    $pt_booking_data['order_details_id']=$no_of_session_notunlimited->order_id;

			    $check_abalible_session=DB::table('personal_training_booking')->where('personal_training_plan_shedules_id',$request->availableTime)->get()->all();

			    if(count($check_abalible_session)==0)
			    {
			      $pt_booking_insert=DB::table('personal_training_booking')->insert($pt_booking_data);

			      $decrease_remaining_session=DB::table('order_details')
				      ->where('id',$no_of_session_notunlimited->order_id)
				      ->where('remaining_sessions','>',0)
				      ->decrement('remaining_sessions',1);

			      $shedule_id=$request->availableTime;
			    }
			    else
			    {
			       return response()->json(["message" => "Already booked this personal training session request, please try again","status" => false],200);
			    }

		        $time_details=DB::table('personal_training_plan_schedules')
			        ->join('slot_times','slot_times.id','personal_training_plan_schedules.plan_st_time_id')
			        ->select('slot_times.id as plan_st_time_id','slot_times.time as plan_st_time','personal_training_plan_schedules.id as schedule_id')
			        ->where('personal_training_plan_schedules.plan_date',$request->availableDate)
			        ->where('personal_training_plan_schedules.id',$request->availableTime)
			        ->where('personal_training_plan_schedules.trainer_id',$request->availableTrainer)
				    ->whereNull('deleted_at')
				    ->get()->all();
				Log::debug ( " time_details ". print_r ($time_details, true));

				foreach($time_details as $key=>$each_time)
		        {
		          $each_time->all_time=date('h:i A', strtotime($each_time->plan_st_time));      
		        }

				$all_data['address']=$request->address;
		        $all_data['date']=$request->availableDate;
		        $all_data['time']=$each_time->all_time;

		        Log::debug ( " all_data ". print_r ($all_data, true));

		        $pt_booking_data=DB::table('personal_training_booking') 
			        ->join('personal_training_plan_schedules','personal_training_plan_schedules.id','personal_training_booking.personal_training_plan_shedules_id')
			        ->where('personal_training_booking.customer_id',Auth::guard('api')->user()->id)
			        ->first();

    			$customer_details=Customer::find($pt_booking_data->customer_id);

    			$notifydata['url'] = '/customer/mybooking';
			    $notifydata['customer_name']=Auth::guard('api')->user()->name;
			    $notifydata['customer_email']=Auth::guard('api')->user()->email;
			    $notifydata['customer_phone']=Auth::guard('api')->user()->ph_no;
			    $notifydata['status']='Boocked PTSession by Customer';
			    $notifydata['session_booked_on']=$pt_booking_data->created_at;
			    $notifydata['all_data']=$all_data;

			    $customer_details->notify(new BootcampSessionNotification($notifydata));

			    $pt_schedule=DB::table('personal_training_plan_schedules')
				    ->where('personal_training_plan_schedules.id',$request->availableTime)
				    ->first();

				$trainer=DB::table('users')->where('users.id',$pt_schedule->trainer_id)->first();
				Log::debug(" no_of_session_notunlimited ".print_r($trainer,true));

				$notifydata['url'] = '/trainer/home';
			    $notifydata['customer_name']=Auth::guard('api')->user()->name;
			    $notifydata['trainer_name']=$trainer->name;
			    $notifydata['status']='Boocked PTSession by Customer send by Trainer';
			    $notifydata['session_booked_on']=$pt_booking_data->created_at;
			    $notifydata['all_data']=$all_data;

			    $customer_details->notify(new BootcampSessionNotification($notifydata));

			    DB::commit();

			    $remaining_sessions=DB::table('order_details')
		          ->join('products','products.id','order_details.product_id')
		          ->join('training_type','training_type.id','products.training_type_id')
		          ->where('order_details.customer_id',Auth::guard('api')->user()->id)
		          ->where('order_details.status',1)
		          ->where('training_type.id',1)
		          ->where('order_details.order_validity_date','>=',$current_date)
		          ->where('order_details.remaining_sessions','>',0)
		          ->where('order_details.total_price',0)
		          ->value('order_details.remaining_sessions');
		        Log::debug ( " remaining_sessions ". print_r ($remaining_sessions, true));

			    return response()->json(["status" => true, 'flag' =>1, 'remaining_sessions' =>$remaining_sessions, 'message' => 'You have successfully sent the below Personal training session request(s)!'], 200);
	        }
	        else
	        {
	        	return response()->json(["status" => true, "flag" => 0, "message" => "You don't have any Personal Training session"], 200);
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