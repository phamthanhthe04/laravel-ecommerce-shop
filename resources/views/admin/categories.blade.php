@extends('layouts.admin')

@section('title', 'Quản lý danh mục')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý danh mục</h2>
    <a href="{{ route('admin.manage.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Thêm danh mục
    </a>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-tags me-2"></i>Danh sách danh mục</h5>
    </div>
    <div class="card-body">
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Số sản phẩm</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ Str::limit($category->description ?? 'Không có mô tả', 50) }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $category->products_count }}</span>
                                </td>
                                <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.manage.categories.edit', $category) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.manage.categories.destroy', $category) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $categories->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-tags fa-2x text-muted mb-2"></i>
                <p class="text-muted">Chưa có danh mục nào</p>
                <a href="{{ route('admin.manage.categories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm danh mục đầu tiên
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
