<div
    class="modal fade"
    id="product-quick-view-modal"
    aria-labelledby="product-quick-view-label"
    aria-hidden="true"
    tabindex="-1"
>
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
        <div class="modal-content position-relative">
            <button
                class="btn-close"
                data-bs-dismiss="modal"
                type="button"
                aria-label="Close"
            ></button>
            <div class="modal-body">
                <div class="product-modal-content py-5">

                </div>
            </div>
        </div>
    </div>
    <div class="modal-loading"></div>
</div>

<style>
/* Quick View Modal Specific Styles */
#product-quick-view-modal .product-button {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-wrap: nowrap !important;
    width: 100% !important;
}

#product-quick-view-modal .quantity {
    display: flex !important;
    align-items: center !important;
    flex-shrink: 0 !important;
    margin-bottom: 0 !important;
}

#product-quick-view-modal .quantity .label-quantity {
    margin-bottom: 0 !important;
    margin-right: 8px !important;
    white-space: nowrap !important;
    font-size: 14px !important;
}

#product-quick-view-modal .quantity .qty-box {
    display: flex !important;
    align-items: center !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    width: 120px !important;
    flex-shrink: 0 !important;
}

#product-quick-view-modal .quantity .qty-box input {
  
    text-align: center !important;
    border: none !important;
    padding: 8px 4px !important;
    font-size: 14px !important;
}

#product-quick-view-modal .quantity .qty-box .svg-icon {
    padding: 8px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#product-quick-view-modal .add-to-cart-button {
    flex: 1 !important;
    min-width: 140px !important;
    padding: 10px 12px !important;
    font-size: 14px !important;
    white-space: nowrap !important;
    margin-bottom: 0 !important;
}

#product-quick-view-modal .add-to-cart-text {
    display: inline !important;
    margin-left: 4px !important;
}

#product-quick-view-modal .btn-black {
    background-color: #333 !important;
    border-color: #333 !important;
}

#product-quick-view-modal .product-loop-buttons {
    display: flex !important;
    gap: 8px !important;
    flex-shrink: 0 !important;
}

#product-quick-view-modal .product-loop-buttons .btn {
    padding: 10px !important;
    min-width: auto !important;
}

/* Responsive adjustments for quick view modal */
@media (max-width: 768px) {
    #product-quick-view-modal .product-button {
        flex-direction: column !important;
        gap: 12px !important;
        align-items: stretch !important;
    }
    
    #product-quick-view-modal .quantity {
        justify-content: center !important;
    }
    
    #product-quick-view-modal .add-to-cart-button {
        width: 100% !important;
        flex: none !important;
    }
    
    #product-quick-view-modal .add-to-cart-text {
        display: inline !important;
    }
    
    #product-quick-view-modal .product-loop-buttons {
        justify-content: center !important;
    }
}

@media (max-width: 576px) {
    #product-quick-view-modal .modal-dialog {
        margin: 10px !important;
    }
    
    #product-quick-view-modal .product-modal-content {
        padding: 20px 0 !important;
    }
}
</style>
