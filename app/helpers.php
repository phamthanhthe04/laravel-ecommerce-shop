<?php

if (!function_exists('isUserAdmin')) {
    /**
     * Check if current user is admin
     */
    function isUserAdmin()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user && isset($user->role) && $user->role === 'admin';
    }
}

if (!function_exists('checkUserRole')) {
    /**
     * Check user role safely
     */
    function checkUserRole($role = 'admin')
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user && isset($user->role) && $user->role === $role;
    }
}