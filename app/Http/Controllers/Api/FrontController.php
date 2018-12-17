<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Mail\Enquiry;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DateTime;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Customer;
use App\User;
use App\Notifications\SessionRequestNotification;
use App\Notifications\BootcampSessionNotification;

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
        ->whereNull('products.deleted_at')
        ->where('products.total_price','>',0)
        ->where('training_type.id',2)
        ->where('products.status',1)
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


  //// Get bootcamp address and date section ////
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
        ->first();
      Log::debug ( " ::Bootcamp_Address:: ". print_r ($bootcampaddress, true));

      $order_details=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$current_date)
        ->where(function($q) {
         $q->where('order_details.remaining_sessions','>',0)
           ->orWhere('order_details.remaining_sessions','Unlimited');
        })
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

      // get customer's product validity last end date
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
      $alredy_booked_shedule_id=DB::table('bootcamp_booking')
        ->where('customer_id',Auth::guard('api')->user()->id)
        ->whereNull('deleted_at')
        ->pluck('bootcamp_plan_shedules_id');

      //check already booked date
      $alredy_booked_date=DB::table('bootcamp_plan_shedules')
        ->whereIn('id',$alredy_booked_shedule_id)
        ->orwhereColumn('max_allowed','no_of_uses')
        ->pluck('plan_date');
      Log::debug ( " ::Alredy booked date:: ". print_r ($alredy_booked_date, true));


      // get all available date to apply
      $date_details=DB::table('bootcamp_plan_shedules')
        ->where('plan_date','<=',$customer_product_validity)
        ->whereNull('deleted_at')
        ->whereNotIn('plan_date',$alredy_booked_date)
        ->where('plan_date','>',$current_date)
        ->select('plan_date')
        ->distinct('plan_date')
        ->get()->all();
      Log::debug ( " ::Date Details:: ". print_r ($date_details, true));

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
        return response(compact('bootcampaddress','order_details','no_of_sessions','date_details','status'), 200);
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
        return response(compact('bootcampaddress','order_details','no_of_sessions','date_details','status'), 200);
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


      if(!empty($no_of_session_unlimited) && $no_of_session_unlimited->remaining_sessions!='Unlimited')
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
        ->select('bootcamp_plan_shedules.plan_date','bootcamp_plan_shedules.plan_day','bootcamp_plan_shedules.plan_st_time','bootcamp_plan_shedules.plan_end_time','bootcamp_plan_address.address_line1','bootcamp_booking.created_at','bootcamp_booking.id as booking_id','bootcamp_booking.cancelled_by as cancelled_by')
        ->where('bootcamp_booking.customer_id',Auth::guard('api')->user()->id);
        
      if($request->option=='past_booking')
      {
        $all_booking=$all_booking->where('bootcamp_plan_shedules.plan_date','<',$now )
          ->whereNull('bootcamp_booking.deleted_at');
      }
      elseif($request->option=='declined_booking')
      {
        $all_booking=$all_booking->whereNotNull('bootcamp_booking.deleted_at')
          ->where('bootcamp_booking.cancelled_by',0);
      }
      elseif($request->option=='cancelled_booking')
      {
        $all_booking=$all_booking->whereNotNull('bootcamp_booking.deleted_at')
          ->where('bootcamp_booking.cancelled_by','>',0);
      }
      else
      {
        $all_booking=$all_booking->whereNull('bootcamp_booking.deleted_at')
          ->where('bootcamp_plan_shedules.plan_date','>=',$now);
      }

      $all_booking=$all_booking->orderby('bootcamp_booking.id','DESC')
        ->get()->all();

        date_default_timezone_set('Asia/Kolkata');
        $current_time = date("Y-m-d H:i:s");

      foreach($all_booking as $each_booking)
      {
        $each_booking->plan_st_time=date('h:i A',strtotime($each_booking->plan_st_time));
        $each_booking->plan_end_time=date('h:i A',strtotime($each_booking->plan_end_time));


      $bootcamp_cancel_time=$each_booking->plan_date.' '.$each_booking->plan_st_time;
      $bootcamp_cancel_time = date("Y-m-d H:i:s", strtotime('-24 hours', strtotime($bootcamp_cancel_time)));

      if($current_time<$bootcamp_cancel_time)
      {
        $each_booking->cancel_flg=1;
      }
      else
      {
        $each_booking->cancel_flg=0;
      }

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

      $total_declined_booking=DB::table('bootcamp_booking')
        ->join('bootcamp_plan_shedules','bootcamp_plan_shedules.id','bootcamp_booking.bootcamp_plan_shedules_id')
        ->join('bootcamp_plan_address','bootcamp_plan_address.id','bootcamp_plan_shedules.address_id')
        ->select('bootcamp_plan_shedules.plan_date','bootcamp_plan_shedules.plan_day','bootcamp_plan_shedules.plan_st_time','bootcamp_plan_shedules.plan_end_time','bootcamp_plan_address.address_line1','bootcamp_booking.created_at')
        ->where('bootcamp_booking.customer_id',Auth::guard('api')->user()->id)
        ->whereNotNull('bootcamp_booking.deleted_at')
        ->where('bootcamp_booking.cancelled_by',0)
        ->count();

      $total_cancelled_booking=DB::table('bootcamp_booking')
        ->join('bootcamp_plan_shedules','bootcamp_plan_shedules.id','bootcamp_booking.bootcamp_plan_shedules_id')
        ->join('bootcamp_plan_address','bootcamp_plan_address.id','bootcamp_plan_shedules.address_id')
        ->select('bootcamp_plan_shedules.plan_date','bootcamp_plan_shedules.plan_day','bootcamp_plan_shedules.plan_st_time','bootcamp_plan_shedules.plan_end_time','bootcamp_plan_address.address_line1','bootcamp_booking.created_at')
        ->where('bootcamp_booking.customer_id',Auth::guard('api')->user()->id)
        ->whereNotNull('bootcamp_booking.deleted_at')
        ->where('bootcamp_booking.cancelled_by','>',0)
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
      return response(compact('all_booking','no_of_sessions','total_future_booking','total_declined_booking','total_cancelled_booking','total_past_booking','status'), 200);

    }
    catch(\Exception $e)
    {
        Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
        return response()->json(["message" => "Something went wrong!","status" => false],404);
    }  
  }



  //// Bootcamp booking cancelation section ////
  public function bootcamp_booking_cancel_customer(Request $request)
  {
    DB::beginTransaction();
    try
    {
      $current_date=Carbon::now()->toDateString();

      $cancelled_booking=DB::table('bootcamp_booking')->where('id',$request->id)->update(['deleted_at'=>Carbon::now(),'cancelled_by'=>1]);

      $bootcamp_schedule_id=DB::table('bootcamp_booking')->where('id',$request->id)->pluck('bootcamp_plan_shedules_id');

      $cancelled_booking_schedule=DB::table('bootcamp_plan_shedules')
        ->where('id',$bootcamp_schedule_id)->decrement('no_of_uses',1);

      $no_of_session_unlimited=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->select('order_details.id as order_id','order_details.remaining_sessions as remaining_sessions')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$current_date)
        ->where('order_details.remaining_sessions','Unlimited')
        ->orderBy('order_details.order_validity_date', 'ASC')
        ->first();

      $no_of_session_notunlimited=DB::table('order_details')
        ->join('products','products.id','order_details.product_id')
        ->join('training_type','training_type.id','products.training_type_id')
        ->select('order_details.id as order_id','order_details.remaining_sessions as remaining_sessions')
        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
        ->where('order_details.status',1)
        ->where('training_type.id',2)
        ->where('order_details.order_validity_date','>=',$current_date)
        ->orderBy('order_details.order_validity_date', 'ASC')
        ->first();

      if(empty($no_of_session_unlimited))
      { 
        $increase_remaining_session=DB::table('order_details')->where('id',$no_of_session_notunlimited->order_id)->increment('remaining_sessions',1);
      }

      $booking_details=DB::table('bootcamp_booking')
        ->join('bootcamp_plan_shedules','bootcamp_plan_shedules.id','bootcamp_booking.bootcamp_plan_shedules_id')
        ->join('bootcamp_plan_address','bootcamp_plan_address.id','bootcamp_plan_shedules.address_id')
        ->select('bootcamp_booking.created_at as booked_on','bootcamp_plan_shedules.plan_date as shedule_date','bootcamp_plan_shedules.plan_st_time as plan_st_time','bootcamp_plan_shedules.plan_end_time as plan_end_time','bootcamp_plan_shedules.plan_day as plan_day','bootcamp_plan_address.address_line1')
        ->where('bootcamp_booking.id',$request->id)
        ->first();

      $client_details=Customer::find(Auth::guard('api')->user()->id);

      $notifydata['url'] = '/customer/mybooking';
      $notifydata['customer_name']=Auth::guard('api')->user()->name;
      $notifydata['customer_email']=Auth::guard('api')->user()->email;
      $notifydata['customer_phone']=Auth::guard('api')->user()->ph_no;
      $notifydata['status']='Cancelled Bootcamp Session By Customer';
      $notifydata['session_booked_on']=$booking_details->booked_on;
      $notifydata['session_booking_date']=$booking_details->shedule_date;
      $notifydata['session_booking_day']=$booking_details->plan_day;
      $notifydata['session_booking_time']=date('h:i A', strtotime($booking_details->plan_st_time)).' to '.date('h:i A', strtotime($booking_details->plan_end_time));
      $notifydata['cancelled_reason']='';
      $notifydata['schedule_address']=$booking_details->address_line1;

      $client_details->notify(new BootcampSessionNotification($notifydata));

      DB::commit();

      return response()->json(["status" => true, 'message' => 'You have successfully cancelled one session!'], 200);
    }
    catch(\Exception $e)
    {
      Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
      return response()->json(["message" => "Something went wrong!","status" => false],404);
    }  
  }
  
}
