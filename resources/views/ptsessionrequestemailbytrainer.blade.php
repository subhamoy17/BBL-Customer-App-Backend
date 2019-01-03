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
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <style>



</style>
</head>
<body style="margin: 0; padding: 0;">
  <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #eae6e6;">
    <tr>
      <td align="center" bgcolor="#fb5b21" style="padding: 15px; width: 35%;">
        <img src="{{asset('frontend/images/logo2.png')}}" alt="Creating Email Magic" width="35%" style="display: block;" />
      </td>
	 
    </tr>
    <tr>
      <td colspan="2" bgcolor="#ffffff" style="padding: 20px 20px 30px 20px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td style="text-align: center;">

              <p align="left"> </p>
    <p align="left">Hello {{$trainer_name}},<br><br>

      @if($status=='Boocked PTSession by Customer send by Trainer' )
      Your customer {{$customer_name}} personal training session booking is successfully booked.Please see the below details.<br>
        <table class="table table-bordered">
  <thead style="background-color:#FACFA4;">
  <tr style="font-size: 13px;">
    <th style="vertical-align:top">Address</th>
    <th style="vertical-align:top">Booked Date</th>
    <!-- <th class="table-bordered">Booking Day</th> -->
    <th style="vertical-align:top">Booked Time</th>
  </tr>
</thead>
<tbody>
   <tr>
    <th class="table-bordered">{{$all_data['address']}}</th>
    <th class="table-bordered">{{date('d F Y', strtotime($all_data['date']))}}</th>
    <th class="table-bordered">{{$all_data['time']}}</th>
  </tr>
     </tbody> 
</table>
<br>
  <a href="{{URL::to('http://192.168.1.201:5200/trainer/personal-training-plan-schedule')}}" style="text-decoration: none;font-size: 13px;font-family: 'Open Sans', sans-serif;background: #fb5b21;padding: 12px;display: inline-block;color: #fff;border-radius: 5px;font-weight: 600; text-transform: capitalize;"><i class="fas fa-check" style="margin-right:3px;"></i> Click to check</a>
 @endif

  @if($status=='Cancelled PT Session By Customer send by Trainer' )
      Your customer {{$customer_name}} cancel his personal training session.Please see the below details.<br>
        <table class="table table-bordered">
  <thead style="background-color:#FACFA4;">
  <tr style="font-size: 13px;">
     <th style="vertical-align:top;">Address</th>
    <th style="vertical-align:top;">Booked On</th>
    <th style="vertical-align:top;">Booked Date</th>
    <th style="vertical-align:top;">Booked Day</th>
    <th style="vertical-align:top;">Time</th>
  </tr>
</thead>
<tbody>
   <tr style="font-size: 12px;">
    <td>{{$schedule_address}}</td>
    <td>{{date('d F Y', strtotime($session_booked_on))}}</td>
    <td>{{date('d F Y', strtotime($session_booking_date))}}</td>
    <td>{{$session_booking_day}}</td>
    <td>{{$session_booking_time}}</td>
  </tr>
    
     </tbody> 
</table>
<br>
  <a href="{{URL::to($url)}}" style="text-decoration: none;font-size: 13px;font-family: 'Open Sans', sans-serif;background: #fb5b21;padding: 12px;display: inline-block;color: #fff;border-radius: 5px;font-weight: 600; text-transform: capitalize;"><i class="fas fa-check" style="margin-right:3px;"></i> Click to check</a>
 @endif


<p align="left">Regards,</p>
  <p align="left">Team BBL</p> 
    
              
    

            </td>
          </tr>
        </table>
      </td>
    </tr>
    <td colspan="2" bgcolor="#5C342C" style="padding: 7px 10px 7px 15px; background: url({{url('frontend/images/ghg.png')}}) no-repeat top center; background-size: cover; width: 100%; height: 60px;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td width="50%" style="margin-top: 40px;">
            <p style="color: #fff; margin-top: 40px;font-family: 'Open Sans', sans-serif;"><i class="fa fa-copyright" aria-hidden="true"></i> Bodybylekan.com <?php echo date("Y"); ?></p>
          </td>
          <td align="right">
            <table border="0" cellpadding="0" cellspacing="0" style="margin-top: 22px;">
              <tr>
                <td>
                  <a href="https://www.youtube.com/channel/UCvFStHTPHjHY-_7BXA17Fug" style="margin-left:15px; font-size: 20px; color: #fff;">
                    <i class="fab fa-youtube"></i>
                  </a>
                </td>
                <td>
                  <a href="https://www.instagram.com/lekanfitness/" style="margin-left:15px; font-size: 20px; color: #fff;">
                    <i class="fab fa-instagram"></i>
                  </a>
                </td>
                <td>
                  <a href="https://twitter.com/bodybylekan" style="margin-left:15px; font-size: 20px; color: #fff;">
                    <i class="fab fa-twitter"></i>
                  </a>
                </td>
                <td>
                  <a href="https://www.facebook.com/bodybylekan" style="margin-left:15px; font-size: 20px; color: #fff;">
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