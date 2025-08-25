@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<!-- Dashboard Content -->
<h2 class="mb-4">Dashboard</h2>
            
<!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card admin-card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h4>{{ $stats['users'] }}</h4>
                            <p class="mb-0">Người dùng</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card admin-card">
                        <div class="card-body text-center">
                            <i class="fas fa-box fa-2x mb-2"></i>
                            <h4>{{ $stats['products'] }}</h4>
                            <p class="mb-0">Sản phẩm</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card admin-card">
                        <div class="card-body text-center">
                            <i class="fas fa-gift fa-2x mb-2 text-warning"></i>
                            <h4>{{ $stats['promotions'] ?? 0 }}</h4>
                            <p class="mb-0">Khuyến mãi</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card admin-card">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <h4>{{ $stats['orders'] }}</h4>
                            <p class="mb-0">Đơn hàng</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue & Savings Row -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-3">
                    <div class="card admin-card">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-2x mb-2 text-success"></i>
                            <h4>{{ number_format($stats['revenue'], 0, ',', '.') }}đ</h4>
                            <p class="mb-0">Doanh thu</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-3">
                    <div class="card admin-card">
                        <div class="card-body text-center">
                            <i class="fas fa-percentage fa-2x mb-2 text-danger"></i>
                            <h4>{{ number_format($stats['savings'] ?? 0, 0, ',', '.') }}đ</h4>
                            <p class="mb-0">Tiết kiệm từ KM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Charts -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-chart-line me-2"></i>Biểu đồ doanh thu</h5>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="chartType" id="daily" value="daily" checked>
                                <label class="btn btn-outline-primary btn-sm" for="daily">Theo ngày</label>

                                <input type="radio" class="btn-check" name="chartType" id="monthly" value="monthly">
                                <label class="btn btn-outline-primary btn-sm" for="monthly">Theo tháng</label>

                                <input type="radio" class="btn-check" name="chartType" id="quarterly" value="quarterly">
                                <label class="btn btn-outline-primary btn-sm" for="quarterly">Theo quý</label>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" style="height: 400px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock me-2"></i>Đơn hàng gần đây</h5>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->user->name }}</td>
                                            <td>{{ number_format($order->total, 0, ',', '.') }}đ</td>
                                            <td>
                                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'pending' ? 'warning' : 'info') }}">
                                                    @switch($order->payment_status)
                                                        @case('paid')
                                                            Đã thanh toán
                                                            @break
                                                        @case('pending')
                                                            Đang chờ
                                                            @break
                                                        @case('failed')
                                                            Thất bại
                                                            @break
                                                        @case('cancelled')
                                                            Đã hủy
                                                            @break
                                                        @default
                                                            {{ $order->payment_status }}
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Chưa có đơn hàng nào</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.orders') }}" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i>Xem tất cả đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu biểu đồ từ backend
    const chartData = @json($chartData);
    
    // Khởi tạo biểu đồ
    const ctx = document.getElementById('revenueChart').getContext('2d');
    let revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.daily.labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: chartData.daily.data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Doanh thu theo ngày (30 ngày gần đây)'
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 8
                }
            }
        }
    });

    // Xử lý thay đổi loại biểu đồ
    const chartTypeRadios = document.querySelectorAll('input[name="chartType"]');
    chartTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const type = this.value;
            let title = '';
            let data = [];
            let labels = [];

            switch(type) {
                case 'daily':
                    title = 'Doanh thu theo ngày (30 ngày gần đây)';
                    data = chartData.daily.data;
                    labels = chartData.daily.labels;
                    break;
                case 'monthly':
                    title = 'Doanh thu theo tháng (12 tháng gần đây)';
                    data = chartData.monthly.data;
                    labels = chartData.monthly.labels;
                    break;
                case 'quarterly':
                    title = 'Doanh thu theo quý (4 quý gần đây)';
                    data = chartData.quarterly.data;
                    labels = chartData.quarterly.labels;
                    break;
            }

            // Cập nhật dữ liệu biểu đồ
            revenueChart.data.labels = labels;
            revenueChart.data.datasets[0].data = data;
            revenueChart.options.plugins.title.text = title;
            revenueChart.update();
        });
    });
});
</script>
@endsection
