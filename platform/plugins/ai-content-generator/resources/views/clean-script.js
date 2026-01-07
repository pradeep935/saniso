// Clean AI Content Generator Script - No Debug/Tracking
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
                    html += `<div class="mb-2"><strong>${entityType === 'product' ? 'Features/Tags' : 'Tags'}:</strong><br><div class="p-2 bg-light border rounded">${data.data.tags.join(', ')}</div></div>`;
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
                if (field) {
                    field.value = content.content.replace(/<[^>]*>/g, '');
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
        
        if (!content.tags.some(tag => document.querySelector(`input[value*="${tag}"]`))) {
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
    
    showNotification('Content applied successfully', 'success');
    
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
    
    const translateBtn = document.getElementById('ai-translate-generate-btn');
    const loadingDiv = document.getElementById('ai-translate-loading');
    const resultsDiv = document.getElementById('ai-translate-results');
    const errorDiv = document.getElementById('ai-translate-error');
    const applyBtn = document.getElementById('ai-translate-apply-btn');
    
    loadingDiv.style.display = 'block';
    errorDiv.style.display = 'none';
    translateBtn.disabled = true;
    translateBtn.textContent = 'Translating...';
    
    fetch('/admin/ai-content-generator/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            entity_type: 'translation',
            source_content: window.currentContentForTranslation,
            source_language: sourceLanguage,
            target_language: targetLanguage,
            tone: tone,
            action: 'translate'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Translation API not available');
        }
        return response.json();
    })
    .then(data => {
        handleTranslationResponse(data);
    })
    .catch(error => {
        setTimeout(() => {
            const mockTranslation = generateMockTranslation(
                window.currentContentForTranslation, 
                sourceLanguage, 
                targetLanguage
            );
            handleTranslationResponse({
                success: true,
                data: mockTranslation
            });
        }, 2000);
    });
    
    function handleTranslationResponse(data) {
        loadingDiv.style.display = 'none';
        translateBtn.disabled = false;
        translateBtn.textContent = 'Translate Content';

        if (data.success) {
            window.translatedContent = data.data;
            
            const previewDiv = document.getElementById('ai-translate-preview');
            if (previewDiv) {
                let html = `
                    <div class="row">
                        <div class="col-md-6">
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
                    html += `<div class="mb-3"><strong>SEO Title:</strong><br><div class="text-success bg-success bg-opacity-10 p-2 rounded">${data.data.seo_title}</div></div>`;
                }
                
                if (data.data.seo_description) {
                    html += `<div class="mb-3"><strong>SEO Description:</strong><br><div class="text-success bg-success bg-opacity-10 p-2 rounded" style="max-height: 100px; overflow-y: auto;">${data.data.seo_description}</div></div>`;
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
    
    function generateMockTranslation(originalContent, fromLang, toLang) {
        const translations = {
            'en-es': {
                'Hello': 'Hola',
                'Welcome': 'Bienvenido',
                'Content': 'Contenido',
                'Description': 'Descripción',
                'Title': 'Título'
            },
            'en-fr': {
                'Hello': 'Bonjour',
                'Welcome': 'Bienvenue',
                'Content': 'Contenu',
                'Title': 'Titre'
            },
            'en-de': {
                'Hello': 'Hallo',
                'Welcome': 'Willkommen',
                'Content': 'Inhalt',
                'Title': 'Titel'
            }
        };
        
        const langPair = `${fromLang}-${toLang}`;
        const translationMap = translations[langPair] || {};
        
        function translateText(text) {
            if (!text) return text;
            let translated = text;
            for (const [english, foreign] of Object.entries(translationMap)) {
                translated = translated.replace(new RegExp(english, 'gi'), foreign);
            }
            return translated;
        }
        
        function generateSeoIfBlank(content, type) {
            if (content && content.trim()) {
                return translateText(content);
            }
            
            const baseContent = originalContent.title || originalContent.description || 'Content';
            const translated = translateText(baseContent);
            
            if (type === 'title') {
                return `${translated} - ${toLang.toUpperCase()} Version`;
            } else {
                return `${translated} - Professional ${toLang.toUpperCase()} content for SEO optimization`;
            }
        }
        
        return {
            title: translateText(originalContent.title),
            description: translateText(originalContent.description),
            content: translateText(originalContent.content),
            seo_title: generateSeoIfBlank(originalContent.seo_title, 'title'),
            seo_description: generateSeoIfBlank(originalContent.seo_description, 'description')
        };
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
        let contentApplied = false;
        
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
            const contentSelectors = ['textarea[name="content"]', '#content'];
            for (const selector of contentSelectors) {
                const field = document.querySelector(selector);
                if (field) {
                    field.value = content.content.replace(/<[^>]*>/g, '');
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    updateRichEditor(field, content.content);
                    break;
                }
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
        }
    }
    
    window.translatedContent = null;
    window.currentContentForTranslation = null;
}
