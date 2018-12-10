<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Mail\Enquiry;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{

  //// For home page ////
  public function index(Request $request)
  {
    try
    {
        $slot_packages=DB::table('slots')->where('deleted_at',null)->get();
        if(count($slot_packages)==0)
        {
          return response(['status' => false, 'message' => 'No slot package found!'], 200);
        }
        else
        {
          $status=true;
          return response(compact('slot_packages','status'), 200);
        }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }


  //// For about us page ////
  public function about(Request $request)
  {
    try 
    {
        $clients=DB::table('our_client')->where('deleted_at',null)->get();
        if(count($clients)==0)
        {
          return response(['status' => false, 'message' => 'No trainer found!'], 200);
        }
        else
        {
          $status=true;
          return response(compact('clients','status'), 200);
        }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }


//// Bootcamp plan section ////
  public function bootcamp_details(Request $request)
  {
    try
    {
        $bootcamp_product_details=DB::table('products')
        ->join('training_type','products.training_type_id','training_type.id')
        ->join('payment_type','products.payment_type_id','payment_type.id')
        ->select('products.id as product_id','training_type.training_name','payment_type.payment_type_name','products.total_sessions','products.price_session_or_month','products.total_price','products.validity_value','products.validity_duration','products.contract','products.notice_period_value','products.notice_period_duration',(DB::raw('products.validity_value * products.validity_duration')),(DB::raw('products.notice_period_value * products.notice_period_duration')))
        ->where('training_type.id',2)->where('products.status',1)
        ->whereNull('products.deleted_at')
        ->orderby('products.id','DESC')->get();

        if(count($bootcamp_product_details)==0)
        {
          return response(['status' => false, 'message' => 'No Bootcamp product found!'], 200);
        }
        else
        {
          $status=true;
          return response(compact('bootcamp_product_details','status'), 200);
        }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }


  //// Exercise section ////
  public function exercise(Request $request)
  {
    try
    {
        $exercises=DB::table('exercise_details')->where('deleted_at',null)->select('title','description','image','video')->get();
        if(count($exercises)==0)
        {
          return response(['status' => false, 'message' => 'No exercise found!'], 200);
        }
        else
        {
          $status=true;
          return response(compact('exercises','status'), 200);
        }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }


  //// List of Testimonials ////
  public function cust_testimonial(Request $request)
  {
    try 
    {
        $testimonials=DB::table('testimonial')->where('deleted_at',null)->select('name','description','designation','image')->get();
        if(count($testimonials)==0)
        {
          return response(['status' => false, 'message' => 'No testimonial found!'], 200);
        }
        else
        {
          $status=true;
          return response(compact('testimonials','status'), 200);
        }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }


  //// My MOT section ////
  public function my_mot(Request $request)
  {
    try
    {
        $my_mots=DB::table('customer_mot')
        ->join('customers','customers.id','customer_mot.customer_id')
        ->join('users','users.id','customer_mot.trainer_id')
        ->select('customers.id','users.name','customer_mot.starting_weight', 'customer_mot.ending_weight','customer_mot.date','customer_mot.right_arm','customer_mot.left_arm','customer_mot.chest','customer_mot.waist','customer_mot.hips','customer_mot.right_thigh','customer_mot.left_thigh','customer_mot.right_calf','customer_mot.left_calf','customer_mot.heart_beat','customer_mot.blood_pressure','customer_mot.height')
        ->where('customer_mot.customer_id',Auth::guard('api')->user()->id)
        ->whereNull('customer_mot.deleted_at')
        ->get();

        if(count($my_mots)==0)
        {
          return response(['status' => false, 'message' => 'No trainer found!'], 200);
        }
         else
        {
          $status=true;
          return response(compact('my_mots','status'), 200);
        }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    } 

  }


  //// Booking bootcamp section ////
  public function booking_bootcamp(Request $request)
  {
    try
    {
      $current_date=Carbon::now()->toDateString();

      $bootcampaddress=DB::table('bootcamp_plan_address')
        ->join('bootcamp_plans','bootcamp_plans.address_id','bootcamp_plan_address.id')
        ->select('bootcamp_plan_address.address_line1','bootcamp_plan_address.id','bootcamp_plans.address_id')
        ->whereNull('bootcamp_plans.deleted_at')
        ->distinct('bootcamp_plans.address_id')
        ->get();
      Log::debug ( " ::Bootcamp_Address:: ". print_r ($bootcampaddress, true));

      $order_details=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$current_date)
        ->get()->all();
      Log::debug ( " ::Order_Details:: ". print_r ($order_details, true));
      if(count($order_details)>0)
      {
        $order_details=count($order_details);
      }
      else
      {
        $order_details=0;
      }

      $no_of_session_unlimited=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$current_date)
        ->where('order_details.remaining_sessions','Unlimited')
        ->get()->all();
      Log::debug ( " ::No_of_Session_Unlimited:: ". print_r ($no_of_session_unlimited, true));

      if(count($no_of_session_unlimited)>0)
      {
        $no_of_sessions='Unlimited';

        $status=true;
        return response(compact('bootcampaddress','order_details','no_of_sessions','status'), 200);
      }
      else
      {
        $no_of_sessions=0;

        $no_of_session_notunlimited=DB::table('order_details')
          ->join('products','products.id','order_details.product_id')
          ->join('training_type','training_type.id','products.training_type_id')
          ->where('order_details.customer_id',Auth::guard('api')->user()->id)
          ->where('order_details.status',1)
          ->where('training_type.id',2)
          ->where('order_details.order_validity_date','>=',$current_date)
          ->where('order_details.remaining_sessions','!=','Unlimited')
          ->get()->all();
        Log::debug ( " ::No_of_Session_not_Unlimited:: ". print_r ($no_of_session_notunlimited, true));

        foreach($no_of_session_notunlimited as $total)
        {
          $no_of_sessions=$no_of_sessions+$total->remaining_sessions;
        }

        $status=true;
        return response(compact('bootcampaddress','order_details','no_of_sessions','status'), 200);
      }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    } 
  }


  //// Get bootcamp Date ////
  public function get_bootcamp_date(Request $request)
  {
    try
    {
      $current_date=Carbon::now()->toDateString();

      $customer_product_validity=DB::table('order_details')
        ->join('payment_history','payment_history.id','order_details.payment_id')
        ->where('payment_history.status','Success')
        ->where('order_details.order_validity_date','>=',$current_date)
        ->where('order_details.status',1)
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->max('order_details.order_validity_date');
      Log::debug ( " ::Customer_Product_Validity:: ". print_r ($customer_product_validity, true));

      $alredy_booked_shedule_id=DB::table('bootcamp_booking')
        ->where('customer_id',Auth::guard('api')->user()->id)
        ->pluck('bootcamp_plan_shedules_id');
      Log::debug ( " ::Alredy_Booked_Shedule_id:: ". print_r ($alredy_booked_shedule_id, true));

      $alredy_booked_date=DB::table('bootcamp_plan_shedules')
        ->whereIn('id',$alredy_booked_shedule_id)
        ->orwhereColumn('max_allowed','no_of_uses')
        ->pluck('plan_date');
      Log::debug ( " ::Alredy_Booked_Date:: ". print_r ($alredy_booked_date, true));

      $date_details=DB::table('bootcamp_plan_shedules')
        ->where('address_id',$request->address_id)
        ->where('plan_date','<=',$customer_product_validity)
        ->whereNotIn('plan_date',$alredy_booked_date)
        ->get()->all();
      Log::debug ( " ::Date_Details:: ". print_r ($date_details, true));

      if(count($date_details)==0)
      {
        return response(['status' => false, 'message' => 'No data found!'], 200);
      }
       else
      {
        $status=true;
        return response(compact('date_details','status'), 200);
      }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    } 
  }


   //// Get bootcamp Time ////
  public function get_bootcamp_time(Request $request)
  {
    try
    {
      $time_details=DB::table('bootcamp_plan_shedules')
        ->where('plan_date',$request->bootcamp_date)
        ->get()->all();

      foreach($time_details as $key=>$each_time)
      {
        $each_time->all_time=date('h:i A', strtotime($each_time->plan_st_time))." to ".date('h:i A', strtotime($each_time->plan_end_time));
      }

      if(count($time_details)==0)
      {
        return response(['status' => false, 'message' => 'No data found!'], 200);
      }
       else
      {
        $status=true;
        return response(compact('time_details','status'), 200);
      }
    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    } 
  }


  //// Bootcamp booking section ////
  public function bootcamp_booking(Request $request)
  {
    Log::debug ( " ::bootcamp_booking:: ". print_r ($request->all(), true));
    try
    {
      $current_date=Carbon::now()->toDateString();

      $no_of_session_unlimited=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->select('order_details.id as order_id','order_details.remaining_sessions as remaining_sessions')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$current_date)
        ->where('order_details.remaining_sessions','Unlimited')
        ->orderBy('order_details.order_validity_date', 'ASC')->first();
      Log::debug ( " ::no_of_session_unlimited:: ". print_r ($no_of_session_unlimited, true));

      $no_of_session_notunlimited=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->select('order_details.id as order_id','order_details.remaining_sessions as remaining_sessions')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$current_date)
        ->where('order_details.remaining_sessions','>',0)
        ->orderBy('order_details.order_validity_date', 'ASC')->first();
      Log::debug ( " ::no_of_session_notunlimited:: ". print_r ($no_of_session_notunlimited, true));

      $bootcamp_booking_data['bootcamp_plan_shedules_id']=$request->schedule_id;
      $bootcamp_booking_data['customer_id']=Auth::guard('api')->user()->id;
      $bootcamp_booking_insert=DB::table('bootcamp_booking')->insert($bootcamp_booking_data);
      $shedule_id=$request->schedule_id;
      Log::debug ( " ::Bootcamp_Booking_Data:: ". print_r ($bootcamp_booking_data, true));

      if($no_of_session_unlimited->remaining_sessions!='Unlimited')
      { 
        $decrease_remaining_session=DB::table('order_details')->where('id',$no_of_session_notunlimited->order_id)->decrement('remaining_sessions',1);
      }
      
      $bootcamp_plan_shedules_update=DB::table('bootcamp_plan_shedules')
        ->where('id',$shedule_id)->increment('no_of_uses', 1);
      Log::debug ( " ::bootcamp_plan_shedules_update:: ". print_r ($bootcamp_plan_shedules_update, true));

      return response()->json(["status" => true, 'message' => 'You have successfully sent the bellow Bootcamp session request(s)!'], 200);

    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    } 

  }

  //// Bootcamp booking history section ////
  public function booking_history(Request $request)
  {  
    try
    {

      $now = Carbon::now()->toDateString();
      $now_month = Carbon::now()->addDays(30)->toDateString();

      $all_booking=DB::table('bootcamp_booking')
        ->join('bootcamp_plan_shedules','bootcamp_plan_shedules.id','bootcamp_booking.bootcamp_plan_shedules_id')
        ->join('bootcamp_plan_address','bootcamp_plan_address.id','bootcamp_plan_shedules.address_id')
        ->select('bootcamp_plan_shedules.plan_date','bootcamp_plan_shedules.plan_day','bootcamp_plan_shedules.plan_st_time','bootcamp_plan_shedules.plan_end_time','bootcamp_plan_address.address_line1','bootcamp_booking.created_at')
        ->where('bootcamp_booking.customer_id',Auth::guard('api')->user()->id);
        
      if($request->option=='past_booking')
      {
        $all_booking=$all_booking->where('bootcamp_plan_shedules.plan_date','<',$now )
          ->whereNull('bootcamp_booking.deleted_at');
      }
      elseif($request->option=='cancelled_booking')
      {
        $all_booking=$all_booking->whereNotNull('bootcamp_booking.deleted_at');
      }
      else
      {
        $all_booking=$all_booking->whereNull('bootcamp_booking.deleted_at')
          ->where('bootcamp_plan_shedules.plan_date','>=',$now);
      }

      $all_booking=$all_booking->orderby('bootcamp_booking.id','DESC')
        ->get()->all();

      foreach($all_booking as $each_booking)
      {
        $each_booking->plan_st_time=date('h:i A',strtotime($each_booking->plan_st_time));
        $each_booking->plan_end_time=date('h:i A',strtotime($each_booking->plan_end_time));
      }

      Log::debug ( " all_booking ". print_r ($all_booking, true)); 

      $no_of_session_unlimited=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$now)
        ->where('order_details.remaining_sessions','Unlimited')
        ->get()->all();

      if(count($no_of_session_unlimited)>0)
      {
        $no_of_sessions='Unlimited';
      }
      else
      {
        $no_of_sessions=0;
        $no_of_session_notunlimited=DB::table('order_details')
          ->join('products','products.id','order_details.product_id')
          ->join('training_type','training_type.id','products.training_type_id')
          ->where('order_details.customer_id',Auth::guard('api')->user()->id)
          ->where('order_details.status',1)
          ->where('training_type.id',2)
          ->where('order_details.order_validity_date','>=',$now)
          ->where('order_details.remaining_sessions','!=','Unlimited')
          ->get()->all();

        foreach($no_of_session_notunlimited as $total)
        {
          $no_of_sessions=$no_of_sessions+$total->remaining_sessions;
        }
      }

      $total_future_booking=DB::table('bootcamp_booking')
        ->join('bootcamp_plan_shedules','bootcamp_plan_shedules.id','bootcamp_booking.bootcamp_plan_shedules_id')
        ->join('bootcamp_plan_address','bootcamp_plan_address.id','bootcamp_plan_shedules.address_id')
        ->select('bootcamp_plan_shedules.plan_date','bootcamp_plan_shedules.plan_day','bootcamp_plan_shedules.plan_st_time','bootcamp_plan_shedules.plan_end_time','bootcamp_plan_address.address_line1','bootcamp_booking.created_at')
        ->where('bootcamp_booking.customer_id',Auth::guard('api')->user()->id)
        ->whereNull('bootcamp_booking.deleted_at')
        ->where('bootcamp_plan_shedules.plan_date','>=',$now)
        ->count();

      $total_cancelled_booking=DB::table('bootcamp_booking')
        ->join('bootcamp_plan_shedules','bootcamp_plan_shedules.id','bootcamp_booking.bootcamp_plan_shedules_id')
        ->join('bootcamp_plan_address','bootcamp_plan_address.id','bootcamp_plan_shedules.address_id')
        ->select('bootcamp_plan_shedules.plan_date','bootcamp_plan_shedules.plan_day','bootcamp_plan_shedules.plan_st_time','bootcamp_plan_shedules.plan_end_time','bootcamp_plan_address.address_line1','bootcamp_booking.created_at')
        ->where('bootcamp_booking.customer_id',Auth::guard('api')->user()->id)
        ->whereNotNull('bootcamp_booking.deleted_at')
        ->count();

      $total_past_booking=DB::table('bootcamp_booking')
        ->join('bootcamp_plan_shedules','bootcamp_plan_shedules.id','bootcamp_booking.bootcamp_plan_shedules_id')
        ->join('bootcamp_plan_address','bootcamp_plan_address.id','bootcamp_plan_shedules.address_id')
        ->select('bootcamp_plan_shedules.plan_date','bootcamp_plan_shedules.plan_day','bootcamp_plan_shedules.plan_st_time','bootcamp_plan_shedules.plan_end_time','bootcamp_plan_address.address_line1','bootcamp_booking.created_at')
        ->where('bootcamp_booking.customer_id',Auth::guard('api')->user()->id)
        ->whereNull('bootcamp_booking.deleted_at')
        ->where('bootcamp_plan_shedules.plan_date','<',$now)
        ->count();
    
      $status=true;
      return response(compact('all_booking','no_of_sessions','total_future_booking','total_cancelled_booking','total_past_booking','status'), 200);

    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    }  
  }
  
}
