<?php

use App\Http\Controllers\RestApiController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('auth/signupOtp', [RestApiController::class, 'signupOtp']);
Route::post('auth/resendSignupOtp', [RestApiController::class, 'resendSignupOtp']);
Route::post('auth/signUp', [RestApiController::class, 'signUp']);
Route::post('auth/signIn', [RestApiController::class, 'signIn']);
Route::post('auth/forgotOtp', [RestApiController::class, 'forgotOtp']);
Route::post('auth/resendForgotOtp', [RestApiController::class, 'resendForgotOtp']);
Route::post('auth/validateForgotOtp', [RestApiController::class, 'validateForgotOtp']);
Route::post('auth/resetPassword', [RestApiController::class, 'resetPassword']);
Route::post('user/userProfile', [RestApiController::class, 'userProfile']);
Route::post('user/updateProfile', [RestApiController::class, 'updateProfile']);
Route::get('sync/contacts', [RestApiController::class, 'syncContacts']);
Route::get('sync/contracts', [RestApiController::class, 'syncContracts']);
Route::post('truncate/database', [RestApiController::class, 'truncateTables']);
