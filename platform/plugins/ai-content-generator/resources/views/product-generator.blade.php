<div class="ai-content-section">
    <h6>
        <i class="fas fa-robot ai-icon"></i>
        Generate Content with AI
        @if($isAvailable)
            <span class="ai-status-indicator available" title="AI service is available">Available</span>
        @else
            <span class="ai-status-indicator unavailable" title="AI service not configured">Unavailable</span>
        @endif
    </h6>
    
    @if($isAvailable)
        <p class="text-muted mb-3">
            Let AI help you create compelling product content. Enter a product title and optional description, then click generate.
        </p>
        
        <div class="ai-options">
            <div class="ai-option-group">
                <label for="ai-style">Content Style</label>
                <select id="ai-style" class="form-select form-select-sm">
                    <option value="professional">Professional</option>
                    <option value="casual">Casual</option>
                    <option value="technical">Technical</option>
                    <option value="luxury">Luxury</option>
                </select>
            </div>
            
            <div class="ai-option-group">
                <label for="ai-language">Language</label>
                <select id="ai-language" class="form-select form-select-sm">
                    <option value="en">English</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                    <option value="de">German</option>
                </select>
            </div>
        </div>
        
        <div class="d-flex gap-2 mb-2">
            <button type="button" class="btn ai-generate-btn ai-generate-product-btn">
                <i class="fas fa-magic ai-icon"></i>
                <span class="btn-text">Generate Product Content</span>
            </button>
            
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm ai-suggest-description ai-tooltip" 
                        data-tooltip="Generate description only">
                    <i class="fas fa-lightbulb"></i> Description
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ai-suggest-content ai-tooltip"
                        data-tooltip="Generate detailed content only">
                    <i class="fas fa-file-alt"></i> Content
                </button>
            </div>
        </div>
        
        <div class="ai-disclaimer">
            <i class="fas fa-info-circle"></i>
            AI-generated content should be reviewed and edited as needed. The generated content will populate the description and content fields below.
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            AI content generation is not available. Please configure your AI API settings in the plugin configuration.
        </div>
    @endif
</div>
