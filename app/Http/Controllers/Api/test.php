public function booking_personal_training(Request $request)
	{
		$pt_session_address=DB::table('bootcamp_plan_address')
		  ->join('bootcamp_plans','bootcamp_plans.address_id','bootcamp_plan_address.id')
		  ->select('bootcamp_plan_address.address_line1','bootcamp_plan_address.id','bootcamp_plans.address_id')
		  ->whereNull('bootcamp_plans.deleted_at')
		  ->distinct('bootcamp_plans.address_id')
		  ->first();

		$all_pt_trainer=DB::table('personal_training_available_trainer')
		  ->join('users','users.id','personal_training_available_trainer.trainer_id')
		  ->select('users.id as trainer_id','users.name as trainer_name')
		  ->whereNull('users.deleted_at')
		  ->where('is_active',1)
		  ->get()->all();

		try
  		{
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
			    ->where('plan_date','>',$current_date)
			    ->where('trainer_id',$request->trainer_id)
			    ->select('plan_date')
			    ->distinct('plan_date')
			    ->get()->all();
		    Log::debug ( " ::Date Details:: ". print_r ($date_details, true));

		    $no_of_session_unlimited=DB::table('order_details')
		      ->join('products','products.id','order_details.product_id')
		      ->join('training_type','training_type.id','products.training_type_id')
		      ->where('order_details.customer_id',Auth::guard('api')->user()->id)
		      ->where('order_details.status',1)
		      ->where('training_type.id',1)
		      ->where('order_details.order_validity_date','>=',$current_date)
		      ->where('order_details.remaining_sessions','Unlimited')
		      ->get()->all();
		    Log::debug ( " ::No_of_Session_Unlimited:: ". print_r ($no_of_session_unlimited, true));

		    if(count($no_of_session_unlimited)>0)
		    {
		        $no_of_sessions='Unlimited';
		        return response()->json(["status" => true, 'flag' =>1, 'pt_session_address'=>$pt_session_address, 'all_pt_trainer'=>$all_pt_trainer, "date_details" =>$date_details],200);
		    }

		    else
		    {
		        $no_of_sessions=0;
		        $flag=1;
		        $no_of_session_notunlimited=DB::table('order_details')
			        ->join('products','products.id','order_details.product_id')
			        ->join('training_type','training_type.id','products.training_type_id')
			        ->where('order_details.customer_id',Auth::guard('api')->user()->id)
			        ->where('order_details.status',1)
			        ->where('training_type.id',1)
			        ->where('order_details.order_validity_date','>=',$current_date)
			        ->where('order_details.remaining_sessions','!=','Unlimited')
			        ->get()->all();
		        Log::debug ( " ::No_of_Session_not_Unlimited:: ". print_r ($no_of_session_notunlimited, true));

		        foreach($no_of_session_notunlimited as $total)
		        {
		          $no_of_sessions=$no_of_sessions+$total->remaining_sessions;
		        }

		        if($no_of_sessions>0)
		        {
		        	return response()->json(['status' => true, 'flag' => 1, 'pt_session_address'=>$pt_session_address,'all_pt_trainer'=>$all_pt_trainer, "date_details" =>$date_details], 200);
		        }
			    else
			    {
			      $no_of_sessions=0;
			      return response()->json(['status' => true, 'flag' => 0], 200);
			    }
			}
		}
		catch (Exception $e) 
	    {
	      Log::debug ( " Exception ". print_r ($e->getMessage(), true));
	      return response()->json(["message" => "Something went wrong!","status" => false],404);
	    }
	}