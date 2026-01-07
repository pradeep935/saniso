@php
    $entityType = $options['data']['entityType'] ?? 'unknown';
    $isAvailable = $options['data']['isAvailable'] ?? false;
    $languages = $options['data']['languages'] ?? ['en' => 'English'];
    $defaultLanguage = $options['data']['defaultLanguage'] ?? 'en';
    $writingStyles = $options['data']['writingStyles'] ?? ['professional' => 'Professional'];
    $wordLimits = $options['data']['wordLimits'] ?? [
        'title' => 10,
        'description' => 50,
        'content' => 500,
        'tags' => 5,
        'seoTitle' => 60,
        'seoDescription' => 160,
    ];
@endphp

@if(in_array($entityType, ['post', 'product']))
@pushOnce('header')
<style>
.ai-content-modal .modal-dialog { max-width: 900px; }
.ai-content-preview { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; }
.ai-generated-field { margin-bottom: 1rem; }
.ai-generated-field strong { color: #0d6efd; }
.ai-loading { text-align: center; padding: 2rem; }
.ai-loading .spinner-border { margin-right: 0.5rem; }
.ai-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 1rem; border-radius: 0.375rem; }
.ai-success { background: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 1rem; border-radius: 0.375rem; }
.ai-btn-generate { background: linear-gradient(45deg, #007bff, #0056b3); border: none; }
.ai-btn-generate:hover { background: linear-gradient(45deg, #0056b3, #004085); }
.border-dashed { border-style: dashed !important; }
.notification-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
.notification { padding: 15px 20px; margin-bottom: 10px; border-radius: 5px; color: white; min-width: 300px; opacity: 0.95; }
.notification.success { background-color: #28a745; }
.notification.error { background-color: #dc3545; }
.notification.warning { background-color: #ffc107; color: #212529; }
.notification.info { background-color: #17a2b8; }
</style>
@endPushOnce
@endif

@if(in_array($entityType, ['post', 'product']))
<div class="form-group mb-3" id="ai-content-generator-wrapper">
    <div class="btn-list">
        <button type="button" class="btn btn-primary" id="ai-generate-content-btn" 
                data-entity-type="{{ $entityType }}" 
                {{ !$isAvailable ? 'disabled' : '' }}>
            <i class="fas fa-robot"></i> Generate AI Content
            @if($entityType === 'post')
                <span class="badge bg-info ms-2 text-white">Blog Post</span>
            @elseif($entityType === 'product')
                <span class="badge bg-success ms-2 text-white">Product</span>
            @endif
        </button>
        
        @if($isAvailable)
            <button type="button" class="btn btn-warning" id="ai-translate-content-btn"
                    data-entity-type="{{ $entityType }}">
                <i class="fas fa-language"></i> Translate Content
                <span class="badge bg-light text-dark ms-2">Multi-Language</span>
            </button>
        @endif
        
        @if(!$isAvailable)
            <small class="text-muted d-block mt-1">
                <i class="fas fa-exclamation-triangle"></i>
                AI Content Generator not configured. 
                <a href="{{ route('ai-content-generator.settings') }}">Configure API settings</a>
            </small>
        @endif
    </div>
</div>
@endif

@if(in_array($entityType, ['post', 'product']))
@pushOnce('footer')
<div id="ai-content-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white">
                    <i class="fas fa-robot"></i> AI Content Generator
                    @if($entityType === 'post')
                        <span class="badge bg-light text-primary ms-2">Blog Post Mode</span>
                    @elseif($entityType === 'product')
                        <span class="badge bg-light text-primary ms-2">Product Mode</span>
                    @endif
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>

            <div class="modal-body">
                @if($entityType === 'post')
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-blog"></i> <strong>Blog Post Mode:</strong> 
                        Generating comprehensive, well-structured blog content with longer word limits and detailed formatting suitable for engaging readers.
                    </div>
                @elseif($entityType === 'product')
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-box"></i> <strong>Product Mode:</strong> 
                        Generating concise, persuasive product content with shorter word limits optimized for e-commerce and quick conversions.
                    </div>
                @endif
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ai-modal-language" class="form-label">Language</label>
                        <select id="ai-modal-language" class="form-select">
                            @foreach($languages as $code => $name)
                                <option value="{{ $code }}" {{ $code === $defaultLanguage ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="ai-modal-writing-style" class="form-label">Writing Style</label>
                        <select id="ai-modal-writing-style" class="form-select">
                            @foreach($writingStyles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Generate Content For:</label>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ai-gen-title" checked>
                                <label class="form-check-label" for="ai-gen-title">
                                    @if($entityType === 'product')
                                        Product Name
                                    @else
                                        Title
                                    @endif
                                    <small class="text-muted d-block">({{ $wordLimits['title'] }} words max)</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ai-gen-description" checked>
                                <label class="form-check-label" for="ai-gen-description">
                                    Description
                                    <small class="text-muted d-block">({{ $wordLimits['description'] }} words max)</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ai-gen-content" checked>
                                <label class="form-check-label" for="ai-gen-content">
                                    Content
                                    <small class="text-muted d-block">({{ $wordLimits['content'] }} words max)</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ai-gen-tags">
                                <label class="form-check-label" for="ai-gen-tags">
                                    @if($entityType === 'product')
                                        Product Tags
                                    @else
                                        Tags
                                    @endif
                                    <small class="text-muted d-block">({{ $wordLimits['tags'] }} tags max)</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ai-gen-seo-title" checked>
                                <label class="form-check-label" for="ai-gen-seo-title">
                                    SEO Title
                                    <small class="text-muted d-block">({{ $wordLimits['seoTitle'] }} chars max)</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ai-gen-seo-description" checked>
                                <label class="form-check-label" for="ai-gen-seo-description">
                                    SEO Description
                                    <small class="text-muted d-block">({{ $wordLimits['seoDescription'] }} chars max)</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="ai-modal-prompt" class="form-label">
                        @if($entityType === 'product')
                            Product Details or Keywords
                        @else
                            Topic or Prompt
                        @endif
                    </label>
                    <textarea id="ai-modal-prompt" class="form-control" rows="3" 
                              placeholder="@if($entityType === 'product')Enter product details, key features, or keywords...@else Enter your blog topic, keywords, or specific instructions...@endif"></textarea>
                    <small class="form-text text-muted">
                        @if($entityType === 'product')
                            Describe your product, its key features, or target audience for better results.
                        @else
                            Describe what you want to write about. Be specific for better results.
                        @endif
                    </small>
                </div>

                <div id="ai-modal-loading" class="text-center" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Generating...</span>
                    </div>
                    <span class="ms-2">Generating content...</span>
                </div>

                <div id="ai-modal-generated-content" class="mt-3" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Generated Content</h6>
                        </div>
                        <div class="card-body">
                            <div id="ai-modal-content-preview"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="ai-modal-generate">
                    <i class="fas fa-magic"></i> Generate Content
                </button>
                <button type="button" class="btn btn-success" id="ai-modal-apply" style="display: none;">
                    <i class="fas fa-check"></i> Apply to Form
                </button>
            </div>
        </div>
    </div>
</div>
@endPushOnce
@endif

@if(in_array($entityType, ['post', 'product']))
@pushOnce('footer')
<div id="ai-translate-modal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white">
                    <i class="fas fa-language"></i> AI Content Translator
                    @if($entityType === 'post')
                        <span class="badge bg-light text-warning ms-2">Blog Post Translation</span>
                    @elseif($entityType === 'product')
                        <span class="badge bg-light text-warning ms-2">Product Translation</span>
                    @endif
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-info-circle"></i> <strong>Translation Mode:</strong> 
                    Translate your existing content to multiple languages while maintaining context and tone.
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="ai-translate-source-lang" class="form-label">Source Language</label>
                        <select id="ai-translate-source-lang" class="form-select">
                            <option value="">-- Select Source Language --</option>
                            @foreach($languages as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ai-translate-target-lang" class="form-label">Target Language</label>
                        <select id="ai-translate-target-lang" class="form-select">
                            <option value="">-- Select Target Language --</option>
                            @foreach($languages as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ai-translate-tone" class="form-label">Translation Tone</label>
                        <select id="ai-translate-tone" class="form-select">
                            <option value="formal">Formal</option>
                            <option value="casual">Casual</option>
                            <option value="professional" selected>Professional</option>
                            <option value="friendly">Friendly</option>
                            <option value="direct">Direct (Exact Translation)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary" id="ai-load-current-content-btn">
                        <i class="fas fa-download"></i> Load Current Form Content
                    </button>
                    <small class="text-muted ms-2">Load content from the current form fields</small>
                </div>

                <div id="ai-translate-loading" class="text-center" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-warning" role="status">
                        <span class="visually-hidden">Translating...</span>
                    </div>
                    <span class="ms-2">Translating content...</span>
                </div>

                <div id="ai-translate-results" class="mt-3" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Translation Results</h6>
                        </div>
                        <div class="card-body">
                            <div id="ai-translate-preview"></div>
                        </div>
                    </div>
                </div>

                <div id="ai-translate-error" class="alert alert-danger mt-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="ai-translate-error-message">Translation failed</span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-warning" id="ai-translate-generate-btn">
                    <i class="fas fa-language"></i> Translate Content
                </button>
                <button type="button" class="btn btn-success" id="ai-translate-apply-btn" style="display: none;">
                    <i class="fas fa-check"></i> Apply Translation
                </button>
            </div>
        </div>
    </div>
</div>
@endPushOnce
@endif

@if(in_array($entityType, ['post', 'product']))
@pushOnce('footer')
<script>
let aiModal = null;
let translateModal = null;

function initAIModals() {
    const modalElement = document.getElementById('ai-content-modal');
    const translateModalElement = document.getElementById('ai-translate-modal');
    
    if (modalElement && typeof bootstrap !== 'undefined') {
        try {
            aiModal = new bootstrap.Modal(modalElement);
        } catch (error) {
        }
    }
    
    if (translateModalElement && typeof bootstrap !== 'undefined') {
        try {
            translateModal = new bootstrap.Modal(translateModalElement);
        } catch (error) {
        }
    }
}

document.addEventListener('click', function(e) {
    if (e.target.id === 'ai-generate-content-btn' || e.target.closest('#ai-generate-content-btn')) {
        e.preventDefault();
        const button = e.target.id === 'ai-generate-content-btn' ? e.target : e.target.closest('#ai-generate-content-btn');
        if (!button.disabled) {
            if (!aiModal) {
                initAIModals();
            }
            if (aiModal) {
                aiModal.show();
            }
        }
        return;
    }
    
    if (e.target.id === 'ai-translate-content-btn' || e.target.closest('#ai-translate-content-btn')) {
        e.preventDefault();
        const button = e.target.id === 'ai-translate-content-btn' ? e.target : e.target.closest('#ai-translate-content-btn');
        if (!button.disabled) {
            if (!translateModal) {
                initAIModals();
            }
            if (translateModal) {
                translateModal.show();
            }
        }
        return;
    }
    
    if (e.target.id === 'ai-modal-generate') {
        e.preventDefault();
        generateAIContent();
        return;
    }
    
    if (e.target.id === 'ai-modal-apply') {
        e.preventDefault();
        applyGeneratedContent();
        if (aiModal) {
            aiModal.hide();
        }
        return;
    }
    
    if (e.target.id === 'ai-load-current-content-btn') {
        e.preventDefault();
        loadCurrentContent();
        return;
    }
    
    if (e.target.id === 'ai-translate-generate-btn') {
        e.preventDefault();
        translateContent();
        return;
    }
    
    if (e.target.id === 'ai-translate-apply-btn') {
        e.preventDefault();
        applyTranslatedContent();
        return;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initAIModals, 100);
    createNotificationContainer();
    
    // Add language dropdown interaction for translation
    const sourceLanguageSelect = document.getElementById('ai-translate-source-lang');
    const targetLanguageSelect = document.getElementById('ai-translate-target-lang');
    
    if (sourceLanguageSelect && targetLanguageSelect) {
        sourceLanguageSelect.addEventListener('change', function() {
            const selectedSource = this.value;
            const targetOptions = targetLanguageSelect.querySelectorAll('option');
            
            targetOptions.forEach(option => {
                if (option.value === '') {
                    // Keep the placeholder option
                    option.style.display = '';
                } else if (option.value === selectedSource) {
                    // Hide the same language option in target
                    option.style.display = 'none';
                    if (option.selected) {
                        targetLanguageSelect.value = '';
                    }
                } else {
                    // Show other language options
                    option.style.display = '';
                }
            });
        });
    }
});

if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        setTimeout(initAIModals, 200);
        createNotificationContainer();
    });
}

function createNotificationContainer() {
    if (!document.getElementById('ai-notification-container')) {
        const container = document.createElement('div');
        container.id = 'ai-notification-container';
        container.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('ai-notification-container');
    if (!container) {
        createNotificationContainer();
        return showNotification(message, type, duration);
    }
    
    const notification = document.createElement('div');
    const typeClasses = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    notification.className = `alert ${typeClasses[type] || 'alert-info'} alert-dismissible fade show mb-2`;
    notification.style.cssText = `
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    `;
    
    notification.innerHTML = `
        <i class="${icons[type] || 'fas fa-info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    container.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, duration);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .border-dashed {
        border: 1px dashed #dee2e6 !important;
    }
`;
document.head.appendChild(style);

function generateAIContent() {
    const entityType = document.querySelector('#ai-generate-content-btn')?.dataset.entityType || 'post';
    const generateBtn = document.getElementById('ai-modal-generate');
    const applyBtn = document.getElementById('ai-modal-apply');
    const loadingDiv = document.getElementById('ai-modal-loading');
    const contentDiv = document.getElementById('ai-modal-generated-content');
    const previewDiv = document.getElementById('ai-modal-content-preview');
    const promptField = document.getElementById('ai-modal-prompt');
    const languageField = document.getElementById('ai-modal-language');
    const writingStyleField = document.getElementById('ai-modal-writing-style');
    
    const generateTitle = document.getElementById('ai-gen-title')?.checked || false;
    const generateDescription = document.getElementById('ai-gen-description')?.checked || false;
    const generateContent = document.getElementById('ai-gen-content')?.checked || false;
    const generateTags = document.getElementById('ai-gen-tags')?.checked || false;
    const generateSeoTitle = document.getElementById('ai-gen-seo-title')?.checked || false;
    const generateSeoDescription = document.getElementById('ai-gen-seo-description')?.checked || false;
    
    if (!generateBtn || !loadingDiv || !contentDiv) {
        return;
    }
    
    const prompt = promptField ? promptField.value.trim() : '';
    if (!prompt) {
        showNotification('Please enter a prompt', 'warning');
        return;
    }
    
    loadingDiv.style.display = 'block';
    contentDiv.style.display = 'none';
    if (applyBtn) applyBtn.style.display = 'none';
    generateBtn.disabled = true;
    generateBtn.textContent = 'Generating...';
    
    fetch('/admin/ai-content-generator/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            entity_type: entityType,
            language: languageField ? languageField.value : 'en',
            writing_style: writingStyleField ? writingStyleField.value : 'professional',
            prompt: prompt,
            generate_title: generateTitle,
            generate_description: generateDescription,
            generate_content: generateContent,
            generate_tags: generateTags,
            generate_seo_title: generateSeoTitle,
            generate_seo_description: generateSeoDescription
        })
    })
    .then(response => response.json())
    .then(data => {
        loadingDiv.style.display = 'none';
        generateBtn.disabled = false;
        generateBtn.textContent = 'Generate Content';

        if (data.success) {
            window.aiGeneratedContent = data.data;
            
            if (previewDiv) {
                let html = '';
                
                if (data.data.title) {
                    html += `<div class="mb-2"><strong>${entityType === 'product' ? 'Product Name' : 'Title'}:</strong><br><div class="p-2 bg-light border rounded">${data.data.title}</div></div>`;
                }
                
                if (data.data.description) {
                    html += `<div class="mb-2"><strong>Description:</strong><br><div class="p-2 bg-light border rounded">${data.data.description}</div></div>`;
                }
                
                if (data.data.content) {
                    html += `<div class="mb-2"><strong>Content:</strong><br><div class="p-2 bg-light border rounded" style="max-height: 200px; overflow-y: auto;">${data.data.content}</div></div>`;
                }
                
                if (data.data.tags && data.data.tags.length > 0) {
                    html += `<div class="mb-2"><strong>${entityType === 'product' ? 'Product Tags' : 'Tags'}:</strong><br><div class="p-2 bg-light border rounded">${data.data.tags.join(', ')}</div></div>`;
                }
                
                if (data.data.seo_title) {
                    html += `<div class="mb-2"><strong>SEO Title:</strong><br><div class="p-2 bg-light border rounded"><small class="text-success">${data.data.seo_title}</small></div></div>`;
                }
                
                if (data.data.seo_description) {
                    html += `<div class="mb-2"><strong>SEO Description:</strong><br><div class="p-2 bg-light border rounded"><small class="text-success">${data.data.seo_description}</small></div></div>`;
                }
                
                previewDiv.innerHTML = html;
            }
            
            contentDiv.style.display = 'block';
            if (applyBtn) applyBtn.style.display = 'inline-block';
        } else {
            showNotification('Error generating content: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        loadingDiv.style.display = 'none';
        generateBtn.disabled = false;
        generateBtn.textContent = 'Generate Content';
        showNotification('Error generating content: ' + error.message, 'error');
    });
}

function applyGeneratedContent() {
    if (!window.aiGeneratedContent) {
        showNotification('No content to apply', 'warning');
        return;
    }
    
    const content = window.aiGeneratedContent;
    
    if (content.title) {
        const titleSelectors = ['input[name="name"]', 'input[name="title"]', '#name', '#title'];
        for (const selector of titleSelectors) {
            const field = document.querySelector(selector);
            if (field) {
                field.value = content.title;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
    
    if (content.description) {
        const descriptionCKEditor = document.querySelector('.ckeditor-description-inline');
        let ckEditorApplied = false;
        
        if (descriptionCKEditor) {
            try {
                let editorInstance = null;
                
                if (descriptionCKEditor.ckeditorInstance) {
                    editorInstance = descriptionCKEditor.ckeditorInstance;
                }
                
                if (!editorInstance && typeof CKEDITOR !== 'undefined') {
                    for (const name in CKEDITOR.instances) {
                        const instance = CKEDITOR.instances[name];
                        if (instance.element && instance.element.$ === descriptionCKEditor) {
                            editorInstance = instance;
                            break;
                        }
                    }
                }
                
                if (editorInstance && editorInstance.setData) {
                    editorInstance.setData(content.description);
                    ckEditorApplied = true;
                } else if (descriptionCKEditor.contentEditable === 'true') {
                    descriptionCKEditor.innerHTML = content.description;
                    descriptionCKEditor.dispatchEvent(new Event('input', { bubbles: true }));
                    ckEditorApplied = true;
                }
            } catch (error) {
            }
        }
        
        if (!ckEditorApplied) {
            const descSelectors = ['textarea[name="description"]', '#description'];
            for (const selector of descSelectors) {
                const field = document.querySelector(selector);
                if (field) {
                    field.value = content.description.replace(/<[^>]*>/g, '');
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    break;
                }
            }
        }
    }
    
    if (content.content) {
        const contentCKEditor = document.querySelector('.ckeditor-content-inline');
        let ckEditorContentApplied = false;
        
        if (contentCKEditor) {
            try {
                let editorInstance = null;
                
                if (contentCKEditor.ckeditorInstance) {
                    editorInstance = contentCKEditor.ckeditorInstance;
                }
                
                if (!editorInstance && typeof CKEDITOR !== 'undefined') {
                    for (const name in CKEDITOR.instances) {
                        const instance = CKEDITOR.instances[name];
                        if (instance.element && instance.element.$ === contentCKEditor) {
                            editorInstance = instance;
                            break;
                        }
                    }
                }
                
                if (editorInstance && editorInstance.setData) {
                    editorInstance.setData(content.content);
                    ckEditorContentApplied = true;
                } else if (contentCKEditor.contentEditable === 'true') {
                    contentCKEditor.innerHTML = content.content;
                    contentCKEditor.dispatchEvent(new Event('input', { bubbles: true }));
                    ckEditorContentApplied = true;
                }
            } catch (error) {
            }
        }
        
        if (!ckEditorContentApplied) {
            const contentSelectors = ['textarea[name="content"]', '#content'];
            for (const selector of contentSelectors) {
                const field = document.querySelector(selector);
                if (field) {                    field.value = content.content.replace(/<[^>]*>/g, '');
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    updateRichEditor(field, content.content);
                    break;
                }
            }
        }
    }

    if (content.tags && content.tags.length > 0) {
        const tagSelectors = [
            'input[name="tag"]', 
            'input[name="tags"]', 
            '#tag', 
            '#tags',
            '.tagify__input',
            '.tags-input input'
        ];
        
        for (const selector of tagSelectors) {
            const field = document.querySelector(selector);
            if (field) {
                const tagString = content.tags.join(',');
                field.value = tagString;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                
                if (field.tagify) {
                    field.tagify.addTags(content.tags);
                } else if (window.Tagify && field.classList.contains('tagify__input')) {
                    const tagify = new Tagify(field);
                    tagify.addTags(content.tags);
                }
                
                break;
            }
        }
        
        if (!document.querySelector('input[name="tag"], input[name="tags"]')) {
            const tagContainer = document.querySelector('.tag-container, .tags-container, #tag-container');
            if (tagContainer) {
                content.tags.forEach(tag => {
                    const tagElement = document.createElement('span');
                    tagElement.className = 'badge bg-primary me-1';
                    tagElement.textContent = tag;
                    tagContainer.appendChild(tagElement);
                });
            }
        }
    }

    if (content.seo_title) {
        const seoTitleSelectors = [
            'input[name="seo_meta[seo_title]"]',
            'input[name="seo_meta[title]"]',
            'input[name="seo_title"]'
        ];
        for (const selector of seoTitleSelectors) {
            const field = document.querySelector(selector);
            if (field) {
                field.value = content.seo_title;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
    
    if (content.seo_description) {
        const seoDescSelectors = [
            'textarea[name="seo_meta[seo_description]"]',
            'textarea[name="seo_meta[description]"]',
            'textarea[name="seo_description"]'
        ];
        for (const selector of seoDescSelectors) {
            const field = document.querySelector(selector);
            if (field) {
                field.value = content.seo_description;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
    
    showNotification('Content applied successfully!', 'success');
    
    const modal = document.getElementById('ai-content-modal');
    if (modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
    
    window.aiGeneratedContent = null;
}

function updateRichEditor(field, content) {
    const fieldId = field.id || field.name || 'content';
    
    if (typeof tinymce !== 'undefined') {
        const editor = tinymce.get(fieldId);
        if (editor) {
            editor.setContent(content);
            return;
        }
    }
    
    const editorElement = document.querySelector(`#${fieldId}`);
    if (editorElement) {
        const editorContainer = editorElement.nextElementSibling;
        if (editorContainer && editorContainer.classList.contains('ck-editor')) {
            const editorInstance = editorContainer.ckeditorInstance;
            if (editorInstance) {
                editorInstance.setData(content);
                return;
            }
        }
    }
    
    if (typeof CKEDITOR !== 'undefined') {
        const editor = CKEDITOR.instances[fieldId];
        if (editor) {
            editor.setData(content);
            return;
        }
    }
}

function loadCurrentContent() {
    const nameField = document.querySelector('input[name="name"]') || document.querySelector('input[name="title"]');
    let descriptionContent = '';
    let mainContent = '';
    
    const descriptionCKEditor = document.querySelector('.ckeditor-description-inline');
    if (descriptionCKEditor && descriptionCKEditor.innerHTML.trim()) {
        descriptionContent = descriptionCKEditor.innerHTML;
    } else {
        const descField = document.querySelector('textarea[name="description"]');
        descriptionContent = descField ? descField.value : '';
    }
    
    const contentCKEditor = document.querySelector('.ckeditor-content-inline');
    if (contentCKEditor && contentCKEditor.innerHTML.trim()) {
        mainContent = contentCKEditor.innerHTML;
    } else {
        const contentField = document.querySelector('textarea[name="content"]');
        mainContent = contentField ? contentField.value : '';
    }
    
    const seoTitleField = document.querySelector('input[name="seo_meta[seo_title]"]') || 
                         document.querySelector('input[name="seo_meta[title]"]') || 
                         document.querySelector('input[name="seo_title"]');
    const seoDescField = document.querySelector('textarea[name="seo_meta[seo_description]"]') || 
                        document.querySelector('textarea[name="seo_meta[description]"]') || 
                        document.querySelector('textarea[name="seo_description"]');
    
    const currentContent = {
        title: nameField ? nameField.value : '',
        description: descriptionContent,
        content: mainContent,
        seo_title: seoTitleField ? seoTitleField.value : '',
        seo_description: seoDescField ? seoDescField.value : ''
    };
    
    window.currentContentForTranslation = currentContent;
    
    const previewDiv = document.getElementById('ai-translate-preview');
    if (previewDiv) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="fas fa-file-alt"></i> Original Content</h6>
                    <div class="border rounded p-3 bg-light" style="min-height: 200px;">
        `;
        
        if (currentContent.title) {
            html += `<div class="mb-3"><strong>Title:</strong><br><span class="text-dark">${currentContent.title}</span></div>`;
        }
        
        if (currentContent.description) {
            html += `<div class="mb-3"><strong>Description:</strong><br><div class="text-dark" style="max-height: 150px; overflow-y: auto;">${currentContent.description}</div></div>`;
        }
        
        if (currentContent.content) {
            html += `<div class="mb-3"><strong>Content:</strong><br><div class="text-dark" style="max-height: 200px; overflow-y: auto;">${currentContent.content}</div></div>`;
        }
        
        if (currentContent.seo_title) {
            html += `<div class="mb-3"><strong>SEO Title:</strong><br><div class="text-dark bg-warning bg-opacity-10 p-2 rounded">${currentContent.seo_title}</div></div>`;
        } else {
            html += `<div class="mb-3"><strong>SEO Title:</strong><br><div class="text-muted bg-light p-2 rounded border-dashed"><i class="fas fa-info-circle"></i> No SEO title found - will be auto-generated during translation</div></div>`;
        }
        
        if (currentContent.seo_description) {
            html += `<div class="mb-3"><strong>SEO Description:</strong><br><div class="text-dark bg-warning bg-opacity-10 p-2 rounded" style="max-height: 100px; overflow-y: auto;">${currentContent.seo_description}</div></div>`;
        } else {
            html += `<div class="mb-3"><strong>SEO Description:</strong><br><div class="text-muted bg-light p-2 rounded border-dashed"><i class="fas fa-info-circle"></i> No SEO description found - will be auto-generated during translation</div></div>`;
        }
        
        if (!currentContent.title && !currentContent.description && !currentContent.content && !currentContent.seo_title && !currentContent.seo_description) {
            html += '<div class="text-muted"><i class="fas fa-info-circle"></i> No content found in form fields.</div>';
        }
        
        html += `
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-language"></i> Translated Content</h6>
                    <div class="border rounded p-3 bg-success bg-opacity-10" style="min-height: 200px;">
                        <div class="text-muted text-center mt-5">
                            <i class="fas fa-arrow-right fa-2x mb-3"></i><br>
                            Click "Translate Content" to generate translation
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        previewDiv.innerHTML = html;
        document.getElementById('ai-translate-results').style.display = 'block';
    }
}

function translateContent() {
    if (!window.currentContentForTranslation) {
        showNotification('Please load current content first', 'warning');
        return;
    }
    
    const sourceLanguage = document.getElementById('ai-translate-source-lang')?.value;
    const targetLanguage = document.getElementById('ai-translate-target-lang')?.value;
    const tone = document.getElementById('ai-translate-tone')?.value;
    
    // Validate language selection
    if (!sourceLanguage) {
        showNotification('Please select a source language', 'warning');
        return;
    }
    
    if (!targetLanguage) {
        showNotification('Please select a target language', 'warning');
        return;
    }
    
    if (sourceLanguage === targetLanguage) {
        showNotification('Source and target languages must be different', 'warning');
        return;
    }
    
    const translateBtn = document.getElementById('ai-translate-generate-btn');
    const loadingDiv = document.getElementById('ai-translate-loading');
    const resultsDiv = document.getElementById('ai-translate-results');
    const errorDiv = document.getElementById('ai-translate-error');
    const applyBtn = document.getElementById('ai-translate-apply-btn');
    
    loadingDiv.style.display = 'block';
    errorDiv.style.display = 'none';
    translateBtn.disabled = true;
    translateBtn.textContent = 'Translating...';
    
    fetch('/admin/ai-content/translate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            content_type: '{{ $entityType }}', // Dynamic content type
            source_content: window.currentContentForTranslation,
            source_language: sourceLanguage,
            target_language: targetLanguage,
            tone: tone
        })
    })
    .then(response => {
        console.log('🔄 Translation API Response Status:', response.status);
        if (!response.ok) {
            throw new Error(`Translation API failed with status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Real AI Translation Response:', data);
        if (data.success && data.data) {
            handleTranslationResponse(data);
        } else {
            throw new Error(data.message || 'AI translation failed');
        }
    })
    .catch(error => {
        console.error('❌ Translation Failed:', error);
        
        // Show error instead of fallback
        loadingDiv.style.display = 'none';
        translateBtn.disabled = false;
        translateBtn.textContent = 'Translate Content';
        
        errorDiv.style.display = 'block';
        const errorMessage = document.getElementById('ai-translate-error-message');
        if (errorMessage) {
            errorMessage.textContent = `Translation failed: ${error.message}. Please check your API configuration and try again.`;
        }
        
        showNotification('❌ AI Translation Error: ' + error.message, 'error');
    });
    
    function handleTranslationResponse(data) {
        loadingDiv.style.display = 'none';
        translateBtn.disabled = false;
        translateBtn.textContent = 'Translate Content';

        if (data.success) {
            window.translatedContent = data.data;
            
            const previewDiv = document.getElementById('ai-translate-preview');
            if (previewDiv) {
                // Get language names from dropdown options
                const sourceSelect = document.getElementById('ai-translate-source-lang');
                const targetSelect = document.getElementById('ai-translate-target-lang');
                const sourceLangName = sourceSelect.options[sourceSelect.selectedIndex]?.text || sourceLanguage.toUpperCase();
                const targetLangName = targetSelect.options[targetSelect.selectedIndex]?.text || targetLanguage.toUpperCase();
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-file-alt"></i> Original Content (${sourceLangName})</h6>
                            <div class="border rounded p-3 bg-light" style="min-height: 300px;">
                `;
                
                const original = window.currentContentForTranslation;
                if (original.title) {
                    html += `<div class="mb-3"><strong>Title:</strong><br><span class="text-dark">${original.title}</span></div>`;
                }
                
                if (original.description) {
                    html += `<div class="mb-3"><strong>Description:</strong><br><div class="text-dark" style="max-height: 150px; overflow-y: auto;">${original.description}</div></div>`;
                }
                
                if (original.content) {
                    html += `<div class="mb-3"><strong>Content:</strong><br><div class="text-dark" style="max-height: 200px; overflow-y: auto;">${original.content}</div></div>`;
                }
                
                if (original.seo_title) {
                    html += `<div class="mb-3"><strong>SEO Title:</strong><br><div class="text-dark bg-warning bg-opacity-10 p-2 rounded">${original.seo_title}</div></div>`;
                } else {
                    html += `<div class="mb-3"><strong>SEO Title:</strong><br><div class="text-muted bg-light p-2 rounded"><small><i class="fas fa-info-circle"></i> No SEO title - will generate new</small></div></div>`;
                }
                
                if (original.seo_description) {
                    html += `<div class="mb-3"><strong>SEO Description:</strong><br><div class="text-dark bg-warning bg-opacity-10 p-2 rounded" style="max-height: 100px; overflow-y: auto;">${original.seo_description}</div></div>`;
                } else {
                    html += `<div class="mb-3"><strong>SEO Description:</strong><br><div class="text-muted bg-light p-2 rounded"><small><i class="fas fa-info-circle"></i> No SEO description - will generate new</small></div></div>`;
                }
                
                html += `
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-language"></i> Translated Content (${targetLangName})</h6>
                            <div class="border rounded p-3 bg-white" style="min-height: 300px;">
                `;
                
                if (data.data.title) {
                    html += `<div class="mb-3"><strong>Title:</strong><br><span class="text-success">${data.data.title}</span></div>`;
                }
                
                if (data.data.description) {
                    html += `<div class="mb-3"><strong>Description:</strong><br><div class="text-success" style="max-height: 150px; overflow-y: auto;">${data.data.description}</div></div>`;
                }
                
                if (data.data.content) {
                    html += `<div class="mb-3"><strong>Content:</strong><br><div class="text-success" style="max-height: 200px; overflow-y: auto;">${data.data.content}</div></div>`;
                }
                
                if (data.data.seo_title) {
                    html += `<div class="mb-3"><strong>SEO Title:</strong><br><div class="text-dark bg-success bg-opacity-10 p-2 rounded">${data.data.seo_title}</div></div>`;
                }
                
                if (data.data.seo_description) {
                    html += `<div class="mb-3"><strong>SEO Description:</strong><br><div class="text-dark bg-success bg-opacity-10 p-2 rounded" style="max-height: 100px; overflow-y: auto;">${data.data.seo_description}</div></div>`;
                }
                
                html += `
                            </div>
                        </div>
                    </div>
                `;
                
                previewDiv.innerHTML = html;
                resultsDiv.style.display = 'block';
                applyBtn.style.display = 'inline-block';
                
                showNotification('Content translated successfully!', 'success');
            }
        } else {
            errorDiv.style.display = 'block';
            const errorMessage = document.getElementById('ai-translate-error-message');
            if (errorMessage) {
                errorMessage.textContent = data.error || 'Translation failed';
            }
            showNotification('Translation failed: ' + (data.error || 'Unknown error'), 'error');
        }
    }
    
}

function applyTranslatedContent() {
    if (!window.translatedContent) {
        showNotification('No translated content to apply', 'warning');
        return;
    }
    
    const content = window.translatedContent;
    
    if (content.title) {
        const titleSelectors = ['input[name="name"]', 'input[name="title"]'];
        for (const selector of titleSelectors) {
            const field = document.querySelector(selector);
            if (field) {
                field.value = content.title;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
    
    if (content.description) {
        const descriptionCKEditor = document.querySelector('.ckeditor-description-inline');
        let descApplied = false;
        
        if (descriptionCKEditor) {
            try {
                if (descriptionCKEditor.ckeditorInstance && descriptionCKEditor.ckeditorInstance.setData) {
                    descriptionCKEditor.ckeditorInstance.setData(content.description);
                    descApplied = true;
                } else if (descriptionCKEditor.contentEditable === 'true') {
                    descriptionCKEditor.innerHTML = content.description;
                    descriptionCKEditor.dispatchEvent(new Event('input', { bubbles: true }));
                    descApplied = true;
                }
            } catch (error) {
            }
        }
        
        if (!descApplied) {
            const descField = document.querySelector('textarea[name="description"]');
            if (descField) {
                descField.value = content.description.replace(/<[^>]*>/g, '');
                descField.dispatchEvent(new Event('input', { bubbles: true }));
                descField.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }
    
    if (content.content) {
        const contentCKEditor = document.querySelector('.ckeditor-content-inline');
        let contentApplied = false;
        
        if (contentCKEditor) {
            try {
                if (contentCKEditor.ckeditorInstance && contentCKEditor.ckeditorInstance.setData) {
                    contentCKEditor.ckeditorInstance.setData(content.content);
                    contentApplied = true;
                } else if (contentCKEditor.contentEditable === 'true') {
                    contentCKEditor.innerHTML = content.content;
                    contentCKEditor.dispatchEvent(new Event('input', { bubbles: true }));
                    contentApplied = true;
                }
            } catch (error) {
            }
        }
        
        if (!contentApplied) {
            const contentField = document.querySelector('textarea[name="content"]');
            if (contentField) {
                contentField.value = content.content.replace(/<[^>]*>/g, '');
                contentField.dispatchEvent(new Event('input', { bubbles: true }));
                contentField.dispatchEvent(new Event('change', { bubbles: true }));
                updateRichEditor(contentField, content.content);
            }
        }
    }
    
    if (content.seo_title) {
        const seoTitleSelectors = [
            'input[name="seo_meta[seo_title]"]',
            'input[name="seo_meta[title]"]',
            'input[name="seo_title"]'
        ];
        for (const selector of seoTitleSelectors) {
            const field = document.querySelector(selector);
            if (field) {
                field.value = content.seo_title;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
    
    if (content.seo_description) {
        const seoDescSelectors = [
            'textarea[name="seo_meta[seo_description]"]',
            'textarea[name="seo_meta[description]"]',
            'textarea[name="seo_description"]'
        ];
        for (const selector of seoDescSelectors) {
            const field = document.querySelector(selector);
            if (field) {
                field.value = content.seo_description;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                break;
            }
        }
    }
    
    showNotification('Translation applied successfully!', 'success');
    
    const modal = document.getElementById('ai-translate-modal');
    if (modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        } else if (translateModal) {
            translateModal.hide();
        }
    }
    
    window.translatedContent = null;
}
</script>
@endPushOnce
@endif
