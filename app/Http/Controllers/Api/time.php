// Find time with respect to date //
	public function booking_pt_time(Request $request)
	{		
		$arr=[];
		try
		{
			$time_details=DB::table('personal_training_plan_schedules')
		        ->where('plan_date',$request->pt_date)
		        ->whereNull('deleted_at')
		        ->get()->all();

		    Log::debug ( " ::time_details:: ". print_r ($time_details, true));

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