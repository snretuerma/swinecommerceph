    <?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/',['as' => 'index_path', function () {
    return view('home');
}])->middleware('guest');

// Test for styling the email verification. It's so hard!
// Route::get('/sample', function(){
//     $data = [
//         'email' => 'jonb@gmail.com',
//         'verCode' => 'kasjSTG43',
//         'type' => 'sent',
//     ];
//     return view('emails.verification', $data);
// });

Route::group(['middleware' => ['web']], function () {

    /**
     * Authentication and Registration Routes
     */
    // Normal Authentication Routes
    Auth::routes();

    // Override default POST logout to GET logout
    Route::get('/logout', 'Auth\LoginController@logout');

    // Third-party Authentication Routes
    Route::get('login/{provider}',['as' => 'provider.redirect', 'uses' => 'Auth\LoginController@redirectToProvider']);
    Route::get('login/{provider}/callback',['as' => 'provider.handle', 'uses' => 'Auth\LoginController@handleProviderCallback']);

    // Email Verification for Authentication
    Route::get('login/redirect/email/{email}/ver-code/{verCode}', ['as' => 'verCode.send', 'uses' => 'Auth\LoginController@verifyCode']);
    Route::get('login/resend/email/{email}/ver-code/{verCode}', ['as' => 'verCode.resend', 'uses' => 'Auth\LoginController@resendCode']);

    // Serve resized image
    Route::get('images/product/{size}/{filename}', ['as' => 'serveImage', 'uses' => 'ServeResizedImageController@serveAppropriateImage']);

    /**
    * User Routes according to roles
    */
    // General controller of the routing according to roles
    Route::get('/home',['as' => 'home_path', 'uses' => 'UserController@index']);

    // Breeder
    Route::group(['prefix' => 'breeder'], function(){

    	Route::get('home',['as' => 'breeder_path', 'uses' => 'BreederController@index']);

        // profile-related
    	Route::get('edit-profile',['as' => 'breeder.edit', 'uses' => 'BreederController@editProfile']);
    	Route::post('edit-profile',['as' => 'breeder.store', 'uses' => 'BreederController@storeProfile']);
        Route::put('edit-profile/personal/edit',['as' => 'breeder.updatePersonal', 'uses' => 'BreederController@updatePersonal']);
        Route::post('edit-profile/farm/add',['as' => 'breeder.addFarm', 'uses' => 'BreederController@addFarm']);
        Route::put('edit-profile/farm/edit',['as' => 'breeder.updateFarm', 'uses' => 'BreederController@updateFarm']);
        Route::delete('edit-profile/farm/delete',['as' => 'breeder.deleteFarm', 'uses' => 'BreederController@deleteFarm']);
        Route::patch('edit-profile/change-password',['as' => 'breeder.changePassword', 'uses' => 'BreederController@changePassword']);
        Route::post('edit-profile/logo-upload',['as' => 'breeder.logoUpload', 'uses' => 'BreederController@uploadLogo']);
        Route::delete('edit-profile/logo-upload',['as' => 'breeder.logoDelete', 'uses' => 'BreederController@deleteLogo']);
        Route::patch('edit-profile/logo-upload',['as' => 'breeder.setLogo', 'uses' => 'BreederController@setLogo']);

        // product-related
        Route::get('products',['as' => 'products', 'uses' => 'ProductController@showProducts']);
        Route::post('products',['as' => 'products.store', 'uses' => 'ProductController@storeProduct']);
        Route::put('products',['as' => 'products.update', 'uses' => 'ProductController@updateProduct']);
        Route::get('products/view/{product}',['as' => 'products.bViewDetail', 'uses' => 'ProductController@breederViewProductDetail']);
        Route::post('products/manage-selected',['as' => 'products.updateSelected', 'uses' => 'ProductController@updateSelected']);
        Route::delete('products/manage-selected',['as' => 'products.deleteSelected', 'uses' => 'ProductController@deleteSelected']);
        Route::get('products/product-summary',['as' => 'products.summary', 'uses' => 'ProductController@productSummary']);
        Route::post('products/set-primary-picture',['as' => 'products.setPrimaryPicture', 'uses' => 'ProductController@setPrimaryPicture']);
        Route::post('products/display-product',['as' => 'products.display', 'uses' => 'ProductController@displayProduct']);
        Route::post('products/media/upload',['as' => 'products.mediaUpload', 'uses' => 'ProductController@uploadMedia']);
        Route::delete('products/media/delete',['as' => 'products.mediaDelete', 'uses' => 'ProductController@deleteMedium']);

        // dashboard-related
        Route::get('dashboard',['as' => 'dashboard', 'uses' => 'DashboardController@showDashboard']);
        Route::get('dashboard/product-status',['as' => 'dashboard.productStatus', 'uses' => 'DashboardController@showProductStatus']);
        Route::get('dashboard/product-status/retrieve-product-requests',['as' => 'dashboard.productRequests', 'uses' => 'DashboardController@retrieveProductRequests']);
        Route::get('dashboard/sold-products',['as' => 'dashboard.soldProducts', 'uses' => 'DashboardController@retrieveSoldProducts']);
        Route::get('dashboard/reviews-and-ratings',['as' => 'dashboard.reviews', 'uses' => 'DashboardController@showReviewsAndRatings']);
        Route::patch('dashboard/product-status/update-status',['as' => 'dashboard.reserveProduct', 'uses' => 'DashboardController@updateProductStatus']);

        // notification-related
        Route::get('notifications',['as' => 'bNotifs', 'uses' => 'BreederController@showNotificationsPage']);
        Route::get('notifications/get',['as' => 'bNotifs.get', 'uses' => 'BreederController@getNotifications']);
        Route::get('notifications/count',['as' => 'bNotifs.count', 'uses' => 'BreederController@getNotificationsCount']);
        Route::patch('notifications/seen',['as' => 'bNotifs.seen', 'uses' => 'BreederController@seeNotification']);

        //message-related
        Route::get('messages', ['as' => 'breeder.messages', 'uses'=> 'MessageController@getMessages']);
        Route::get('messages/countUnread', ['as' => 'messages.countUnread', 'uses'=> 'MessageController@countUnread']);
        Route::get('messages/{customer}', ['as' => 'messages.messages', 'uses'=> 'MessageController@getMessages']);

        Route::get('customers', ['as' => 'customers', 'uses'=> 'BreederController@viewCustomers']);
        Route::post('customers', ['as' => 'customers', 'uses'=> 'BreederController@viewCustomersChange']);

        Route::get('test', ['as' => 'test', 'uses'=> 'BreederController@test']);
    });


    // Customer
    Route::group(['prefix' => 'customer'], function(){

    	Route::get('home',['as' => 'customer_path', 'uses' => 'CustomerController@index']);

        // profile-related
      	Route::get('edit-profile',['as' => 'customer.edit', 'uses' => 'CustomerController@editProfile']);
      	Route::post('edit-profile',['as' => 'customer.store', 'uses' => 'CustomerController@storeProfile']);
      	Route::put('edit-profile/personal/edit',['as' => 'customer.updatePersonal', 'uses' => 'CustomerController@updatePersonal']);
        Route::post('edit-profile/farm/add',['as' => 'customer.addFarm', 'uses' => 'CustomerController@addFarm']);
        Route::put('edit-profile/farm/edit',['as' => 'customer.updateFarm', 'uses' => 'CustomerController@updateFarm']);
        Route::delete('edit-profile/farm/delete',['as' => 'customer.deleteFarm', 'uses' => 'CustomerController@deleteFarm']);
        Route::patch('edit-profile/change-password',['as' => 'customer.changePassword', 'uses' => 'CustomerController@changePassword']);

        // product-related
        Route::get('view-products',['as' => 'products.view', 'uses' => 'ProductController@viewProducts']);
        Route::get('view-products/{product}',['as' => 'products.cViewDetail', 'uses' => 'ProductController@customerViewProductDetail']);

        // swinecart-related
        Route::get('swine-cart',['as' => 'cart.items', 'uses' => 'SwineCartController@getSwineCartItems']);
        Route::get('swine-cart/transaction-history',['as' => 'cart.history', 'uses' => 'SwineCartController@getTransactionHistory']);
        Route::post('swine-cart/add',['as' => 'cart.add', 'uses' => 'SwineCartController@addToSwineCart']);
        Route::patch('swine-cart/request',['as' => 'cart.request', 'uses' => 'SwineCartController@requestSwineCartItem']);
        Route::delete('swine-cart/delete',['as' => 'cart.delete', 'uses' => 'SwineCartController@deleteFromSwineCart']);
        Route::get('swine-cart/quantity',['as' => 'cart.quantity', 'uses' => 'SwineCartController@getSwineCartQuantity']);
        Route::post('swine-cart/rate', ['as' => 'rate.breeder', 'uses' => 'SwineCartController@rateBreeder']);
        Route::get('view-swine-cart',['as'=> 'view.cart', 'uses' => 'SwineCartController@getSwineCartItems']);

        // notification-related
        Route::get('notifications',['as' => 'cNotifs', 'uses' => 'CustomerController@showNotificationsPage']);
        Route::get('notifications/get',['as' => 'cNotifs.get', 'uses' => 'CustomerController@getNotifications']);
        Route::get('notifications/count',['as' => 'cNotifs.count', 'uses' => 'CustomerController@getNotificationsCount']);
        Route::patch('notifications/seen',['as' => 'cNotifs.seen', 'uses' => 'CustomerController@seeNotification']);

         //message-related
        Route::get('messages', ['as' => 'customer.messages', 'uses'=> 'MessageController@getMessages']);
        Route::get('messages/countUnread', ['as' => 'messages.countUnread', 'uses'=> 'MessageController@countUnread']);
        Route::get('messages/{breeder}', ['as' => 'messages.messages', 'uses'=> 'MessageController@getMessages']);

        Route::get('breeders', ['as' => 'breeders', 'uses'=> 'CustomerController@viewBreeders']);
        Route::post('breeders', ['as' => 'breedersChange', 'uses'=> 'CustomerController@viewBreedersChange']);

        // breeder-related
        Route::get('view-breeder/{breeder}',['as' => 'viewBProfile', 'uses' => 'ProductController@viewBreederProfile']);

    });

    // Admin
    Route::group(['prefix'=>'admin'], function(){

        // Route to admin home page
        Route::get('home',['as'=>'admin_path', 'uses'=>'AdminController@index']);
        Route::get('form', ['as'=>'registration.form', 'uses'=>'AdminController@getRegistrationForm']);
        Route::post('form/register', ['as'=>'admin.register.submit', 'uses'=>'AdminController@submitRegistrationForms']);
        Route::get('home/logs', ['as'=>'admin_logs', 'uses'=>'AdminController@getAdministratorLogs']);
        Route::get('home/logs/search', ['as' => 'admin.search.logs', 'uses' => 'AdminController@searchAdministratorLogs']);

        //message-related
        Route::get('messages/breeder', ['as' => 'admin.breeder.messages', 'uses'=> 'MessageController@getBreederMessagesAdmin']);
        Route::get('messages/countUnread', ['as' => 'messages.countUnread', 'uses'=> 'MessageController@countUnread']);
        Route::get('messages/breeder/{breeder}', ['as' => 'messages.messages', 'uses'=> 'MessageController@getBreederMessagesAdmin']);

        Route::get('messages/customer', ['as' => 'admin.customer.messages', 'uses'=> 'MessageController@getCustomerMessagesAdmin']);
        Route::get('messages/customer/{customer}', ['as' => 'messages.messages', 'uses'=> 'MessageController@getCustomerMessagesAdmin']);

        // Route for statistics pages
        Route::get('home/statistics/dashboard',['as'=>'admin.statistics.dashboard', 'uses'=>'AdminController@showStatisticsDashboard']);

        //  Breeder statistics
        Route::get('home/statistics/breeder/active', ['as' => 'admin.statistics.breeder.active', 'uses'=> 'AdminController@showStatisticsActiveBreeder']);
        Route::get('home/statistics/breeder/active-year', ['as' => 'admin.statistics.breeder.active-year', 'uses'=> 'AdminController@showStatisticsActiveBreederYear']);
        Route::get('home/statistics/breeder/deleted', ['as' => 'admin.statistics.breeder.deleted', 'uses'=> 'AdminController@showStatisticsDeletedBreeder']);
        Route::get('home/statistics/breeder/deleted-year', ['as' => 'admin.statistics.breeder.deleted-year', 'uses'=> 'AdminController@showStatisticsDeletedBreederYear']);
        Route::get('home/statistics/breeder/blocked', ['as' => 'admin.statistics.breeder.blocked', 'uses'=> 'AdminController@showStatisticsBlockedBreeder']);
        Route::get('home/statistics/breeder/blocked-year', ['as' => 'admin.statistics.breeder.blocked-year', 'uses'=> 'AdminController@showStatisticsBlockedBreederYear']);
        // Customer statistics
        Route::get('home/statistics/customer/active', ['as' => 'admin.statistics.customer.active', 'uses'=> 'AdminController@showStatisticsActiveCustomer']);
        Route::get('home/statistics/customer/active-year', ['as' => 'admin.statistics.customer.active-year', 'uses'=> 'AdminController@showStatisticsActiveCustomerYear']);
        Route::get('home/statistics/customer/deleted', ['as' => 'admin.statistics.customer.deleted', 'uses'=> 'AdminController@showStatisticsDeletedCustomer']);
        Route::get('home/statistics/customer/deleted-year', ['as' => 'admin.statistics.customer.deleted-year', 'uses'=> 'AdminController@showStatisticsDeletedCustomerYear']);
        Route::get('home/statistics/customer/blocked', ['as' => 'admin.statistics.customer.blocked', 'uses'=> 'AdminController@showStatisticsBlockedCustomer']);
        Route::get('home/statistics/customer/blocked-year', ['as' => 'admin.statistics.customer.blocked-year', 'uses'=> 'AdminController@showStatisticsBlockedCustomerYear']);

        Route::get('home/statistics/timeline', ['as' => 'admin.statistics.timeline', 'uses'=> 'AdminController@showStatisticsTimeline']);
        Route::get('home/statistics/timeline-date', ['as' => 'admin.statistics.timeline-date', 'uses'=> 'AdminController@showStatisticsTimelineDate']);

        Route::get('home/statistics/transactions', ['as' => 'admin.statistics.transactions', 'uses'=> 'AdminController@showStatisticsTransactions']);
        Route::get('home/statistics/transactions-date', ['as' => 'admin.statistics.transactions-date', 'uses'=> 'AdminController@showStatisticsTransactionsYear']);
        Route::get('home/statistics/totaltransactions', ['as' => 'admin.statistics.totaltransactions', 'uses'=> 'AdminController@showStatisticsTotalTransactions']);
        Route::get('home/statistics/totaltransactions-year', ['as' => 'admin.statistics.totaltransactions-year', 'uses'=> 'AdminController@showStatisticsTotalTransactionsModified']);

        // Average Statistics for Breeders
        Route::get('home/statistics/average-new-breeder', ['as' => 'admin.statistics.averageNewBreeder', 'uses'=> 'AdminController@averageMonthlyNewBreeders']);
        Route::get('home/statistics/average-new-breeder-year', ['as' => 'admin.statistics.averageNewBreederYear', 'uses'=> 'AdminController@averageMonthlyNewBreedersYear']);
        Route::get('home/statistics/average-blocked-breeder', ['as' => 'admin.statistics.averageBlockedBreeder', 'uses'=> 'AdminController@averageMonthlyBlockedBreeders']);
        Route::get('home/statistics/average-blocked-breeder-year', ['as' => 'admin.statistics.averageBlockedBreederYear', 'uses'=> 'AdminController@averageMonthlyBlockedBreedersYear']);
        Route::get('home/statistics/average-deleted-breeder', ['as' => 'admin.statistics.averageDeletedBreeder', 'uses'=> 'AdminController@averageMonthlyDeletedBreeders']);
        Route::get('home/statistics/average-deleted-breeder-year', ['as' => 'admin.statistics.averageDeletedBreederYear', 'uses'=> 'AdminController@averageMonthlyDeletedBreedersYear']);


        // Average Statistics for Customers
        Route::get('home/statistics/average-new-customer', ['as' => 'admin.statistics.averageNewCustomers', 'uses'=> 'AdminController@averageMonthlyNewCustomers']);
        Route::get('home/statistics/average-new-customer-year', ['as' => 'admin.statistics.averageNewCustomerYear', 'uses'=> 'AdminController@averageMonthlyNewCustomersYear']);
        Route::get('home/statistics/average-blocked-customer', ['as' => 'admin.statistics.averageBlockedCustomers', 'uses'=> 'AdminController@averageMonthlyBlockedCustomers']);
        Route::get('home/statistics/average-blocked-customer-year', ['as' => 'admin.statistics.averageBlockedCustomerYear', 'uses'=> 'AdminController@averageMonthlyBlockedCustomersYear']);
        Route::get('home/statistics/average-deleted-customer', ['as' => 'admin.statistics.averageDeletedCustomers', 'uses'=> 'AdminController@averageMonthlyDeletedCustomers']);
        Route::get('home/statistics/average-deleted-customer-year', ['as' => 'admin.statistics.averageDeletedCustomerYear', 'uses'=> 'AdminController@averageMonthlyDeletedCustomersYear']);


        Route::get('home/userlist', ['as'=>'admin.userlist', 'uses'=>'AdminController@displayAllUsers']);
        Route::get('home/userlist/details', ['as'=>'admin.userlist.details', 'uses'=>'AdminController@fetchUserInformation']);
        Route::get('home/userlist/transaction', ['as'=>'admin.userlist.transaction', 'uses'=>'AdminController@fetchUserTransaction']);
        Route::get('home/userlist/transaction-user',['as'=>'admin.userlist.transactionHistory', 'uses'=>'AdminController@fetchUserTransactionHistory']);
        Route::get('home/userlist/transaction-user/search',['as'=>'admin.userlist.transactionHistory.search', 'uses'=>'AdminController@searchUserTransactionHistory']);

        Route::get('home/spectatorlist', ['as'=>'admin.spectatorlist', 'uses'=>'AdminController@displaySpectators']);
        Route::get('home/spectatorlist-search', ['as'=>'admin.spectatorlist-search', 'uses'=>'AdminController@searchSpectators']);

        Route::get('home/approved/breeder', ['as'=>'admin.approved.breeder', 'uses'=>'AdminController@displayApprovedBreeders']);
        Route::get('home/approved/customer', ['as'=>'admin.approved.customer', 'uses'=>'AdminController@displayApprovedCustomer']);
        Route::get('home/pending/users', ['as'=>'admin.pending.users', 'uses'=>'AdminController@displayPendingUsers']);
        Route::get('home/approved/blocked', ['as'=>'admin.blocked.users', 'uses'=>'AdminController@displayBlockedUsers']);
        Route::delete('home/delete', ['as'=>'admin.delete', 'uses'=>'AdminController@deleteUser']);
        Route::put('home/block', ['as'=>'admin.block', 'uses'=>'AdminController@blockUser']);
        Route::put('home/approve', ['as'=>'admin.approve', 'uses'=>'AdminController@acceptUser']);
        Route::delete('home/reject', ['as'=>'admin.reject', 'uses'=>'AdminController@rejectUser']);
        Route::get('home/search', ['as' => 'admin.search', 'uses' => 'AdminController@searchUser']);
        Route::get('home/search_blocked', ['as' => 'admin.searchBlocked', 'uses' => 'AdminController@searchBlockedUsers']);
        Route::get('home/pending/search', ['as' => 'admin.searchPending', 'uses' => 'AdminController@searchPendingUser']);
        Route::post('home/add', ['as' => 'admin.add.user', 'uses' => 'AdminController@createUser']);

        Route::get('home/manage/homepage', ['as'=>'admin.manage.homepage', 'uses' => 'AdminController@manageHomePage']);
        Route::get('home/manage/homepage/fetchimages', ['as'=>'admin.manage.fetchimages', 'uses'=>'AdminController@getHomeImages']);
        Route::post('home/manage/homepage/addcontent', ['as'=>'admin.manage.addcontent', 'uses'=>'AdminController@addHomeImage']);
        Route::delete('home/manage/homepage/deletecontent', ['as'=>'admin.manage.deletecontent', 'uses'=>'AdminController@deleteContent']);
        Route::put('home/manage/homepage/editcontent', ['as'=>'admin.manage.editcontent', 'uses'=>'AdminController@editContent']);
        Route::get('home/manage/return/userlist', ['as' => 'admin.return.userlist', 'uses'=> 'AdminController@goToUserlist']);
        Route::get('home/manage/return/pending', ['as' => 'admin.return.pending', 'uses'=> 'AdminController@goToPending']);

        Route::get('broadcast', ['as'=>'admin.broadcast','uses'=>'AdminController@broadcastMessagePage']);
        Route::post('broadcast/send', ['as'=>'admin.broadcast.send','uses'=>'AdminController@sendBroadcastMessage']);

        Route::get('home/users', ['as' => 'users', 'uses'=> 'AdminController@viewUsers']);
        Route::get('admin_info',['as'=>'admin_info', 'uses'=>'AdminController@getAdminInformation']);

        Route::get('home/messenger', ['as' => 'users', 'uses'=> 'AdminController@messenger']);
        Route::post('home/messenger/send', ['as' => 'users', 'uses'=> 'AdminController@send']);

    });

    Route::group(['prefix'=>'spectator'], function(){

        // Route to admin home page
        Route::get('home',['as'=>'spectator_path', 'uses'=>'SpectatorController@index']);
        Route::get('users',['as'=>'spectator.users', 'uses'=>'SpectatorController@viewUsers']);
        Route::get('products',['as'=>'spectator.products', 'uses'=>'SpectatorController@viewProducts']);
        Route::get('logs',['as'=>'spectator.logs', 'uses'=>'SpectatorController@viewLogs']);
        Route::get('statistics',['as'=>'spectator.statistics', 'uses'=>'SpectatorController@viewStatisticsDashboard']);

        Route::get('users/search',['as'=>'spectator.searchUser', 'uses'=>'SpectatorController@searchUser']);
        Route::get('users/details', ['as'=>'spectator.fetchUserInformation', 'uses'=>'SpectatorController@fetchUserInformation']);

        Route::get('products/product-details', ['as'=>'spectator.productDetails', 'uses'=>'SpectatorController@fetchProductDetails']);
        Route::get('home/product/search', ['as'=>'spectator.searchProduct', 'uses'=>'SpectatorController@searchProduct']);
        Route::get('home/product/advancedsearch', ['as'=>'spectator.advancedSearchProduct', 'uses'=>'SpectatorController@advancedSearchProduct']);

        Route::get('statistics/customer/active', ['as'=>'spectator.statisticsActiveCustomer', 'uses'=>'SpectatorController@viewActiveCustomerStatistics']);
        Route::get('statistics/customer/active-year', ['as'=>'spectator.statisticsActiveCustomerYear', 'uses'=>'SpectatorController@viewActiveCustomerStatisticsYear']);
        Route::get('statistics/customer/blocked', ['as'=>'spectator.statisticsBlockedCustomer', 'uses'=>'SpectatorController@viewBlockedCustomerStatistics']);
        Route::get('statistics/customer/blocked-year', ['as'=>'spectator.statisticsBlockedCustomerYear', 'uses'=>'SpectatorController@viewBlockedCustomerStatisticsYear']);
        Route::get('statistics/customer/deleted', ['as'=>'spectator.statisticsDeletedCustomer', 'uses'=>'SpectatorController@viewDeletedCustomerStatistics']);
        Route::get('statistics/customer/deleted-year', ['as'=>'spectator.statisticsDeletedCustomerYear', 'uses'=>'SpectatorController@viewDeletedCustomerStatisticsYear']);

        Route::get('statistics/customer/averagecreated', ['as'=>'spectator.averageCustomerStatisticsCreated', 'uses'=>'SpectatorController@averageCustomerCreated']);
        Route::get('statistics/customer/averagecreated-year', ['as'=>'spectator.averageCustomerStatisticsCreatedYear', 'uses'=>'SpectatorController@averageCustomerCreatedYear']);
        Route::get('statistics/customer/averageblocked', ['as'=>'spectator.averageCustomerStatisticsBlocked', 'uses'=>'SpectatorController@averageCustomerBlocked']);
        Route::get('statistics/customer/averageblocked-year', ['as'=>'spectator.averageCustomerStatisticsBlockedYear', 'uses'=>'SpectatorController@averageCustomerBlockedYear']);
        Route::get('statistics/customer/averagedeleted', ['as'=>'spectator.averageCustomerStatisticsDeleted', 'uses'=>'SpectatorController@averageCustomerDeleted']);
        Route::get('statistics/customer/averagedeleted-year', ['as'=>'spectator.averageCustomerStatisticsDeletedYear', 'uses'=>'SpectatorController@averageCustomerDeletedYear']);



        Route::get('statistics/breeder/active', ['as'=>'spectator.statisticsActiveBreeder', 'uses'=>'SpectatorController@viewActiveBreederStatistics']);
        Route::get('statistics/breeder/active-year', ['as'=>'spectator.statisticsActiveBreederYear', 'uses'=>'SpectatorController@viewActiveBreederStatisticsYear']);
        Route::get('statistics/breeder/blocked', ['as'=>'spectator.statisticsBlockedBreeder', 'uses'=>'SpectatorController@viewBlockedBreederStatistics']);
        Route::get('statistics/breeder/blocked-year', ['as'=>'spectator.statisticsBlockedBreederYear', 'uses'=>'SpectatorController@viewBlockedBreederStatisticsYear']);
        Route::get('statistics/breeder/deleted', ['as'=>'spectator.statisticsDeletedBreeder', 'uses'=>'SpectatorController@viewDeletedBreederStatistics']);
        Route::get('statistics/breeder/deleted-year', ['as'=>'spectator.statisticsDeletedBreederYear', 'uses'=>'SpectatorController@viewDeletedBreederStatisticsYear']);

        Route::get('statistics/breeder/averagecreated', ['as'=>'spectator.averageBreederStatisticsCreated', 'uses'=>'SpectatorController@averageBreedersCreated']);
        Route::get('statistics/breeder/averagecreated-year', ['as'=>'spectator.averageBreederStatisticsCreatedYear', 'uses'=>'SpectatorController@averageBreedersCreatedYear']);
        Route::get('statistics/breeder/averageblocked', ['as'=>'spectator.averageBreederStatisticsBlocked', 'uses'=>'SpectatorController@averageBreedersBlocked']);
        Route::get('statistics/breeder/averageblocked-year', ['as'=>'spectator.averageBreederStatisticsBlockedYear', 'uses'=>'SpectatorController@averageBreedersBlockedYear']);
        Route::get('statistics/breeder/averagedeleted', ['as'=>'spectator.averageBreederStatisticsDeleted', 'uses'=>'SpectatorController@averageBreedersDeleted']);
        Route::get('statistics/breeder/averagedeleted-year', ['as'=>'spectator.averageBreederStatisticsDeletedYear', 'uses'=>'SpectatorController@averageBreedersDeletedYear']);

        Route::get('statistics/productbreakdown', ['as'=>'spectator.productbreakdown', 'uses'=>'SpectatorController@viewProductBreakdown']);
        Route::get('spectator_info',['as'=>'spectator_info', 'uses'=>'SpectatorController@getSpectatorInformation']);

    });


});
