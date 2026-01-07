/**
 * Mollie POS Terminal Integration
 * Handles payment processing from POS interface
 */
window.MolliePosTerminal = (function() {
    let currentPaymentId = null;
    let paymentCheckInterval = null;
    let paymentTimeout = null;

    /**
     * Validate Mollie configuration
     */
    async function validateConfig() {
        try {
            const response = await fetch('/admin/mollie/pos/validate-config', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.message || 'Configuration validation failed');
            }

            return data.data;
        } catch (error) {
            console.error('Config validation error:', error);
            throw error;
        }
    }

    /**
     * Process payment via terminal
     */
    async function processPayment(orderId, paymentType = 'card', voucherCategories = [], terminalId = null) {
        try {
            const response = await fetch('/admin/mollie/pos/process-payment', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include',
                body: JSON.stringify({
                    order_id: orderId,
                    payment_type: paymentType,
                    voucher_categories: voucherCategories,
                    terminal_id: terminalId
                })
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.message || 'Payment processing failed');
            }

            currentPaymentId = data.data.payment_id;
            
            // Start status checking
            startPaymentStatusCheck(data.data.timeout || 120);

            return data.data;
        } catch (error) {
            console.error('Payment processing error:', error);
            throw error;
        }
    }

    /**
     * Check payment status
     */
    async function checkPaymentStatus(paymentId) {
        try {
            const response = await fetch(`/admin/mollie/pos/payment-status?payment_id=${paymentId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.message || 'Status check failed');
            }

            return data.data;
        } catch (error) {
            console.error('Status check error:', error);
            throw error;
        }
    }

    /**
     * Cancel payment
     */
    async function cancelPayment(paymentId) {
        try {
            const response = await fetch('/admin/mollie/pos/cancel-payment', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include',
                body: JSON.stringify({
                    payment_id: paymentId
                })
            });

            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.message || 'Cancellation failed');
            }

            // Stop status checking
            stopPaymentStatusCheck();

            return data.data;
        } catch (error) {
            console.error('Payment cancellation error:', error);
            throw error;
        }
    }

    /**
     * Start checking payment status periodically
     */
    function startPaymentStatusCheck(timeoutSeconds = 120) {
        if (!currentPaymentId) return;

        console.log('🕐 POS Terminal: Using centralized PaymentTimer for', timeoutSeconds, 'seconds');

        // Clear any existing intervals
        stopPaymentStatusCheck();

        // Use centralized timer if available
        if (window.PaymentTimer) {
            console.log('✅ POS Terminal: Using centralized PaymentTimer');
            window.PaymentTimer.configure(timeoutSeconds).start({
                onTick: function(timeLeft, formattedTime) {
                    // Terminal doesn't show visual timer, but can trigger events if needed
                    document.dispatchEvent(new CustomEvent('mollie-payment-timer', {
                        detail: { timeLeft, formattedTime }
                    }));
                },
                onTimeout: function() {
                    console.log('⏰ POS Terminal: Payment timeout via centralized timer');
                    stopPaymentStatusCheck();
                    // Payment timeout handled by centralized system
                }
            });
        } else {
            console.log('⚠️ POS Terminal: PaymentTimer not available, using local monitoring only');
        }

        // Check every 3 seconds
        paymentCheckInterval = setInterval(async () => {
            try {
                const status = await checkPaymentStatus(currentPaymentId);
                
                // Trigger status update event
                document.dispatchEvent(new CustomEvent('mollie-payment-status', {
                    detail: status
                }));

                // Stop checking if payment is completed or failed
                if (status.is_completed || status.is_failed) {
                    stopPaymentStatusCheck();
                    
                    // Trigger completion event
                    document.dispatchEvent(new CustomEvent('mollie-payment-complete', {
                        detail: {
                            success: status.is_completed,
                            status: status
                        }
                    }));
                }
            } catch (error) {
                console.error('Status check failed:', error);
                // Continue checking unless it's a critical error
            }
        }, 3000);

        // Set timeout for auto-cancellation
        paymentTimeout = setTimeout(async () => {
            try {
                await cancelPayment(currentPaymentId);
                
                document.dispatchEvent(new CustomEvent('mollie-payment-timeout', {
                    detail: { payment_id: currentPaymentId }
                }));
            } catch (error) {
                console.error('Auto-cancel failed:', error);
            }
        }, timeoutSeconds * 1000);
    }

    /**
     * Stop payment status checking
     */
    function stopPaymentStatusCheck() {
        if (paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
            paymentCheckInterval = null;
        }
        
        if (paymentTimeout) {
            clearTimeout(paymentTimeout);
            paymentTimeout = null;
        }
    }

    /**
     * Show payment UI (modal/dialog)
     */
    function showPaymentModal(orderData) {
        const modalHtml = `
            <div class="modal fade" id="molliePaymentModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-credit-card me-2"></i>
                                Mollie Terminal Payment
                            </h5>
                        </div>
                        <div class="modal-body text-center">
                            <div id="payment-progress">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Processing...</span>
                                </div>
                                <h5 id="payment-status-text">Sending payment to terminal...</h5>
                                <p id="payment-details" class="text-muted">
                                    Order: ${orderData.order_code || orderData.id}<br>
                                    Amount: €${parseFloat(orderData.amount || 0).toFixed(2)}
                                </p>
                                <div class="progress mb-3">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" style="width: 25%" id="payment-progress-bar"></div>
                                </div>
                                <div id="payment-timer" class="small text-muted"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="cancel-payment-btn" class="btn btn-danger">
                                Cancel Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        document.getElementById('molliePaymentModal')?.remove();
        
        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('molliePaymentModal'));
        modal.show();

        // Setup cancel button
        document.getElementById('cancel-payment-btn').addEventListener('click', async () => {
            if (currentPaymentId) {
                try {
                    await cancelPayment(currentPaymentId);
                } catch (error) {
                    console.error('Cancel failed:', error);
                }
            }
            modal.hide();
        });

        return modal;
    }

    /**
     * Update payment UI
     */
    function updatePaymentUI(status, timeRemaining = null) {
        const statusText = document.getElementById('payment-status-text');
        const progressBar = document.getElementById('payment-progress-bar');
        const timer = document.getElementById('payment-timer');

        if (statusText) {
            statusText.textContent = status.is_completed ? 'Payment completed successfully!' :
                                   status.is_failed ? 'Payment failed or was canceled' :
                                   'Waiting for customer to pay on terminal...';
        }

        if (progressBar) {
            const width = status.is_completed ? '100%' : 
                         status.is_failed ? '100%' : '50%';
            progressBar.style.width = width;
            progressBar.className = status.is_completed ? 'progress-bar bg-success' :
                                   status.is_failed ? 'progress-bar bg-danger' :
                                   'progress-bar progress-bar-striped progress-bar-animated';
        }

        if (timer && timeRemaining) {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            timer.textContent = `Time remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }

    // Public API
    return {
        validateConfig,
        processPayment,
        checkPaymentStatus,
        cancelPayment,
        showPaymentModal,
        updatePaymentUI,
        getCurrentPaymentId: () => currentPaymentId
    };
})();

// Auto-setup event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Listen for payment status updates
    document.addEventListener('mollie-payment-status', function(event) {
        console.log('Payment status update:', event.detail);
        MolliePosTerminal.updatePaymentUI(event.detail);
    });

    // Listen for payment completion
    document.addEventListener('mollie-payment-complete', function(event) {
        console.log('Payment completed:', event.detail);
        
        if (event.detail.success) {
            // Payment successful - could trigger order completion UI
            setTimeout(() => {
                document.getElementById('molliePaymentModal')?.querySelector('.btn-close')?.click();
                // Refresh POS interface or show success message
                location.reload(); // Or custom refresh function
            }, 2000);
        } else {
            // Payment failed - show error and close modal
            setTimeout(() => {
                document.getElementById('molliePaymentModal')?.querySelector('.btn-close')?.click();
            }, 3000);
        }
    });

    // Listen for payment timeout
    document.addEventListener('mollie-payment-timeout', function(event) {
        console.log('Payment timed out:', event.detail);
        // Auto-close modal and show timeout message
        document.getElementById('molliePaymentModal')?.querySelector('.btn-close')?.click();
    });
});