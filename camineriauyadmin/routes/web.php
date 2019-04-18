<?php

use App\Department;
use App\ChangeRequest;
use App\MtopChangeRequest;

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

URL::forceRootUrl(getenv('APP_URL'));

Route::get('/', function () {
    //return redirect('viewer_login');
    return redirect('visor');
});

Route::prefix('dashboard')
->middleware(['auth'])
->group(function () {
        Route::get('changerequests/datatables', 'ChangeRequestController@anyData')->name('changerequests.datatables');
        Route::resource('changerequests', 'ChangeRequestController', ['except' => [
            'show', 'create', 'store', 'destroy'
        ]]);
        Route::get('mtopchangerequests/datatables', 'MtopChangeRequestController@anyData')->name('mtopchangerequests.datatables');
        Route::resource('mtopchangerequests', 'MtopChangeRequestController', ['except' => [
            'show', 'create', 'store', 'destroy'
        ]]);
        Route::get('mtopchangerequests/{id}/feature-{codigo_camino}.geojson', 'MtopChangeRequestController@feature')
        ->where(['id' => '[0-9]+', 'name' => 'UY[A-Z][A-Z][A-Z0-9]+'])
            ->name('mtopchangerequests.feature');
            Route::get('mtopchangerequests/{id}/feature-{codigo_camino}-{feature_id}.geojson', 'MtopChangeRequestController@feature')
            ->where(['id' => '[0-9]+', 'name' => 'UY[A-Z][A-Z][A-Z0-9]+', 'feature_id' => '[0-9]+'])
            ->name('mtopchangerequests.feature.gid');

        Route::get('interventions/datatables', 'InterventionController@anyData')->name('interventions.datatables');
        Route::resource('interventions', 'InterventionController', ['except' => [
            'show'
        ]]);
        
        Route::get('imports', 'ImportLayerController@query')->name('imports.query');
        Route::post('imports', 'ImportLayerController@import')->name('imports.import');
        
    Route::group(['middleware' => ['isadmin']], function() {
        
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
        Route::post('users/{id}/enable', 'UserController@enable')->name('users.enable');
        Route::post('users/{id}/disable', 'UserController@disable')->name('users.disable');
    });
});
/* 
Route::get('dashboard/department', 'DepartmentController@index')->name('department.index');
Route::get('dashboard/department/create', 'DepartmentController@create')->name('department.create');
Route::post('dashboard/department', 'DepartmentController@store')->name('department.store');
Route::get('dashboard/department/{id}/update', 'DepartmentController@updateForm')->name('department.updateForm')->where('id', '[0-9]+');
Route::post('dashboard/department/update', 'DepartmentController@update')->name('department.update');
*/

Route::get('viewer_login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('viewer_login', 'Auth\LoginController@login');


Route::group(['middleware' => ['auth']], function() {
    Route::post('viewer_logout', 'Auth\LoginController@logout')->name('logout');
    Route::get('home', function () {
        $user = Auth::user();
        /*
        if ($user->isAdmin()) {
            $departments = Department::orderBy('code')->get();
        }
        else {
            $departments = $user->departments()->orderBy('code')->get();
        }*/
        return view('resumen', ['departments'=> $user->departments()->orderBy('code')->get(), 'userOpen'=>$user->changeRequests()->open()->count(), 'userMtopOpen'=>$user->mtopChangeRequests()->open()->count(), 'allOpen'=> ChangeRequest::open()->count(),  'mtopAllOpen'=> MtopChangeRequest::open()->count()]);
    })->name('home');
    Route::get('seleccionar_departamento', function(){
        // Lo redirijo a /home, ya que Laravel se empeña en redirigir a esta dirección
        // si el usuario está autenticado
        return redirect('/home');
    })->name('selectdepartment');
    
    Route::get('selectdepartment', function(){
        $user = Auth::user();
        return view('selectdepartment', ['departments'=>$user->departments]);
    })->name('selectdepartment');
    Route::get('/api/config/department/{code}', 'ViewerConfigApiController@getViewerConfig')->name('departmentviewerconfig');
    Route::post('/api/changerequest', 'ChangeRequestApiController@store')->name('api.changerequest');
    Route::post('/api/mtopchangerequest', 'MtopChangeRequestApiController@store')->name('api.mtopchangerequest');
});

Route::get('/api/config/global', 'ViewerConfigApiController@getViewerConfig')->name('globalviewerconfig');
Route::post('/api/logout', 'Auth\LoginController@logout')->name('apilogout');
// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

Route::get('dashboard/reports', 'ReportController@query')->name('reports.query');
//Route::post('dashboard/reports', 'ReportController@download')->name('reports.download');
Route::post('dashboard/reports', 'ReportController@export')->name('reports.download');

Route::prefix('/api/layers')->group(function () {
    Route::resource('cr_caminos', 'CaminoLayerApiController', ['only' => [
        'index', 'show'
    ]]);
});
