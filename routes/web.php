<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MockPaymentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\VNPayDebugController;
use App\Http\Controllers\VNPaySignatureTestController;
use App\Http\Controllers\VNPaySandboxController;
use App\Http\Controllers\VNPayDebugCompareController;
use App\Http\Controllers\PromotionController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Shop routes (public)
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

// Cart routes (require auth)
Route::middleware('auth')->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update/{productId}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{productId}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::match(['POST', 'PUT'], '/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

// User orders (require auth)
Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('orders.my');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Payment routes
Route::middleware('auth')->prefix('payment')->name('payment.')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::post('/vnpay', [PaymentController::class, 'vnpayPayment'])->name('vnpay');
    Route::post('/cod', [PaymentController::class, 'codPayment'])->name('cod');
});

// Mock Payment routes (Test only)
Route::middleware('auth')->prefix('mock-payment')->name('mock.payment.')->group(function () {
    Route::get('/{order}', [MockPaymentController::class, 'showPaymentForm'])->name('form');
    Route::post('/process', [MockPaymentController::class, 'processPayment'])->name('process');
    Route::post('/quick', [MockPaymentController::class, 'quickPayment'])->name('quick');
});

// VNPay callback (no auth required, but with logging)
Route::middleware('vnpay.logger')->group(function () {
    Route::get('/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('vnpay.return');
});

// Debug routes (remove in production)
Route::get('/debug/vnpay-test', function () {
    return view('debug.vnpay-test');
})->name('debug.vnpay.test');

Route::get('/debug/payment-status', function () {
    return view('debug.payment-status');
})->name('debug.payment.status');

Route::get('/debug/vnpay-guide', function () {
    return view('debug.vnpay-guide');
})->name('debug.vnpay.guide');

// VNPay Debug routes
Route::prefix('debug/vnpay')->name('debug.vnpay.')->group(function () {
    Route::get('/', [App\Http\Controllers\VNPayDebugController::class, 'index'])->name('index');
    Route::post('/generate-url', [App\Http\Controllers\VNPayDebugController::class, 'generateTestUrl'])->name('generate.url');
    Route::post('/validate-hash', [App\Http\Controllers\VNPayDebugController::class, 'validateHash'])->name('validate.hash');
    Route::get('/logs', [App\Http\Controllers\VNPayDebugController::class, 'getLogs'])->name('logs');
    Route::get('/test-connectivity', [App\Http\Controllers\VNPayDebugController::class, 'testConnectivity'])->name('test.connectivity');
    Route::any('/analyze-callback', [App\Http\Controllers\VNPayDebugController::class, 'analyzeCallback'])->name('analyze.callback');
});

// API for monitoring
Route::get('/api/order/{id}/status', function($id) {
    $order = \App\Models\Order::find($id);
    if (!$order) {
        return response()->json(['error' => 'Order not found'], 404);
    }
    
    return response()->json([
        'id' => $order->id,
        'total' => $order->total,
        'status' => $order->status,
        'payment_status' => $order->payment_status,
        'payment_method' => $order->payment_method,
        'updated_at' => $order->updated_at->toISOString()
    ]);
});

// Monitor page
Route::get('/debug/vnpay-monitor', function() {
    return view('debug.vnpay-monitor');
})->name('debug.vnpay.monitor');

// Admin routes (require admin role)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('/promotions', [AdminController::class, 'promotions'])->name('promotions.index');
    
    // API for chart data
    Route::get('/api/revenue-chart/{type}', [AdminController::class, 'getRevenueChartData'])->name('api.revenue.chart');
    
    // Resource routes for admin management
    Route::resource('manage-categories', CategoryController::class)->names([
        'index' => 'manage.categories.index',
        'create' => 'manage.categories.create',
        'store' => 'manage.categories.store',
        'edit' => 'manage.categories.edit',
        'update' => 'manage.categories.update',
        'destroy' => 'manage.categories.destroy',
    ])->except(['show']);
    
    Route::resource('manage-products', ProductController::class)->names([
        'index' => 'manage.products.index',
        'create' => 'manage.products.create',
        'store' => 'manage.products.store',
        'show' => 'manage.products.show',
        'edit' => 'manage.products.edit',
        'update' => 'manage.products.update',
        'destroy' => 'manage.products.destroy',
    ])->parameters(['manage-products' => 'product']);
    
    Route::resource('manage-orders', OrderController::class)->names([
        'show' => 'manage.orders.show',
        'update' => 'manage.orders.update',
        'destroy' => 'manage.orders.destroy',
    ])->only(['show', 'update', 'destroy'])->parameters(['manage-orders' => 'order']);
    
    // Admin payment management
    Route::put('/payments/{order}/cod-status', [PaymentController::class, 'updateCodStatus'])->name('payments.cod.update');
    
    // Promotion management routes (admin only)
    Route::resource('promotions', PromotionController::class);
    Route::patch('/promotions/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('promotions.toggle-status');
});

// Promotion API routes (public access for cart integration)
Route::prefix('api/promotions')->group(function () {
    Route::get('/active', [PromotionController::class, 'getActivePromotions'])->name('api.promotions.active');
    Route::post('/apply', [PromotionController::class, 'applyPromotion'])->name('api.promotions.apply');
});

// VNPay Debug Routes
Route::prefix('vnpay/debug')->group(function () {
    Route::get('/', [VNPayDebugController::class, 'index']);
    Route::get('/guide', [VNPayDebugController::class, 'guide']);
    Route::get('/monitor', [VNPayDebugController::class, 'monitor']);
    Route::get('/generate-test', [VNPayDebugController::class, 'generateTestUrl']);
    Route::get('/validate-hash', [VNPayDebugController::class, 'validateHash']);
    Route::get('/logs', [VNPayDebugController::class, 'getLogs']);
    Route::get('/analyze-callback', [VNPayDebugController::class, 'analyzeCallback']);
    Route::post('/create-test-order', [VNPayDebugController::class, 'createTestOrder']);
});

// VNPay Signature Test Routes
Route::prefix('vnpay/test-signature')->group(function () {
    Route::get('/', [VNPaySignatureTestController::class, 'testSignature']);
    Route::post('/validate-url', [VNPaySignatureTestController::class, 'validateFromUrl']);
});

// Simple test route
Route::get('/vnpay/test-simple', function() {
    return view('vnpay-test-simple');
});

// VNPay URL Demo route
Route::get('/vnpay/url-demo', function() {
    return view('vnpay-url-demo');
});

// API Routes for debug tools
Route::prefix('api')->group(function () {
    Route::get('/orders/latest', [VNPayDebugController::class, 'getLatestOrder']);
    Route::get('/orders/{id}/status', [VNPayDebugController::class, 'getOrderStatus']);
});

// VNPay Sandbox Routes
Route::prefix('vnpay-sandbox')->name('vnpay.sandbox.')->group(function () {
    Route::get('/', [VNPaySandboxController::class, 'showPaymentForm'])->name('form');
    Route::post('/create-payment', [VNPaySandboxController::class, 'createPayment'])->name('create');
    Route::get('/return', [VNPaySandboxController::class, 'handleReturn'])->name('return');
});

// VNPay Debug Compare Route
Route::get('/debug/vnpay-compare', [VNPayDebugCompareController::class, 'compareImplementations'])->name('debug.vnpay.compare');

// Test checkout route
Route::get('/test-checkout', function() {
    return view('cart.test-simple');
})->name('test.checkout.simple');