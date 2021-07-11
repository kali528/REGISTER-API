<?php
use App\Mail\Authorization;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
//Public routes
Route::get('/email/verify', [AuthController::class, 'verify']);

Route::group(['middleware' => ['web']], function () {
    Route::post('/login',       [AuthController::class, 'login']);
    Route::post('/logout',      [AuthController::class, 'logout']); 
    Route::post('/register',    [AuthController::class, 'register']);
    
    Route::get('/token', function (Request $request) {
        $token = $request->session()->token();
        $token = csrf_token();
        return $token;
    });

});
// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::get('/article',      [ArticleController::class, 'index']);
    Route::get('/article/{id}', [ArticleController::class, 'show']);
    
    Route::group(['middleware' => ['web']], function () {
        Route::post('/article',     [ArticleController::class, 'store']);
        Route::post('/logout',      [AuthController::class, 'logout']);
    });
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('auth:sanctum')->get('/asd', function () {
//     return ['asd'=>'asd'];
// });
