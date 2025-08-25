@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Chi tiết danh mục</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('categories.index') }}"> Quay lại</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>ID:</strong>
                {{ $category->id }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Tên danh mục:</strong>
                {{ $category->name }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Ngày tạo:</strong>
                {{ $category->created_at ? $category->created_at->format('d/m/Y H:i:s') : '' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Ngày cập nhật:</strong>
                {{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i:s') : '' }}
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <h4>Sản phẩm trong danh mục này:</h4>
            @if($category->products && $category->products->count() > 0)
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($product->price, 0, ',', '.') }} VNĐ</td>
                                <td>
                                    <a class="btn btn-info btn-sm" href="{{ route('products.show', $product->id) }}">Xem</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Chưa có sản phẩm nào trong danh mục này.</p>
            @endif
        </div>
    </div>
</div>
@endsection
