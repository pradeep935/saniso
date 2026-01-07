{{-- Quote Notification System for Admin/Vendor --}}
@if(Auth::check())
<script src="{{ asset('vendor/core/plugins/ecommerce/js/quote-notifications.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification system with user context
    if (window.quoteNotificationSystem) {
        // Set user-specific settings
        window.quoteNotificationSystem.userType = '{{ Auth::user()->super_user || Auth::user()->manage_supers ? "admin" : "vendor" }}';
        window.quoteNotificationSystem.userId = {{ Auth::id() }};
        
        console.log('Quote notification system loaded for {{ Auth::user()->super_user || Auth::user()->manage_supers ? "admin" : "vendor" }}');
    }
});
</script>

{{-- Notification Sound File --}}
<audio id="quote-notification-sound" preload="auto" style="display: none;">
    <source src="{{ asset('vendor/core/plugins/ecommerce/sounds/notification.mp3') }}" type="audio/mpeg">
    <source src="{{ asset('vendor/core/plugins/ecommerce/sounds/notification.ogg') }}" type="audio/ogg">
</audio>

{{-- Notification Styles --}}
<style>
.quote-notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc3545;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
    display: none;
}

.quote-notification-toggle {
    position: relative;
}

.quote-notifications-container {
    max-height: 80vh;
    overflow-y: auto;
}

.quote-notification-item {
    transition: all 0.3s ease;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

@media (max-width: 768px) {
    .quote-notifications-container {
        left: 10px !important;
        right: 10px !important;
        max-width: none !important;
    }
}
</style>
@endif