<?php

use App\Http\Controllers\Api\V1\AffiliateController;
use App\Http\Controllers\Api\V1\AffiliateTrackingController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CmsPageController;
use App\Http\Controllers\Api\V1\DashboardMetricsController;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    $ipmartAccount = $user->ipmart()->first(['plan_balance', 'proxyName', 'proxyPwd']);

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar_path' => $user->avatar_path,
        'avatar_url' => $user->avatar_path ? asset('storage/'.$user->avatar_path) : null,
        'skype_profile' => $user->skype_profile,
        'telegram_profile' => $user->telegram_profile,
        'facebook_profile' => $user->facebook_profile,
        'x_profile' => $user->x_profile,
        'youtube_profile' => $user->youtube_profile,
        'instagram_profile' => $user->instagram_profile,
        'balance' => $user->balance,
        'plan_balance' => $ipmartAccount?->plan_balance,
        'proxyName' => $ipmartAccount?->proxyName,
        'proxyPwd' => $ipmartAccount?->proxyPwd,
    ]);
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('api.v1.auth.forgot-password');
    Route::get('affiliate/track', [AffiliateTrackingController::class, 'track'])->name('api.v1.affiliate.track');
    Route::get('auth/github/redirect', [SocialAuthController::class, 'redirectToGithub'])->name('api.v1.auth.github.redirect');
    Route::get('auth/github/callback', [SocialAuthController::class, 'handleGithubCallback'])->name('api.v1.auth.github.callback');
    Route::get('auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('api.v1.auth.google.redirect');
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('api.v1.auth.google.callback');
    Route::get('auth/x/redirect', [SocialAuthController::class, 'redirectToX'])->name('api.v1.auth.x.redirect');
    Route::get('auth/x/callback', [SocialAuthController::class, 'handleXCallback'])->name('api.v1.auth.x.callback');
    Route::get('plans', [PlanController::class, 'index'])->name('api.v1.plans.index');
    Route::get('faq', [FaqController::class, 'index'])->name('api.v1.faq.index');
    Route::get('help-center/categories', [HelpCenterController::class, 'categories'])->name('api.v1.help-center.categories');
    Route::get('help-center/popular-searches', [HelpCenterController::class, 'popularSearches'])->name('api.v1.help-center.popular-searches');
    Route::get('help-center/articles', [HelpCenterController::class, 'articles'])->name('api.v1.help-center.articles');
    Route::get('help-center/articles/{slug}', [HelpCenterController::class, 'show'])->name('api.v1.help-center.articles.show');
    Route::get('posts', [PostController::class, 'index'])->name('api.v1.posts.index');
    Route::get('posts/{slug}', [PostController::class, 'show'])->name('api.v1.posts.show');
    Route::get('posts/{slug}/comments', [PostCommentController::class, 'index'])->name('api.v1.posts.comments.index');
    Route::get('cms-pages', [CmsPageController::class, 'index'])->name('api.v1.cms-pages.index');
    Route::get('cms-pages/{slug}', [CmsPageController::class, 'show'])->name('api.v1.cms-pages.show');
    Route::post('payments/webhooks/{provider}', [PaymentWebhookController::class, 'store'])->name('api.v1.payments.webhook');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('dashboard/metrics', [DashboardMetricsController::class, 'show'])->name('api.v1.dashboard.metrics');
        Route::get('orders', [OrderController::class, 'index'])->name('api.v1.orders.index');
        Route::get('wallet', [WalletController::class, 'show'])->name('api.v1.wallet.show');
        Route::get('wallet/history', [WalletController::class, 'history'])->name('api.v1.wallet.history');
        Route::post('profile', [ProfileController::class, 'update'])->name('api.v1.profile.update');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('api.v1.checkout.store');
        Route::get('notifications', [UserNotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::post('notifications/{notification}/read', [UserNotificationController::class, 'markAsRead'])->name('api.v1.notifications.read');
        Route::get('tickets', [TicketController::class, 'index'])->name('api.v1.tickets.index');
        Route::post('tickets', [TicketController::class, 'store'])->name('api.v1.tickets.store');
        Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('api.v1.tickets.show');
        Route::post('tickets/{ticket}/replies', [TicketController::class, 'reply'])->name('api.v1.tickets.reply');
        Route::post('tickets/{ticket}/close', [TicketController::class, 'close'])->name('api.v1.tickets.close');
        Route::post('posts/{slug}/comments', [PostCommentController::class, 'store'])->name('api.v1.posts.comments.store');
        Route::get('affiliate/dashboard', [AffiliateController::class, 'dashboard'])->name('api.v1.affiliate.dashboard');
        Route::get('affiliate/conversions', [AffiliateController::class, 'conversions'])->name('api.v1.affiliate.conversions');

        Route::prefix('ipmart')->group(function (): void {
            Route::get('proxy-options', [IPmartController::class, 'getProxyOptions'])->name('api.v1.ipmart.proxy-options');
            Route::get('static-products', [IPmartController::class, 'getStaticProducts'])->name('api.v1.ipmart.static-products');
            Route::get('static-ip-count', [IPmartController::class, 'getStaticIpCount'])->name('api.v1.ipmart.static-ip-count');
            Route::get('traffic-history', [IPmartController::class, 'getTrafficHistory'])->name('api.v1.ipmart.traffic-history');
        });
    });
});
