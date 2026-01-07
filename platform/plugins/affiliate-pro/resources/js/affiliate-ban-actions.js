$(document).ready(function () {
    'use strict';

    // Handle confirm actions (ban/unban)
    $(document).on('click', '[data-bb-toggle="confirm-action"]', function (e) {
        e.preventDefault();
        
        const $this = $(this);
        const url = $this.attr('href');
        const message = $this.data('bb-message') || 'Are you sure?';
        
        // Check if Botble has a confirm method
        if (typeof Botble !== 'undefined' && typeof Botble.showConfirm === 'function') {
            Botble.showConfirm(message, () => {
                performAction($this, url);
            });
        } else if (typeof bootbox !== 'undefined') {
            // Use bootbox if available
            bootbox.confirm({
                message: message,
                buttons: {
                    confirm: {
                        label: 'Yes',
                        className: 'btn-primary'
                    },
                    cancel: {
                        label: 'No',
                        className: 'btn-secondary'
                    }
                },
                callback: function (result) {
                    if (result) {
                        performAction($this, url);
                    }
                }
            });
        } else {
            // Fallback to native confirm
            if (confirm(message)) {
                performAction($this, url);
            }
        }
    });
    
    function performAction($button, url) {
        // Show loading state
        $button.addClass('button-loading');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.error) {
                    if (typeof Botble !== 'undefined' && typeof Botble.showError === 'function') {
                        Botble.showError(data.message);
                    } else {
                        alert(data.message);
                    }
                } else {
                    if (typeof Botble !== 'undefined' && typeof Botble.showSuccess === 'function') {
                        Botble.showSuccess(data.message);
                    } else {
                        alert(data.message);
                    }
                    
                    // Reload the table if it exists
                    if (typeof window.LaravelDataTables !== 'undefined') {
                        // Find the table ID dynamically
                        const tableId = Object.keys(window.LaravelDataTables).find(key => key.includes('affiliate-table'));
                        if (tableId && window.LaravelDataTables[tableId]) {
                            window.LaravelDataTables[tableId].ajax.reload();
                        } else {
                            // Fallback: reload the page
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    } else if ($('.dataTable').length > 0) {
                        // Try to reload any DataTable on the page
                        $('.dataTable').DataTable().ajax.reload();
                    } else {
                        // Fallback: reload the page
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                }
            },
            error: function (xhr) {
                let errorMessage = 'An error occurred';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    errorMessage = xhr.statusText;
                }
                
                if (typeof Botble !== 'undefined' && typeof Botble.showError === 'function') {
                    Botble.showError(errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function () {
                $button.removeClass('button-loading');
            }
        });
    }
});