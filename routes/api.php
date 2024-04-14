<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\post\CommentController;
use App\Http\Controllers\Api\post\CommentLikeController;
use App\Http\Controllers\Api\post\LikeController;
use App\Http\Controllers\Api\post\PostController;
use App\Http\Controllers\Api\post\ReplyController;
use App\Http\Controllers\Api\School\SchoolClassController;
use App\Http\Controllers\Api\School\SchoolController;
use App\Http\Controllers\Api\Student\StudentController;
use App\Http\Controllers\profile\ProfileController;
use App\Http\Controllers\QuoteController;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/verify', [OtpController::class, 'verify']);

Route::middleware([
    'api',
    StartSession::class,
])->group(function () {
    Route::post('/forgot-password', [LoginController::class, 'forgotPassword']);
    Route::post('/validate-otp', [LoginController::class ,'validateOtp']);
    Route::post('/reset-password', [LoginController::class, 'resetPassword']);
});

// ->middleware('verified') will use it later to check if the user is verified

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/{user}', [ProfileController::class, 'update']);

   // Route::apiResource('schools', SchoolController::class);
    Route::prefix('schools')->group(function () {
        Route::get('/', [SchoolController::class, 'index']);
        Route::post('/', [SchoolController::class, 'store']);
        Route::get('/{school}', [SchoolController::class, 'show']);
        Route::put('/{school}', [SchoolController::class, 'update']);
        Route::delete('/{school}', [SchoolController::class, 'destroy']);
        Route::post('/join', [SchoolController::class, 'joinSchool']);
        Route::get('/{school}/members', [SchoolController::class, 'getSchoolMembers']);
        Route::get('/{school}/classes', [SchoolController::class, 'getSchoolClasses']);
    });

    Route::prefix('school-join-requests')->group(function () {
        // Route for users to send a school join request
        Route::post('/', [SchoolController::class, 'createSchoolJoinRequest']);

        // Route for admins to view all join requests for their school
        Route::get('/', [SchoolController::class, 'viewSchoolJoinRequests']);

        // Route for users to view their own join requests
        Route::get('/user', [SchoolController::class, 'viewSchoolJoinRequestsForUser']);

        // Route for admins to approve a join request
        Route::post('/{schoolJoinRequest}/approve', [SchoolController::class, 'approveSchoolJoinRequest']);

        // Route for admins to reject a join request
        Route::post('/{schoolJoinRequest}/reject', [SchoolController::class, 'rejectSchoolJoinRequest']);
        Route::post('/join-school', [SchoolController::class, 'joinSchoolUsingCode']);
    });

    Route::prefix('classes')->group(function () {
        Route::get('/', [SchoolClassController::class, 'index']);
        Route::get('/{id}', [SchoolClassController::class, 'show']);
        Route::post('/', [SchoolClassController::class, 'store']);
        Route::get('/student/{studentId}', [SchoolClassController::class, 'classesForStudent']);
        Route::put('/{id}', [SchoolClassController::class, 'update']);
        Route::delete('/{id}', [SchoolClassController::class, 'destroy']);
        Route::get('/{class}/members', [SchoolClassController::class, 'getClassMembers']);
    });

    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::post('/', [StudentController::class, 'store']);
//        Route::post('/{studentId}/classes', [StudentController::class, 'addStudentToClass']);
//        Route::get('/{studentId}/classes', [StudentController::class, 'classesForStudent']);
        Route::put('/{id}', [StudentController::class, 'update']);
        Route::delete('/{id}', [StudentController::class, 'destroy']);
        Route::get('/children/parent', [StudentController::class, 'childrenForParent']);
    });

    Route::prefix('join-requests')->group(function () {
        // Route for parents to send a join request to a class
        Route::post('/{studentId}', [SchoolClassController::class, 'addStudentToClass']);
        // Route for teachers to approve a join request
        Route::post('/{joinRequestId}/approve', [SchoolClassController::class, 'approveJoinRequest']);

        // Route for teachers to reject a join request
        Route::post('/{joinRequestId}/reject', [SchoolClassController::class, 'rejectJoinRequest']);
        Route::post('/class/join', [SchoolClassController::class, 'joinClassUsingCode']);
        // Route for parents to view the status of their join requests
        Route::get('/', [SchoolClassController::class, 'viewJoinRequests']);

        // Route for teachers to view all join requests for their classes
        Route::get('/all', [SchoolClassController::class, 'viewAllJoinRequests']);
    });

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{post}', [PostController::class, 'show']);
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{post}', [PostController::class, 'destroy']);
        Route::post('/vote/{id}', [PostController::class, 'vote']);
        Route::get('/class/{classId}', [PostController::class, 'postsByClass']);
        Route::get('/user/classes', [PostController::class, 'postsByUserClasses']);

        //saving post
        Route::post('/{post}/toggle-save', [PostController::class, 'toggleSave']);


        // Route for getting all posts of each class in a specific school for the admin
        Route::get('/school/admin', [PostController::class, 'postsBySchoolAdmin']);

        // Route for getting posts by a specific school
        Route::get('/school/{schoolId}', [PostController::class, 'postsBySchool']);

        Route::ApiResource('/{post}/comments', CommentController::class);
        Route::get('/{comment}/comment', [CommentController::class, 'comment']);
        // /api/posts/{post}/comments/{comment}
        Route::get('/{post}/all-comments', [CommentController::class, 'comments']);

        Route::post('/{post}/likes', [LikeController::class, 'store']);
//        Route::delete('/{post}/likes', [LikeController::class, 'destroy']);
        Route::get('/{post}/isliked', [LikeController::class, 'isLiked']);

        Route::post('/{comment}/like', [CommentLikeController::class, 'store']);
        Route::delete('/{comment}/like', [CommentLikeController::class, 'destroy']);

        Route::get('/comments/{comment}/replies', [ReplyController::class, 'index']);
        Route::get('/comments/{comment}/replies/{reply}', [ReplyController::class, 'show']);
        Route::post('/{comment}/replies', [ReplyController::class, 'store']);
        Route::delete('/{comment}/replies/{reply}', [ReplyController::class, 'destroy']);
        Route::put('/comments/{comment}/replies/{reply}', [ReplyController::class, 'update']);
        Route::post('/replies/{replyId}/like', [ReplyController::class, 'like']);

    });
});









Route::post('/quotes', [QuoteController::class, 'store']);
Route::get('/quotes/random', [QuoteController::class, 'showRandom']);
