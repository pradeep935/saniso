<!-- AI Content Generator for Products -->
@if($isAvailable ?? false)
    <div class="row">
        <!-- Language Selection -->
        <div class="col-md-6 mb-3">
            <label for="ai-language" class="form-label">Language</label>
            <select id="ai-language" class="form-select">
                @if(isset($languages) && is_array($languages))
                    @foreach($languages as $code => $name)
                        <option value="{{ $code }}" {{ $code === ($defaultLanguage ?? 'en') ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                @else
                    <option value="en" selected>English</option>
                    <option value="nl">Dutch</option>
                @endif
            </select>
        </div>

        <!-- Writing Style -->
        <div class="col-md-6 mb-3">
            <label for="ai-writing-style" class="form-label">Writing Style</label>
            <select id="ai-writing-style" class="form-select">
                @if(isset($writingStyles) && is_array($writingStyles))
                    @foreach($writingStyles as $value => $label)
                        <option value="{{ $value }}" {{ $value === 'professional' ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                @else
                    <option value="professional" selected>Professional</option>
                    <option value="casual">Casual</option>
                    <option value="technical">Technical</option>
                    <option value="luxury">Luxury</option>
                @endif
            </select>
        </div>
    </div>

    <!-- Content Type Selection -->
    <div class="mb-3">
        <label class="form-label">Generate Content For:</label>
        <div class="row">
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ai-gen-name" checked>
                    <label class="form-check-label" for="ai-gen-name">Product Name</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ai-gen-description" checked>
                    <label class="form-check-label" for="ai-gen-description">Description</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ai-gen-content" checked>
                    <label class="form-check-label" for="ai-gen-content">Content</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ai-gen-features">
                    <label class="form-check-label" for="ai-gen-features">Features</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Prompt Input -->
    <div class="mb-3">
        <label for="ai-product-prompt" class="form-label">Product Details or Keywords</label>
        <textarea id="ai-product-prompt" class="form-control" rows="3" 
                  placeholder="Enter product details, category, key features, or keywords..."></textarea>
        <small class="form-text text-muted">
            Describe your product, its category, key features, or target audience for better results.
        </small>
    </div>

    <!-- Generate Button -->
    <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-primary" id="ai-generate-product-content">
            <i class="fas fa-magic"></i> Generate Content
        </button>
        <button type="button" class="btn btn-outline-secondary" id="ai-clear-content">
            <i class="fas fa-eraser"></i> Clear
        </button>
    </div>

    <!-- Loading Indicator -->
    <div id="ai-loading" class="text-center" style="display: none;">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Generating...</span>
        </div>
        <span class="ms-2">Generating content...</span>
    </div>

    <!-- Generated Content Preview -->
    <div id="ai-generated-content" class="mt-3" style="display: none;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Generated Content</h6>
                <div>
                    <button type="button" class="btn btn-sm btn-success" id="ai-apply-content">
                        <i class="fas fa-check"></i> Apply to Form
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ai-regenerate">
                        <i class="fas fa-redo"></i> Regenerate
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="ai-content-preview"></div>
            </div>
        </div>
    </div>

@else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        AI Content Generator is not configured. Please check your API settings in 
        <a href="{{ route('ai-content-generator.settings') }}">Settings</a>.
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AI Content Generator for Products
    if (typeof AIContentGenerator !== 'undefined') {
        AIContentGenerator.init('product');
    }
});
</script>
@endpush

                            <input class="form-check-input" type="checkbox" id="generate_content" checked>
                            <label class="form-check-label" for="generate_content">{{ trans('plugins/ai-content-generator::ai-content-generator.generate_content') }}</label>
                        </div>
                        @if(ai_setting('auto_populate_tags', '0') === '1')
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="generate_tags">
                            <label class="form-check-label" for="generate_tags">{{ trans('plugins/ai-content-generator::ai-content-generator.generate_tags') }}</label>
                        </div>
                        @endif
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="generate_ai_content" data-type="product">
                            <i class="fas fa-magic"></i> {{ trans('plugins/ai-content-generator::ai-content-generator.generate_content_btn') }}
                        </button>
                    </div>

                    <div class="mt-3" id="ai_generation_status" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-spinner fa-spin"></i>
                            {{ trans('plugins/ai-content-generator::ai-content-generator.generating') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> {{ trans('plugins/ai-content-generator::ai-content-generator.api_key_required') }}</h6>
                    <p class="mb-2">{{ trans('plugins/ai-content-generator::ai-content-generator.api_key_required_desc') }}</p>
                    <a href="{{ route('ai-content-generator.settings') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-cog"></i> {{ trans('plugins/ai-content-generator::ai-content-generator.configure_now') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
