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
  Route::get('pricing', 'Api\FrontController@frontprice');
  Route::get('contact-us', 'Api\FrontController@front_contact');
  Route::get('exercise', 'Api\FrontController@exercise');
  Route::get('testimonial', 'Api\FrontController@cust_testimonial');
  Route::post('customer-registration','Api\CustomerController@register');

//// After customer login router ////
  Route::post('customer-login','Api\CustomerController@login');
  Route::post('forgot_password','Api\CustomerController@forgot_password');
  Route::post('customer-updateprofile','Api\CustomerController@updateprofile');
  Route::post('customer-changepassword', 'Api\ChangePasswordController@updateAdminPassword');

  // for customer my mot section //
  Route::post('customer_my_mot', 'Api\FrontController@my_mot');

  // for bootcamp section //
  Route::get('pricing', 'Api\FrontController@bootcamp_details');
  Route::post('bootcamp-stripe-payment', 'Api\CustomerController@bootcamp_stripe_payment');
  Route::post('bootcamp-bankpayment', 'Api\CustomerController@bootcamp_bankpayment');
  Route::get('purchased-history', 'Api\CustomerController@purchased_history');

  Route::get('booking-bootcamp', 'Api\FrontController@booking_bootcamp');
  Route::post('get_bootcamp_time', 'Api\FrontController@get_bootcamp_time');
  Route::post('bootcamp-booking', 'Api\FrontController@bootcamp_booking');
  Route::post('mybooking', 'Api\FrontController@booking_history');
  Route::post('bootcamp-booking-cancel-customer', 'Api\FrontController@bootcamp_booking_cancel_customer');

});

