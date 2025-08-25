@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Test Checkout Form
                </div>
                <div class="card-body">
                    <form action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="selected_products[]" value="1">
                        <button type="submit" class="btn btn-primary">Test Checkout</button>
                    </form>
                    
                    <hr>
                    
                    <p><strong>Route Info:</strong></p>
                    <p>URL: {{ route('cart.checkout') }}</p>
                    <p>Method: POST</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
