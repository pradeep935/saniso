/**
 * Centralized Payment Timer System
 * Single source of truth for all payment timeouts
 */
window.PaymentTimer = {
    // Timer configuration
    TIMEOUT_SECONDS: 120, // 2 minutes
    
    // Timer state
    currentTimer: null,
    timeLeft: 0,
    isRunning: false,
    callbacks: {
        onTick: [],
        onTimeout: [],
        onStop: []
    },
    
    /**
     * Start the payment timer
     * @param {Object} options - Configuration options
     * @param {Function} options.onTick - Called every second with timeLeft
     * @param {Function} options.onTimeout - Called when timer expires
     * @param {Function} options.onStop - Called when timer is stopped
     */
    start: function(options = {}) {
        console.log('🕒 PaymentTimer: Starting timer for', this.TIMEOUT_SECONDS, 'seconds');
        
        // Stop any existing timer
        this.stop();
        
        // Reset timer
        this.timeLeft = this.TIMEOUT_SECONDS;
        this.isRunning = true;
        
        // Register callbacks
        if (options.onTick) this.callbacks.onTick.push(options.onTick);
        if (options.onTimeout) this.callbacks.onTimeout.push(options.onTimeout);
        if (options.onStop) this.callbacks.onStop.push(options.onStop);
        
        // Start countdown
        this.currentTimer = setInterval(() => {
            this.timeLeft--;
            
            // Notify all tick callbacks
            this.callbacks.onTick.forEach(callback => {
                try {
                    callback(this.timeLeft, this.getFormattedTime());
                } catch (error) {
                    console.error('PaymentTimer tick callback error:', error);
                }
            });
            
            // Check if expired
            if (this.timeLeft <= 0) {
                console.log('⏰ PaymentTimer: Timer expired!');
                this.handleTimeout();
            }
        }, 1000);
        
        // Initial tick
        this.callbacks.onTick.forEach(callback => {
            try {
                callback(this.timeLeft, this.getFormattedTime());
            } catch (error) {
                console.error('PaymentTimer initial tick callback error:', error);
            }
        });
        
        return this;
    },
    
    /**
     * Stop the timer
     */
    stop: function() {
        if (this.currentTimer) {
            clearInterval(this.currentTimer);
            this.currentTimer = null;
        }
        
        if (this.isRunning) {
            console.log('🛑 PaymentTimer: Timer stopped');
            this.isRunning = false;
            
            // Notify stop callbacks
            this.callbacks.onStop.forEach(callback => {
                try {
                    callback();
                } catch (error) {
                    console.error('PaymentTimer stop callback error:', error);
                }
            });
        }
        
        // Clear callbacks
        this.clearCallbacks();
        
        return this;
    },
    
    /**
     * Handle timer timeout
     */
    handleTimeout: function() {
        this.isRunning = false;
        
        if (this.currentTimer) {
            clearInterval(this.currentTimer);
            this.currentTimer = null;
        }
        
        // Notify timeout callbacks
        this.callbacks.onTimeout.forEach(callback => {
            try {
                callback();
            } catch (error) {
                console.error('PaymentTimer timeout callback error:', error);
            }
        });
        
        // Clear callbacks
        this.clearCallbacks();
    },
    
    /**
     * Get formatted time string (MM:SS)
     */
    getFormattedTime: function() {
        const minutes = Math.floor(this.timeLeft / 60);
        const seconds = this.timeLeft % 60;
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    },
    
    /**
     * Get progress percentage (0-100)
     */
    getProgress: function() {
        return (this.timeLeft / this.TIMEOUT_SECONDS) * 100;
    },
    
    /**
     * Check if timer is running
     */
    isActive: function() {
        return this.isRunning && this.timeLeft > 0;
    },
    
    /**
     * Get remaining time
     */
    getTimeLeft: function() {
        return this.timeLeft;
    },
    
    /**
     * Clear all callbacks
     */
    clearCallbacks: function() {
        this.callbacks.onTick = [];
        this.callbacks.onTimeout = [];
        this.callbacks.onStop = [];
    },
    
    /**
     * Reset timer configuration
     */
    configure: function(timeoutSeconds) {
        this.TIMEOUT_SECONDS = timeoutSeconds;
        console.log('⚙️ PaymentTimer: Configured for', timeoutSeconds, 'seconds');
        return this;
    }
};

// Auto-cleanup on page unload
$(window).on('beforeunload', function() {
    if (window.PaymentTimer) {
        window.PaymentTimer.stop();
    }
});

console.log('✅ PaymentTimer: Centralized timer system loaded');