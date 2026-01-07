// Multi-Branch Inventory Main JavaScript

$(document).ready(function() {
    // Initialize Multi-Branch Inventory functionality
    MultiBranchInventory.init();
});

const MultiBranchInventory = {
    
    // Main initialization
    init: function() {
        this.initFormInteractions();
        this.initFilters();
        this.initTableInteractions();
        this.initExcelMode();
        this.initModals();
        this.initNotifications();
    },
    
    // Enhanced form interactions
    initFormInteractions: function() {
        $('.modern-form-control').on('focus', function() {
            $(this).closest('.form-group, .col-lg-3, .col-md-6').addClass('focused');
        }).on('blur', function() {
            $(this).closest('.form-group, .col-lg-3, .col-md-6').removeClass('focused');
        });
        
        // Auto-submit search with debounce
        let searchTimer;
        $('#search').on('input', function() {
            clearTimeout(searchTimer);
            const searchValue = $(this).val();
            
            searchTimer = setTimeout(function() {
                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#filterForm').submit();
                }
            }, 500);
        });
        
        // Auto-submit search form on enter
        $('#search').on('keypress', function(e) {
            if (e.which === 13) {
                $(this).closest('form').submit();
            }
        });
    },
    
    // Filter functionality
    initFilters: function() {
        // Auto-submit filters with loading state
        $('#branch_id, #stock_status').on('change', function() {
            const form = $(this).closest('form');
            const submitBtn = form.find('button[type="submit"]');
            
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
            submitBtn.prop('disabled', true);
            
            form.submit();
        });
    },
    
    // Table interactions
    initTableInteractions: function() {
        // Smooth animations for stats cards
        $('.stats-card').hover(
            function() { 
                $(this).css('transform', 'translateY(-8px)');
            },
            function() { 
                $(this).css('transform', 'translateY(0px)');
            }
        );

        // Enhanced table interactions
        $('.table-modern tbody tr').hover(
            function() {
                $(this).addClass('shadow-sm').css('transform', 'scale(1.01)');
            },
            function() {
                $(this).removeClass('shadow-sm').css('transform', 'scale(1)');
            }
        );
    },
    
    // Excel-like inline editing functionality
    initExcelMode: function() {
        let isExcelMode = false;
        let updateTimeout;

        $('#toggle-excel-mode').on('click', function() {
            isExcelMode = !isExcelMode;
            
            if (isExcelMode) {
                // Enable Excel mode
                $(this).removeClass('btn-success').addClass('btn-warning');
                $('#excel-mode-text').text('Disable Excel Mode');
                
                // Show input fields, hide display spans
                $('.quantity-cell').each(function() {
                    $(this).find('.quantity-display').addClass('d-none');
                    $(this).find('.quantity-input').removeClass('d-none');
                });
                
                // Show auto-save indicator
                if (!$('#auto-save-indicator').length) {
                    $('body').append('<div id=\"auto-save-indicator\" class=\"position-fixed notification-fixed d-none\"><div class=\"alert alert-success\"><i class=\"fas fa-sync-alt fa-spin me-2\"></i>Auto-saving...</div></div>');
                }
                
            } else {
                // Disable Excel mode
                $(this).removeClass('btn-warning').addClass('btn-success');
                $('#excel-mode-text').text('Enable Excel Mode');
                
                // Hide input fields, show display spans
                $('.quantity-cell').each(function() {
                    $(this).find('.quantity-display').removeClass('d-none');
                    $(this).find('.quantity-input').addClass('d-none');
                });
            }
        });

        // Auto-save functionality with 1-second delay
        $(document).on('input', '.quantity-input', function() {
            const $input = $(this);
            const $cell = $input.closest('.quantity-cell');
            const inventoryId = $cell.data('inventory-id');
            const field = $cell.data('field');
            const value = $input.val();
            
            // Clear previous timeout
            if (updateTimeout) {
                clearTimeout(updateTimeout);
            }
            
            // Set new timeout for 1 second delay
            updateTimeout = setTimeout(function() {
                MultiBranchInventory.updateInventoryField(inventoryId, field, value, $cell);
            }, 1000);
        });
    },
    
    // Update inventory field via AJAX
    updateInventoryField: function(inventoryId, field, value, $cell) {
        $('#auto-save-indicator').show();
        
        $.ajax({
            url: window.routes.updateQuantity,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                inventory_id: inventoryId,
                field: field,
                value: value
            },
            success: function(response) {
                if (response.success) {
                    // Update the display span with new value
                    const $display = $cell.find('.quantity-display');
                    $display.text(parseInt(value).toLocaleString());
                    
                    // Update badge color based on value and field
                    if (field === 'quantity_on_hand') {
                        $display.removeClass('bg-success bg-danger').addClass(value > 0 ? 'bg-success' : 'bg-danger');
                    } else if (field === 'quantity_available') {
                        $display.removeClass('bg-info bg-warning').addClass(value > 0 ? 'bg-info' : 'bg-warning');
                    }
                    
                    // Show success feedback
                    MultiBranchInventory.showAutoSaveSuccess();
                } else {
                    MultiBranchInventory.showAutoSaveError(response.message);
                }
            },
            error: function(xhr) {
                MultiBranchInventory.showAutoSaveError('Failed to update inventory');
            },
            complete: function() {
                $('#auto-save-indicator').hide();
            }
        });
    },
    
    // Auto-save feedback
    showAutoSaveSuccess: function() {
        const $indicator = $('#auto-save-indicator');
        $indicator.html('<div class="alert alert-success"><i class="fas fa-check me-2"></i>Saved!</div>').show();
        setTimeout(() => $indicator.hide(), 2000);
    },
    
    showAutoSaveError: function(message) {
        const $indicator = $('#auto-save-indicator');
        $indicator.html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' + message + '</div>').show();
        setTimeout(() => $indicator.hide(), 3000);
    },
    
    // Modal functionality
    initModals: function() {
        // Form validation for adjust stock modal
        $('#adjustStockForm').on('submit', function(e) {
            const quantity = parseInt($('#adjustmentQuantity').val());
            const type = $('#adjustmentType').val();
            const currentStock = parseInt($('#modalCurrentStock').text());

            if (type === 'subtract' && quantity > currentStock) {
                e.preventDefault();
                alert('Cannot subtract more than available stock!');
                return false;
            }

            if (quantity <= 0) {
                e.preventDefault();
                alert('Quantity must be greater than 0!');
                return false;
            }
        });

        // Form validation for transfer stock modal
        $('#transferStockForm').on('submit', function(e) {
            const quantity = parseInt($('#transferQuantity').val());
            const maxQuantity = parseInt($('#transferQuantity').attr('max'));

            if (quantity > maxQuantity) {
                e.preventDefault();
                alert('Cannot transfer more than available stock!');
                return false;
            }
        });
    },
    
    // Notification system
    initNotifications: function() {
        // Auto-remove notifications after 5 seconds
        $(document).on('click', '.alert .btn-close', function() {
            $(this).closest('.alert').fadeOut(() => $(this).remove());
        });
    },
    
    // Modal functions
    openAdjustStockModal: function(inventoryId, productName, currentStock) {
        if (!inventoryId) {
            alert('Please add this product to branch inventory first.');
            return;
        }
        
        $('#adjustStockForm').attr('action', window.routes.adjustStock.replace(':id', inventoryId));
        $('#modalProductName').text(productName);
        $('#modalCurrentStock').text(currentStock + ' units');
        
        // Fetch detailed stock information
        $.ajax({
            url: window.routes.inventoryDetails.replace(':id', inventoryId),
            method: 'GET',
            success: function(response) {
                if (response && response.product_name) {
                    $.ajax({
                        url: window.routes.inventoryDetails.replace(':id', response.product_id),
                        method: 'GET',
                        data: { branch_id: response.branch_id },
                        success: function(detailResponse) {
                            if (detailResponse.success) {
                                $('#modalEcommerceStock').text(detailResponse.data.ecommerce_quantity);
                                $('#modalBranchStock').text(detailResponse.data.branch_quantity);
                            }
                        }
                    });
                } else {
                    $('#modalEcommerceStock').text('-');
                    $('#modalBranchStock').text(currentStock);
                }
            },
            error: function() {
                $('#modalEcommerceStock').text('-');
                $('#modalBranchStock').text(currentStock);
            }
        });
        
        $('#adjustStockModal').modal('show');
        
        // Focus on first input
        setTimeout(() => {
            $('#adjustmentType').focus();
        }, 500);
    },
    
    openTransferModal: function(inventoryId, productName, availableStock) {
        // Get the branch ID and product ID from the inventory item
        $.get(window.routes.inventoryDetails.replace(':id', inventoryId), function(data) {
            $('#transferProductId').val(data.product_id);
            $('#transferFromBranch').val(data.branch_id);
            $('#transferProductName').text(productName);
            $('#transferAvailableStock').text(availableStock + ' units');
            $('#transferQuantity').attr('max', availableStock);
            
            // Remove current branch from transfer options
            $('#transferToBranch option').each(function() {
                $(this).prop('disabled', $(this).val() == data.branch_id);
            });
            
            $('#transferStockModal').modal('show');
        });
    },
    
    showInventoryDetails: function(inventoryId) {
        if (!inventoryId) {
            alert('This product is not in branch inventory yet.');
            return;
        }
        
        // Enhanced loading state
        const btn = $(`button[onclick="MultiBranchInventory.showInventoryDetails(${inventoryId})"]`);
        const originalHtml = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        // Navigate to details page
        setTimeout(() => {
            window.location.href = window.routes.inventoryShow.replace(':id', inventoryId);
        }, 500);
    },
    
    addToBranchInventory: function(productId, productName, branchId) {
        // Show confirmation dialog
        if (!confirm(`Add "${productName}" to branch inventory?`)) {
            return;
        }
        
        const btn = $(`button[onclick="MultiBranchInventory.addToBranchInventory(${productId}, '${productName}', ${branchId})"]`);
        const originalHtml = btn.html();
        
        // Show loading state
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Adding...').prop('disabled', true);
        
        // Make AJAX request to add product to branch inventory
        $.post(window.routes.addProductToBranch, {
            _token: $('meta[name="csrf-token"]').attr('content'),
            product_id: productId,
            branch_id: branchId,
            quantity_on_hand: 0,
            minimum_stock: 0
        })
        .done(function(response) {
            if (response.success) {
                // Show success message
                MultiBranchInventory.showNotification('success', response.message);
                
                // Reload the page to show updated inventory
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                MultiBranchInventory.showNotification('error', response.error || 'Failed to add product to inventory');
                btn.html(originalHtml).prop('disabled', false);
            }
        })
        .fail(function(xhr) {
            let errorMessage = 'Failed to add product to inventory';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            }
            
            MultiBranchInventory.showNotification('error', errorMessage);
            btn.html(originalHtml).prop('disabled', false);
        });
    },
    
    showNotification: function(type, message) {
        // Create and show notification
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show notification">
                <i class="fas ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 5000);
    }
};

// Global functions for backwards compatibility
window.openAdjustStockModal = MultiBranchInventory.openAdjustStockModal;
window.openTransferModal = MultiBranchInventory.openTransferModal;
window.showInventoryDetails = MultiBranchInventory.showInventoryDetails;
window.addToBranchInventory = MultiBranchInventory.addToBranchInventory;
window.showNotification = MultiBranchInventory.showNotification;