/**
 * Product Bulk Edit - Excel-like Interface with Auto-Save
 * Uses Handsontable for spreadsheet functionality
 */

(function ($) {
    'use strict';

    class ProductBulkEdit {
        constructor() {
            this.hot = null;
            this.changedData = new Map();
            this.originalData = [];
            this.currentPage = 1;
            this.totalPages = 1;
            this.perPage = 50;
            this.isLoading = false;
            this.autoSave = true;
            this.saveTimeout = null;

            // All available columns
            this.allColumns = [
                { key: 'select', label: 'Select', enabled: true, fixed: true },
                { key: 'id', label: 'ID', enabled: true, fixed: true },
                { key: 'name', label: 'Name', enabled: true },
                { key: 'sku', label: 'SKU', enabled: true },
                { key: 'price', label: 'Price', enabled: true },
                { key: 'sale_price', label: 'Sale Price', enabled: true },
                { key: 'cost_per_item', label: 'Cost Per Item', enabled: false },
                { key: 'quantity', label: 'Quantity', enabled: true },
                { key: 'allow_checkout_when_out_of_stock', label: 'Allow Out of Stock', enabled: false },
                { key: 'with_storehouse_management', label: 'Manage Stock', enabled: true },
                { key: 'stock_status', label: 'Stock Status', enabled: true },
                { key: 'status', label: 'Status', enabled: true },
                { key: 'brand_id', label: 'Brand', enabled: true },
                { key: 'category_names', label: 'Categories', enabled: true },
                { key: 'tax_id', label: 'Tax', enabled: false },
                { key: 'weight', label: 'Weight', enabled: false },
                { key: 'length', label: 'Length', enabled: false },
                { key: 'wide', label: 'Width', enabled: false },
                { key: 'height', label: 'Height', enabled: false },
                { key: 'barcode', label: 'Barcode', enabled: false },
                { key: 'minimum_order_quantity', label: 'Min Order', enabled: false },
                { key: 'maximum_order_quantity', label: 'Max Order', enabled: false },
                { key: 'is_featured', label: 'Is Featured', enabled: false },
                { key: 'description', label: 'Description', enabled: false },
                { key: 'content', label: 'Content (Long Description)', enabled: false },
                { key: 'image', label: 'Featured Image', enabled: true },
                { key: 'images', label: 'Gallery Images', enabled: false },
                { key: 'tag_names', label: 'Tags', enabled: false },
                { key: 'seo_title', label: 'SEO Title', enabled: false },
                { key: 'seo_description', label: 'SEO Description', enabled: false },
                { key: 'seo_image', label: 'SEO Image', enabled: false }
            ];

            this.init();
        }

        init() {
            console.log('ProductBulkEdit initialized with all fields support');
            console.log('Total columns:', this.allColumns.length);
            console.log('Editable columns:', this.allColumns.filter(c => !c.fixed).length);
            this.loadColumnPreferences();
            this.bindEvents();
        }

        loadColumnPreferences() {
            const saved = localStorage.getItem('productBulkEditColumns');
            if (saved) {
                try {
                    const preferences = JSON.parse(saved);
                    this.allColumns.forEach(col => {
                        if (preferences[col.key] !== undefined && !col.fixed) {
                            col.enabled = preferences[col.key];
                        }
                    });
                } catch (e) {
                    console.error('Error loading column preferences:', e);
                }
            }
        }

        saveColumnPreferences() {
            const preferences = {};
            this.allColumns.forEach(col => {
                preferences[col.key] = col.enabled;
            });
            localStorage.setItem('productBulkEditColumns', JSON.stringify(preferences));
        }

        setupColumnSelector() {
            console.log('Setting up column selector...');
            const leftContainer = $('#column-checkboxes-left');
            const rightContainer = $('#column-checkboxes-right');
            
            if (leftContainer.length === 0 || rightContainer.length === 0) {
                console.error('Column checkbox containers not found!');
                return;
            }
            
            leftContainer.empty();
            rightContainer.empty();

            // Filter out fixed columns first
            const editableColumns = this.allColumns.filter(col => !col.fixed);
            const half = Math.ceil(editableColumns.length / 2);

            editableColumns.forEach((col, index) => {
                const checkbox = $(`
                    <div class="form-check mb-2">
                        <input class="form-check-input column-toggle" 
                               type="checkbox" 
                               id="col-${col.key}" 
                               data-key="${col.key}" 
                               ${col.enabled ? 'checked' : ''}>
                        <label class="form-check-label" for="col-${col.key}">
                            ${col.label}
                        </label>
                    </div>
                `);

                if (index < half) {
                    leftContainer.append(checkbox);
                } else {
                    rightContainer.append(checkbox);
                }
            });
            
            console.log(`Added ${editableColumns.length} column checkboxes`);
        }

        bindEvents() {
            console.log('Binding events...');
            
            $('#btn-load-products').on('click', () => this.loadProducts());
            $('#btn-save-changes').on('click', () => this.saveChanges());
            $('#btn-undo').on('click', () => this.hot && this.hot.undo());
            $('#btn-redo').on('click', () => this.hot && this.hot.redo());
            $('#btn-delete-selected').on('click', () => this.deleteSelected());
            $('#btn-export').on('click', () => this.exportProducts());
            $('#btn-process-import').on('click', () => this.importProducts());

            // Setup column selector when modal is shown (Bootstrap 5)
            const modalElement = document.getElementById('columnModal');
            if (modalElement) {
                console.log('Modal element found, attaching show event');
                modalElement.addEventListener('show.bs.modal', () => {
                    console.log('Modal show event triggered');
                    this.setupColumnSelector();
                });
            } else {
                console.error('Modal element #columnModal not found!');
            }

            // Column selector events
            $('#btn-apply-columns').on('click', () => {
                console.log('Apply columns clicked');
                $('.column-toggle').each((i, el) => {
                    const key = $(el).data('key');
                    const col = this.allColumns.find(c => c.key === key);
                    if (col) {
                        col.enabled = $(el).is(':checked');
                    }
                });
                this.saveColumnPreferences();
                if (this.originalData.length > 0) {
                    this.initSpreadsheet(this.originalData);
                }
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
                Botble.showSuccess('Column settings applied');
            });

            $('#btn-select-all-columns').on('click', (e) => {
                e.preventDefault();
                console.log('Select all clicked');
                $('.column-toggle').prop('checked', true);
            });

            $('#btn-deselect-all-columns').on('click', (e) => {
                e.preventDefault();
                console.log('Deselect all clicked');
                $('.column-toggle').prop('checked', false);
            });

            // Auto-save toggle
            $('#auto-save-toggle').on('change', (e) => {
                this.autoSave = $(e.target).is(':checked');
                if (!this.autoSave) {
                    $('#btn-save-changes').show();
                } else {
                    $('#btn-save-changes').hide();
                }
            });

            $('#search-products').on('keypress', (e) => {
                if (e.which === 13) {
                    this.loadProducts();
                }
            });

            // Reset import modal when it's closed
            const importModal = document.getElementById('importModal');
            if (importModal) {
                importModal.addEventListener('hidden.bs.modal', () => {
                    $('#importFileInput').val('');
                    $('#importProgress').hide();
                    $('#importResult').hide();
                });
            }

            // Image upload handlers - use mousedown to prevent Handsontable from intercepting click
            $(document).on('mousedown', '.upload-image-trigger', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const productId = $(e.currentTarget).data('product-id');
                console.log('Upload image clicked for product ID:', productId);
                $('#imageUploadProductId').val(productId);
                $('#imageFileInput').val('');
                $('#imageUrlInput').val('');
                $('#imagePreview').hide();
                // Reset to file tab
                $('#file-tab').click();
                const modalElement = document.getElementById('imageUploadModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    console.error('Image upload modal not found');
                }
                return false;
            });
            
            // Also handle regular click for safety
            $(document).on('click', '.upload-image-trigger', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            });

            $('#imageFileInput').on('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreviewImg').attr('src', e.target.result);
                        $('#imagePreview').show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#btn-upload-image').on('click', () => {
                const productId = $('#imageUploadProductId').val();
                const activeTab = $('#imageUploadTabs .nav-link.active').attr('id');
                
                if (activeTab === 'file-tab') {
                    // File upload
                    const file = $('#imageFileInput')[0].files[0];
                    
                    if (!file) {
                        Botble.showError('Please select an image');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('id', productId);

                    $.ajax({
                        url: window.productBulkEditData.routes.uploadImage || '/admin/product-bulk-edit/upload-image',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: (response) => {
                            if (response.error === false) {
                                Botble.showSuccess('Image uploaded successfully');
                                const modal = bootstrap.Modal.getInstance($('#imageUploadModal')[0]);
                                modal.hide();
                                this.loadProducts(); // Reload to show new image
                            } else {
                                Botble.showError(response.message || 'Upload failed');
                            }
                        },
                        error: (xhr) => {
                            Botble.showError(xhr.responseJSON?.message || 'Upload failed');
                        }
                    });
                } else if (activeTab === 'url-tab') {
                    // URL import
                    const url = $('#imageUrlInput').val().trim();
                    
                    if (!url) {
                        Botble.showError('Please enter an image URL');
                        return;
                    }

                    $.ajax({
                        url: window.productBulkEditData.routes.uploadImageFromUrl || '/admin/product-bulk-edit/upload-image-from-url',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: productId,
                            url: url
                        },
                        success: (response) => {
                            if (response.error === false) {
                                Botble.showSuccess('Image imported successfully from URL');
                                const modal = bootstrap.Modal.getInstance($('#imageUploadModal')[0]);
                                modal.hide();
                                this.loadProducts(); // Reload to show new image
                            } else {
                                Botble.showError(response.message || 'Import failed');
                            }
                        },
                        error: (xhr) => {
                            Botble.showError(xhr.responseJSON?.message || 'Import failed');
                        }
                    });
                }
            });

            // Gallery management handlers
            $(document).on('mousedown', '.manage-gallery-trigger', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const productId = $(e.currentTarget).data('product-id');
                this.openGalleryModal(productId);
                return false;
            });

            $(document).on('click', '.manage-gallery-trigger', (e) => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });

            $('#btn-upload-gallery').on('click', () => {
                const productId = $('#galleryProductId').val();
                const activeTab = $('#galleryUploadTabs .nav-link.active').attr('id');
                
                if (activeTab === 'gallery-file-tab') {
                    // File upload
                    const files = $('#galleryFileInput')[0].files;
                    
                    if (files.length === 0) {
                        Botble.showError('Please select at least one image');
                        return;
                    }

                    const formData = new FormData();
                    for (let i = 0; i < files.length; i++) {
                        formData.append('images[]', files[i]);
                    }
                    formData.append('id', productId);

                    $.ajax({
                        url: window.productBulkEditData.routes.uploadGallery || '/admin/product-bulk-edit/upload-gallery',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: (response) => {
                            if (response.error === false) {
                                Botble.showSuccess(`${files.length} image(s) uploaded successfully`);
                                $('#galleryFileInput').val('');
                                this.loadGalleryImages(productId);
                                this.loadProducts(); // Reload to show updated gallery
                            } else {
                                Botble.showError(response.message || 'Upload failed');
                            }
                        },
                        error: (xhr) => {
                            Botble.showError(xhr.responseJSON?.message || 'Upload failed');
                        }
                    });
                } else if (activeTab === 'gallery-url-tab') {
                    // URL import
                    const urlsText = $('#galleryUrlInput').val().trim();
                    
                    if (!urlsText) {
                        Botble.showError('Please enter at least one image URL');
                        return;
                    }

                    // Split by newlines and filter empty lines
                    const urls = urlsText.split('\n')
                        .map(url => url.trim())
                        .filter(url => url.length > 0);

                    if (urls.length === 0) {
                        Botble.showError('Please enter at least one valid URL');
                        return;
                    }

                    $.ajax({
                        url: window.productBulkEditData.routes.uploadGalleryFromUrl || '/admin/product-bulk-edit/upload-gallery-from-url',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: productId,
                            urls: urls
                        },
                        success: (response) => {
                            if (response.error === false) {
                                Botble.showSuccess(`${urls.length} image(s) imported successfully from URLs`);
                                $('#galleryUrlInput').val('');
                                this.loadGalleryImages(productId);
                                this.loadProducts(); // Reload to show updated gallery
                            } else {
                                Botble.showError(response.message || 'Import failed');
                            }
                        },
                        error: (xhr) => {
                            Botble.showError(xhr.responseJSON?.message || 'Import failed');
                        }
                    });
                }
            });

            $(document).on('click', '.delete-gallery-image', (e) => {
                e.preventDefault();
                const productId = $('#galleryProductId').val();
                const imagePath = $(e.currentTarget).data('image');
                
                if (!confirm('Are you sure you want to delete this image?')) {
                    return;
                }

                $.ajax({
                    url: window.productBulkEditData.routes.deleteGalleryImage || '/admin/product-bulk-edit/delete-gallery-image',
                    type: 'POST',
                    data: {
                        id: productId,
                        image: imagePath
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (response) => {
                        if (response.error === false) {
                            Botble.showSuccess('Image deleted successfully');
                            this.loadGalleryImages(productId);
                            this.loadProducts();
                        } else {
                            Botble.showError(response.message || 'Delete failed');
                        }
                    },
                    error: (xhr) => {
                        Botble.showError(xhr.responseJSON?.message || 'Delete failed');
                    }
                });
            });

            $(document).on('click', '.page-link', (e) => {
                e.preventDefault();
                const page = $(e.currentTarget).data('page');
                if (page && !this.isLoading) {
                    this.currentPage = page;
                    this.loadProducts();
                }
            });
        }

        loadProducts() {
            if (this.isLoading) return;

            if (this.changedData.size > 0 && !this.autoSave) {
                if (!confirm('You have unsaved changes. Loading new data will discard them. Continue?')) {
                    return;
                }
            }

            this.isLoading = true;
            const $btn = $('#btn-load-products');
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="ti ti-loader"></i> Loading...');

            const params = {
                page: this.currentPage,
                per_page: this.perPage,
                search: $('#search-products').val(),
                category_id: $('#filter-category').val(),
                brand_id: $('#filter-brand').val()
            };

            $.ajax({
                url: window.productBulkEditData.routes.getData,
                type: 'GET',
                data: params,
                success: (response) => {
                    if (response.error === false) {
                        this.originalData = response.data;
                        this.totalPages = response.total_pages;
                        this.initSpreadsheet(response.data);
                        this.updatePagination(response);
                        this.updateProductCount(response.total);
                        this.changedData.clear();
                        this.updateChangesCount();
                    } else {
                        Botble.showError(response.message || 'Error loading products');
                    }
                },
                error: (xhr) => {
                    const message = xhr.responseJSON?.message || 'Error loading products';
                    Botble.showError(message);
                    console.error('Load error:', xhr);
                },
                complete: () => {
                    this.isLoading = false;
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        }

        getEnabledColumns() {
            return this.allColumns.filter(col => col.enabled);
        }

        getColumnConfig(colDef) {
            const brands = window.productBulkEditData.brands || {};
            const brandOptions = [''].concat(Object.values(brands));
            const brandIds = [''].concat(Object.keys(brands));

            const taxes = window.productBulkEditData.taxes || {};
            const taxOptions = [''].concat(Object.values(taxes));
            const taxIds = [''].concat(Object.keys(taxes));

            const stockStatuses = window.productBulkEditData.stockStatuses || {};
            const stockStatusKeys = Object.keys(stockStatuses);
            const stockStatusValues = Object.values(stockStatuses);

            const statuses = window.productBulkEditData.statuses || {};
            const statusKeys = Object.keys(statuses);
            const statusValues = Object.values(statuses);

            const configs = {
                'select': {
                    data: 'selected',
                    type: 'checkbox',
                    readOnly: false,
                    width: 40
                },
                'id': {
                    data: 'id',
                    type: 'numeric',
                    readOnly: true,
                    width: 60
                },
                'name': {
                    data: 'name',
                    type: 'text',
                    width: 250
                },
                'sku': {
                    data: 'sku',
                    type: 'text',
                    width: 120
                },
                'price': {
                    data: 'price',
                    type: 'numeric',
                    numericFormat: { pattern: '0,0.00' },
                    width: 100
                },
                'sale_price': {
                    data: 'sale_price',
                    type: 'numeric',
                    numericFormat: { pattern: '0,0.00' },
                    width: 100
                },
                'cost_per_item': {
                    data: 'cost_per_item',
                    type: 'numeric',
                    numericFormat: { pattern: '0,0.00' },
                    width: 110
                },
                'quantity': {
                    data: 'quantity',
                    type: 'numeric',
                    width: 90
                },
                'allow_checkout_when_out_of_stock': {
                    data: 'allow_checkout_when_out_of_stock',
                    type: 'checkbox',
                    width: 120
                },
                'with_storehouse_management': {
                    data: 'with_storehouse_management',
                    type: 'checkbox',
                    width: 100
                },
                'stock_status': {
                    data: 'stock_status',
                    type: 'dropdown',
                    source: stockStatusValues,
                    width: 120
                },
                'status': {
                    data: 'status',
                    type: 'dropdown',
                    source: statusValues,
                    width: 100
                },
                'brand_id': {
                    data: 'brand_id',
                    type: 'dropdown',
                    source: brandIds,
                    width: 150
                },
                'category_names': {
                    data: 'category_names',
                    type: 'text',
                    width: 250
                },
                'tax_id': {
                    data: 'tax_id',
                    type: 'dropdown',
                    source: taxIds,
                    width: 120
                },
                'weight': {
                    data: 'weight',
                    type: 'numeric',
                    numericFormat: { pattern: '0,0.00' },
                    width: 80
                },
                'length': {
                    data: 'length',
                    type: 'numeric',
                    numericFormat: { pattern: '0,0.00' },
                    width: 80
                },
                'wide': {
                    data: 'wide',
                    type: 'numeric',
                    numericFormat: { pattern: '0,0.00' },
                    width: 80
                },
                'height': {
                    data: 'height',
                    type: 'numeric',
                    numericFormat: { pattern: '0,0.00' },
                    width: 80
                },
                'barcode': {
                    data: 'barcode',
                    type: 'text',
                    width: 120
                },
                'minimum_order_quantity': {
                    data: 'minimum_order_quantity',
                    type: 'numeric',
                    width: 90
                },
                'maximum_order_quantity': {
                    data: 'maximum_order_quantity',
                    type: 'numeric',
                    width: 90
                },
                'is_featured': {
                    data: 'is_featured',
                    type: 'checkbox',
                    width: 100
                },
                'description': {
                    data: 'description',
                    type: 'text',
                    width: 300
                },
                'content': {
                    data: 'content',
                    type: 'text',
                    width: 350
                },
                'image': {
                    data: 'image',
                    type: 'text',
                    width: 250
                },
                'images': {
                    data: 'images',
                    type: 'text',
                    readOnly: true,
                    width: 300
                },
                'tag_names': {
                    data: 'tag_names',
                    type: 'text',
                    readOnly: true,
                    width: 150
                },
                'seo_title': {
                    data: 'seo_title',
                    type: 'text',
                    width: 250
                },
                'seo_description': {
                    data: 'seo_description',
                    type: 'text',
                    width: 300
                },
                'seo_image': {
                    data: 'seo_image',
                    type: 'text',
                    width: 200
                }
            };

            return configs[colDef.key] || { data: colDef.key, type: 'text', width: 100 };
        }

        initSpreadsheet(data) {
            const container = document.getElementById('spreadsheet-container');

            if (this.hot) {
                this.hot.destroy();
            }

            const enabledColumns = this.getEnabledColumns();
            const colHeaders = enabledColumns.map(col => {
                if (col.key === 'select') {
                    return '<input type="checkbox" id="select-all">';
                }
                return col.label;
            });

            const columns = enabledColumns.map(col => this.getColumnConfig(col));

            this.hot = new Handsontable(container, {
                data: data,
                colHeaders: colHeaders,
                columns: columns,
                rowHeaders: true,
                width: '100%',
                height: 600,
                stretchH: 'all',
                autoWrapRow: true,
                autoWrapCol: true,
                manualRowResize: true,
                manualColumnResize: true,
                contextMenu: true,
                filters: true,
                dropdownMenu: true,
                licenseKey: 'non-commercial-and-evaluation',
                afterChange: (changes, source) => {
                    if (source === 'loadData' || !changes) {
                        return;
                    }

                    changes.forEach(([row, prop, oldValue, newValue]) => {
                        if (oldValue !== newValue && prop !== 'selected') {
                            const rowData = this.hot.getSourceDataAtRow(row);
                            
                            if (this.autoSave) {
                                this.autoSaveField(rowData.id, prop, newValue, row);
                            } else {
                                const key = `${rowData.id}_${prop}`;
                                this.changedData.set(key, {
                                    id: rowData.id,
                                    field: prop,
                                    value: newValue,
                                    row: row
                                });
                                this.updateChangesCount();
                            }
                        }
                    });
                },
                cells: (row, col) => {
                    const cellProperties = {};
                    const colDef = enabledColumns[col];
                    
                    // Make all cells editable except readonly ones
                    if (colDef && colDef.key !== 'id' && colDef.key !== 'category_names' && 
                        colDef.key !== 'tag_names') {
                        cellProperties.readOnly = false;
                    }
                    
                    // Custom renderer for dropdown fields
                    if (colDef) {
                        if (colDef.key === 'stock_status') {
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                td.textContent = value || '';
                                return td;
                            };
                        } else if (colDef.key === 'status') {
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                td.textContent = value || '';
                                return td;
                            };
                        } else if (colDef.key === 'brand_id') {
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                const brands = window.productBulkEditData.brands || {};
                                td.textContent = brands[value] || '';
                                return td;
                            };
                        } else if (colDef.key === 'tax_id') {
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                const taxes = window.productBulkEditData.taxes || {};
                                td.textContent = taxes[value] || '';
                                return td;
                            };
                        } else if (colDef.key === 'category_names') {
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                if (value) {
                                    const categories = value.split(',').map(c => c.trim());
                                    td.innerHTML = categories.map(cat => 
                                        `<span style="display: inline-block; background-color: #6c757d; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 4px; margin-right: 4px;">${cat}</span>`
                                    ).join('');
                                } else {
                                    td.innerHTML = '<span style="color: #999; font-style: italic;">No category</span>';
                                }
                                return td;
                            };
                        } else if (colDef.key === 'image') {
                            cellProperties.readOnly = true;
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                const productId = instance.getSourceDataAtRow(row).id;
                                td.style.padding = '4px';
                                td.style.cursor = 'pointer';
                                
                                if (value) {
                                    const imageUrl = value.startsWith('http') ? value : `${window.location.origin}/storage/${value}`;
                                    const filename = value.substring(value.lastIndexOf('/') + 1);
                                    td.innerHTML = `
                                        <div class="upload-image-trigger" data-product-id="${productId}" style="display: flex; align-items: center; gap: 8px; cursor: pointer; width: 100%;" title="Click to change image">
                                            <div style="position: relative; width: 40px; height: 40px;">
                                                <img src="${imageUrl}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; transition: opacity 0.2s; display: block;" 
                                                     onmouseover="this.parentElement.parentElement.style.opacity='0.8'" onmouseout="this.parentElement.parentElement.style.opacity='1'"
                                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'40\\' height=\\'40\\'%3E%3Crect fill=\\'%23ddd\\' width=\\'40\\' height=\\'40\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\' fill=\\'%23999\\'%3ENo Img%3C/text%3E%3C/svg%3E'">
                                                <div style="position: absolute; top: -2px; right: -2px; background: rgba(0,0,0,0.7); color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 11px;">✎</div>
                                            </div>
                                            <span style="font-size: 11px; color: #666;">${filename}</span>
                                        </div>
                                    `;
                                } else {
                                    td.innerHTML = `<button class="btn btn-sm btn-primary upload-image-trigger" data-product-id="${productId}" style="font-size: 11px; padding: 4px 8px;">📷 Upload</button>`;
                                }
                                return td;
                            };
                        } else if (colDef.key === 'images') {
                            cellProperties.readOnly = true;
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                const productId = instance.getSourceDataAtRow(row).id;
                                td.style.padding = '4px';
                                td.style.cursor = 'pointer';
                                
                                if (value && Array.isArray(value) && value.length > 0) {
                                    const count = value.length;
                                    const firstImage = value[0];
                                    const imageUrl = firstImage.startsWith('http') ? firstImage : `${window.location.origin}/storage/${firstImage}`;
                                    td.innerHTML = `
                                        <div class="manage-gallery-trigger" data-product-id="${productId}" style="display: flex; align-items: center; gap: 8px; cursor: pointer;" title="Click to manage gallery">
                                            <div style="position: relative; width: 40px; height: 40px;">
                                                <img src="${imageUrl}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 2px solid #3b82f6;" 
                                                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'40\\' height=\\'40\\'%3E%3Crect fill=\\'%23ddd\\' width=\\'40\\' height=\\'40\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\' fill=\\'%23999\\'%3E\ud83d\uddbc\ufe0f%3C/text%3E%3C/svg%3E'">
                                                <div style="position: absolute; bottom: -2px; right: -2px; background: #3b82f6; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold; border: 2px solid white;">${count}</div>
                                            </div>
                                            <span style="font-size: 11px; color: #3b82f6; font-weight: 500;">${count} image${count > 1 ? 's' : ''}</span>
                                        </div>
                                    `;
                                } else {
                                    td.innerHTML = `<button class="btn btn-sm btn-outline-primary manage-gallery-trigger" data-product-id="${productId}" style="font-size: 11px; padding: 4px 8px;">\ud83d\uddbc\ufe0f Add Gallery</button>`;
                                }
                                return td;
                            };
                        } else if (colDef.key === 'description' || colDef.key === 'content') {
                            cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
                                const plainText = value ? value.replace(/<[^>]*>/g, '').substring(0, colDef.key === 'content' ? 150 : 100) : '';
                                td.textContent = plainText + (plainText.length >= (colDef.key === 'content' ? 150 : 100) ? '...' : '');
                                td.title = value ? value.replace(/<[^>]*>/g, '') : '';
                                return td;
                            };
                        }
                    }
                    
                    return cellProperties;
                }
            });

            // Handle select all checkbox
            setTimeout(() => {
                $('#select-all').on('change', (e) => {
                    const checked = $(e.target).is(':checked');
                    const dataLength = this.hot.countRows();
                    for (let i = 0; i < dataLength; i++) {
                        this.hot.setDataAtRowProp(i, 'selected', checked);
                    }
                });
            }, 100);
        }

        autoSaveField(productId, field, value, row) {
            // Debounce saves
            if (this.saveTimeout) {
                clearTimeout(this.saveTimeout);
            }

            this.saveTimeout = setTimeout(() => {
                console.log('Auto-saving field:', productId, field, value);

                $.ajax({
                    url: window.productBulkEditData.routes.updateField,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: productId,
                        field: field,
                        value: value
                    },
                    success: (response) => {
                        if (response.error) {
                            Botble.showError(response.message);
                            // Revert the change
                            const originalItem = this.originalData.find(item => item.id === productId);
                            if (originalItem) {
                                this.hot.setDataAtRowProp(row, field, originalItem[field]);
                            }
                        } else {
                            // Update original data
                            const originalItem = this.originalData.find(item => item.id === productId);
                            if (originalItem) {
                                originalItem[field] = response.data.value;
                            }
                            
                            // Show subtle success indicator
                            const cell = this.hot.getCell(row, this.hot.propToCol(field));
                            if (cell) {
                                $(cell).addClass('bg-success-subtle').delay(1000).queue(function() {
                                    $(this).removeClass('bg-success-subtle').dequeue();
                                });
                            }
                        }
                    },
                    error: (xhr) => {
                        const message = xhr.responseJSON?.message || 'Error saving field';
                        Botble.showError(message);
                        // Revert the change
                        const originalItem = this.originalData.find(item => item.id === productId);
                        if (originalItem) {
                            this.hot.setDataAtRowProp(row, field, originalItem[field]);
                        }
                    }
                });
            }, 500); // 500ms debounce
        }

        saveChanges() {
            if (this.changedData.size === 0) {
                Botble.showError('No changes to save');
                return;
            }

            const $btn = $('#btn-save-changes');
            const originalText = $btn.html();
            $btn.prop('disabled', true).html('<i class="ti ti-loader"></i> Saving...');

            const updates = Array.from(this.changedData.values()).reduce((acc, change) => {
                let item = acc.find(u => u.id === change.id);
                if (!item) {
                    item = { id: change.id };
                    acc.push(item);
                }
                item[change.field] = change.value;
                return acc;
            }, []);

            $.ajax({
                url: window.productBulkEditData.routes.update,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    updates: updates
                },
                success: (response) => {
                    if (response.error) {
                        Botble.showError(response.message);
                    } else {
                        Botble.showSuccess(response.message);
                        this.changedData.clear();
                        this.updateChangesCount();
                        // Update original data
                        updates.forEach(update => {
                            const original = this.originalData.find(item => item.id === update.id);
                            if (original) {
                                Object.assign(original, update);
                            }
                        });
                    }
                },
                error: (xhr) => {
                    const message = xhr.responseJSON?.message || 'Error saving changes';
                    Botble.showError(message);
                },
                complete: () => {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        }

        deleteSelected() {
            const selected = this.hot.getData().filter(row => row[0] === true);
            if (selected.length === 0) {
                Botble.showError('Please select products to delete');
                return;
            }

            if (!confirm(`Are you sure you want to delete ${selected.length} product(s)?`)) {
                return;
            }

            const ids = selected.map(row => row[1]); // ID is at index 1

            $.ajax({
                url: window.productBulkEditData.routes.delete,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ids: ids
                },
                success: (response) => {
                    if (response.error) {
                        Botble.showError(response.message);
                    } else {
                        Botble.showSuccess(response.message);
                        this.loadProducts();
                    }
                },
                error: (xhr) => {
                    const message = xhr.responseJSON?.message || 'Error deleting products';
                    Botble.showError(message);
                }
            });
        }

        updateChangesCount() {
            $('#changed-count').text(`${this.changedData.size} Changes`);
        }

        updateProductCount(total) {
            $('#total-products').text(`${total} Products`);
        }

        updatePagination(response) {
            const container = $('#pagination-container');
            if (response.total_pages <= 1) {
                container.empty();
                return;
            }

            let html = '<ul class="pagination">';
            
            if (response.page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${response.page - 1}">Previous</a></li>`;
            }

            const startPage = Math.max(1, response.page - 2);
            const endPage = Math.min(response.total_pages, response.page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const active = i === response.page ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }

            if (response.page < response.total_pages) {
                html += `<li class="page-item"><a class="page-link" href="#" data-page="${response.page + 1}">Next</a></li>`;
            }

            html += '</ul>';
            container.html(html);
        }

        openGalleryModal(productId) {
            $('#galleryProductId').val(productId);
            $('#galleryFileInput').val('');
            $('#galleryUrlInput').val('');
            // Reset to file tab
            $('#gallery-file-tab').click();
            this.loadGalleryImages(productId);
            const modalElement = document.getElementById('galleryModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        }

        loadGalleryImages(productId) {
            const product = this.originalData.find(p => p.id == productId);
            const container = $('#galleryImagesContainer');
            container.html('<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"></div></div>');
            
            setTimeout(() => {
                container.empty();
                
                if (product && product.images && Array.isArray(product.images) && product.images.length > 0) {
                    product.images.forEach((imagePath, index) => {
                        const imageUrl = imagePath.startsWith('http') ? imagePath : `${window.location.origin}/storage/${imagePath}`;
                        container.append(`
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="card">
                                    <img src="${imageUrl}" class="card-img-top" style="height: 150px; object-fit: cover;" 
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'100\\' height=\\'100\\'%3E%3Crect fill=\\'%23ddd\\' width=\\'100\\' height=\\'100\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\' fill=\\'%23999\\'%3EError%3C/text%3E%3C/svg%3E'">
                                    <div class="card-body p-2">
                                        <button class="btn btn-danger btn-sm w-100 delete-gallery-image" data-image="${imagePath}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    container.html('<div class="col-12 text-center text-muted">No gallery images yet. Upload some images above.</div>');
                }
            }, 300);
        }

        exportProducts() {
            // Get current filters
            const search = $('#search-products').val();
            const category = $('#filter-category').val();
            const brand = $('#filter-brand').val();
            
            // Build export URL with filters
            let exportUrl = window.productBulkEditData.routes.export;
            const params = new URLSearchParams();
            
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (brand) params.append('brand', brand);
            
            const queryString = params.toString();
            if (queryString) {
                exportUrl += '?' + queryString;
            }
            
            // Download CSV
            window.location.href = exportUrl;
            Botble.showSuccess('Export started. Your download will begin shortly.');
        }

        importProducts() {
            const fileInput = $('#importFileInput')[0];
            const file = fileInput.files[0];
            
            if (!file) {
                Botble.showError('Please select a CSV file');
                return;
            }

            if (!file.name.endsWith('.csv')) {
                Botble.showError('Please select a valid CSV file');
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            const $btn = $('#btn-process-import');
            const originalText = $btn.html();
            $btn.prop('disabled', true);
            
            $('#importProgress').show();
            $('#importResult').hide();

            $.ajax({
                url: window.productBulkEditData.routes.import,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    $('#importProgress').hide();
                    
                    if (response.error === false) {
                        const created = response.data?.created || 0;
                        const updated = response.data?.updated || 0;
                        const errors = response.data?.errors || [];
                        
                        let resultHtml = '<div class="alert alert-success">';
                        resultHtml += `<strong>✅ Import Completed!</strong><br>`;
                        resultHtml += `<ul class="mb-0 mt-2">`;
                        resultHtml += `<li>Created: <strong>${created}</strong> new products</li>`;
                        resultHtml += `<li>Updated: <strong>${updated}</strong> existing products</li>`;
                        resultHtml += `</ul>`;
                        resultHtml += '</div>';
                        
                        if (errors.length > 0) {
                            resultHtml += '<div class="alert alert-warning mt-2">';
                            resultHtml += '<strong>⚠️ Some Errors Occurred:</strong>';
                            resultHtml += '<ul class="mb-0 mt-2" style="max-height: 150px; overflow-y: auto;">';
                            errors.slice(0, 10).forEach(error => {
                                resultHtml += `<li style="font-size: 12px;">${error}</li>`;
                            });
                            if (errors.length > 10) {
                                resultHtml += `<li style="font-size: 12px;"><em>... and ${errors.length - 10} more errors</em></li>`;
                            }
                            resultHtml += '</ul></div>';
                        }
                        
                        $('#importResult').html(resultHtml).show();
                        
                        // Clear file input
                        fileInput.value = '';
                        
                        // Reload products after 2 seconds
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance($('#importModal')[0]);
                            if (modal) modal.hide();
                            this.loadProducts();
                            Botble.showSuccess(response.message);
                        }, 2000);
                        
                    } else {
                        $('#importResult').html(`<div class="alert alert-danger">${response.message}</div>`).show();
                    }
                },
                error: (xhr) => {
                    $('#importProgress').hide();
                    const message = xhr.responseJSON?.message || 'Import failed';
                    $('#importResult').html(`<div class="alert alert-danger">${message}</div>`).show();
                },
                complete: () => {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        }
    }

    // Initialize on document ready
    $(document).ready(function () {
        console.log('Starting ProductBulkEdit...');
        
        if (typeof Handsontable === 'undefined') {
            console.error('Handsontable library not loaded!');
            Botble.showError('Handsontable library failed to load. Please refresh the page.');
            return;
        }

        if (!window.productBulkEditData) {
            console.error('Product bulk edit data not available!');
            return;
        }

        new ProductBulkEdit();
    });

})(jQuery);
