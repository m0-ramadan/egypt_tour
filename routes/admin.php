<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AttractionController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\ErrorController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;

use App\Http\Controllers\Admin\PackageCategoryController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PackagePriceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReadyTourController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoMetaController;
use App\Http\Controllers\Admin\SeoRedirectController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SocialMediaController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Admin\SubscribeController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes - Travel Website CMS
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware('translate.admin')->group(function () {
    Route::get('lang/{locale}', function (Request $request, string $locale) {
        $request->session()->put('admin_locale', 'en');

        return redirect()->back();
    })->name('lang.switch');

    /*
    |--------------------------------------------------------------------------
    | Guest Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'loginPage'])->name('login.page');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login');

        Route::get('forgot-password', [AdminAuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('forgot-password', [AdminAuthController::class, 'sendResetOtp'])->name('password.email');
        Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('reset-password', [AdminAuthController::class, 'resetPassword'])->name('password.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Authenticated Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:admin')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/admin', [AdminAuthController::class, 'home'])->name('index');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('visitors', [VisitorController::class, 'index'])->name('visitors.index');
        Route::get('visitors/chart', [VisitorController::class, 'chartData'])->name('visitors.chart');
        Route::get('dashboard/quick-stats', [VisitorController::class, 'quickStats'])->name('dashboard.quick-stats');
        Route::get('bookings/stats/{year?}', [BookingController::class, 'yearlyStats'])->name('bookings.yearly-stats');

        /*
        |--------------------------------------------------------------------------
        | Basic Setting Pages
        |--------------------------------------------------------------------------
        */
        Route::prefix('setting')->name('setting.')->group(function () {
            Route::get('pages', [SettingController::class, 'pages'])->name('pages');
            Route::get('edit', [SettingController::class, 'edit'])->name('edit');
            Route::post('update', [SettingController::class, 'update'])->name('update');
            Route::post('update-pages', [SettingController::class, 'updatepages'])->name('updatepages');
        });

        /*
        |--------------------------------------------------------------------------
        | Admins
        |--------------------------------------------------------------------------
        */
        Route::resource('admins', AdminController::class);

        Route::prefix('admins')->name('admins.')->group(function () {
            Route::post('check-email', [AdminController::class, 'checkEmail'])->name('check-email');
            Route::post('{admin}/toggle-status', [AdminController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('{admin}/reset-password', [AdminController::class, 'resetPassword'])->name('reset-password');
            Route::post('bulk/delete', [AdminController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('bulk/status', [AdminController::class, 'bulkStatus'])->name('bulk-status');
            Route::get('export/data', [AdminController::class, 'export'])->name('export');
        });

        /*
        |--------------------------------------------------------------------------
        | Roles & Permissions
        |--------------------------------------------------------------------------
        */
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('{role}', [RoleController::class, 'show'])->name('show');
            Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
            Route::get('{role}/permissions', [RoleController::class, 'permissions'])->name('permissions');
            Route::post('{role}/permissions', [RoleController::class, 'syncPermissions'])->name('permissions.sync');

            Route::get('assign/index', [RoleController::class, 'assignIndex'])->name('assign.index');
            Route::post('assign/store', [RoleController::class, 'assignRoles'])->name('assign.store');
        });

        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('create', [PermissionController::class, 'create'])->name('create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::post('generate', [PermissionController::class, 'generateForModule'])->name('generate');
            Route::get('{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
            Route::put('{permission}', [PermissionController::class, 'update'])->name('update');
            Route::delete('{permission}', [PermissionController::class, 'destroy'])->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Route::resource('users', UserController::class);

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('export', [UserController::class, 'export'])->name('export');
            Route::get('stats', [UserController::class, 'getStats'])->name('stats');
            Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('{user}/activities', [UserController::class, 'activities'])->name('activities');
        });

        /*
        |--------------------------------------------------------------------------
        | Localization
        |--------------------------------------------------------------------------
        */
        Route::prefix('languages')->name('languages.')->group(function () {
            Route::get('/', [LanguageController::class, 'index'])->name('index');
            Route::get('create', [LanguageController::class, 'create'])->name('create');
            Route::post('/', [LanguageController::class, 'store'])->name('store');
            Route::get('{language}/edit', [LanguageController::class, 'edit'])->name('edit');
            Route::put('{language}', [LanguageController::class, 'update'])->name('update');
            Route::delete('{language}', [LanguageController::class, 'destroy'])->name('destroy');

            Route::post('{language}/toggle', [LanguageController::class, 'toggle'])->name('toggle');
            Route::post('{language}/set-default', [LanguageController::class, 'setDefault'])->name('set-default');
            Route::post('toggle-all', [LanguageController::class, 'toggleAll'])->name('toggle-all');
        });

        Route::resource('translations', TranslationController::class)->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);

        Route::prefix('translations')->name('translations.')->group(function () {
            Route::get('model/{type}/{id}', [TranslationController::class, 'byModel'])->name('by-model');
            Route::post('bulk-update', [TranslationController::class, 'bulkUpdate'])->name('bulk-update');
        });

        /*
        |--------------------------------------------------------------------------
        | Geography / Destinations
        |--------------------------------------------------------------------------
        */
        Route::resource('countries', CountryController::class);
        Route::resource('cities', CityController::class);
        Route::resource('regions', RegionController::class);
        Route::resource('destinations', DestinationController::class);

        Route::prefix('countries')->name('countries.')->group(function () {
            Route::post('{country}/toggle-status', [CountryController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('{country}/destinations', [CountryController::class, 'destinations'])->name('destinations');
        });

        Route::prefix('cities')->name('cities.')->group(function () {
            Route::get('by-country/{country}', [CityController::class, 'byCountry'])->name('by-country');
            Route::post('{city}/toggle-status', [CityController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('regions')->name('regions.')->group(function () {
            Route::get('by-country/{country}', [RegionController::class, 'byCountry'])->name('by-country');
            Route::post('{region}/toggle-status', [RegionController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('destinations')->name('destinations.')->group(function () {
            Route::get('statistics', [DestinationController::class, 'statistics'])->name('statistics');
            Route::post('bulk-action', [DestinationController::class, 'bulkAction'])->name('bulk-action');
            Route::post('{destination}/toggle-status', [DestinationController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('{destination}/toggle-featured', [DestinationController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::post('{destination}/duplicate', [DestinationController::class, 'duplicate'])->name('duplicate');
        });

        /*
        |--------------------------------------------------------------------------
        | Packages
        |--------------------------------------------------------------------------
        */
        Route::resource('package-categories', PackageCategoryController::class);
        Route::resource('packages', PackageController::class);
        Route::resource('package-prices', PackagePriceController::class);

        Route::prefix('package')->name('packages.')->group(function () {
            Route::get('statistics', [PackageController::class, 'statistics'])->name('statistics');
            Route::post('bulk-action', [PackageController::class, 'bulkAction'])->name('bulk-action');
            Route::post('{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('{package}/toggle-featured', [PackageController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::post('{package}/duplicate', [PackageController::class, 'duplicate'])->name('duplicate');

            Route::get('create-with-ai', [PackageController::class, 'createWithAI'])->name('create-with-ai');
            Route::post('store-with-ai', [PackageController::class, 'storeWithAI'])->name('store-with-ai');
            Route::post('ai-enhance', [PackageController::class, 'enhanceWithAI'])->name('ai-enhance');
            Route::post('ai-generate-seo', [PackageController::class, 'generateSeoWithAI'])->name('ai-generate-seo');
            Route::post('ai-translate', [PackageController::class, 'translateWithAI'])->name('ai-translate');
        });

        Route::prefix('ai-translations')->name('ai-translations.')->group(function () {
            Route::post('translate-missing', [TranslationController::class, 'translateMissing'])->name('translate-missing');
            Route::post('translate-field', [TranslationController::class, 'translateField'])->name('translate-field');
        });

        Route::prefix('package-prices')->name('package-prices.')->group(function () {
            Route::get('by-package/{package}', [PackagePriceController::class, 'byPackage'])->name('by-package');
            Route::post('bulk-action', [PackagePriceController::class, 'bulkAction'])->name('bulk-action');
        });

        /*
        |--------------------------------------------------------------------------
        | Ready Tours / الرحلات الجاهزة
        |--------------------------------------------------------------------------
        */
        Route::prefix('ready-tours')->name('ready-tours.')->group(function () {
            Route::get('/', [ReadyTourController::class, 'index'])->name('index');
            Route::post('sync', [ReadyTourController::class, 'sync'])->name('sync');
            Route::get('sync-progress/{process}', [ReadyTourController::class, 'syncProgress'])->name('sync-progress');
            Route::post('{template}/import', [ReadyTourController::class, 'import'])->name('import');
            Route::get('import-progress/{process}', [ReadyTourController::class, 'importProgress'])->name('import-progress');
            Route::post('import-selected', [ReadyTourController::class, 'importSelected'])->name('import-selected');
            Route::post('import-all', [ReadyTourController::class, 'importAll'])->name('import-all');
        });

        /*
        |--------------------------------------------------------------------------
        | Booking / CRM
        |--------------------------------------------------------------------------
        */
        // Static CRM routes must be declared before resource wildcards.
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('export', [ClientController::class, 'export'])->name('export');
            Route::get('{client}/bookings', [ClientController::class, 'bookings'])->name('bookings');
            Route::get('{client}/inquiries', [ClientController::class, 'inquiries'])->name('inquiries');
            Route::post('{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('inquiries')->name('inquiries.')->group(function () {
            Route::get('statistics', [InquiryController::class, 'statistics'])->name('statistics');
            Route::post('bulk-action', [InquiryController::class, 'bulkAction'])->name('bulk-action');
            Route::post('{inquiry}/reply', [InquiryController::class, 'reply'])->name('reply');
            Route::post('{inquiry}/update-status', [InquiryController::class, 'updateStatus'])->name('update-status');
        });

        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('statistics', [BookingController::class, 'statistics'])->name('statistics');
            Route::post('bulk-action', [BookingController::class, 'bulkAction'])->name('bulk-action');
            Route::post('{booking}/update-status', [BookingController::class, 'updateStatus'])->name('update-status');
            Route::get('{booking}/print', [BookingController::class, 'print'])->name('print');
        });

        Route::resource('clients', ClientController::class);
        Route::resource('inquiries', InquiryController::class);
        Route::resource('bookings', BookingController::class);
        Route::resource('communications', CommunicationController::class)->only([
            'index',
            'show',
            'store',
            'destroy',
        ]);

        Route::prefix('communications')->name('communications.')->group(function () {
            Route::get('client/{client}', [CommunicationController::class, 'clientCommunications'])->name('client');
            Route::get('inquiry/{inquiry}', [CommunicationController::class, 'inquiryCommunications'])->name('inquiry');
            Route::get('booking/{booking}', [CommunicationController::class, 'bookingCommunications'])->name('booking');
            Route::post('{communication}/mark-sent', [CommunicationController::class, 'markSent'])->name('mark-sent');
        });

        /*
        |--------------------------------------------------------------------------
        | Payments
        |--------------------------------------------------------------------------
        */
        // Static payment routes must be declared before resource wildcards.
        Route::patch('payment-methods/{paymentMethod}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])
            ->name('payment-methods.toggle-status');

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('statistics', [PaymentController::class, 'statistics'])->name('statistics');
            Route::get('export', [PaymentController::class, 'export'])->name('export');
            Route::post('{payment}/update-status', [PaymentController::class, 'updateStatus'])->name('update-status');
            Route::post('{payment}/refund', [PaymentController::class, 'refund'])->name('refund');
            Route::post('{payment}/reconcile', [PaymentController::class, 'reconcile'])->name('reconcile');
        });

        Route::resource('payment-methods', PaymentMethodController::class);
        Route::resource('payments', PaymentController::class);

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */
        Route::resource('articles', ArticleController::class);
        Route::resource('static-pages', StaticPageController::class);
        Route::resource('faqs', FaqController::class);
        Route::resource('testimonials', TestimonialController::class)->except(['create', 'edit']);

        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/sync', [MediaController::class, 'sync'])->name('sync');
            Route::get('/sync-progress', [MediaController::class, 'syncProgress'])->name('sync-progress');
        });



        Route::prefix('article')->name('articles.')->group(function () {
            Route::get('statistics', [ArticleController::class, 'statistics'])->name('statistics');
            Route::post('bulk-actions', [ArticleController::class, 'bulkActions'])->name('bulk-actions');
            Route::patch('{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('{article}/toggle-featured', [ArticleController::class, 'toggleFeatured'])->name('toggle-featured');

            Route::get('create-with-ai', [ArticleController::class, 'createWithAI'])->name('create-with-ai');
            Route::post('store-with-ai', [ArticleController::class, 'storeWithAI'])->name('store-with-ai');
            Route::post('ai-enhance', [ArticleController::class, 'enhanceWithAI'])->name('ai-enhance');
            Route::post('ai-generate', [ArticleController::class, 'generateWithAI'])->name('ai-generate');
            Route::post('ai-generate-full', [ArticleController::class, 'generateFullArticle'])->name('ai-generate-full');
            Route::post('ai-generate-title', [ArticleController::class, 'generateTitle'])->name('ai-generate-title');
            Route::post('ai-generate-content', [ArticleController::class, 'generateContent'])->name('ai-generate-content');
            Route::post('ai-enhance-content', [ArticleController::class, 'enhanceContent'])->name('ai-enhance-content');
            Route::post('ai-generate-excerpt', [ArticleController::class, 'generateExcerpt'])->name('ai-generate-excerpt');
            Route::post('ai-translate-all', [ArticleController::class, 'translateAll'])->name('ai-translate-all');
            Route::post('ai-improve-all', [ArticleController::class, 'improveAll'])->name('ai-improve-all');
            Route::post('ai-generate-meta-title', [ArticleController::class, 'generateMetaTitle'])->name('ai-generate-meta-title');
            Route::post('ai-generate-meta-description', [ArticleController::class, 'generateMetaDescription'])->name('ai-generate-meta-description');
            Route::post('ai-generate-keywords', [ArticleController::class, 'generateKeywords'])->name('ai-generate-keywords');
        });

        Route::prefix('static-pages')->name('static-pages.')->group(function () {
            Route::post('bulk-action', [StaticPageController::class, 'bulkAction'])->name('bulk-action');
            Route::get('{page}/edit-with-ai', [StaticPageController::class, 'editWithAI'])->name('edit-with-ai');

            Route::prefix('ai')->name('ai.')->group(function () {
                Route::post('enhance-title', [StaticPageController::class, 'enhanceTitleWithAI'])->name('enhance-title');
                Route::post('translate-content', [StaticPageController::class, 'translateContentWithAI'])->name('translate-content');
                Route::post('translate', [StaticPageController::class, 'translateWithAI'])->name('translate');
                Route::post('enhance-content', [StaticPageController::class, 'enhanceContentWithAI'])->name('enhance-content');
                Route::post('expand-content', [StaticPageController::class, 'expandContentWithAI'])->name('expand-content');
                Route::post('simplify-content', [StaticPageController::class, 'simplifyContentWithAI'])->name('simplify-content');
                Route::post('load-template', [StaticPageController::class, 'loadTemplateWithAI'])->name('load-template');
                Route::post('generate-from-prompt', [StaticPageController::class, 'generateFromPromptWithAI'])->name('generate-from-prompt');
                Route::post('generate-page', [StaticPageController::class, 'generatePageWithAI'])->name('generate-page');
                Route::post('generate-title', [StaticPageController::class, 'generateTitleWithAI'])->name('generate-title');
                Route::post('generate-content', [StaticPageController::class, 'generateContentWithAI'])->name('generate-content');
                Route::post('format-content', [StaticPageController::class, 'formatContentWithAI'])->name('format-content');
                Route::post('check-grammar', [StaticPageController::class, 'checkGrammarWithAI'])->name('check-grammar');
                Route::post('enhance-text', [StaticPageController::class, 'enhanceTextWithAI'])->name('enhance-text');
                Route::post('add-section', [StaticPageController::class, 'addSectionWithAI'])->name('add-section');
                Route::post('generate-meta-title', [StaticPageController::class, 'generateMetaTitleWithAI'])->name('generate-meta-title');
                Route::post('generate-meta-description', [StaticPageController::class, 'generateMetaDescriptionWithAI'])->name('generate-meta-description');
                Route::post('generate-keywords', [StaticPageController::class, 'generateKeywordsWithAI'])->name('generate-keywords');
            });
        });

        Route::prefix('faqs')->name('faqs.')->group(function () {
            Route::post('{faq}/toggle-status', [FaqController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('bulk-action', [FaqController::class, 'bulkAction'])->name('bulk-action');
        });

        Route::resource('attractions', AttractionController::class);
        Route::prefix('attractions')->name('attractions.')->group(function () {
            Route::get('statistics', [AttractionController::class, 'statistics'])->name('statistics');
            Route::post('bulk-actions', [AttractionController::class, 'bulkActions'])->name('bulk-actions');
            Route::post('quick-store', [AttractionController::class, 'quickStore'])->name('quick-store');
            Route::patch('{attraction}/toggle-status', [AttractionController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('testimonials')->name('testimonials.')->group(function () {
            Route::post('{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('{testimonial}/toggle-featured', [TestimonialController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::post('bulk-action', [TestimonialController::class, 'bulkAction'])->name('bulk-action');
        });

        /*
        |--------------------------------------------------------------------------
        | Menus
        |--------------------------------------------------------------------------
        */
        Route::resource('menus', MenuController::class);

        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('{menu}/items', [MenuController::class, 'items'])->name('items');
            Route::post('{menu}/items', [MenuController::class, 'storeItem'])->name('items.store');
            Route::put('items/{item}', [MenuController::class, 'updateItem'])->name('items.update');
            Route::delete('items/{item}', [MenuController::class, 'destroyItem'])->name('items.destroy');
            Route::post('items/reorder', [MenuController::class, 'reorderItems'])->name('items.reorder');
        });

        /*
        |--------------------------------------------------------------------------
        | SEO
        |--------------------------------------------------------------------------
        */
        Route::resource('seo-meta', SeoMetaController::class)->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);

        Route::resource('seo-redirects', SeoRedirectController::class);

        Route::prefix('seo-meta')->name('seo-meta.')->group(function () {
            Route::get('model/{type}/{id}', [SeoMetaController::class, 'byModel'])->name('by-model');
            Route::post('bulk-update', [SeoMetaController::class, 'bulkUpdate'])->name('bulk-update');
        });

        Route::prefix('seo-redirects')->name('seo-redirects.')->group(function () {
            Route::post('{seoRedirect}/toggle-status', [SeoRedirectController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('bulk-action', [SeoRedirectController::class, 'bulkAction'])->name('bulk-action');
        });

        /*
        |--------------------------------------------------------------------------
        | Contact / Social / Newsletter
        |--------------------------------------------------------------------------
        */
        Route::prefix('contact-us')->name('contact-us.')->group(function () {
            Route::get('/', [ContactUsController::class, 'index'])->name('index');
            Route::get('{contactUs}', [ContactUsController::class, 'show'])->name('show');
            Route::post('{contactUs}/reply', [ContactUsController::class, 'reply'])->name('reply');
            Route::post('{contactUs}/status', [ContactUsController::class, 'updateStatus'])->name('status');
            Route::delete('{contactUs}', [ContactUsController::class, 'destroy'])->name('destroy');
            Route::post('bulk/status', [ContactUsController::class, 'bulkStatus'])->name('bulk-status');
            Route::post('bulk/destroy', [ContactUsController::class, 'bulkDestroy'])->name('bulk-destroy');
        });

        Route::prefix('subscriptions')->name('subscribe.')->group(function () {
            Route::get('/', [SubscribeController::class, 'index'])->name('index');
            Route::delete('{subscription}', [SubscribeController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('social-media')->name('social-media.')->group(function () {
            Route::get('/', [SocialMediaController::class, 'index'])->name('index');
            Route::get('{id}/edit', [SocialMediaController::class, 'edit'])->name('edit');
            Route::put('{id}', [SocialMediaController::class, 'update'])->name('update');
            Route::get('create', [SocialMediaController::class, 'create'])->name('create');
            Route::post('store', [SocialMediaController::class, 'store'])->name('store');
            Route::post('bulk-update', [SocialMediaController::class, 'bulkUpdate'])->name('bulk-update');
        });

        /*
        |--------------------------------------------------------------------------
        | System Settings
        |--------------------------------------------------------------------------
        */
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');

            Route::prefix('general')->group(function () {
                Route::get('/', [SettingsController::class, 'general'])->name('general');
                Route::put('/', [SettingsController::class, 'updateGeneral'])->name('general.update');
            });

            Route::prefix('smtp')->group(function () {
                Route::get('/', [SettingsController::class, 'smtp'])->name('smtp');
                Route::put('/', [SettingsController::class, 'updateSmtp'])->name('smtp.update');
                Route::post('test', [SettingsController::class, 'testSmtp'])->name('smtp.test');
            });

            Route::prefix('communication')->group(function () {
                Route::get('/', [SettingsController::class, 'communication'])->name('communication');
                Route::put('/', [SettingsController::class, 'updateCommunication'])->name('communication.update');
            });

            Route::prefix('files')->group(function () {
                Route::get('/', [SettingsController::class, 'files'])->name('files');
                Route::put('/', [SettingsController::class, 'updateFiles'])->name('files.update');
                Route::delete('/', [SettingsController::class, 'deleteFile'])->name('files.delete');
                Route::post('clear-temp', [SettingsController::class, 'clearTempFiles'])->name('files.clear-temp');
            });

            Route::get('storage-usage', [SettingsController::class, 'getStorageUsage'])->name('storage-usage');
            Route::get('quick-stats', [SettingsController::class, 'getQuickStats'])->name('quick-stats');
            Route::get('recent-activities', [SettingsController::class, 'getRecentActivitiesAjax'])->name('recent-activities');
            Route::get('system-status', [SettingsController::class, 'getSystemStatus'])->name('system-status');
            Route::post('clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
            Route::post('toggle-maintenance', [SettingsController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        });

        /*
        |--------------------------------------------------------------------------
        | Error Logs
        |--------------------------------------------------------------------------
        */
        Route::prefix('errors')->name('errors.')->group(function () {
            Route::get('/', [ErrorController::class, 'index'])->name('index');
            Route::get('php-errors', [ErrorController::class, 'phpErrors'])->name('php-errors');
            Route::get('search', [ErrorController::class, 'search'])->name('search');
            Route::get('download/{filename}', [ErrorController::class, 'download'])->name('download');
            Route::delete('destroy', [ErrorController::class, 'destroy'])->name('destroy');
            Route::post('clear-all', [ErrorController::class, 'clearAll'])->name('clear-all');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Public Stats / Reports
|--------------------------------------------------------------------------
*/
Route::get('/orders/stats/{year}', [VisitorController::class, 'ordersStats']);
