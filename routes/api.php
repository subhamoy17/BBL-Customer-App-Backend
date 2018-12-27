<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//// Before customer login router ////
Route::group (['prefix' => 'v1'], function () {
	
  Route::get('testapi', 'Api\FrontController@testapi');
  Route::get('home', 'Api\FrontController@index');
  Route::get('about-us', 'Api\FrontController@about');
  Route::get('pricing', 'Api\FrontController@front_pricing');
  Route::get('contact-us', 'Api\FrontController@front_contact');
  Route::get('exercise', 'Api\FrontController@exercise');
  Route::get('testimonial', 'Api\FrontController@cust_testimonial');


  //// Customer Registration controller ////
  Route::post('customer-registration','Api\CustomerController@register');


  //// After customer login router ////
  Route::post('customer-login','Api\CustomerController@login');
  Route::post('forgot_password','Api\CustomerController@forgot_password');
  Route::post('customer-updateprofile','Api\CustomerController@updateprofile');
  Route::post('customer-changepassword', 'Api\ChangePasswordController@updateAdminPassword');


  // for customer my mot section //
  Route::post('customer_my_mot', 'Api\FrontController@my_mot');


  // for bootcamp section //
  Route::post('bootcamp-stripe-payment', 'Api\CustomerController@bootcamp_stripe_payment');
  Route::post('bootcamp-bankpayment', 'Api\CustomerController@bootcamp_bankpayment');
  Route::get('purchased-history', 'Api\CustomerController@purchased_history');

  Route::get('booking-bootcamp', 'Api\FrontController@booking_bootcamp');
  Route::post('get_bootcamp_time', 'Api\FrontController@get_bootcamp_time');
  Route::post('bootcamp-booking', 'Api\FrontController@bootcamp_booking');

  Route::post('mybooking', 'Api\FrontController@booking_history');
  Route::post('bootcamp-booking-cancel-customer', 'Api\FrontController@bootcamp_booking_cancel_customer');


  //// For social login ////
  Route::get('auth/{provider}/login', 'Api\SocialLoginController@redirectToProvider');
  Route::post('social_auth', 'Api\SocialLoginController@handleProviderCallback');


  //// For bootcamp free session ////
  Route::get('free-sessions','Api\FrontController@free_sessions');
  

  //// For Personal Training ////
  Route::post('pt-stripe-payment','Api\PersonalTrainingController@pt_stripe_payment');
  Route::post('pt-bank-payment','Api\PersonalTrainingController@pt_bank_payment');
  



});

