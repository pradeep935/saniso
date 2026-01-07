@extends('core/base::layouts.master')

@section('title', 'You\'re Offline')

@section('content')
    <div class="empty">
        <div class="empty-img">
            <img src="{{ asset('storage/logo.png') }}" alt="logo" height="96" onerror="this.style.display='none'">
        </div>
        <p class="empty-title">You're Offline</p>
        <p class="empty-subtitle text-secondary">It looks like you lost your internet connection. Please check your connection and try again.</p>
        <p class="empty-subtitle text-secondary"><button class="btn btn-primary" onclick="window.location.reload()">🔄 Try Again</button></p>
    </div>
@endsection
