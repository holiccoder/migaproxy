<?php

use App\Http\Controllers\Api\V1\AffiliateController;
use App\Http\Controllers\Api\V1\AffiliateTrackingController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CmsPageController;
use App\Http\Controllers\Api\V1\DashboardMetricsController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\HelpCenterController;
use App\Http\Controllers\Api\V1\IPmartController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\PostCommentController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SocialAuthController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\UserNotificationController;
use App\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/user', [ProfileController::class, 'show'])->middleware('auth:sanctum');

Route::prefix('v1')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('api.v1.auth.forgot-password');
    Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->whereNumber('id')
        ->name('verification.verify');

    Route::prefix('affiliate')->group(function (): void {
        Route::get('track', [AffiliateTrackingController::class, 'track'])->name('api.v1.affiliate.track');
    });

    Route::prefix('auth')->controller(SocialAuthController::class)->group(function (): void {
        Route::get('github/redirect', 'redirectToGithub')->name('api.v1.auth.github.redirect');
        Route::get('github/callback', 'handleGithubCallback')->name('api.v1.auth.github.callback');
        Route::get('google/redirect', 'redirectToGoogle')->name('api.v1.auth.google.redirect');
        Route::get('google/callback', 'handleGoogleCallback')->name('api.v1.auth.google.callback');
        Route::get('x/redirect', 'redirectToX')->name('api.v1.auth.x.redirect');
        Route::get('x/callback', 'handleXCallback')->name('api.v1.auth.x.callback');
    });

    Route::get('plans', [PlanController::class, 'index'])->name('api.v1.plans.index');
    Route::get('faq', [FaqController::class, 'index'])->name('api.v1.faq.index');

    Route::prefix('help-center')->controller(HelpCenterController::class)->group(function (): void {
        Route::get('categories', 'categories')->name('api.v1.help-center.categories');
        Route::get('popular-searches', 'popularSearches')->name('api.v1.help-center.popular-searches');
        Route::get('articles', 'articles')->name('api.v1.help-center.articles');
        Route::get('articles/{slug}', 'show')->name('api.v1.help-center.articles.show');
    });

    Route::prefix('posts')->controller(PostController::class)->group(function (): void {
        Route::get('/', 'index')->name('api.v1.posts.index');
        Route::get('{slug}', 'show')->name('api.v1.posts.show');
    });

    Route::get('posts/{slug}/comments', [PostCommentController::class, 'index'])->name('api.v1.posts.comments.index');

    Route::prefix('cms-pages')->controller(CmsPageController::class)->group(function (): void {
        Route::get('/', 'index')->name('api.v1.cms-pages.index');
        Route::get('{slug}', 'show')->name('api.v1.cms-pages.show');
    });

    Route::post('payments/webhooks/{provider}', [PaymentWebhookController::class, 'store'])->name('api.v1.payments.webhook');

    Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
        Route::get('dashboard/metrics', [DashboardMetricsController::class, 'show'])->name('api.v1.dashboard.metrics');
        Route::get('orders', [OrderController::class, 'index'])->name('api.v1.orders.index');

        Route::prefix('wallet')->controller(WalletController::class)->group(function (): void {
            Route::get('/', 'show')->name('api.v1.wallet.show');
            Route::get('history', 'history')->name('api.v1.wallet.history');
        });

        Route::post('profile', [ProfileController::class, 'update'])->name('api.v1.profile.update');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('api.v1.checkout.store');

        Route::prefix('notifications')->controller(UserNotificationController::class)->group(function (): void {
            Route::get('/', 'index')->name('api.v1.notifications.index');
            Route::post('{notification}/read', 'markAsRead')->name('api.v1.notifications.read');
        });

        Route::prefix('tickets')->controller(TicketController::class)->group(function (): void {
            Route::get('/', 'index')->name('api.v1.tickets.index');
            Route::post('/', 'store')->name('api.v1.tickets.store');
            Route::get('{ticket}', 'show')->name('api.v1.tickets.show');
            Route::post('{ticket}/replies', 'reply')->name('api.v1.tickets.reply');
            Route::post('{ticket}/close', 'close')->name('api.v1.tickets.close');
        });

        Route::post('posts/{slug}/comments', [PostCommentController::class, 'store'])->name('api.v1.posts.comments.store');

        Route::prefix('affiliate')->controller(AffiliateController::class)->group(function (): void {
            Route::get('dashboard', 'dashboard')->name('api.v1.affiliate.dashboard');
            Route::get('conversions', 'conversions')->name('api.v1.affiliate.conversions');
        });

        Route::prefix('ipmart')->controller(IPmartController::class)->group(function (): void {
            Route::get('proxy-options', 'getProxyOptions')->name('api.v1.ipmart.proxy-options');
            Route::get('proxy-states', 'getStates')->name('api.v1.ipmart.proxy-states');
            Route::get('proxy-cities', 'getCities')->name('api.v1.ipmart.proxy-cities');
            Route::post('change-proxy-password', 'changeProxyPassword')->name('api.v1.ipmart.change-proxy-password');
            Route::get('static-products', 'getStaticProducts')->name('api.v1.ipmart.static-products');
            Route::get('static-ip-count', 'getStaticIpCount')->name('api.v1.ipmart.static-ip-count');
            Route::get('traffic-history', 'getTrafficHistory')->name('api.v1.ipmart.traffic-history');
            Route::get('proxy-api-link', 'getProxyAPILink')->name('api.v1.ipmart.proxy-api-link');
        });
    });
});
