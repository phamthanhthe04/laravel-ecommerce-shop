<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
            'promotions' => Promotion::count(),
            'revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'savings' => $this->calculateTotalSavings(),
        ];

        $recentOrders = Order::with('user')->latest()->take(5)->get();

        // Dữ liệu biểu đồ doanh thu theo ngày (30 ngày gần đây)
        $dailyRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subDays(29))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Dữ liệu biểu đồ doanh thu theo tháng (12 tháng gần đây)
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Dữ liệu biểu đồ doanh thu theo quý (4 quý gần đây)
        $quarterlyRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subQuarters(3)->startOfQuarter())
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('QUARTER(created_at) as quarter'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('year', 'quarter')
            ->orderBy('year')
            ->orderBy('quarter')
            ->get();

        // Chuẩn bị dữ liệu cho biểu đồ
        $chartData = [
            'daily' => $this->prepareDailyChartData($dailyRevenue),
            'monthly' => $this->prepareMonthlyChartData($monthlyRevenue),
            'quarterly' => $this->prepareQuarterlyChartData($quarterlyRevenue)
        ];

        return view('admin.dashboard', compact('stats', 'recentOrders', 'chartData'));
    }

    public function users()
    {
        $users = User::paginate(15);
        return view('admin.users', compact('users'));
    }

    public function products()
    {
        $products = Product::with('category')->paginate(15);
        return view('admin.products', compact('products'));
    }

    public function orders()
    {
        $orders = Order::with('user')->latest()->paginate(15);
        return view('admin.orders', compact('orders'));
    }

    public function categories()
    {
        $categories = Category::withCount('products')->paginate(15);
        return view('admin.categories', compact('categories'));
    }

    public function promotions()
    {
        $promotions = Promotion::with('product')->latest()->paginate(15);
        return view('promotions.index', compact('promotions'));
    }

    /**
     * Chuẩn bị dữ liệu biểu đồ theo ngày
     */
    private function prepareDailyChartData($dailyRevenue)
    {
        $labels = [];
        $data = [];
        
        // Tạo danh sách 30 ngày gần đây
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d/m');
            
            // Tìm doanh thu cho ngày này
            $revenue = $dailyRevenue->where('date', $date)->first();
            $data[] = $revenue ? (float)$revenue->revenue : 0;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Chuẩn bị dữ liệu biểu đồ theo tháng
     */
    private function prepareMonthlyChartData($monthlyRevenue)
    {
        $labels = [];
        $data = [];
        
        // Tạo danh sách 12 tháng gần đây
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $labels[] = $date->format('m/Y');
            
            // Tìm doanh thu cho tháng này
            $revenue = $monthlyRevenue->where('year', $year)->where('month', $month)->first();
            $data[] = $revenue ? (float)$revenue->revenue : 0;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Chuẩn bị dữ liệu biểu đồ theo quý
     */
    private function prepareQuarterlyChartData($quarterlyRevenue)
    {
        $labels = [];
        $data = [];
        
        // Tạo danh sách 4 quý gần đây
        for ($i = 3; $i >= 0; $i--) {
            $date = Carbon::now()->subQuarters($i);
            $year = $date->year;
            $quarter = $date->quarter;
            $labels[] = "Q{$quarter}/{$year}";
            
            // Tìm doanh thu cho quý này
            $revenue = $quarterlyRevenue->where('year', $year)->where('quarter', $quarter)->first();
            $data[] = $revenue ? (float)$revenue->revenue : 0;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * API endpoint để lấy dữ liệu biểu đồ
     */
    public function getRevenueChartData($type)
    {
        switch ($type) {
            case 'daily':
                $data = Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', Carbon::now()->subDays(29))
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('SUM(total) as revenue')
                    )
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                return response()->json($this->prepareDailyChartData($data));

            case 'monthly':
                $data = Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
                    ->select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('SUM(total) as revenue')
                    )
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
                return response()->json($this->prepareMonthlyChartData($data));

            case 'quarterly':
                $data = Order::where('payment_status', 'paid')
                    ->where('created_at', '>=', Carbon::now()->subQuarters(3)->startOfQuarter())
                    ->select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('QUARTER(created_at) as quarter'),
                        DB::raw('SUM(total) as revenue')
                    )
                    ->groupBy('year', 'quarter')
                    ->orderBy('year')
                    ->orderBy('quarter')
                    ->get();
                return response()->json($this->prepareQuarterlyChartData($data));

            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Tính tổng tiết kiệm từ khuyến mãi
     */
    private function calculateTotalSavings()
    {
        $totalSavings = 0;
        
        // Lấy tất cả đơn hàng đã thanh toán
        $orders = Order::where('payment_status', 'paid')->with('orderItems.product')->get();
        
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $product = $item->product;
                if ($product) {
                    $originalPrice = $product->price;
                    $sellingPrice = $item->price; // Giá đã có khuyến mãi được lưu
                    $savings = ($originalPrice - $sellingPrice) * $item->quantity;
                    $totalSavings += max(0, $savings); // Chỉ tính tiết kiệm dương
                }
            }
        }
        
        return $totalSavings;
    }
}