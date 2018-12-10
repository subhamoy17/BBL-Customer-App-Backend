<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Bodybylekan | Verify Your Account</title>
  <!--Font awesome cdn-->

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
  <link rel="shortcut icon" href="{{url('images/icon-fav.png')}}" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css?family=Black+Han+Sans|Open+Sans:400,600,700|Righteous" rel="stylesheet">
  <script defer src="https://use.fontawesome.com/releases/v5.5.0/js/all.js" integrity="sha384-GqVMZRt5Gn7tB9D9q7ONtcp4gtHIUEW/yG7h98J7IpE3kpi+srfFyyB/04OV6pG0" crossorigin="anonymous"></script>

</head>
<body style="margin: 0; padding: 0;">
  <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #eae6e6;">
    <tr>
      <td align="left" bgcolor="#fb5b21" style="padding: 20px; width: 45%;">
        <img src="{{url('frontend/images/logo2.png')}}" alt="Logo" width="90%" style="display: block;" />
      </td>
    <td align="right" bgcolor="#fb5b21" style="padding: 20px; width: 55%;">
        <h3 style="color:#fff; font-family: 'Open Sans', sans-serif;"><i class="far fa-handshake" style="font-size:22px;"></i> Join With Us</h3>
      </td>
    </tr>
    <tr>
      <td colspan="2" bgcolor="#ffffff" style="padding: 10px 30px 10px 30px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td style="text-align: center;">
              <h2 style="font-family: 'Black Han Sans', sans-serif; font-size:24px;color: #5c5a63;letter-spacing: 1px;"><span style="display: block; color: #ccc; font-size: 100px;"><i class="far fa-envelope"></i></span>Verify Your Email Address</h2>
            </td>
          </tr>
          <tr>
            <td style="padding: 0 0 10px 0; text-align: center;">

              <p style="font-family: 'Open Sans', sans-serif;font-weight: 600;line-height: 24px;font-size: 16px;color: #565050; padding-bottom: 30px; margin-bottom: 30px; margin-top: 0;  border-bottom: 1px dashed #dedede;">Thank you for creating your account with Bodybylekan. Please follow the link below to verify your email address.</p>
    
              <a href="{{URL::to('http://192.168.1.201:5200/register/verify/'.$confirmation_code)}}" style="text-decoration: none;font-size: 18px;font-family: 'Open Sans', sans-serif;background: #fb5b21;padding: 18px;display: inline-block;color: #fff;border-radius: 5px;font-weight: 600; text-transform: capitalize;"><i class="fas fa-check" style="margin-right:3px;"></i> Verify your account</a>
    

            </td>
          </tr>
        </table>
      </td>
    </tr>
    <td colspan="2" bgcolor="#5C342C" style="padding: 7px 30px 7px 30px; background: url('{{url('frontend/images/ghg.png')}}') no-repeat top center; background-size: cover; width: 100%; height: 146px;">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td width="50%" style="padding: 0 0 0 0;">
            <p style="color: #fff; padding-top: 15px;font-family: 'Open Sans', sans-serif;"><i class="fa fa-copyright" aria-hidden="true"></i> Bodybylekan.com</p>
          </td>
          <td align="right">
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <a href="http://www.youtube.com/" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-youtube"></i>
                  </a>
                </td>
                <td>
                  <a href="http://www.google.com/" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-google-plus-g"></i>
                  </a>
                </td>
                <td>
                  <a href="http://www.twitter.com/" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-twitter"></i>
                  </a>
                </td>
                <td>
                  <a href="http://www.facebook.com/" style="margin-left:15px; font-size: 30px; color: #fff;">
                    <i class="fab fa-facebook-f"></i>
                  </a>
                </td>

              </tr>
            </table>
        </tr>
      </table>
    </td>
  </table>
</body>
</html>