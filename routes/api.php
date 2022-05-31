<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AdminAuth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CooperativeController;
use App\Http\Controllers\LoanApprovalController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AdminAuth::class, 'createAdmin']);
    Route::post('login', [AdminAuth::class, 'login']);
    Route::post('change-password', [AdminAuth::class, 'changePassword']);
});
Route::prefix('cooperative')->group(function () {
    Route::post('create', [CooperativeController::class, 'createCooperative']);
    Route::post('member/login', [CooperativeController::class, 'loginCooperative']);
});
Route::prefix('member')->group(function () {
    Route::post('create', [AdminController::class, 'createMember']);
    Route::post('one', [MemberController::class, 'oneMember']);
    Route::post('loan/one', [MemberController::class, 'oneLoan']);
    Route::post('loan/all', [MemberController::class, 'allLoans']);
});
Route::prefix('loan')->group(function () {
    Route::post('apply', [LoanController::class, 'applyLoan']);
    Route::post('approve', [LoanApprovalController::class, 'approveLoan']);
    Route::post('disbursement-check', [AdminController::class, 'disbursementCheck']);
    Route::post('disbursement-check-manual', [AdminController::class, 'disbursementCheckAuthorization']);
    Route::post('disburse', [AdminController::class, 'disburseLoan']);
});

Route::prefix('admin')->group(function () {
    Route::post('loan/one', [AdminController::class, 'oneLoan']);
    Route::post('loan/all', [AdminController::class, 'allLoans']);
    Route::post('loan/cancel', [AdminController::class, 'cancelLoan']);
    Route::post('cooperative/one', [AdminController::class, 'viewOneCooperative']);
    Route::post('cooperative/list', [AdminController::class, 'listCooperatives']);
    Route::post('cooperative/members/list', [AdminController::class, 'listCooperativeMembers']);
    Route::post('cooperative/edit', [AdminController::class, 'EditCooperative']);
    Route::post('cooperative/status/change', [AdminController::class, 'changeCooperativeStatus']);
});

