<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Home

Route::get('/', 'OverviewController@index')->name('homepage');

// Authentication
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');

Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// Games
Route::get('bestsellers', 'GameController@showBestSellers')->name('bestsellers');
Route::get('comingsoon', 'GameController@showComingSoon')->name('comingsoon');
Route::get('all', 'GameController@showAll')->name('viewallgames');

// Product Information
Route::get('details/{game_id}', 'GameController@index')->name('game');

// Categories
Route::get('categories', 'CategoryController@index')->name('categories');
Route::get('category/{categoryid}', 'CategoryController@showCategoryGames')->name('gamecategories');

// Shopping Cart
Route::get('cart', 'ShoppingCartController@index')->name('shoppingcart'); //View Contents
Route::post('addtocart', 'ShoppingCartController@store')->name('addtocart'); //Store Cart Contents (Authenticated Only)
Route::delete('removefromcart', 'ShoppingCartController@destroy')->name('removefromcart'); //Removes Cart Item (Authenticated Only)
Route::get('addToCartGuest/{gameid}', 'ShoppingCartController@addToCartGuest')->name('addToCartGuest'); //Store Cart Contents (Guest Only)
Route::get('removeFromCart/{gameid}', 'ShoppingCartController@removeFromCartGuest')->name('removeFromCartGuest'); //Removes Cart Item (Guest Only)
Route::get('guestCheckout', 'ShoppingCartController@guestItemstoCookie')->name('guestCheckout'); //Adds session cart items to a cookie in order to store this values temporarily

//Checkout
Route::get('checkout', 'OrderController@showPaymentGateway')->name('checkout');
Route::post('finalize', 'OrderController@finalizeOrderPaypal')->name('finalizePayment');

// User Dashboard
Route::get('profile', 'UserController@showProfilePage')->name('userprofile'); //Account Details
Route::get('orders', 'UserController@showOrders')->name('userorders'); //Orders
Route::get('wishlist', 'UserController@showWishlist')->name('userwishlist'); //Wishlist

// Search
Route::get('search', 'GameController@search')->name('search');

//Static Pages
Route::get('about-us', 'StaticController@showAboutPage')->name('aboutpage'); //AboutUs
Route::get('faq', 'StaticController@showFAQPage')->name('faqpage'); //FAQ
Route::get('faq/account', 'StaticController@showFAQAccount')->name('faqaccount');//FAQ Account
Route::get('faq/games', 'StaticController@showFAQGames')->name('faqgames');//FAQ Games
Route::get('contacts', 'StaticController@showContactUsPage')->name('contactuspage')->middleware('auth'); //ContactUS
Route::post('submitTicket', 'TicketController@storeTicket')->name('submitTicket');//Submits User Ticket on ContactUS page
                                                                                  //TODO FINALIZE TICKET SYSTEM
Route::get('purchasesuccess', 'StaticController@showSuccess')->name('successpage');

//Wishlist
Route::post('addtowishlist', 'WishlistController@store')->name('addtowishlist');
Route::delete('removefromwishlist', 'WishlistController@destroy')->name('removefromwishlist');

//Reviews
Route::post('publishreview', 'ReviewController@store')->name('userpublishreview');
Route::delete('removereview', 'ReviewController@destroy')->name('userremovereview');
Route::put('editreview', 'ReviewController@update')->name('userupdatereview');

//TODO TRANSFORMAR AS ROTAS DE AJAX NUMA API REST
