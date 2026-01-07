<div class="form-group mb-3">
    <label class="form-label" for="affiliate_rules_content">
        {{ trans('plugins/affiliate-pro::settings.affiliate_rules_content') }}
    </label>
    <div class="form-text text-muted mb-2">
        {{ trans('plugins/affiliate-pro::settings.affiliate_rules_content_helper') }}
    </div>
    <textarea
        class="form-control editor-ckeditor"
        name="rules_content"
        id="affiliate_rules_content"
        rows="3"
        data-editor-type="ckeditor"
    >{{ old('affiliate_rules_content', $value) }}</textarea>
</div>
