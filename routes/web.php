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
Route::get('api/category/{categoryid}', 'CategoryController@ApiGetCategoryGames')->name('apigamecategories');

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
// AJAX Search
Route::get('api/search/{gametitle}', 'GameController@searchAJAX')->name('searchAJAX');

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

//Admin Page
Route::get('/admin/dashboard', 'UserController@showAdminDashboard')->name('admindashboard')->middleware('admin'); //Shows admin dashboard page
Route::get('/admin/orders', 'OrderController@showAllOrders')->name('adminshoworders')->middleware('admin'); //List all orders
Route::put('/admin/api', 'OrderController@updateOrderStatus')->name('adminupdateorderstate')->middleware('admin');  //Update order state
Route::get('/admin/users', 'UserController@showAllUsers')->name('adminshowusers')->middleware('admin'); //Shows all Users in pagination mode
Route::get('/admin/userinfo/', 'UserController@findUserbyUsername')->name('adminexactsearchusername')->middleware('admin'); //Exact Match Search by Exact Match Search
Route::put('/user/updatebanstatues', 'UserController@updateBanStatus')->name('adminupdateuserbanstatus')->middleware('admin'); //Updates Ban Status of the User
Route::get('/admin/form/createnewuser', 'UserController@showadminCreateUserAccount')->name('adminformcreateuseraccount')->middleware('admin');
Route::post('/admin/createnewuser', 'UserController@storeadminCreateUserAccount')->name('admincreateuseraccount')->middleware('admin'); //Admin Create User Account

//Filters
Route::get('/api/games/desc', 'GameController@showGamesHigh2Low')->name('high2lowfilter')->middleware('admin');
Route::get('/api/games/asc', 'GameController@showGamesLow2High')->name('low2highfilter')->middleware('admin');
Route::get('/api/games/discount', 'GameController@showGamesbyDiscount')->name('discountbest')->middleware('admin');
Route::get('/api/games/latestreleases', 'GameController@showGamesbyLatestRelease')->name('orderlatestreleases')->middleware('admin');
Route::get('/api/games/bestreviewed', 'GameController@showBestReviewed')->name('orderbestreviewed')->middleware('admin');
Route::get('/api/games/{startprice}', 'GameController@getGameStartingAtPrice')->name('getgamestartingatprice')->middleware('admin');
Route::get('/api/games/max/{startprice}', 'GameController@getGameBelowPrice')->name('getgamebelowprice')->middleware('admin');
Route::get('/api/games/{startprice}/{maxprice}', 'GameController@getGameBetweenPrice')->name('getgamebetweenprice')->middleware('admin');
Route::get('/api(games/', 'GameController@showAllGames')->name('getallgames')->middleware('admin');


//TODO TRANSFORMAR AS ROTAS DE AJAX NUMA API REST
