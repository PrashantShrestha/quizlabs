<?php

use Illuminate\Support\Facades\Route;
use App\Models\Type;

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

Route::namespace('Api')->name('api.')->group(function () {

    Route::get('general-setting', function () {
        $general = gs();
        $notify[] = 'General setting data';
        return response()->json([
            'remark' => 'general_setting',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'general_setting' => $general,
            ],
        ]);
    });

    Route::get('get-countries', function () {
        $c = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $notify[] = 'General setting data';
        foreach ($c as $k => $country) {
            $countries[] = [
                'country' => $country->country,
                'dial_code' => $country->dial_code,
                'country_code' => $k,
            ];
        }
        return response()->json([
            'remark' => 'country_data',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'countries' => $countries,
            ],
        ]);
    });

    Route::controller('SiteController')->group(function () {
        Route::get('change/{lang?}', 'changeLanguage');
    });

    Route::namespace('Auth')->group(function () {
        Route::post('login', 'LoginController@login');
        Route::post('register', 'RegisterController@register');

        Route::controller('ForgotPasswordController')->group(function () {
            Route::post('password/email', 'sendResetCodeEmail')->name('password.email');
            Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
            Route::post('password/reset', 'reset')->name('password.update');
        });

        // social
        Route::post('social-login', 'SocialiteController@socialLogin');
    });

    Route::middleware('auth:sanctum')->group(function () {

        //authorization
        Route::controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorization')->name('authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
            Route::post('verify-email', 'emailVerification')->name('verify.email');
            Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        });

        Route::middleware(['check.status'])->group(function () {
            Route::post('user-data-submit', 'UserController@userDataSubmit')->name('data.submit');

            Route::middleware('registration.complete')->group(function () {

                Route::controller('UserController')->group(function () {

                    Route::post('get/device/token', 'getDeviceToken')->name('get.device.token');

                    //Report
                    Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                    Route::get('transactions', 'transactions')->name('transactions');

                    // dashboard
                    Route::get('dashboard', 'dashboard');

                    // delete account
                    Route::get('account/delete', 'deleteUser');
                });

                Route::controller('CoinPlanController')->group(function () {
                    Route::get('coin-store', 'coinStore');
                    Route::get('coin-history', 'coinHistory');
                });

                //Profile setting
                Route::controller('UserController')->group(function () {
                    Route::get('user-info', 'userDetails');
                    Route::post('profile-setting', 'submitProfile');
                    Route::post('change-password', 'submitPassword');
                    Route::post('profile-avatar-update', 'profileAvatarUpdate');
                    Route::get('leader-board', 'leaderBoard');
                });

                // Payment
                Route::controller('PaymentController')->group(function () {
                    Route::get('deposit/methods', 'methods')->name('deposit');
                    Route::post('deposit/insert', 'depositInsert')->name('deposit.insert');
                    Route::get('deposit/confirm', 'depositConfirm')->name('deposit.confirm');
                    Route::get('deposit/manual', 'manualDepositConfirm')->name('deposit.manual.confirm');
                    Route::post('deposit/manual', 'manualDepositUpdate')->name('deposit.manual.update');
                });

                // general quiz
                Route::controller('GeneralQuizController')->prefix('general-quiz')->group(function () {
                    Route::get('category', 'categories');
                    Route::get('subcategory/{categoryId}', 'subcategory');
                    Route::get('level/question/{quizInfoId}', 'questionList');
                    Route::post('answer-submit', 'answerStore');
                });

                // contest
                Route::controller('ContestController')->prefix('contest')->group(function () {
                    Route::get('', 'contestList');
                    Route::get('questions/{contestId}', 'questionList');
                    Route::post('answer-submit', 'answerStore');
                });

                // daily quiz
                Route::controller('DailyQuizController')->prefix('daily-quiz')->group(function () {
                    Route::get('', 'questionList');
                    Route::post('answer-submit', 'answerStore');
                });

                // guess word
                Route::controller('GuessWordController')->prefix('guess-word')->group(function () {
                    Route::get('category-list', 'guessCategoryList');
                    Route::get('subcategory-list/{categoryId}', 'guessSubcategoryList');
                    Route::get('question-list/{quizInfoId}', 'guessWordQuestionList');
                    Route::post('answer-submit', 'answerStore');
                });

                // fun n learn
                Route::controller('FunController')->prefix('fun')->group(function () {
                    Route::get('category-list', 'funCategoryList');
                    Route::get('subcategory-list/{categoryId}', 'funSubcategoryList');
                    Route::get('fun-list/{categoryId}/{subcategoryId?}', 'funList');
                    Route::get('question-list/{quizInfoId}', 'questionList');
                    Route::post('answer-submit', 'answerStore');
                });



                // live exam
                Route::controller('ExamController')->prefix('exam')->group(function () {
                    Route::get('exam-list', 'examList');
                    Route::get('exam-details/{id}', 'examDetails');
                    Route::post('question-list', 'examQuestionList');
                    Route::post('answer-submit', 'answerStore');
                    Route::get('exam-code/{id}', 'examCode');
                    Route::get('completed-exam', 'completeExam');
                });

                // single battle
                Route::controller('SingleBattleController')->prefix('single-battle')->group(function () {
                    Route::get('', 'singleBattle');
                    Route::post('search-player', 'searchPlayer');
                    Route::get('question-list/{userId?}/{opponentId?}/{categoryId?}', 'questionList');
                    Route::post('answer-submit', 'answerStore');
                });

                // group battle
                Route::controller('GroupBattleController')->prefix('group-battle')->group(function () {
                    Route::get('', 'groupBattle');
                    Route::post('store-room', 'createRoom');
                    Route::post('join-room', 'joinRoom');
                });
            });
        });

        Route::get('logout', 'Auth\LoginController@logout');
    });

    Route::controller('FrontendController')->group(function () {
        Route::get('privacy-policy', 'policyPages');
    });
});
