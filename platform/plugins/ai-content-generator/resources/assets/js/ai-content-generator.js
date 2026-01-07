/**
 * AI Content Generator JavaScript - Production Version
 * Supports: Posts and Products only
 */
class AIContentGenerator {
    constructor() {
        this.isGenerating = false;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Product AI generation
        $(document).on('click', '.ai-generate-product-btn', (e) => {
            e.preventDefault();
            this.generateProductContent();
        });

        // Blog AI generation
        $(document).on('click', '.ai-generate-blog-btn', (e) => {
            e.preventDefault();
            this.generateBlogContent();
        });

        // AI suggestion buttons for individual fields
        $(document).on('click', '.ai-suggest-description', (e) => {
            e.preventDefault();
            this.generateDescription('product');
        });

        $(document).on('click', '.ai-suggest-content', (e) => {
            e.preventDefault();
            this.generateContent('product');
        });
    }

    generateProductContent() {
        if (this.isGenerating) return;

        const title = this.getFieldValue('name');
        const description = this.getFieldValue('description');

        if (!title.trim()) {
            this.showError('Please enter a product title first');
            return;
        }

        this.setGeneratingState(true);
        this.showProgress('Generating product content...');

        // Get product-specific options
        const style = $('#ai-style').val() || 'professional';
        const language = $('#ai-language').val() || 'en';

        $.ajax({
            url: '/admin/ai-content-generator/generate',
            method: 'POST',
            data: {
                entity_type: 'product',
                prompt: `${title}${description ? ' - ' + description : ''}`,
                language: language,
                writing_style: style,
                generate_title: true,
                generate_description: true,
                generate_content: true,
                generate_tags: true,
                generate_seo_title: true,
                generate_seo_description: true,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    this.populateProductFields(response.data);
                    this.showSuccess('Product content generated successfully!');
                } else {
                    this.showError(response.message || 'Failed to generate content');
                }
            },
            error: (xhr) => {
                const response = xhr.responseJSON;
                this.showError(response?.message || 'An error occurred while generating content');
            },
            complete: () => {
                this.setGeneratingState(false);
                this.hideProgress();
            }
        });
    }

    generateBlogContent() {
        if (this.isGenerating) return;

        const title = this.getFieldValue('name');
        const description = this.getFieldValue('description');

        if (!title.trim()) {
            this.showError('Please enter a blog title first');
            return;
        }

        this.setGeneratingState(true);
        this.showProgress('Generating blog content...');

        // Get blog-specific options
        const style = $('#ai-blog-style').val() || 'informative';
        const language = $('#ai-blog-language').val() || 'en';

        $.ajax({
            url: '/admin/ai-content-generator/generate',
            method: 'POST',
            data: {
                entity_type: 'post',
                prompt: `${title}${description ? ' - ' + description : ''}`,
                language: language,
                writing_style: style,
                generate_title: true,
                generate_description: true,
                generate_content: true,
                generate_tags: true,
                generate_seo_title: true,
                generate_seo_description: true,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    this.populateBlogFields(response.data);
                    this.showSuccess('Blog content generated successfully!');
                } else {
                    this.showError(response.message || 'Failed to generate content');
                }
            },
            error: (xhr) => {
                const response = xhr.responseJSON;
                this.showError(response?.message || 'An error occurred while generating content');
            },
            complete: () => {
                this.setGeneratingState(false);
                this.hideProgress();
            }
        });
    }

    populateProductFields(data) {
        // Populate title/name
        if (data.title) {
            this.setFieldValue('name', data.title);
        }

        // Populate description
        if (data.description) {
            this.setFieldValue('description', data.description);
        }

        // Populate content (rich text editor)
        if (data.content) {
            this.setEditorContent('content', data.content);
        }

        // Populate tags
        if (data.tags && Array.isArray(data.tags)) {
            const tagsString = data.tags.join(',');
            this.setFieldValue('tag', tagsString);
        }

        // Populate SEO fields
        if (data.seo_title) {
            this.setFieldValue('seo_meta[seo_title]', data.seo_title);
        }

        if (data.seo_description) {
            this.setFieldValue('seo_meta[seo_description]', data.seo_description);
        }
    }

    populateBlogFields(data) {
        // Populate title/name
        if (data.title) {
            this.setFieldValue('name', data.title);
        }

        // Populate description
        if (data.description) {
            this.setFieldValue('description', data.description);
        }

        // Populate content (rich text editor)
        if (data.content) {
            this.setEditorContent('content', data.content);
        }

        // Populate tags
        if (data.tags && Array.isArray(data.tags)) {
            const tagsString = data.tags.join(',');
            this.setFieldValue('tag', tagsString);
        }

        // Populate SEO fields
        if (data.seo_title) {
            this.setFieldValue('seo_meta[seo_title]', data.seo_title);
        }

        if (data.seo_description) {
            this.setFieldValue('seo_meta[seo_description]', data.seo_description);
        }
    }

    getFieldValue(fieldName) {
        const field = $(`[name="${fieldName}"]`);
        if (field.length) {
            return field.val() || '';
        }
        return '';
    }

    setFieldValue(fieldName, value) {
        const field = $(`[name="${fieldName}"]`);
        if (field.length) {
            field.val(value).trigger('change');
        }
    }

    setEditorContent(fieldName, content) {
        // Handle different editor types (TinyMCE, CKEditor, etc.)
        const editorId = fieldName;
        
        // TinyMCE
        if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
            tinymce.get(editorId).setContent(content);
            return;
        }

        // CKEditor
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[editorId]) {
            CKEDITOR.instances[editorId].setData(content);
            return;
        }

        // Fallback to textarea
        this.setFieldValue(fieldName, content);
    }

    setGeneratingState(isGenerating) {
        this.isGenerating = isGenerating;
        
        $('.ai-generate-product-btn, .ai-generate-blog-btn').prop('disabled', isGenerating);
        
        if (isGenerating) {
            $('.ai-generate-product-btn, .ai-generate-blog-btn')
                .addClass('loading')
                .find('.btn-text').text('Generating...');
        } else {
            $('.ai-generate-product-btn, .ai-generate-blog-btn')
                .removeClass('loading')
                .find('.btn-text').text('Generate with AI');
        }
    }

    showProgress(message) {
        // Create or update progress indicator
        let progressBar = $('.ai-progress-bar');
        if (!progressBar.length) {
            progressBar = $(`
                <div class="ai-progress-bar alert alert-info">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="progress-text">${message}</span>
                    </div>
                </div>
            `);
            $('.card-body').first().prepend(progressBar);
        } else {
            progressBar.find('.progress-text').text(message);
            progressBar.show();
        }
    }

    hideProgress() {
        $('.ai-progress-bar').fadeOut(300);
    }

    showSuccess(message) {
        Botble.showSuccess(message);
    }

    showError(message) {
        Botble.showError(message);
    }
}

// Initialize when document is ready
$(document).ready(function() {
    if (typeof window.aiContentGenerator === 'undefined') {
        window.aiContentGenerator = new AIContentGenerator();
    }
});
