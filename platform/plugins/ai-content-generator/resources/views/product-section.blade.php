<div class="ai-content-floating-panel" id="ai-product-panel">
    <div class="ai-content-section">
        <h6>
            <i class="fas fa-robot ai-icon"></i>
            AI Product Generator
            @if($isAvailable)
                <span class="ai-status-indicator available" title="AI service is available">●</span>
            @else
                <span class="ai-status-indicator unavailable" title="AI service not configured">●</span>
            @endif
        </h6>
        
        @if($isAvailable)
            <p class="text-muted mb-3 small">
                Generate compelling product content with AI. Enter a title and click generate.
            </p>
            
            <div class="ai-options mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label for="ai-style" class="small">Style</label>
                        <select id="ai-style" class="form-select form-select-sm">
                            @foreach(['professional' => 'Professional', 'casual' => 'Casual', 'technical' => 'Technical', 'luxury' => 'Luxury'] as $value => $label)
                                <option value="{{ $value }}" {{ setting('ai_content_generator.content_style', 'professional') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="ai-language" class="small">Language</label>
                        <select id="ai-language" class="form-select form-select-sm">
                            @php
                                $enabledLanguages = json_decode(setting('ai_content_generator.enabled_languages', '["en","es","fr","de"]'), true) ?: ['en'];
                                $defaultLanguage = setting('ai_content_generator.default_language', 'en');
                                $languageNames = [
                                    'en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German',
                                    'it' => 'Italian', 'pt' => 'Portuguese', 'ru' => 'Russian', 'ja' => 'Japanese',
                                    'ko' => 'Korean', 'zh' => 'Chinese', 'ar' => 'Arabic', 'hi' => 'Hindi'
                                ];
                            @endphp
                            @foreach($enabledLanguages as $langCode)
                                @if(isset($languageNames[$langCode]))
                                    <option value="{{ $langCode }}" {{ $defaultLanguage == $langCode ? 'selected' : '' }}>
                                        {{ $languageNames[$langCode] }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-2 mb-2">
                <button type="button" class="btn ai-generate-btn ai-generate-product-btn btn-sm">
                    <i class="fas fa-magic ai-icon"></i>
                    <span class="btn-text">Generate Content</span>
                </button>
                
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm ai-suggest-description" 
                            title="Generate description only">
                        <i class="fas fa-lightbulb"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm ai-suggest-content"
                            title="Generate detailed content only">
                        <i class="fas fa-file-alt"></i>
                    </button>
                </div>
            </div>
            
            <div class="ai-disclaimer small">
                <i class="fas fa-info-circle"></i>
                AI-generated content will populate the form fields. Review and edit as needed.
            </div>
        @else
            <div class="alert alert-warning small">
                <i class="fas fa-exclamation-triangle"></i>
                AI not configured. 
                <a href="{{ route('ai-content-generator.settings') }}" target="_blank">Setup here</a>.
            </div>
        @endif
    </div>
</div>

<style>
.ai-content-floating-panel {
    position: fixed;
    top: 100px;
    right: 20px;
    width: 300px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 1050;
    max-height: 90vh;
    overflow-y: auto;
}

.ai-content-floating-panel .ai-content-section {
    padding: 15px;
}

.ai-content-floating-panel h6 {
    margin-bottom: 10px;
    font-size: 14px;
}

.ai-status-indicator.available {
    color: #28a745;
}

.ai-status-indicator.unavailable {
    color: #dc3545;
}

@media (max-width: 768px) {
    .ai-content-floating-panel {
        position: relative;
        width: 100%;
        right: auto;
        top: auto;
        margin-bottom: 20px;
    }
}
</style>
