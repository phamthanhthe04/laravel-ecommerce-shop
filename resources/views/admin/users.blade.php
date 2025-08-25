@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
<h2 class="mb-4">Quản lý người dùng</h2>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-users me-2"></i>Danh sách người dùng</h5>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="badge bg-danger">admin</span>
                                    @else
                                        <span class="badge bg-primary">user</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                <p class="text-muted">Chưa có người dùng nào</p>
            </div>
        @endif
    </div>
</div>
@endsection
