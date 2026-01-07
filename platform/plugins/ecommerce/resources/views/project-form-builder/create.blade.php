@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="widget meta-boxes">
                <div class="widget-title">
                    <h4>{{ trans('plugins/ecommerce::ecommerce.add_new_field') }}</h4>
                </div>
                <div class="widget-body">
                    <form method="POST" action="{{ route('project-form-builder.store') }}" class="row">
                        @csrf
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="label" class="control-label required">{{ trans('plugins/ecommerce::ecommerce.field_label') }}</label>
                                <input type="text" class="form-control" id="label" name="label" value="{{ old('label') }}" required>
                                @error('label')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type" class="control-label required">{{ trans('plugins/ecommerce::ecommerce.field_type') }}</label>
                                <select class="form-control" id="type" name="type" required>
                                    @foreach($fieldTypes as $key => $type)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field_width" class="control-label">{{ trans('plugins/ecommerce::ecommerce.field_width') }}</label>
                                <select class="form-control" id="field_width" name="field_width">
                                    <option value="col-12" {{ old('field_width') == 'col-12' ? 'selected' : '' }}>Full Width (12/12)</option>
                                    <option value="col-6" {{ old('field_width') == 'col-6' ? 'selected' : '' }}>Half Width (6/12)</option>
                                    <option value="col-4" {{ old('field_width') == 'col-4' ? 'selected' : '' }}>One Third (4/12)</option>
                                    <option value="col-3" {{ old('field_width') == 'col-3' ? 'selected' : '' }}>Quarter Width (3/12)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="placeholder" class="control-label">{{ trans('plugins/ecommerce::ecommerce.placeholder') }}</label>
                                <input type="text" class="form-control" id="placeholder" name="placeholder" value="{{ old('placeholder') }}">
                                @error('placeholder')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12" id="options-group" style="display: none;">
                            <div class="form-group">
                                <label for="options" class="control-label">{{ trans('plugins/ecommerce::ecommerce.options') }}</label>
                                <textarea class="form-control" id="options" name="options" rows="3" placeholder="Enter options separated by new lines">{{ old('options') }}</textarea>
                                <small class="text-muted">For select, radio, or checkbox fields. Enter each option on a new line.</small>
                                @error('options')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="validation_rules" class="control-label">{{ trans('plugins/ecommerce::ecommerce.validation_rules') }}</label>
                                <input type="text" class="form-control" id="validation_rules" name="validation_rules" value="{{ old('validation_rules') }}" placeholder="max:255,email,numeric">
                                <small class="text-muted">Laravel validation rules (optional)</small>
                                @error('validation_rules')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="help_text" class="control-label">{{ trans('plugins/ecommerce::ecommerce.help_text') }}</label>
                                <input type="text" class="form-control" id="help_text" name="help_text" value="{{ old('help_text') }}">
                                @error('help_text')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">
                                    <input type="checkbox" name="required" value="1" {{ old('required') ? 'checked' : '' }}>
                                    {{ trans('plugins/ecommerce::ecommerce.required') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">{{ trans('core/base::forms.save') }}</button>
                                <a href="{{ route('project-form-builder.index') }}" class="btn btn-secondary">{{ trans('core/base::forms.cancel') }}</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    <script>
        $(document).ready(function() {
            function toggleOptionsField() {
                const type = $('#type').val();
                if (['select', 'radio', 'checkbox'].includes(type)) {
                    $('#options-group').show();
                } else {
                    $('#options-group').hide();
                }
            }

            $('#type').on('change', toggleOptionsField);
            toggleOptionsField();
        });
    </script>
@endpush