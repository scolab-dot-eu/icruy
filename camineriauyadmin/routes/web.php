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
    //return redirect('viewer_login');
    return redirect('visor');
});

Route::prefix('dashboard')
->middleware(['auth', 'isadmin'])
->group(function () {
        //Route::resource('users', 'UsersController');
        Route::resource('roles', 'RoleController', ['except' => [
            'show'
        ]]);
        Route::resource('editablelayerdefs', 'EditableLayerDefController', ['except' => [
            'show', 'destroy'
        ]]);
        Route::post('editablelayerdefs/{id}/enable', 'EditableLayerDefController@enable')->name('editablelayerdefs.enable');
        Route::post('editablelayerdefs/{id}/disable', 'EditableLayerDefController@disable')->name('editablelayerdefs.disable');
        Route::resource('supportlayerdefs', 'SupportLayerDefController', ['except' => [
            'show'
        ]]);
        Route::resource('users', 'UserController', ['except' => [
            'show'
        ]]);
        Route::resource('changerequests', 'ChangeRequestController', ['except' => [
            'show', 'create', 'store', 'destroy'
        ]]);
        //Route::get('login', 'Auth\LoginController@showLoginForm')->name('dashboardlogin');
        //Route::post('login', 'Auth\LoginController@login');
        // Registration Routes...
        //Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
        //Route::post('register', 'Auth\RegisterController@register');
        // Password Reset Routes...
        Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');
    });
/* 
Route::get('dashboard/department', 'DepartmentController@index')->name('department.index');
Route::get('dashboard/department/create', 'DepartmentController@create')->name('department.create');
Route::post('dashboard/department', 'DepartmentController@store')->name('department.store');
Route::get('dashboard/department/{id}/update', 'DepartmentController@updateForm')->name('department.updateForm')->where('id', '[0-9]+');
Route::post('dashboard/department/update', 'DepartmentController@update')->name('department.update');
*/

//Auth::routes();
/*Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');
// Registration Routes...
//Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
//Route::post('register', 'Auth\RegisterController@register');
// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
*/

Route::get('viewer_login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('viewer_login', 'Auth\LoginController@login');


Route::group(['middleware' => ['auth']], function() {
    Route::post('viewer_logout', 'Auth\LoginController@logout')->name('logout');
    Route::get('seleccionar_departamento', function(){
        // Lo redirijo a /home, ya que Laravel se empeña en redirigir a esta dirección
        // si el usuario está autenticado
        return redirect('/home');
    })->name('selectdepartment');
    Route::get('home', function(){
        $user = Auth::user();
        return view('selectdepartment', ['departments'=>$user->departments]);
    })->name('home');
    Route::get('/api/config/department/{code}', 'DepartmentConfigApiController@getDepartmentConfig')->name('departmentconfig');
    Route::post('/api/changerequest', 'ChangeRequestApiController@store')->name('api.changerequest');
    /* Route::resource('/api/changerequest', 'ChangeRequestApiController', ['except' => [
        'create', 'edit', 'show', 'update'
    ]]); */
});

Route::get('/api/config/global', 'DepartmentConfigApiController@getGlobalConfig')->name('globalconfig');
Route::post('/api/logout', 'Auth\LoginController@logout')->name('apilogout');

