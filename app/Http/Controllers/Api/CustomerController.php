<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Mail\Enquiry;
use App\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use App\Notifications\PlanPurchasedNotification;

class CustomerController extends Controller
{
  ////  Registration for new user ////
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|min:3',
      'email' => 'required|email|max:255|unique:customers',
      'ph_no' => 'required|numeric|unique:customers',
      'password' => 'required|min:6|confirmed',
    ]);

    if($validator->fails())
    {
      $errors = $validator->errors();
      return response()->json(["message" => $errors->first(),"status" => false], 200);
    }

    DB::beginTransaction();
    try
    {
      // Generate confirmation code 
      $confirmation_code = str_random(30);
      $customers = $this->create($request->all(),$confirmation_code);
      Log::debug ( " Customers ". print_r ($customers, true)); 

      // for social login 
      if($request->provider_id && $request->provider_name)
      {
        $social_account_data['provider_id']=$request->provider_id;
        $social_account_data['provider_name']=$request->provider_name;
        $social_account_data['customers_id']=$customers->id;

        $customer_data['confirmed']=1;
        $customer_data['confirmation_code']=NULL;

        $savedata=Customer::where('id',$social_account_data['customers_id'])->update($customer_data);

        $customer_social_account = DB::table('social_accounts')->insert($social_account_data);

        Mail::send('socialenquirycustomermail',['email' =>$customers->email, 'name' =>$request->name],function($message) {
            $message->to(Input::get('email'))->subject('Successfully Register');   
        });

        $this->postRegistrationPlan($customers->id);

        $token = $customers->createToken('Body By Lekan Customer App')->accessToken; 
         DB::commit();
        return response()->json(['token' => $token, 'status' => true, 'message' => 'Now you are a register user'], 200);
      }

      // for manual registration 
      if($customers && !$request->provider_id && !$request->provider_name)
      {
        Mail::send('enquirycustomermail',['email' =>$customers->email, 'confirmation_code' => $confirmation_code], function($message) { $message->to(
              Input::get('email'))->subject('Successfully Register');
        });

        $this->postRegistrationPlan($customers->id);
            
        $token = $customers->createToken('Body By Lekan Customer App')->accessToken; 
        DB::commit(); 
        return response()->json(['token' => $token, 'status' => true, 'message' => 'A verification code has been sent to your email. Please confirm to complete the registration process!'], 200);          
        }
      } 
    catch(\Exception $e)
    {
      DB::rollback();
      Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
      return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }
  // For create registration model 
  protected function create(array $data,$confirmation_code)
  {
      return Customer::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'ph_no'=>$data['ph_no'],
          'password' => Hash::make($data['password']),
          'confirmation_code' => $confirmation_code,
      ]);
  }
  // Post registration free bootcamp session package and notification mail
  protected function postRegistrationPlan($customer_id)
  {
    Log::debug(" Function ");
    $package_details=DB::table('products')
      ->join('training_type','training_type.id','products.training_type_id')
      ->join('payment_type','payment_type.id','products.payment_type_id')
      ->select('training_type.training_name as product_name','payment_type.payment_type_name as payment_type_name','products.total_sessions as total_sessions','products.id as product_id',(DB::raw('products.validity_value * products.validity_duration  as validity')),'products.total_price as total_price','products.price_session_or_month as price_session_or_month','products.validity_value as validity_value','products.validity_duration as validity_duration','products.contract as contract','products.notice_period_value as notice_period_value','products.notice_period_duration as notice_period_duration')
      ->whereNull('products.deleted_at')
      ->where('products.id',9)->first(); 

    if($package_details)
    {
      if($package_details->validity!='')
      {
        $product_validity=Carbon::now()->addDay($package_details->validity);
      }
      else
      {
        $product_validity='2099-12-30';
      }

      if($product_validity>Carbon::now()->toDateString())
        {        
          $payment_history_data['amount']=$package_details->total_price;
          $payment_history_data['status']='Success';

          $order_data['customer_id']=$customer_id;
          $order_data['product_id']=$package_details->product_id;
          $order_data['training_type']=$package_details->product_name;
          $order_data['payment_type']=$package_details->payment_type_name;
          $order_data['order_purchase_date']=Carbon::now()->toDateString();

          if($package_details->validity!='')
          {
            $order_data['order_validity_date']=Carbon::now()->addDay($package_details->validity);
          }
          else
          {
            $order_data['order_validity_date']='2099-12-30';
          }

          $order_data['payment_option']='';
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
          $order_data['free_product']=1;

          $payment_history=DB::table('payment_history')->insert($payment_history_data);

          $order_data['payment_id']=DB::getPdo()->lastInsertId();

          Log::debug ( " Order data ". print_r ($order_data, true));
          $order_history=DB::table('order_details')->insert($order_data);

          $customer_details=Customer::find($customer_id);

          $notifydata['product_name'] =$package_details->product_name;
          $notifydata['no_of_sessions'] =$package_details->total_sessions;
          $notifydata['product_validity'] =$order_data['order_validity_date'];
          $notifydata['product_purchase_date'] =$order_data['order_purchase_date'];
          $notifydata['product_amount'] =$package_details->total_price;
          $notifydata['order_id'] ='';
          $notifydata['payment_mode'] ='';
          $notifydata['url'] = '/customer/freebootcamp';
          $notifydata['customer_name']=$customer_details->name;
          $notifydata['customer_email']=$customer_details->email;
          $notifydata['customer_phone']=$customer_details->ph_no;
          $notifydata['status']='Get free bootcamp trial';

          Log::debug ( " Notify data ". print_r ($notifydata, true));
          $customer_details->notify(new PlanPurchasedNotification($notifydata));

        }
      }
  }   


  //// Login section ////
   public function login(Request $request)
  {
    try
    {
        $givenInputs = [
            'email' => $request->email,
            'password' => $request->password,
            'confirmed' =>1
        ];
        Log::debug ( " ::Customer:: ". print_r ($givenInputs, true)); 

         if (Auth()->attempt($givenInputs))
          {
            $data['name'] = Auth::user()->name;
            $data['email'] = Auth::user()->email;
            $data['ph_no'] = Auth::user()->ph_no;
            $data['address'] = Auth::user()->address;
            Log::debug ( " ::Customer:: ". print_r ($data, true));

            $token = Auth()->user()->createToken('Body By Lekan Customer App')->accessToken;
            return response()->json(['token' => $token,"status" => true, 'user'=>$data, 'message' => 'You are logged in.'], 200);
          } 
          else 
          {
            return response()->json(['message' => 'These credentials do not match our records.',  "status" => false,], 200);
          }

    }
    catch(\Exception $e)
    {
      Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
      return response()->json(["message" => "Something went wrong!", "status" => false],404);
    }
  }


  //// Forgot password section ////
  public function forgot_password(Request $request)
  { 
    try
    {
        $request->request->add(['email' => $request->email]);
        $this->validateEmail($request);

        $response = $this->broker()->sendResetLink($request->only('email'));
        Log::debug("Response ".print_r($response,true));

        if($response == Password::RESET_LINK_SENT)
          return response()->json(['status' => true, 'message' => trans($response)],200);
        else
          return response()->json(['status' => false, 'message' => trans($response)],200);
    }
    catch(\Exception $e)
    {
      Log::debug ( " Exception ". print_r ($e->getMessage(), true)); 
      return response()->json(["message" => "Something went wrong!", "status" => false],404);
    }

  }

  // Validation check for forgot password
  protected function validateEmail(Request $request)
  {
    $this->validate($request, ['email' => 'required|email']);
  }

  //Password broker function
  public function broker()
  {
      return Password::broker('customers');
  }


  //// Edit profile section ////
  public function updateprofile(Request $request)
  {
    try
    {
      $customer=[];
      $user = Auth::guard('api')->user();
      if($request->hasFile('image'))
      {
        $myimage=$request->image;
        $folder="backend/images/"; 
        $extension=$myimage->getClientOriginalExtension(); 
        $image_name=time()."_trainerimg.".$extension; 
        $upload=$myimage->move($folder,$image_name); 
        $customer['image']=$image_name; 
      }

      if($request->name!=Auth::guard('api')->user()->name)
      {
          $validator = Validator::make($request->all(), [
              'name' => 'required|min:3'
            ]);
          if($validator->fails())
          {
            $errors = $validator->errors();
            return response()->json(["message" => $errors->first(),"status" => false], 200);
          }
          $customer['name']=$request->name;
      }

      if($request->email!=Auth::guard('api')->user()->email)
      {
          $validator = Validator::make($request->all(), [
              'email' => 'required|email|unique:customers'
            ]);
          if($validator->fails())
          {
            $errors = $validator->errors();
            return response()->json(["message" => $errors->first(),"status" => false], 200);
          }
          $customer['email']=$request->email;
      }

      if($request->ph_no!=Auth::guard('api')->user()->ph_no)
      { 
          $validator = Validator::make($request->all(), [
              'ph_no' => 'required|numeric|unique:customers'
            ]);

          if($validator->fails())
          {
            $errors = $validator->errors();
            return response()->json(["message" => $errors->first(),"status" => false], 200);
          }
          $customer['ph_no']=$request->ph_no;
      }

      if($request->address!=Auth::guard('api')->user()->address)
      { 
          $validator = Validator::make($request->all(), [
              'address' => 'required'
            ]);

          if($validator->fails())
          {
            $errors = $validator->errors();
            return response()->json(["message" => $errors->first(),"status" => false], 200);
          }
          $customer['address']=$request->address;
      }

      if(count($customer)>=1)
      {
        $update=Customer::where('id',Auth::guard('api')->user()->id)->update($customer);
        $updatedData=DB::table('customers')->select('name','ph_no','email','address','image')
        ->where('id',Auth::guard('api')->user()->id)
        ->where('deleted_at',null)
        ->first();
        return response()->json(['status' => true,'updatedUser'=>$updatedData, 'message' => 'Your profile is update successfully !'], 200);
      }
      else{
        $updatedData=DB::table('customers')->select('name','ph_no','email','address','image')
        ->where('id',Auth::guard('api')->user()->id)
        ->where('deleted_at',null)
        ->first();
        return response()->json(['status' => true, 'updatedUser'=>$updatedData, 'message' => 'Nothing to change.'], 200);
      }
     
    }
    catch (Exception $e) 
    {
      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
      return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }

  
  //// Bootcamp online payment section ////
  public function bootcamp_stripe_payment(Request $request)
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
    catch ( \Exception $e) 
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
      else{
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


  //// Bootcamp bank payment section ////
  public function bootcamp_bankpayment(Request $request)
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


  //// Bootcamp purchase history section ////
  public function purchased_history(Request $request)
  {
    try
    {
        $all_purchase=DB::table('order_details')
         ->join('products','products.id','order_details.product_id')
         ->join('training_type','training_type.id','products.training_type_id')
         ->join('payment_type','payment_type.id','products.payment_type_id')
         ->join('payment_history','payment_history.id','order_details.payment_id')
         ->select('order_details.id as order_details_id','order_details.customer_id as customer_id','order_details.order_purchase_date as order_purchase_date','order_details.remaining_sessions as remaining_sessions','order_details.payment_type as payment_type','order_details.training_type as training_type','order_details.order_validity_date as order_validity_date','order_details.payment_option as payment_option','order_details.status as status','products.training_type_id as training_type_id', 'products.total_sessions as total_sessions', 'order_details.price_session_or_month as price_session_or_month','products.id as product_id','order_details.total_price as total_price','products.validity_value as validity_value','products.validity_duration as validity_duration','training_type.training_name as training_name','payment_type.payment_type_name as payment_type_name','payment_history.status as payment_status')
         ->where('order_details.customer_id',Auth::guard('api')->user()->id)
         ->whereNull('order_details.deleted_at')
         ->orderby('order_details.id','DESC')->paginate(10);

        if(isset($request->start_date) && isset($request->end_date) && !empty($request->start_date) && !empty($request->end_date))
        {
          $now = Carbon::now()->toDateString();
          $start_date=$request->start_date;
          $end_date=$request->end_date;   
          $all_purchase->whereBetween('order_details.order_purchase_date', [$start_date, $end_date]);
         }

        Log::debug(":: All purchased_history :: ".print_r($all_purchase,true));
        if(count($all_purchase)==0)
        {
          return response(['status' => false, 'message' => 'No data found!'], 200);
        }
         else
        {
         return response()->json(["status" => true, 'purchase_history'=>$all_purchase], 200);
        }
    }
    catch(\Exception $e)
    { 
      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
      return response()->json(["message" => "Something went wrong!","status" => false],404);
    }
  }
}