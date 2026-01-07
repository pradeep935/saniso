<!-- AI Content Generator for Blog Posts -->
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
                        <option value="{{ $value }}" {{ $value === 'informative' ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                @else
                    <option value="informative" selected>Informative</option>
                    <option value="professional">Professional</option>
                    <option value="casual">Casual</option>
                    <option value="technical">Technical</option>
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
                    <input class="form-check-input" type="checkbox" id="ai-gen-title" checked>
                    <label class="form-check-label" for="ai-gen-title">Title</label>
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
                    <input class="form-check-input" type="checkbox" id="ai-gen-tags">
                    <label class="form-check-label" for="ai-gen-tags">Tags</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Prompt Input -->
    <div class="mb-3">
        <label for="ai-blog-prompt" class="form-label">Topic or Prompt</label>
        <textarea id="ai-blog-prompt" class="form-control" rows="3" 
                  placeholder="Enter your blog topic, keywords, or specific instructions..."></textarea>
        <small class="form-text text-muted">
            Describe what you want to write about. Be specific for better results.
        </small>
    </div>

    <!-- Generate Button -->
    <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-primary" id="ai-generate-blog-content">
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
    // Initialize AI Content Generator for Blog Posts
    if (typeof AIContentGenerator !== 'undefined') {
        AIContentGenerator.init('blog');
    }
});
</script>
@endpush
