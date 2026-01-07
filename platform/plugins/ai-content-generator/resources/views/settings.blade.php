@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <!-- Important Notice -->
    <div class="alert alert-info mb-4">
        <h5><i class="fas fa-info-circle"></i> Important Setup Information</h5>
        <p class="mb-2">
            This plugin requires a <strong>paid OpenAI API account</strong>. Free trial credits may have expired or be rate-limited.
        </p>
        <ul class="mb-0 small">
            <li>Check your <a href="https://platform.openai.com/usage" target="_blank" class="alert-link">usage dashboard</a> for remaining credits</li>
            <li>Set up <a href="https://platform.openai.com/account/billing" target="_blank" class="alert-link">billing and payment method</a> for continued access</li>
            <li>Monitor costs: GPT-3.5-turbo ≈ $0.002-0.006 per request, GPT-4 ≈ $0.03-0.06 per request</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-9">
            <!-- API Configuration Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-key"></i>
                        API Configuration
                    </h4>
                </div>
                
                {!! Form::open(['route' => 'ai-content-generator.settings.update']) !!}
                <div class="card-body">
                    
                    <div class="form-group mb-3">
                        <label for="api_key" class="form-label required">API Key</label>
                        <div class="input-group">
                            <input type="password" 
                                   name="api_key" 
                                   id="api_key" 
                                   class="form-control" 
                                   value="{{ ai_setting('api_key') }}"
                                   placeholder="sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('api_key')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            Your OpenAI API key. Get one from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="api_url" class="form-label">API URL</label>
                                <input type="url" 
                                       name="api_url" 
                                       id="api_url" 
                                       class="form-control" 
                                       value="{{ ai_setting('api_url', 'https://api.openai.com/v1') }}"
                                       placeholder="https://api.openai.com/v1">
                                <small class="form-text text-muted">API endpoint URL</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="model" class="form-label">AI Model</label>
                                <select name="model" id="model" class="form-select">
                                    <option value="gpt-3.5-turbo" {{ ai_setting('model', 'gpt-3.5-turbo') == 'gpt-3.5-turbo' ? 'selected' : '' }}>
                                        GPT-3.5 Turbo (Recommended)
                                    </option>
                                    <option value="gpt-4" {{ ai_setting('model') == 'gpt-4' ? 'selected' : '' }}>
                                        GPT-4 (More expensive)
                                    </option>
                                    <option value="gpt-4-turbo" {{ ai_setting('model') == 'gpt-4-turbo' ? 'selected' : '' }}>
                                        GPT-4 Turbo
                                    </option>
                                    <option value="gpt-4o" {{ ai_setting('model') == 'gpt-4o' ? 'selected' : '' }}>
                                        GPT-4o (Latest)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <div id="connection-status" class="align-self-center me-3"></div>
                    </div>
                </div>
            </div>

            <!-- Generation Settings Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-cogs"></i>
                        Generation Settings
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="max_tokens" class="form-label">
                                    Max Tokens 
                                    <span class="badge bg-info ms-1 text-white">Dynamic</span>
                                </label>
                                <input type="number" 
                                       name="max_tokens" 
                                       id="max_tokens" 
                                       class="form-control" 
                                       value="{{ ai_setting('max_tokens', 2000) }}"
                                       min="100" 
                                       max="4000" 
                                       step="100">
                                <small class="form-text text-muted">
                                    Maximum token cap (100-4000). 
                                    <strong>Tokens are now calculated dynamically:</strong><br>
                                    • Products: ~900 tokens (based on 300 words + buffer)<br>
                                    • Blogs: ~2400 tokens (based on 1200 words + buffer)
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="temperature" class="form-label">Temperature</label>
                                <input type="number" 
                                       name="temperature" 
                                       id="temperature" 
                                       class="form-control" 
                                       value="{{ ai_setting('temperature', 0.7) }}"
                                       min="0" 
                                       max="2" 
                                       step="0.1">
                                <small class="form-text text-muted">Creativity level (0-2)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="timeout" class="form-label">Timeout (seconds)</label>
                                <input type="number" 
                                       name="timeout" 
                                       id="timeout" 
                                       class="form-control" 
                                       value="{{ ai_setting('timeout', 30) }}"
                                       min="10" 
                                       max="120">
                                <small class="form-text text-muted">Request timeout (10-120 seconds)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Content Limits Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-box"></i>
                        Product Content Limits
                    </h4>
                    <p class="card-subtitle text-muted mb-0">Shorter, punchy content for product descriptions</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="title_word_limit" class="form-label">Title Word Limit</label>
                                <input type="number" 
                                       name="title_word_limit" 
                                       id="title_word_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('title_word_limit', 8) }}"
                                       min="3" 
                                       max="15">
                                <small class="form-text text-muted">3-15 words</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="description_word_limit" class="form-label">Description Word Limit</label>
                                <input type="number" 
                                       name="description_word_limit" 
                                       id="description_word_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('description_word_limit', 30) }}"
                                       min="15" 
                                       max="100">
                                <small class="form-text text-muted">15-100 words</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="content_word_limit" class="form-label">Content Word Limit</label>
                                <input type="number" 
                                       name="content_word_limit" 
                                       id="content_word_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('content_word_limit', 300) }}"
                                       min="100" 
                                       max="800">
                                <small class="form-text text-muted">100-800 words</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="tags_count_limit" class="form-label">Number of Tags</label>
                                <input type="number" 
                                       name="tags_count_limit" 
                                       id="tags_count_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('tags_count_limit', 5) }}"
                                       min="3" 
                                       max="10">
                                <small class="form-text text-muted">3-10 tags</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="seo_title_limit" class="form-label">SEO Title Character Limit</label>
                                <input type="number" 
                                       name="seo_title_limit" 
                                       id="seo_title_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('seo_title_limit', 60) }}"
                                       min="30" 
                                       max="70">
                                <small class="form-text text-muted">30-70 characters (optimal for search engines)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="seo_description_limit" class="form-label">SEO Description Character Limit</label>
                                <input type="number" 
                                       name="seo_description_limit" 
                                       id="seo_description_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('seo_description_limit', 160) }}"
                                       min="120" 
                                       max="200">
                                <small class="form-text text-muted">120-200 characters (optimal for search engines)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blog Content Limits Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-blog"></i>
                        Blog Content Limits
                    </h4>
                    <p class="card-subtitle text-muted mb-0">Longer, comprehensive content for blog posts</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="blog_title_word_limit" class="form-label">Title Word Limit</label>
                                <input type="number" 
                                       name="blog_title_word_limit" 
                                       id="blog_title_word_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('blog_title_word_limit', 15) }}"
                                       min="5" 
                                       max="25">
                                <small class="form-text text-muted">5-25 words</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="blog_description_word_limit" class="form-label">Description Word Limit</label>
                                <input type="number" 
                                       name="blog_description_word_limit" 
                                       id="blog_description_word_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('blog_description_word_limit', 80) }}"
                                       min="30" 
                                       max="150">
                                <small class="form-text text-muted">30-150 words</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="blog_content_word_limit" class="form-label">Content Word Limit</label>
                                <input type="number" 
                                       name="blog_content_word_limit" 
                                       id="blog_content_word_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('blog_content_word_limit', 1200) }}"
                                       min="500" 
                                       max="3000">
                                <small class="form-text text-muted">500-3000 words</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label for="blog_tags_count_limit" class="form-label">Number of Tags</label>
                                <input type="number" 
                                       name="blog_tags_count_limit" 
                                       id="blog_tags_count_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('blog_tags_count_limit', 8) }}"
                                       min="5" 
                                       max="15">
                                <small class="form-text text-muted">5-15 tags</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="blog_seo_title_limit" class="form-label">SEO Title Character Limit</label>
                                <input type="number" 
                                       name="blog_seo_title_limit" 
                                       id="blog_seo_title_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('blog_seo_title_limit', 60) }}"
                                       min="30" 
                                       max="70">
                                <small class="form-text text-muted">30-70 characters (optimal for search engines)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="blog_seo_description_limit" class="form-label">SEO Description Character Limit</label>
                                <input type="number" 
                                       name="blog_seo_description_limit" 
                                       id="blog_seo_description_limit" 
                                       class="form-control" 
                                       value="{{ ai_setting('blog_seo_description_limit', 160) }}"
                                       min="120" 
                                       max="200">
                                <small class="form-text text-muted">120-200 characters (optimal for search engines)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Settings Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-toggle-on"></i>
                        Feature Settings
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="enable_for_products" value="0">
                                <input type="checkbox" 
                                       name="enable_for_products" 
                                       id="enable_for_products" 
                                       class="form-check-input" 
                                       value="1" 
                                       {{ ai_setting('enable_for_products', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_for_products">
                                    <strong>Enable for Products</strong>
                                    <br><small class="text-muted">Show AI generator in product edit forms</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="enable_for_blogs" value="0">
                                <input type="checkbox" 
                                       name="enable_for_blogs" 
                                       id="enable_for_blogs" 
                                       class="form-check-input" 
                                       value="1" 
                                       {{ ai_setting('enable_for_blogs', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_for_blogs">
                                    <strong>Enable for Blog Posts</strong>
                                    <br><small class="text-muted">Show AI generator in blog post edit forms</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Placeholder for future features -->
                        </div>
                        <div class="col-md-6">
                            <!-- Placeholder for future features -->
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="rate_limit_per_minute" class="form-label">Rate Limit (per minute)</label>
                                <input type="number" 
                                       name="rate_limit_per_minute" 
                                       id="rate_limit_per_minute" 
                                       class="form-control" 
                                       value="{{ ai_setting('rate_limit_per_minute', 10) }}"
                                       min="1" 
                                       max="100">
                                <small class="form-text text-muted">Max requests per minute to prevent API abuse</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="content_style" class="form-label">Default Content Style</label>
                                <select name="content_style" id="content_style" class="form-select">
                                    <option value="professional" {{ ai_setting('content_style', 'professional') == 'professional' ? 'selected' : '' }}>
                                        Professional
                                    </option>
                                    <option value="casual" {{ ai_setting('content_style') == 'casual' ? 'selected' : '' }}>
                                        Casual
                                    </option>
                                    <option value="technical" {{ ai_setting('content_style') == 'technical' ? 'selected' : '' }}>
                                        Technical
                                    </option>
                                    <option value="luxury" {{ ai_setting('content_style') == 'luxury' ? 'selected' : '' }}>
                                        Luxury
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Language Settings Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-language"></i>
                        Language & Localization Settings
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="default_language" class="form-label">Default Language</label>
                                <select name="default_language" id="default_language" class="form-select">
                                    <option value="en" {{ ai_setting('default_language', 'en') == 'en' ? 'selected' : '' }}>
                                        English
                                    </option>
                                    <option value="es" {{ ai_setting('default_language') == 'es' ? 'selected' : '' }}>
                                        Spanish (Español)
                                    </option>
                                    <option value="nl" {{ ai_setting('default_language') == 'nl' ? 'selected' : '' }}>
                                        Dutch (Nederlands)
                                    </option>
                                    <option value="fr" {{ ai_setting('default_language') == 'fr' ? 'selected' : '' }}>
                                        French (Français)
                                    </option>
                                    <option value="de" {{ ai_setting('default_language') == 'de' ? 'selected' : '' }}>
                                        German (Deutsch)
                                    </option>
                                    <option value="it" {{ ai_setting('default_language') == 'it' ? 'selected' : '' }}>
                                        Italian (Italiano)
                                    </option>
                                    <option value="pt" {{ ai_setting('default_language') == 'pt' ? 'selected' : '' }}>
                                        Portuguese (Português)
                                    </option>
                                    <option value="ru" {{ ai_setting('default_language') == 'ru' ? 'selected' : '' }}>
                                        Russian (Русский)
                                    </option>
                                    <option value="ja" {{ ai_setting('default_language') == 'ja' ? 'selected' : '' }}>
                                        Japanese (日本語)
                                    </option>
                                    <option value="ko" {{ ai_setting('default_language') == 'ko' ? 'selected' : '' }}>
                                        Korean (한국어)
                                    </option>
                                    <option value="zh" {{ ai_setting('default_language') == 'zh' ? 'selected' : '' }}>
                                        Chinese (中文)
                                    </option>
                                    <option value="ar" {{ ai_setting('default_language') == 'ar' ? 'selected' : '' }}>
                                        Arabic (العربية)
                                    </option>
                                    <option value="hi" {{ ai_setting('default_language') == 'hi' ? 'selected' : '' }}>
                                        Hindi (हिन्दी)
                                    </option>
                                </select>
                                <small class="form-text text-muted">Default language for content generation</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="writing_style" class="form-label">Default Writing Style</label>
                                <select name="writing_style" id="writing_style" class="form-select">
                                    <option value="informative" {{ ai_setting('writing_style', 'informative') == 'informative' ? 'selected' : '' }}>
                                        Informative
                                    </option>
                                    <option value="persuasive" {{ ai_setting('writing_style') == 'persuasive' ? 'selected' : '' }}>
                                        Persuasive
                                    </option>
                                    <option value="storytelling" {{ ai_setting('writing_style') == 'storytelling' ? 'selected' : '' }}>
                                        Storytelling
                                    </option>
                                    <option value="technical" {{ ai_setting('writing_style') == 'technical' ? 'selected' : '' }}>
                                        Technical
                                    </option>
                                    <option value="conversational" {{ ai_setting('writing_style') == 'conversational' ? 'selected' : '' }}>
                                        Conversational
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="enabled_languages" class="form-label">Enabled Languages</label>
                        
                        @php
                            // Define available languages
                            $availableLanguages = [
                                'en' => 'English',
                                'nl' => 'Dutch',
                                'es' => 'Spanish',
                                'fr' => 'French', 
                                'de' => 'German',
                                'it' => 'Italian',
                                'pt' => 'Portuguese'
                            ];
                            
                            // Get enabled languages from database - ensure we read the simple comma-separated format
                            $enabledLanguagesSetting = ai_setting('enabled_languages', 'en,nl');
                            
                            // Always treat as comma-separated string and convert to array
                            if (is_string($enabledLanguagesSetting)) {
                                $enabledLanguages = array_filter(array_map('trim', explode(',', $enabledLanguagesSetting)));
                            } else {
                                // Fallback for any other format
                                $enabledLanguages = ['en', 'nl'];
                            }
                            
                            // Ensure we have at least default languages
                            if (empty($enabledLanguages)) {
                                $enabledLanguages = ['en', 'nl'];
                            }
                        @endphp
                        
                        <!-- Simple checkboxes for language selection -->
                        <div class="row">
                            @foreach($availableLanguages as $code => $name)
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               name="enabled_languages[]" 
                                               value="{{ $code }}" 
                                               class="form-check-input" 
                                               id="lang_{{ $code }}"
                                               {{ in_array($code, $enabledLanguages) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_{{ $code }}">
                                            {{ $name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <small class="form-text text-muted mt-2">
                            Select the languages you want to use for content generation. English and Dutch are selected by default.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Content Customization Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-edit"></i>
                        Content Customization
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="product_prompt_prefix" class="form-label">Product Prompt Prefix</label>
                                <textarea name="product_prompt_prefix" 
                                          id="product_prompt_prefix" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Additional instructions for product content generation...">{{ ai_setting('product_prompt_prefix') }}</textarea>
                                <small class="form-text text-muted">Custom instructions added to product generation prompts</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="blog_prompt_prefix" class="form-label">Blog Prompt Prefix</label>
                                <textarea name="blog_prompt_prefix" 
                                          id="blog_prompt_prefix" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Additional instructions for blog content generation...">{{ ai_setting('blog_prompt_prefix') }}</textarea>
                                <small class="form-text text-muted">Custom instructions added to blog generation prompts</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="auto_populate_tags" value="0">
                                <input type="checkbox" 
                                       name="auto_populate_tags" 
                                       id="auto_populate_tags" 
                                       class="form-check-input" 
                                       value="1" 
                                       {{ ai_setting('auto_populate_tags', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_populate_tags">
                                    Auto-populate Tags
                                </label>
                                <small class="form-text text-muted">Automatically fill tag fields with AI suggestions</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="show_word_count" value="0">
                                <input type="checkbox" 
                                       name="show_word_count" 
                                       id="show_word_count" 
                                       class="form-check-input" 
                                       value="1" 
                                       {{ ai_setting('show_word_count', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_word_count">
                                    Show Word Count
                                </label>
                                <small class="form-text text-muted">Display word count in generated content</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save All Settings
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetToDefaults()">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        
        <div class="col-md-3">
            <!-- Quick Setup Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-rocket"></i>
                        Quick Setup Guide
                    </h5>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li>Get an API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a></li>
                        <li>Ensure your OpenAI account has <a href="https://platform.openai.com/account/billing" target="_blank">billing set up</a></li>
                        <li>Paste your API key above</li>
                        <li>Configure languages and settings</li>
                        <li>Save settings</li>
                        <li>Go to Products/Blog to use AI generation</li>
                    </ol>
                </div>
            </div>

            <!-- Troubleshooting Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-wrench"></i>
                        Troubleshooting
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <h6>Common Issues:</h6>
                        <ul>
                            <li><strong>Quota Exceeded:</strong> Check your <a href="https://platform.openai.com/usage" target="_blank">usage limits</a> and <a href="https://platform.openai.com/account/billing" target="_blank">billing</a></li>
                            <li><strong>Invalid API Key:</strong> Ensure key starts with "sk-" and is from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a></li>
                            <li><strong>Rate Limited:</strong> Wait a few minutes between requests</li>
                            <li><strong>Model Unavailable:</strong> Try "gpt-3.5-turbo" first</li>
                        </ul>
                        
                        <h6>Free Tier Limits:</h6>
                        <ul>
                            <li>$5 credit expires after 3 months</li>
                            <li>Rate limits: 3 requests/minute</li>
                            <li>Need payment method for higher limits</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Cost Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-dollar-sign"></i>
                        Estimated Costs
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <h6>Per Request (Approximate)</h6>
                        <ul class="list-unstyled">
                            <li><strong>GPT-3.5 Turbo:</strong> $0.002 - $0.006</li>
                            <li><strong>GPT-4:</strong> $0.03 - $0.06</li>
                            <li><strong>GPT-4 Turbo:</strong> $0.01 - $0.03</li>
                            <li><strong>GPT-4o:</strong> $0.005 - $0.015</li>
                        </ul>
                        
                        <h6>Content Types</h6>
                        <ul class="list-unstyled">
                            <li><strong>Product Description:</strong> 500-800 tokens</li>
                            <li><strong>Blog Post:</strong> 1000-1500 tokens</li>
                            <li><strong>Short Content:</strong> 300-500 tokens</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Plugin Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Version:</strong> 1.0.0</p>
                        <p><strong>Status:</strong> 
                            @if(ai_setting('api_key'))
                                <span class="badge bg-success text-white">Configured</span>
                            @else
                                <span class="badge bg-warning text-white">Needs Setup</span>
                            @endif
                        </p>
                        <p><strong>Products:</strong> 
                            @if(ai_setting('enable_for_products', '1') == '1')
                                <span class="badge bg-success text-white">Enabled</span>
                            @else
                                <span class="badge bg-secondary text-white">Disabled</span>
                            @endif
                        </p>
                        <p><strong>Blog Posts:</strong> 
                            @if(ai_setting('enable_for_blogs', '1') == '1')
                                <span class="badge bg-success text-white">Enabled</span>
                            @else
                                <span class="badge bg-secondary text-white">Disabled</span>
                            @endif
                        </p>
                        
                        <hr>
                        <h6>Today's Usage</h6>
                        <p><strong>Requests:</strong> {{ $todayUsage['requests'] ?? 0 }}</p>
                        <p><strong>Tokens:</strong> {{ number_format($todayUsage['total_tokens'] ?? 0) }}</p>
                        <p><strong>Est. Cost:</strong> ${{ number_format($todayUsage['estimated_cost'] ?? 0, 4) }}</p>
                        
                        @if(isset($todayUsage['by_type']) && !empty($todayUsage['by_type']))
                            <small class="text-muted">
                                @foreach($todayUsage['by_type'] as $type => $stats)
                                    <div>{{ ucfirst($type) }}: {{ $stats['count'] }} requests</div>
                                @endforeach
                            </small>
                        @endif
                    </div>
                    
                    <div class="alert alert-warning small mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> Keep your API key secure and monitor usage to control costs.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        button.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        button.className = 'fas fa-eye';
    }
}

function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
        // Reset form to default values
        document.getElementById('api_url').value = 'https://api.openai.com/v1';
        document.getElementById('model').value = 'gpt-3.5-turbo';
        document.getElementById('max_tokens').value = '2000';
        document.getElementById('temperature').value = '0.7';
        document.getElementById('timeout').value = '30';
        document.getElementById('rate_limit_per_minute').value = '10';
        document.getElementById('content_style').value = 'professional';
        document.getElementById('default_language').value = 'en';
        document.getElementById('writing_style').value = 'informative';
        
        // Reset checkboxes
        document.getElementById('enable_for_products').checked = true;
        document.getElementById('enable_for_blogs').checked = true;
        document.getElementById('auto_populate_tags').checked = true;
        document.getElementById('show_word_count').checked = true;
        
        // Reset language checkboxes
        const defaultLangs = ['en', 'nl'];
        document.querySelectorAll('input[name="enabled_languages[]"]').forEach(checkbox => {
            checkbox.checked = defaultLangs.includes(checkbox.value);
        });
        
        // Clear text areas
        document.getElementById('product_prompt_prefix').value = '';
        document.getElementById('blog_prompt_prefix').value = '';
        
        Botble.showSuccess('Settings reset to defaults. Remember to save to apply changes.');
    }
}
</script>
@endpush
