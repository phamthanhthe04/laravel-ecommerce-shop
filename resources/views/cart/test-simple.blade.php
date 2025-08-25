@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Test Checkout Form</h2>
    
    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif
    
    @if ($message = Session::get('error'))
        <div class="alert alert-danger">{{ $message }}</div>
    @endif
    
    <form action="{{ route('cart.checkout') }}" method="POST">
        @csrf
        <input type="hidden" name="selected_products[]" value="7">
        <button type="submit" class="btn btn-primary">Test Checkout</button>
    </form>
    
    <hr>
    
    <p><strong>Form action:</strong> {{ route('cart.checkout') }}</p>
    <p><strong>CSRF Token:</strong> {{ csrf_token() }}</p>
    <p><strong>Current User:</strong> {{ Auth::check() ? Auth::user()->name : 'Not logged in' }}</p>
</div>
@endsection
