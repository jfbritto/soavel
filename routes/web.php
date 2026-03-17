<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Site;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── SITE PÚBLICO ──────────────────────────────────────────────────────────────

Route::get('/', [Site\VehicleController::class, 'home'])->name('site.home');

Route::prefix('estoque')->name('site.vehicles.')->group(function () {
    Route::get('/', [Site\VehicleController::class, 'index'])->name('index');
    Route::get('/{slug}', [Site\VehicleController::class, 'show'])->name('show');
});

Route::post('/contato', [Site\ContactController::class, 'store'])->name('site.contact.store');
Route::get('/sitemap.xml', [Site\SitemapController::class, 'index'])->name('sitemap');
Route::post('/interesse/{vehicle}', [Site\ContactController::class, 'interesse'])->name('site.interesse.store');

// ── ADMIN ─────────────────────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Onboarding (sem middleware de onboarding para evitar loop)
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [Admin\OnboardingController::class, 'index'])->name('index');
        Route::get('/step/{step}', [Admin\OnboardingController::class, 'step'])->name('step');
        Route::post('/step/{step}', [Admin\OnboardingController::class, 'save'])->name('save');
        Route::get('/complete', [Admin\OnboardingController::class, 'complete'])->name('complete');
        Route::get('/finish', [Admin\OnboardingController::class, 'finish'])->name('finish');
        Route::post('/ai-generate', [Admin\OnboardingController::class, 'aiGenerate'])->name('ai');

    });

    // Dashboard
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard')->middleware('onboarding');

    // Configurações (acessível mesmo sem onboarding completo)
    Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');

    // Minha Assinatura
    Route::get('billing', [Admin\BillingController::class, 'index'])->name('billing.index');

    // ── Rotas protegidas pelo onboarding ──────────────────────────────────
    Route::middleware('onboarding')->group(function () {

        // Veículos
        Route::resource('vehicles', Admin\VehicleController::class);
        Route::patch('vehicles/{vehicle}/destaque', [Admin\VehicleController::class, 'toggleDestaque'])->name('vehicles.toggleDestaque');
        Route::post('vehicles/suggest-features', [Admin\VehicleController::class, 'suggestFeatures'])->name('vehicles.suggestFeatures');
        Route::post('vehicles/review', [Admin\VehicleController::class, 'reviewVehicle'])->name('vehicles.review');

        // Fotos dos veículos
        Route::prefix('vehicles/{vehicle}/photos')->name('vehicles.photos.')->group(function () {
            Route::post('/', [Admin\VehiclePhotoController::class, 'store'])->name('store');
            Route::delete('/{photo}', [Admin\VehiclePhotoController::class, 'destroy'])->name('destroy');
            Route::patch('/{photo}/principal', [Admin\VehiclePhotoController::class, 'setPrincipal'])->name('principal');
            Route::post('/reorder', [Admin\VehiclePhotoController::class, 'reorder'])->name('reorder');
        });

        // Documentos dos veículos
        Route::prefix('vehicles/{vehicle}/documents')->name('vehicles.documents.')->group(function () {
            Route::post('/', [Admin\VehicleDocumentController::class, 'store'])->name('store');
            Route::delete('/{document}', [Admin\VehicleDocumentController::class, 'destroy'])->name('destroy');
            Route::get('/{document}/download', [Admin\VehicleDocumentController::class, 'download'])->name('download');
        });

        // Vendas
        Route::get('sales', [Admin\SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [Admin\SaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [Admin\SaleController::class, 'store'])->name('sales.store');
        Route::get('sales/{sale}', [Admin\SaleController::class, 'show'])->name('sales.show');
        Route::patch('sales/{sale}/status', [Admin\SaleController::class, 'updateStatus'])->name('sales.status');

        // Clientes (rotas específicas ANTES do resource para evitar conflito com {customer})
        Route::get('customers/cpf-check', [Admin\CustomerController::class, 'cpfCheck'])->name('customers.cpf-check');
        Route::post('customers/quick-store', [Admin\CustomerController::class, 'quickStore'])->name('customers.quick-store');
        Route::resource('customers', Admin\CustomerController::class);

        // Documentos do cliente
        Route::post('customers/{customer}/documents', [Admin\CustomerDocumentController::class, 'store'])->name('customers.documents.store');
        Route::delete('customers/{customer}/documents/{document}', [Admin\CustomerDocumentController::class, 'destroy'])->name('customers.documents.destroy');
        Route::get('customers/{customer}/documents/{document}/download', [Admin\CustomerDocumentController::class, 'download'])->name('customers.documents.download');

        // Leads
        Route::get('leads', [Admin\LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/create', [Admin\LeadController::class, 'create'])->name('leads.create');
        Route::post('leads', [Admin\LeadController::class, 'store'])->name('leads.store');
        Route::get('leads/{lead}/edit', [Admin\LeadController::class, 'edit'])->name('leads.edit');
        Route::put('leads/{lead}', [Admin\LeadController::class, 'update'])->name('leads.update');
        Route::delete('leads/{lead}', [Admin\LeadController::class, 'destroy'])->name('leads.destroy');
        Route::patch('leads/{lead}/status', [Admin\LeadController::class, 'updateStatus'])->name('leads.status');
        Route::patch('leads/{lead}/assign', [Admin\LeadController::class, 'assign'])->name('leads.assign');

        // FIPE (proxy)
        Route::prefix('fipe')->name('fipe.')->group(function () {
            Route::get('/marcas',                       [Admin\FipeController::class, 'marcas'])->name('marcas');
            Route::get('/modelos/{marca}',              [Admin\FipeController::class, 'modelos'])->name('modelos');
            Route::get('/anos/{marca}/{modelo}',        [Admin\FipeController::class, 'anos'])->name('anos');
            Route::get('/preco/{marca}/{modelo}/{ano}', [Admin\FipeController::class, 'preco'])->name('preco');
        });

        // Sócios
        Route::resource('partners', Admin\PartnerController::class)->except(['show']);
        Route::post('vehicles/{vehicle}/partners', [Admin\PartnerController::class, 'attachVehicle'])->name('vehicles.partners.attach');
        Route::delete('vehicles/{vehicle}/partners/{partner}', [Admin\PartnerController::class, 'detachVehicle'])->name('vehicles.partners.detach');

        // Despesas
        Route::resource('expenses', Admin\ExpenseController::class)->except(['show']);

        // Relatórios
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/financial', [Admin\ReportController::class, 'financial'])->name('financial');
            Route::get('/vehicles', [Admin\ReportController::class, 'vehicles'])->name('vehicles');
            Route::get('/financial/export', [Admin\ReportController::class, 'exportFinancial'])->name('financial.export');
        });

    }); // fim onboarding
});

// Redireciona /home para o admin dashboard
Route::get('/home', fn() => redirect()->route('admin.dashboard'))->name('home')->middleware('auth');


Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
