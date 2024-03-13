//BuyerWeb Route
Route::post('buyerWebLogin', 'API\BuyerWeb\AuthController@login');
Route::post('buyerWebRegisteration', 'API\BuyerWeb\AuthController@register');
Route::post('/validateBuyerWebRegisterationOtp', 'API\BuyerWeb\AuthController@validateOtp');
Route::get('/getCategoriesForBuyer/{catId}', 'API\BuyerWeb\ProductsController@getCategoriesForBuyer');
Route::get('/getBuyerWebProductsListsByPage', 'API\BuyerWeb\ProductsController@getBuyerWebProductsListsByPage');
Route::get('/getBuyerWebProductDetails/{productId}', 'API\BuyerWeb\ProductsController@getBuyerWebProductDetails'); //productId- prod_slug
Route::get('/getBuyerWebUserProfile/{userId}', 'API\BuyerWeb\UsersController@getBuyerWebUserProfile'); //userId- profile_slug
Route::get('/getBuyerWebUserProducts/{userId}', 'API\BuyerWeb\UsersController@getBuyerWebUserProducts'); //userId- profile_slug
