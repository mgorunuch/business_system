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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::post('/payments/status','PaymentController@pay');
Route::post('/payments/success','PaymentController@pay');
Route::post('/payments/failed',function(){
    return redirect('/referal');
});

Route::group(['middleware' => 'auth'], function () {

    Route::group(['middleware' => 'auth.frizzed'], function () {
        Route::get('/payments/pay', 'PaymentController@index');
        Route::get('/home', 'PaymentController@index');
        Route::post('/payments/activate_account', 'PaymentController@activate');
    });

    Route::group(['middleware' => 'auth.banned'], function () {
        Route::get('/payments/pay/banned', 'PaymentController@banned');
    });

    Route::group(['middleware' => 'auth.admin'], function() {
        Route::get('/dashboard/moderate/articles', 'ArticleController@moderate');
        Route::get('/dashboard/moderate/articles/allow/{id}', 'ArticleController@activate');
        Route::post('/dashboard/moderate/articles/decline/{id}', 'ArticleController@decline');

        Route::get('/dashboard/moderate/categories', 'CategoriesController@moderate');
        Route::post('/dashboard/moderate/categories/edit/{id}', 'CategoriesController@edit');

        Route::get('/dashboard/moderate/payments', 'PaymentController@moderate');
        Route::post('/dashboard/moderate/payments/change_status', 'PaymentController@change_status');

        Route::get('/messages',function(){
            return view('components.messages');
        });
    });

    Route::group(['middleware' => 'auth.activated'], function () {
        Route::get('/home',function(){return redirect('/blog');});
        Route::post('/payments/withdraw', 'PaymentController@withdraw');

        Route::post('/payments/internal_transaction', 'PaymentController@internal');

        Route::get('/settings', function (){
            return view('dashboard.user.settings',['user'=>\Illuminate\Support\Facades\Auth::user()]);
        });
        Route::get('/achievements', function (){
            return view('dashboard.user.achievements',['user'=>\Illuminate\Support\Facades\Auth::user()]);
        });
        Route::get('/referal', function (){
            return view('dashboard.user.referal',[
                'user'=>\Illuminate\Support\Facades\Auth::user(),
                'withdraw_processing' => \App\Http\Controllers\PaymentController::getWithdraw(),
                'config'=>\Illuminate\Support\Facades\Config::get('PerfectMoney'),
                'levels'=>\Illuminate\Support\Facades\Auth::user()->get_users_count(),
                'referals'=>\Illuminate\Support\Facades\Auth::user()->getReferals(10),
                'referal_counts'=>\Illuminate\Support\Facades\Auth::user()->getReferalCounts(),
                'referal_week'=>\Illuminate\Support\Facades\Auth::user()->getReferalWeek(),
                'referal_tree_week'=>\Illuminate\Support\Facades\Auth::user()->getReferalTreeWeek(),
                'left_time_lucky'=>\Illuminate\Support\Facades\Auth::user()->getLuckyStep(),
                'payment_left_time'=>\Illuminate\Support\Facades\Auth::user()->getPaymentLeft()
            ]);
        });

        Route::post('/user/password_change', 'UserController@changePassword');
        Route::post('/user/info_change', 'UserController@changeInfo');
        Route::post('/user/image_change', 'UserController@image_change');

        Route::get('/lessons','LessonsController@index');

        Route::get('/blog', function() {
            return view('dashboard.blog.main', [
                'articles'=>
                    [
                        'pool'=>\App\Category::find(1)->getArticles(1),
                        'cat'=>1,
                        'page'=>1,
                        'pages'=>\App\Category::find(1)->getPaginate()
                    ]
            ]);
        });

        Route::get('/blog/article/new', 'ArticleController@create');
        Route::post('/blog/article/new', 'ArticleController@store');
        Route::post('/blog/article/destroy/{id}', 'ArticleController@destroy');
        Route::post('/blog/article/addLike/{id}', 'ArticleController@addLike');
        Route::post('/blog/article/addDislike/{id}', 'ArticleController@addDislike');

        Route::get('/blog/my-articles', function() {
            return view('dashboard.blog.main', [
                'articles'=>[
                    'pool'=>\App\Article::user_articles(1,10),
                    'page'=>1,
                    'cat'=>'my-articles',
                    'pages'=>ceil(\Illuminate\Support\Facades\Auth::user()->articles()->count() / 10)
                ]
            ]);
        });
        Route::get('/blog/my-articles/{page_id}', function($page_id) {
            return view('dashboard.blog.main', [
                'articles'=>[
                    'pool'=>\App\Article::user_articles($page_id,10),
                    'page'=>$page_id,
                    'cat'=>'my-articles',
                    'pages'=>ceil(\Illuminate\Support\Facades\Auth::user()->articles()->count() / 10)
                ]
            ]);
        });

        Route::get('/blog/article/edit/{id}', 'ArticleController@edit');
        Route::post('/blog/article/edit/{id}', 'ArticleController@update');

        Route::get('/blog/article/{id}', 'ArticleController@show');

        Route::post('/blog/categories/new', 'CategoriesController@create');

        Route::get('/blog/{cat_id}/', function ($cat_id) {
            return view('dashboard.blog.main')->with([
                'articles'=>[
                    'pool'=>\App\Category::find($cat_id)->getArticles(1),
                    'page'=>1,
                    'cat'=>$cat_id,
                    'pages'=>\App\Category::find(1)->getPaginate()
                ]
            ]);
        });
        Route::get('/blog/{cat_id}/{page}/', function ($cat_id, $page_id) {
            return view('dashboard.blog.main')->with([
                'articles'=>[
                    'pool'=>\App\Category::find($cat_id)->getArticles($page_id),
                    'page'=>$page_id,
                    'cat'=>$cat_id,
                    'pages'=>\App\Category::find($page_id)->getPaginate()
                ]
            ]);
        });
        Route::post('/ajax/reffersAjax', 'AjaxController@getReffers');
        Route::post('/ajax/getBallance', 'AjaxController@getBallance');
        Route::post('/ajax/gainedAllTime', 'AjaxController@gainedAllTime');
    });

    Route::group(['middleware' => 'auth.superadmin'], function() {
        Route::get('/superadmin', 'SuperadminController@index');
        Route::post('/superadmin/money/send', 'SuperadminController@money_send');
        Route::post('/superadmin/money/spend', 'SuperadminController@money_spend');
        Route::post('/superadmin/user/ban', 'SuperadminController@ban_user');
        Route::post('/superadmin/user/activate', 'SuperadminController@activate_user');
        Route::post('/superadmin/user/frizz', 'SuperadminController@frizz_user');
        Route::post('/superadmin/user/get_info', 'SuperadminController@get_user_info');
        Route::post('/superadmin/user/delete_banned_user', 'SuperadminController@delete_banned_user');
        Route::post('/superadmin/user/set_last_payment', 'SuperadminController@set_last_payment');
        Route::post('/superadmin/user/reset_lucky_week', 'SuperadminController@reset_lucky_week');
    });
});