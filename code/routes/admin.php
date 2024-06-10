<?php

use Illuminate\Support\Facades\Route;


Route::namespace('Auth')->group(function () {
    Route::controller('LoginController')->group(function () {
        Route::get('/', 'showLoginForm')->name('login');
        Route::post('/', 'login')->name('login');
        Route::get('logout', 'logout')->name('logout');
    });

    // Admin Password Reset
    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('reset');
        Route::post('reset', 'sendResetCodeEmail');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset.form');
        Route::post('password/reset/change', 'reset')->name('password.change');
    });
});

Route::middleware('admin')->group(function () {
    Route::controller('AdminController')->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');
        Route::get('password', 'password')->name('password');
        Route::post('password', 'passwordUpdate')->name('password.update');

        //Notification
        Route::get('notifications', 'notifications')->name('notifications');
        Route::get('notification/read/{id}', 'notificationRead')->name('notification.read');
        Route::get('notifications/read-all', 'readAll')->name('notifications.readAll');

        //Report Bugs
        Route::get('request-report', 'requestReport')->name('request.report');
        Route::post('request-report', 'reportSubmit');

        Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
    });

    // Users Manager
    Route::controller('ManageUsersController')->name('users.')->prefix('users')->group(function () {
        Route::get('/', 'allUsers')->name('all');
        Route::get('active', 'activeUsers')->name('active');
        Route::get('banned', 'bannedUsers')->name('banned');
        Route::get('email-verified', 'emailVerifiedUsers')->name('email.verified');
        Route::get('email-unverified', 'emailUnverifiedUsers')->name('email.unverified');
        Route::get('mobile-unverified', 'mobileUnverifiedUsers')->name('mobile.unverified');
        Route::get('mobile-verified', 'mobileVerifiedUsers')->name('mobile.verified');
        Route::get('with-coins', 'usersWithCoins')->name('with.coins');

        Route::get('detail/{id}', 'detail')->name('detail');
        Route::post('update/{id}', 'update')->name('update');
        Route::get('send-notification/{id}', 'showNotificationSingleForm')->name('notification.single');
        Route::post('send-notification/{id}', 'sendNotificationSingle')->name('notification.single');
        Route::post('status/{id}', 'status')->name('status');
        Route::post('account/delete/{id}', 'deleteAccount')->name('delete.account');

        Route::get('send-notification', 'showNotificationAllForm')->name('notification.all');
        Route::post('send-notification', 'sendNotificationAll')->name('notification.all.send');
        Route::get('notification-log/{id}', 'notificationLog')->name('notification.log');

        Route::get('send/promotional-notification', 'showPromotionalNotificationForm')->name('promotional.notification.all');
        Route::post('send-promotional-notification', 'sendPromotionalNotificationAll')->name('promotional.notification.all.send');
    });

    Route::get('leader-board', 'ManageUsersController@leaderBoard')->name('leader.board');

    // Deposit Gateway
    Route::name('gateway.')->prefix('gateway')->group(function () {

        // Automatic Gateway
        Route::controller('AutomaticGatewayController')->prefix('automatic')->name('automatic.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{code}', 'update')->name('update');
            Route::post('remove/{id}', 'remove')->name('remove');
            Route::post('status/{id}', 'status')->name('status');
        });


        // Manual Methods
        Route::controller('ManualGatewayController')->prefix('manual')->name('manual.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('new', 'create')->name('create');
            Route::post('new', 'store')->name('store');
            Route::get('edit/{alias}', 'edit')->name('edit');
            Route::post('update/{id}', 'update')->name('update');
            Route::post('status/{id}', 'status')->name('status');
        });
    });


    // DEPOSIT SYSTEM
    Route::controller('DepositController')->prefix('deposit')->name('deposit.')->group(function () {
        Route::get('/', 'deposit')->name('list');
        Route::get('pending', 'pending')->name('pending');
        Route::get('rejected', 'rejected')->name('rejected');
        Route::get('approved', 'approved')->name('approved');
        Route::get('successful', 'successful')->name('successful');
        Route::get('initiated', 'initiated')->name('initiated');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('reject', 'reject')->name('reject');
        Route::post('approve/{id}', 'approve')->name('approve');
    });

    // Report
    Route::controller('ReportController')->prefix('report')->name('report.')->group(function () {
        Route::get('login/history', 'loginHistory')->name('login.history');
        Route::get('login/ipHistory/{ip}', 'loginIpHistory')->name('login.ipHistory');
        Route::get('notification/history', 'notificationHistory')->name('notification.history');
        Route::get('email/detail/{id}', 'emailDetails')->name('email.details');
    });

    // Language Manager
    Route::controller('LanguageController')->prefix('language')->name('language.')->group(function () {
        Route::get('/', 'langManage')->name('manage');
        Route::post('/', 'langStore')->name('manage.store');
        Route::post('delete/{id}', 'langDelete')->name('manage.delete');
        Route::post('update/{id}', 'langUpdate')->name('manage.update');
        Route::get('edit/{id}', 'langEdit')->name('key');
        Route::post('import', 'langImport')->name('import.lang');
        Route::post('store/key/{id}', 'storeLanguageJson')->name('store.key');
        Route::post('delete/key/{id}', 'deleteLanguageJson')->name('delete.key');
        Route::post('update/key/{id}', 'updateLanguageJson')->name('update.key');
        Route::get('get-keys', 'getKeys')->name('get.key');
    });

    Route::controller('GeneralSettingController')->group(function () {
        // General Setting
        Route::get('general-setting', 'index')->name('setting.index');
        Route::post('general-setting', 'update')->name('setting.update');

        //configuration
        Route::get('setting/system-configuration', 'systemConfiguration')->name('setting.system.configuration');
        Route::post('setting/system-configuration', 'systemConfigurationSubmit');

        // Logo-Icon
        Route::get('setting/logo-icon', 'logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo-icon', 'logoIconUpdate')->name('setting.logo.icon');


        // quiz settings
        Route::get('setting/quiz', 'quizSetting')->name('setting.quiz');
        Route::post('setting/quiz', 'quizStore');

        Route::get('ads', 'ads')->name('ads');
        Route::post('ads/store', 'adsStore')->name('ads.store');
    });

    //Notification Setting
    Route::controller('NotificationController')->prefix('notification')->name('setting.notification.')->group(function () {
        //Template Setting
        Route::get('global', 'global')->name('global');
        Route::post('global/update', 'globalUpdate')->name('global.update');
        Route::get('templates', 'templates')->name('templates');
        Route::get('template/edit/{id}', 'templateEdit')->name('template.edit');
        Route::post('template/update/{id}', 'templateUpdate')->name('template.update');

        //Email Setting
        Route::get('email/setting', 'emailSetting')->name('email');
        Route::post('email/setting', 'emailSettingUpdate');
        Route::post('email/test', 'emailTest')->name('email.test');

        //SMS Setting
        Route::get('sms/setting', 'smsSetting')->name('sms');
        Route::post('sms/setting', 'smsSettingUpdate');
        Route::post('sms/test', 'smsTest')->name('sms.test');

        route::get('push', 'push')->name('push');
        route::post('push/store', 'pushStore')->name('push.store');
    });

    // Plugin
    Route::controller('ExtensionController')->prefix('extensions')->name('extensions.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('update/{id}', 'update')->name('update');
        Route::post('status/{id}', 'status')->name('status');
    });


    //System Information
    Route::controller('SystemController')->name('system.')->prefix('system')->group(function () {
        Route::get('info', 'systemInfo')->name('info');
        Route::get('server-info', 'systemServerInfo')->name('server.info');
        Route::get('optimize', 'optimize')->name('optimize');
        Route::get('optimize-clear', 'optimizeClear')->name('optimize.clear');
        Route::get('system-update', 'systemUpdate')->name('update');
        Route::post('update-upload', 'updateUpload')->name('update.upload');
    });

    // type
    Route::controller('QuizTypeController')->name('type.')->prefix('quiz-types')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::post('status/{id}', 'changeStatus')->name('status');
    });

    // category
    Route::controller('CategoryController')->prefix('category')->name('category.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'changeStatus')->name('status');
    });

    // subcategory
    Route::controller('SubCategoryController')->prefix('subcategory')->name('subcategory.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::post('status/{id}', 'changeStatus')->name('status');
    });

    // level
    Route::controller('LevelController')->prefix('level')->name('level.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
    });

    // daily quiz
    Route::controller('DailyQuizController')->prefix('daily-quiz/')->name('daily.quiz.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store/{id?}', 'store')->name('store');
        Route::get('details/{id}', 'dailyQuizDetails')->name('details');
        Route::get('question/create/{id}', 'addQuestion')->name('question.create');
        Route::get('question/edit/{id}/{quizInfo_id}', 'editQuestion')->name('question.edit');
        Route::get('question/import/{id}', 'questionImport')->name('question.import');
        Route::post('status/{id}', 'changeStatus')->name('status');
        Route::post('send/notification/{id}', 'sendNotification')->name('send.notification');
    });

    // fun
    Route::controller('FunController')->prefix('fun/')->name('fun.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('details/{id}', 'funDetails')->name('details');
        Route::get('question/create/{id}', 'addQuestion')->name('question.create');
        Route::get('question/edit/{id}/{quizInfo_id}', 'editQuestion')->name('question.edit');
        Route::post('status/{id}', 'changeStatus')->name('status');
        Route::post('send/notification/{id}', 'sendNotification')->name('send.notification');
    });



    // general quiz
    Route::controller('GeneralQuizController')->prefix('general-quiz')->name('general.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('form/{id}', 'addQuestion')->name('create');
        Route::get('edit/{id}/{quizInfo_id}', 'editQuestion')->name('edit');
        Route::get('list/{id}', 'questionList')->name('list');
        Route::post('status/{id}', 'changeStatus')->name('status');
        Route::get('question/import/{id}', 'questionImport')->name('question.import');
    });

    // guess word
    Route::controller('GuessWordController')->prefix('guess-word/')->name('guess.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('list/{id}', 'questionList')->name('list');
        Route::get('question/create/{id}', 'addQuestion')->name('question.create');
        Route::get('question/edit/{id}/{quizInfo_id}', 'editQuestion')->name('question.edit');
        Route::post('status/{id}', 'changeStatus')->name('status');
    });
    // contest
    Route::controller('ContestController')->prefix('contest/')->name('contest.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::get('details/{id}', 'contestDetails')->name('details');
        Route::get('question/create/{id}', 'addQuestion')->name('question.create');
        Route::get('question/edit/{id}/{quizInfo_id}', 'editQuestion')->name('question.edit');
        Route::get('question/import/{id}', 'questionImport')->name('question.import');
        Route::post('status/{id}', 'changeStatus')->name('status');
        Route::post('send/notification/{id}', 'sendNotification')->name('send.notification');
    });

    // exam
    Route::controller('ExamController')->prefix('exam/')->name('exam.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::get('details/{id}', 'examDetails')->name('details');
        Route::get('question/create/{id}', 'addQuestion')->name('question.create');
        Route::get('question/edit/{id}/{quizInfo_id}', 'editQuestion')->name('question.edit');
        Route::post('status/{id}', 'changeStatus')->name('status');
        Route::post('send/notification/{id}', 'sendNotification')->name('send.notification');
        Route::get('question/import/{id}', 'questionImport')->name('question.import');
    });

    // question
    Route::controller('QuestionController')->prefix('manage-questions')->name('question.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('add', 'add')->name('create');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('store', 'store')->name('store');
        Route::post('change/status/{id}', 'changeStatus')->name('status');
        Route::post('option/delete/{id}', 'optionDelete')->name('option.delete');
        Route::get('import/{id}', 'questionImport')->name('import');
        Route::post('import/update', 'questionImportUpdate')->name('import.update');
        Route::post('import/csv/question', 'importCsvQuestion')->name('csv.import');
    });

    Route::controller('CoinPlanController')->prefix('coin-plan')->name('coin.plan.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store/{id?}', 'store')->name('store');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('status/{id}', 'changeStatus')->name('status');
    });

    Route::name('frontend.')->prefix('frontend')->group(function () {
        Route::controller('FrontendController')->group(function () {
            Route::get('templates', 'templates')->name('templates');
            Route::post('templates', 'templatesActive')->name('templates.active');
            Route::get('frontend-sections/{key}', 'frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'frontendElement')->name('sections.element');
            Route::post('remove/{id}', 'remove')->name('remove');
        });
    });
});
