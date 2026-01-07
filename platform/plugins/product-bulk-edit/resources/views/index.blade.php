@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="product-bulk-edit-container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ trans('plugins/product-bulk-edit::product-bulk-edit.name') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="text" id="search-products" class="form-control" placeholder="{{ trans('plugins/product-bulk-edit::product-bulk-edit.search') }}">
                            </div>
                            <div class="col-md-3">
                                <select id="filter-category" class="form-control">
                                    <option value="">{{ trans('plugins/product-bulk-edit::product-bulk-edit.all_categories') }}</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="filter-brand" class="form-control">
                                    <option value="">{{ trans('plugins/product-bulk-edit::product-bulk-edit.all_brands') }}</option>
                                    @foreach($brands as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="btn-load-products" class="btn btn-primary">
                                    <i class="ti ti-search"></i> {{ trans('plugins/product-bulk-edit::product-bulk-edit.load_products') }}
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="btn-group me-2" role="group">
                                    <button type="button" id="btn-save-changes" class="btn btn-success">
                                        <i class="ti ti-device-floppy"></i> {{ trans('plugins/product-bulk-edit::product-bulk-edit.save_changes') }}
                                    </button>
                                    <button type="button" id="btn-undo" class="btn btn-warning">
                                        <i class="ti ti-arrow-back-up"></i> {{ trans('plugins/product-bulk-edit::product-bulk-edit.undo') }}
                                    </button>
                                    <button type="button" id="btn-redo" class="btn btn-warning">
                                        <i class="ti ti-arrow-forward-up"></i> {{ trans('plugins/product-bulk-edit::product-bulk-edit.redo') }}
                                    </button>
                                    <button type="button" id="btn-delete-selected" class="btn btn-danger">
                                        <i class="ti ti-trash"></i> {{ trans('plugins/product-bulk-edit::product-bulk-edit.delete_selected') }}
                                    </button>
                                    <button type="button" id="btn-column-settings" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#columnModal">
                                        <i class="ti ti-columns"></i> {{ trans('plugins/product-bulk-edit::product-bulk-edit.column_settings') }}
                                    </button>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" id="btn-export" class="btn btn-primary">
                                        <i class="ti ti-download"></i> Export CSV
                                    </button>
                                    <button type="button" id="btn-import" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="ti ti-upload"></i> Import CSV
                                    </button>
                                </div>
                                <div class="float-end">
                                    <label class="me-2">
                                        <input type="checkbox" id="auto-save-toggle" checked> Auto-save on change
                                    </label>
                                    <span id="total-products" class="badge bg-info">0 {{ trans('plugins/product-bulk-edit::product-bulk-edit.products') }}</span>
                                    <span id="changed-count" class="badge bg-warning">0 {{ trans('plugins/product-bulk-edit::product-bulk-edit.changes') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div id="spreadsheet-container" style="height: 600px; overflow: auto;"></div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <nav id="pagination-container">
                                    <!-- Pagination will be inserted here -->
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">📥 Import Products from CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" style="font-size: 13px;">
                        <strong>📋 How Import Works:</strong>
                        <ul class="mb-0 mt-2" style="padding-left: 20px;">
                            <li><strong>Update Existing:</strong> Products with matching <strong>Barcode</strong> will be updated</li>
                            <li><strong>Add New:</strong> Products without matching Barcode will be created as new products</li>
                            <li><strong>Format:</strong> Use CSV file with headers matching export format</li>
                            <li><strong>Tip:</strong> Export your current products first to get the correct CSV format</li>
                            <li><strong>Categories:</strong> Use pipe separator (|) for multiple: Category1|Category2</li>
                            <li><strong>Gallery Images:</strong> Use pipe separator (|) for multiple URLs</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="importFileInput" class="form-label fw-bold">Select CSV File:</label>
                        <input type="file" id="importFileInput" accept=".csv" class="form-control">
                        <small class="text-muted">Maximum file size: 10MB</small>
                    </div>

                    <div id="importProgress" style="display:none;" class="mt-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                                Importing...
                            </div>
                        </div>
                    </div>

                    <div id="importResult" style="display:none;" class="mt-3">
                        <!-- Import results will be shown here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-process-import">
                        <i class="ti ti-upload"></i> Import Products
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Column Settings Modal -->
    <div class="modal fade" id="columnModal" tabindex="-1" aria-labelledby="columnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="columnModalLabel">{{ trans('plugins/product-bulk-edit::product-bulk-edit.column_settings') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">{{ trans('plugins/product-bulk-edit::product-bulk-edit.select_columns_to_display') }}</p>
                    <div class="row">
                        <div class="col-md-6" id="column-checkboxes-left"></div>
                        <div class="col-md-6" id="column-checkboxes-right"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btn-select-all-columns">Select All</button>
                    <button type="button" class="btn btn-secondary" id="btn-deselect-all-columns">Deselect All</button>
                    <button type="button" class="btn btn-primary" id="btn-apply-columns">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Upload Modal -->
    <div class="modal fade" id="imageUploadModal" tabindex="-1" aria-labelledby="imageUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageUploadModalLabel">Upload Product Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" style="font-size: 13px;">
                        <strong>📷 How to upload/change product image:</strong>
                        <ul class="mb-0 mt-2" style="padding-left: 20px;">
                            <li><strong>Click on any product image thumbnail</strong> in the spreadsheet to change it</li>
                            <li>Or click the <strong>"📷 Upload Image"</strong> button for products without images</li>
                            <li>Supported formats: All image types</li>
                            <li>The main product image will be updated immediately after upload</li>
                        </ul>
                    </div>
                    
                    <ul class="nav nav-tabs mb-3" id="imageUploadTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-upload" type="button" role="tab">Upload File</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-upload" type="button" role="tab">Import from URL</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="imageUploadTabsContent">
                        <div class="tab-pane fade show active" id="file-upload" role="tabpanel">
                            <label for="imageFileInput" class="form-label fw-bold">Select Image File:</label>
                            <input type="file" id="imageFileInput" accept="image/*" class="form-control">
                        </div>
                        <div class="tab-pane fade" id="url-upload" role="tabpanel">
                            <label for="imageUrlInput" class="form-label fw-bold">Enter Image URL:</label>
                            <input type="url" id="imageUrlInput" placeholder="https://example.com/image.jpg" class="form-control">
                            <small class="text-muted">Enter the full URL of the image you want to import</small>
                        </div>
                    </div>
                    
                    <input type="hidden" id="imageUploadProductId">
                    <div id="imagePreview" class="mt-3" style="display:none;">
                        <p class="text-muted mb-2"><strong>Preview:</strong></p>
                        <img id="imagePreviewImg" src="" style="max-width: 100%; max-height: 300px; height: auto; border-radius: 4px; border: 2px solid #ddd;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-upload-image">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Management Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">🖼️ Manage Product Gallery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" style="font-size: 13px;">
                        <strong>Gallery Images:</strong> Add multiple images that will appear in the product gallery slider.
                        <br>The first image will be shown as the main gallery image.
                    </div>
                    <input type="hidden" id="galleryProductId">
                    
                    <ul class="nav nav-tabs mb-3" id="galleryUploadTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="gallery-file-tab" data-bs-toggle="tab" data-bs-target="#gallery-file-upload" type="button" role="tab">Upload Files</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="gallery-url-tab" data-bs-toggle="tab" data-bs-target="#gallery-url-upload" type="button" role="tab">Import from URLs</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mb-3" id="galleryUploadTabsContent">
                        <div class="tab-pane fade show active" id="gallery-file-upload" role="tabpanel">
                            <label for="galleryFileInput" class="form-label fw-bold">Add Images to Gallery:</label>
                            <input type="file" id="galleryFileInput" accept="image/*" multiple class="form-control">
                            <small class="text-muted">You can select multiple images at once</small>
                        </div>
                        <div class="tab-pane fade" id="gallery-url-upload" role="tabpanel">
                            <label for="galleryUrlInput" class="form-label fw-bold">Enter Image URLs (one per line):</label>
                            <textarea id="galleryUrlInput" rows="5" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg&#10;https://example.com/image3.jpg" class="form-control"></textarea>
                            <small class="text-muted">Enter one URL per line to import multiple images</small>
                        </div>
                    </div>

                    <div id="galleryImagesContainer" class="row g-2">
                        <!-- Gallery images will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-upload-gallery">Upload Images</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.productBulkEditData = {
            routes: {
                getData: '{{ route('product-bulk-edit.data') }}',
                update: '{{ route('product-bulk-edit.update') }}',
                updateField: '{{ route('product-bulk-edit.updateField') }}',
                uploadImage: '{{ route('product-bulk-edit.uploadImage') }}',
                uploadImageFromUrl: '{{ route('product-bulk-edit.uploadImageFromUrl') }}',
                uploadGallery: '{{ route('product-bulk-edit.uploadGallery') }}',
                uploadGalleryFromUrl: '{{ route('product-bulk-edit.uploadGalleryFromUrl') }}',
                deleteGalleryImage: '{{ route('product-bulk-edit.deleteGalleryImage') }}',
                export: '{{ route('product-bulk-edit.export') }}',
                import: '{{ route('product-bulk-edit.import') }}',
                delete: '{{ route('product-bulk-edit.delete') }}'
            },
            brands: @json($brands),
            categories: @json($categories),
            taxes: @json($taxes),
            stockStatuses: {
                'in_stock': '{{ trans('plugins/ecommerce::products.stock_statuses.in_stock') }}',
                'out_of_stock': '{{ trans('plugins/ecommerce::products.stock_statuses.out_of_stock') }}',
                'on_backorder': '{{ trans('plugins/ecommerce::products.stock_statuses.on_backorder') }}'
            },
            statuses: {
                'published': '{{ trans('core/base::enums.statuses.published') }}',
                'draft': '{{ trans('core/base::enums.statuses.draft') }}',
                'pending': '{{ trans('core/base::enums.statuses.pending') }}'
            },
            trans: {
                confirm_delete: '{{ trans('plugins/product-bulk-edit::product-bulk-edit.confirm_delete') }}',
                no_changes: '{{ trans('plugins/product-bulk-edit::product-bulk-edit.no_changes') }}',
                saving: '{{ trans('plugins/product-bulk-edit::product-bulk-edit.saving') }}',
                loading: '{{ trans('plugins/product-bulk-edit::product-bulk-edit.loading') }}'
            }
        };
    </script>
@endsection
