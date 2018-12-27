<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Body By Lekan</title>
  <!--Font awesome cdn-->
  <script defer src="https://use.fontawesome.com/releases/v5.5.0/js/all.js" integrity="sha384-GqVMZRt5Gn7tB9D9q7ONtcp4gtHIUEW/yG7h98J7IpE3kpi+srfFyyB/04OV6pG0" crossorigin="anonymous"></script>
  <link rel="shortcut icon" href="http://localhost:8000/images/icon-fav.png" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Black+Han+Sans|Open+Sans:400,600,700|Righteous" rel="stylesheet">

  <style>

.table-bordered
{
  border: 1px solid black;
}

</style>
</head>
<body style="margin: 0; padding: 0;">
  <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #eae6e6;">
    <tr>
      <td align="left" bgcolor="#fb5b21" style="padding: 20px; width: 40%;">
        <img src="{{asset('frontend/images/logo2.png')}}" alt="Creating Email Magic" width="85%" style="display: block;" />
      </td>
	  <td align="right" bgcolor="#fb5b21" style="padding: 20px; width: 60%;">
        <h3 style="color:#fff; font-family: 'Open Sans', sans-serif;"><i class="far fa-handshake" style="font-size:24px;"></i> Join With Us</h3>
      </td>
    </tr>
    <tr>
      <td colspan="2" bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td style="padding: 0 0 30px 0; text-align: center;">

              <p align="left"> </p>
    <p align="left">Hello {{$customer_name}},<br><br>

      @if($status=='Boocked BootcampSession by Customer' )
        Your bootcamp session booking is Successfully booked.Please see the below details.<br>
        <table style="width:100%" class="table-bordered">
  <tr>
    <th class="table-bordered">Address</th>
    <th class="table-bordered">Booked Date</th>
    <!-- <th class="table-bordered">Booking Day</th> -->
    <th class="table-bordered">Booked Time</th>
  </tr>

  <tr>
    <th class="table-bordered">{{$all_data['address']}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($all_data['date']))}}</th>
    <th class="table-bordered">{{$all_data['time']}}</th>
  </tr>
   
</table>
<br>
  <a href="{{URL::to('http://192.168.1.201:5200/customer-login')}}" style="text-decoration: none;font-size: 13px;font-family: 'Open Sans', sans-serif;background: #fb5b21;padding: 18px;display: inline-block;color: #fff;border-radius: 5px;font-weight: 600; text-transform: capitalize;"><i class="fas fa-check" style="margin-right:3px;"></i> Click to check your booked session(s)</a>

      @elseif($status=='Declined Bootcamp Session By Admin')
        Your bootcamp session declined by BBL admin due to {{$cancelled_reason}}.<br>

         <table style="width:100%" class="table-bordered">
  <tr>
    <th class="table-bordered">Address</th>
    <th class="table-bordered">Booked On</th>
    <th class="table-bordered">Booked Date</th>
    <th class="table-bordered">Booked Day</th>
    <th class="table-bordered">Time</th>
  </tr>
  <tr>
    <th class="table-bordered">{{$schedule_address}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($session_booked_on))}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($session_booking_date))}}</th>
    <th class="table-bordered">{{$session_booking_day}}</th>
    <th class="table-bordered">{{$session_booking_time}}</th>
  </tr>
</table>
<br>
<a href="{{URL::to($url)}}" style="text-decoration: none;font-size: 13px;font-family: 'Open Sans', sans-serif;background: #fb5b21;padding: 18px;display: inline-block;color: #fff;border-radius: 5px;font-weight: 600; text-transform: capitalize;"><i class="fas fa-check" style="margin-right:3px;"></i> Click to check declined session</a>

      @elseif($status=='Cancelled Bootcamp Session By Customer')
        You have successfully cancelled the below bootcamp session.<br>

        <table style="width:100%" class="table-bordered">
  <tr>
    
    <th class="table-bordered">Address</th>
    <th class="table-bordered">Booked On</th>
    <th class="table-bordered">Booked Date</th>
    <th class="table-bordered">Booked Day</th>
    <th class="table-bordered">Time</th>
  </tr>
  <tr>
    <th class="table-bordered">{{$schedule_address}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($session_booked_on))}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($session_booking_date))}}</th>
    <th class="table-bordered">{{$session_booking_day}}</th>
    <th class="table-bordered">{{$session_booking_time}}</th>
  </tr>
</table>
<br>
<a href="{{URL::to($url)}}" style="text-decoration: none;font-size: 13px;font-family: 'Open Sans', sans-serif;background: #fb5b21;padding: 18px;display: inline-block;color: #fff;border-radius: 5px;font-weight: 600; text-transform: capitalize;"><i class="fas fa-check" style="margin-right:3px;"></i> Click to check cancelled session</a>

      @elseif($status=='Cancelled Bootcamp Session By Admin')
        Your bootcamp session cancelled by BBL admin. Please see the below details of cancelled session.<br>
        <table style="width:100%" class="table-bordered">
  <tr>
    <th class="table-bordered">Address</th>
    <th class="table-bordered">Booked On</th>
    <th class="table-bordered">Booked Date</th>
    <th class="table-bordered">Booked Day</th>
    <th class="table-bordered">Time</th>
  </tr>
  <tr>
    <th class="table-bordered">{{$schedule_address}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($session_booked_on))}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($session_booking_date))}}</th>
    <th class="table-bordered">{{$session_booking_day}}</th>
    <th class="table-bordered">{{$session_booking_time}}</th>
  </tr>
</table>
<br>
<a href="{{URL::to($url)}}" style="text-decoration: none;font-size: 13px;font-family: 'Open Sans', sans-serif;background: #fb5b21;padding: 18px;display: inline-block;color: #fff;border-radius: 5px;font-weight: 600; text-transform: capitalize;"><i class="fas fa-check" style="margin-right:3px;"></i> Click to check your cancelled session</a>
      @endif
      
 


<p align="left">Regards,</p>
  <p align="left">Team BBL</p> 
    
              
    

            </td>
          </tr>
        </table>
      </td>
    </tr>
    <td colspan="2" bgcolor="#5C342C" style="padding: 7px 30px 7px 30px; background: url({{url('frontend/images/ghg.png')}}) no-repeat top center; background-size: cover; width: 100%; height: 146px;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td width="50%" style="padding: 0 0 0 0;">
            <p style="color: #fff; padding-top: 15px;font-family: 'Open Sans', sans-serif;"><i class="fa fa-copyright" aria-hidden="true"></i> Bodybylekan.com 2018</p>
          </td>
          <td align="right">
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <a href="https://www.youtube.com/channel/UCvFStHTPHjHY-_7BXA17Fug" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-youtube"></i>
                  </a>
                </td>
                <td>
                  <a href="https://www.instagram.com/lekanfitness/" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-instagram"></i>
                  </a>
                </td>
                <td>
                  <a href="https://twitter.com/bodybylekan" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-twitter"></i>
                  </a>
                </td>
                <td>
                  <a href="https://www.facebook.com/bodybylekan" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-facebook-f"></i>
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </table>
</body>
</html>