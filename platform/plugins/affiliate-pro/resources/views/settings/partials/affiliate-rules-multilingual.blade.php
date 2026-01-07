<div class="form-group mb-3">
    <label class="form-label">
        {{ trans('plugins/affiliate-pro::settings.affiliate_rules_content') }}
    </label>
    <div class="form-text text-muted mb-3">
        {{ trans('plugins/affiliate-pro::settings.affiliate_rules_content_helper') }}
    </div>

    <div class="language-tabs">
        <ul class="nav nav-tabs" role="tablist">
            @foreach($fields as $locale => $field)
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link @if($loop->first) active @endif"
                        id="affiliate-rules-{{ $locale }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#affiliate-rules-{{ $locale }}"
                        type="button"
                        role="tab"
                        aria-controls="affiliate-rules-{{ $locale }}"
                        aria-selected="@if($loop->first) true @else false @endif"
                    >
                        {!! language_flag($field['language']['lang_flag']) !!}
                        <span class="d-inline-block ms-1">{{ $field['language']['lang_name'] }}</span>
                        @if($field['isDefault'])
                            <span class="badge bg-primary text-white ms-1 small">{{ trans('plugins/affiliate-pro::settings.default') }}</span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content mt-3">
            @foreach($fields as $locale => $field)
                <div
                    class="tab-pane fade @if($loop->first) show active @endif"
                    id="affiliate-rules-{{ $locale }}"
                    role="tabpanel"
                    aria-labelledby="affiliate-rules-{{ $locale }}-tab"
                >
                    @php
                        $fieldName = $field['isDefault'] ? 'rules_content' : "rules_content_{$locale}";
                        $fieldId = $field['isDefault'] ? 'rules_content' : "rules_content_{$locale}";
                    @endphp

                    @if($field['isDefault'])
                        <div class="alert alert-info mb-3">
                            <x-core::icon name="ti ti-info-circle" class="me-1" />
                            {{ trans('plugins/affiliate-pro::settings.default_language_note') }}
                        </div>
                    @else
                        <div class="alert alert-warning mb-3">
                            <x-core::icon name="ti ti-alert-triangle" class="me-1" />
                            {{ trans('plugins/affiliate-pro::settings.fallback_language_note') }}
                        </div>
                    @endif

                    <textarea
                        class="form-control editor-ckeditor"
                        name="{{ $fieldName }}"
                        id="{{ $fieldId }}"
                        rows="3"
                        data-editor-type="ckeditor"
                        placeholder="{{ trans('plugins/affiliate-pro::settings.affiliate_rules_content_placeholder', ['language' => $field['language']['lang_name']]) }}"
                    >{{ old($fieldName, $field['value']) }}</textarea>
                </div>
            @endforeach
        </div>
    </div>
</div>
