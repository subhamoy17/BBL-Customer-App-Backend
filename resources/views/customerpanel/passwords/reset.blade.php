<!DOCTYPE html>
<html lang="zxx">

<head>
    <title>Gym Trainer a Sports Category Bootstrap Responsive Website Template | Home :: gylayouts</title>
    <!-- custom-theme -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- //custom-theme -->
            <title>{{ config('app.customerpaneltitle') }}</title>
            
            <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{url('frontend/css/bootstrap.css')}}" rel="stylesheet" type="text/css" media="all" />
    <!-- Owl-carousel-CSS -->
    
    <!-- Testimonials-slider-css-files -->
    <link rel="stylesheet" href="{{url('frontend/css/owl.carousel.css')}}" type="text/css" media="all">

    <link href="{{url('frontend/css/owl.theme.default.css')}}" rel="stylesheet">
    <!-- //Testimonials-slider-css-files -->
    <!--Main Menu-->
    <link href="{{url('frontend/css/stellarnav.min.css')}}" rel="stylesheet">

    <link href="{{url('frontend/css/style.css')}}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{url('frontend/css/responsive.css')}}" rel="stylesheet" type="text/css" media="all" />
    <!-- font-awesome-icons -->
    <link href=" {{url('frontend/css/font-awesome.css')}}" rel="stylesheet">
    <!-- //font-awesome-icons -->
    <link href="//fonts.googleapis.com/css?family=Raleway:400,500,600,700,800" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,400,400i,500,500i,600,600i,700,700i,800" rel="stylesheet">
</head>
<body class="lg-body">
    <div class="whole-wrp"></div>
    <div class="logo-m"><img src="{{asset('frontend/images/logo.png')}}"></div>
<section class="login-section">
            <div class="container-fluid">
                <div class="login-wrapper" style="display: block;">
                   <form method="POST" action="{{ route('customerpanel.password.request') }}" id="pwreset">
                        @csrf
                         <input type="hidden" name="token" value="{{ $token }}">
                        <div class="log-box-header">
                            <h3>Reset Password</h3>
                        </div>
                        <div class="input-box">
                                     
                            <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"placeholder="Email" name="email" value="{{ $email ?? old('email') }}" autofocus>
                            @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                                    <br>
                             <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="Password"  name="password">
                              @if ($errors->has('password'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                                <br>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password">
                            <button class="lg-in" type="submit">{{ __('Reset Password') }}</button>
                        </div>
                    </form>
                </div>
            </div> 
    </section>


    <script type="text/javascript" src="{{url('frontend/js/jquery-2.1.4.min.js')}}"></script>
    <!-- //js -->

    <!-- Slider script -->
    <script src="{{url('frontend/js/responsiveslides.min.js')}}"></script>

    <script>
        // You can also use "$(window).load(function() {"
        $(function () {
            $("#slider").responsiveSlides({
                auto: true,
                nav: true,
                manualControls: '#slider3-pager',
            });
        });
    </script>
    <script type="text/javascript" src="{{url('frontend/js/bootstrap-3.1.1.min.js')}}"></script>

    <!-- for testimonials slider-js-file-->
            <script src="{{url('frontend/js/owl.carousel.js')}}"></script>

            <script src="{{asset('backend/assets/js/jquery.validate.min.js')}}"></script>

    <script>
        $(document).ready(function() { 
        $('#price-slider').owlCarousel({
            loop:true,
            margin:30,
            nav:true,
            items: 3,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:3
                },
                1000:{
                    items:3
                }
            }
        })
        }); 
</script>


<script>
$(document).ready(function() { 
        $('#team-slider').owlCarousel({
            loop:true,
            autoplay: true,
            margin:30,
            nav:true,
            items: 4,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:3
                },
                1000:{
                    items:4
                }
            }
        })
});
    </script>
<!-- for testimonials slider-js-script-->
<!-- stats -->
    <script src="{{url('frontend/js/jquery.waypoints.min.js')}}"></script>


    <script src="{{url('frontend/js/jquery.countup.js')}}"></script>

    <script>
        $('.counter').countUp();
    </script>
    <!-- //stats -->
    <script src="{{url('frontend/js/stellarnav.min.js')}}"></script>
    

    <script>
        $(document).ready(function(){
            jQuery('#main-nav').stellarNav();
        });  
    </script>
    <script>
        window.onscroll = function() {myFunction()};
        var header = document.getElementById("myHeader");
        var sticky = header.offsetTop;
        function myFunction() {
            if (window.pageYOffset >= sticky) {
                header.classList.add("sticky");
            } else {
                header.classList.remove("sticky");
            }
        }
    </script>
    
    <script>
        /*Scroll to top when arrow up clicked BEGIN*/
$(window).scroll(function() {
    var height = $(window).scrollTop();
    if (height > 100) {
        $('#back2Top').fadeIn();
    } else {
        $('#back2Top').fadeOut();
    }
});
$(document).ready(function() {
    $("#back2Top").click(function(event) {
        event.preventDefault();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });

});
 /*Scroll to top when arrow up clicked END*/
    </script>
<script>
        $(document).ready(function(){
            $(".fa-search").click(function(){
                    $(".fa-search").hide();
                    $(".fa-times").show();
                    $(".srch-box").show();
                });

                $(".fa-times").click(function(){
                    $(".fa-times").hide();
                    $(".fa-search").show();
                    $(".srch-box").hide();
                });
        });  
    </script>


    <script src="{{url('frontend/js/accotab.js')}}"></script>
    <script>
         $(document).ready(function(){
    $(".cm-cls").click(function(){
        $(".login-wrapper").fadeToggle("slow", "linear");
        $(".login-wrapper").hide("slow", "linear");
        $(".reg-wrapper").fadeToggle("slow", "linear");
         $(".reg-wrapper").show("slow", "linear");
        
    });
        $(".cmr-cls").click(function(){
        $(".reg-wrapper").fadeToggle("slow", "linear");
        $(".reg-wrapper").hide("slow", "linear");
        $(".login-wrapper").fadeToggle("slow", "linear");
         $(".login-wrapper").show("slow", "linear");
        
    }); 
         });
    </script>
        <script src="{{url('frontend/js/accotab.js')}}"></script>
    <!--Fontawesome script-->
    <!--<script defer src="https://use.fontawesome.com/releases/v5.1.0/js/all.js" integrity="sha384-3LK/3kTpDE/Pkp8gTNp2gR/2gOiwQ6QaO7Td0zV76UFJVhqLl4Vl3KL1We6q6wR9" crossorigin="anonymous"></script>-->
<script type="text/javascript">
    /* validation of email,password and confirmation password for forgote password */
$(document).ready(function() {
$('#pwreset').validate({  
  rules: {
    "email": {
      required: true,
      email: true
    },
    "password": {
      required: true,
      minlength: 6
    },
    "password_confirmation": {
        required: true,
        minlength: 6,
        equalTo:"#password"
    }
  },
  messages: {
    "email":{
    email: 'Enter a valid email',
    required:'Please Enter your email'
    },
    "password":{
    minlength: 'Password must be at least 6 characters long',
    required:'Please Enter your password'
    },
    "password_confirmation":{
        minlength: 'Confirm Password must be at least 6 characters long',
        required:'Please Enter Confirm password',
        equalTo:"Please enter confirm password same as password"
    }
  }
});
});
</script>

</body>

</html>




