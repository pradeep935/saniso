@extends('core/base::layouts.master')

@section('content')
    <div class="page-content">
        <div class="page-header">
            <h1>Quote Form Styles</h1>
            <div class="page-header-actions">
                <a href="{{ route('admin.ecommerce.quote-form-builder.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Fields
                </a>
                <a href="{{ route('admin.ecommerce.quote-form-builder.preview') }}" class="btn btn-success" target="_blank">
                    <i class="fa fa-eye"></i> Preview Form
                </a>
            </div>
        </div>

        <div class="page-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.ecommerce.quote-form-builder.update-styles') }}">
                @csrf
                
                <div class="row">
                    <!-- Form Container Styles -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">Form Container</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="container_bg_color">Background Color</label>
                                    <div class="input-group">
                                        <input type="color" name="form_container[background_color]" id="container_bg_color" 
                                               class="form-control" style="width: 60px;" 
                                               value="{{ $styles['form_container']['background_color'] ?? '#ffffff' }}">
                                        <input type="text" name="form_container[background_color]" 
                                               class="form-control ml-2" 
                                               value="{{ $styles['form_container']['background_color'] ?? '#ffffff' }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="container_padding">Padding</label>
                                            <input type="text" name="form_container[padding]" id="container_padding" 
                                                   class="form-control" placeholder="30px" 
                                                   value="{{ $styles['form_container']['padding'] ?? '30px' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="container_margin">Margin</label>
                                            <input type="text" name="form_container[margin]" id="container_margin" 
                                                   class="form-control" placeholder="20px 0" 
                                                   value="{{ $styles['form_container']['margin'] ?? '20px 0' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="container_border_radius">Border Radius</label>
                                            <input type="text" name="form_container[border_radius]" id="container_border_radius" 
                                                   class="form-control" placeholder="8px" 
                                                   value="{{ $styles['form_container']['border_radius'] ?? '8px' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="container_max_width">Max Width</label>
                                            <input type="text" name="form_container[max_width]" id="container_max_width" 
                                                   class="form-control" placeholder="600px" 
                                                   value="{{ $styles['form_container']['max_width'] ?? '600px' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="container_box_shadow">Box Shadow</label>
                                    <input type="text" name="form_container[box_shadow]" id="container_box_shadow" 
                                           class="form-control" placeholder="0 2px 10px rgba(0,0,0,0.1)" 
                                           value="{{ $styles['form_container']['box_shadow'] ?? '0 2px 10px rgba(0,0,0,0.1)' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Fields Styles -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">Form Fields</h4>
                            </div>
                            <div class="card-body">
                                <h6>Labels</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="label_color">Label Color</label>
                                            <div class="input-group">
                                                <input type="color" name="form_fields[label_color]" id="label_color" 
                                                       class="form-control" style="width: 60px;" 
                                                       value="{{ $styles['form_fields']['label_color'] ?? '#333333' }}">
                                                <input type="text" name="form_fields[label_color]" 
                                                       class="form-control ml-2" 
                                                       value="{{ $styles['form_fields']['label_color'] ?? '#333333' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="label_font_size">Font Size</label>
                                            <input type="text" name="form_fields[label_font_size]" id="label_font_size" 
                                                   class="form-control" placeholder="14px" 
                                                   value="{{ $styles['form_fields']['label_font_size'] ?? '14px' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="label_font_weight">Font Weight</label>
                                    <select name="form_fields[label_font_weight]" id="label_font_weight" class="form-control">
                                        <option value="normal" {{ ($styles['form_fields']['label_font_weight'] ?? '500') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="500" {{ ($styles['form_fields']['label_font_weight'] ?? '500') == '500' ? 'selected' : '' }}>Medium</option>
                                        <option value="600" {{ ($styles['form_fields']['label_font_weight'] ?? '500') == '600' ? 'selected' : '' }}>Semi Bold</option>
                                        <option value="bold" {{ ($styles['form_fields']['label_font_weight'] ?? '500') == 'bold' ? 'selected' : '' }}>Bold</option>
                                    </select>
                                </div>

                                <hr>
                                <h6>Input Fields</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="input_border_color">Border Color</label>
                                            <div class="input-group">
                                                <input type="color" name="form_fields[input_border_color]" id="input_border_color" 
                                                       class="form-control" style="width: 60px;" 
                                                       value="{{ $styles['form_fields']['input_border_color'] ?? '#e1e5e9' }}">
                                                <input type="text" name="form_fields[input_border_color]" 
                                                       class="form-control ml-2" 
                                                       value="{{ $styles['form_fields']['input_border_color'] ?? '#e1e5e9' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="input_border_radius">Border Radius</label>
                                            <input type="text" name="form_fields[input_border_radius]" id="input_border_radius" 
                                                   class="form-control" placeholder="4px" 
                                                   value="{{ $styles['form_fields']['input_border_radius'] ?? '4px' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="input_padding">Padding</label>
                                            <input type="text" name="form_fields[input_padding]" id="input_padding" 
                                                   class="form-control" placeholder="12px 15px" 
                                                   value="{{ $styles['form_fields']['input_padding'] ?? '12px 15px' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="input_font_size">Font Size</label>
                                            <input type="text" name="form_fields[input_font_size]" id="input_font_size" 
                                                   class="form-control" placeholder="14px" 
                                                   value="{{ $styles['form_fields']['input_font_size'] ?? '14px' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="input_background">Background</label>
                                            <div class="input-group">
                                                <input type="color" name="form_fields[input_background]" id="input_background" 
                                                       class="form-control" style="width: 60px;" 
                                                       value="{{ $styles['form_fields']['input_background'] ?? '#ffffff' }}">
                                                <input type="text" name="form_fields[input_background]" 
                                                       class="form-control ml-2" 
                                                       value="{{ $styles['form_fields']['input_background'] ?? '#ffffff' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="field_margin_bottom">Field Spacing</label>
                                            <input type="text" name="form_fields[field_margin_bottom]" id="field_margin_bottom" 
                                                   class="form-control" placeholder="20px" 
                                                   value="{{ $styles['form_fields']['field_margin_bottom'] ?? '20px' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Button Styles -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">Submit Button</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="submit_bg_color">Background Color</label>
                                            <div class="input-group">
                                                <input type="color" name="form_buttons[submit_bg_color]" id="submit_bg_color" 
                                                       class="form-control" style="width: 60px;" 
                                                       value="{{ $styles['form_buttons']['submit_bg_color'] ?? '#007bff' }}">
                                                <input type="text" name="form_buttons[submit_bg_color]" 
                                                       class="form-control ml-2" 
                                                       value="{{ $styles['form_buttons']['submit_bg_color'] ?? '#007bff' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="submit_text_color">Text Color</label>
                                            <div class="input-group">
                                                <input type="color" name="form_buttons[submit_text_color]" id="submit_text_color" 
                                                       class="form-control" style="width: 60px;" 
                                                       value="{{ $styles['form_buttons']['submit_text_color'] ?? '#ffffff' }}">
                                                <input type="text" name="form_buttons[submit_text_color]" 
                                                       class="form-control ml-2" 
                                                       value="{{ $styles['form_buttons']['submit_text_color'] ?? '#ffffff' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="submit_padding">Padding</label>
                                            <input type="text" name="form_buttons[submit_padding]" id="submit_padding" 
                                                   class="form-control" placeholder="12px 30px" 
                                                   value="{{ $styles['form_buttons']['submit_padding'] ?? '12px 30px' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="submit_border_radius">Border Radius</label>
                                            <input type="text" name="form_buttons[submit_border_radius]" id="submit_border_radius" 
                                                   class="form-control" placeholder="4px" 
                                                   value="{{ $styles['form_buttons']['submit_border_radius'] ?? '4px' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="submit_font_size">Font Size</label>
                                            <input type="text" name="form_buttons[submit_font_size]" id="submit_font_size" 
                                                   class="form-control" placeholder="16px" 
                                                   value="{{ $styles['form_buttons']['submit_font_size'] ?? '16px' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="submit_font_weight">Font Weight</label>
                                            <select name="form_buttons[submit_font_weight]" id="submit_font_weight" class="form-control">
                                                <option value="normal" {{ ($styles['form_buttons']['submit_font_weight'] ?? '500') == 'normal' ? 'selected' : '' }}>Normal</option>
                                                <option value="500" {{ ($styles['form_buttons']['submit_font_weight'] ?? '500') == '500' ? 'selected' : '' }}>Medium</option>
                                                <option value="600" {{ ($styles['form_buttons']['submit_font_weight'] ?? '500') == '600' ? 'selected' : '' }}>Semi Bold</option>
                                                <option value="bold" {{ ($styles['form_buttons']['submit_font_weight'] ?? '500') == 'bold' ? 'selected' : '' }}>Bold</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="button_margin_top">Top Margin</label>
                                            <input type="text" name="form_buttons[button_margin_top]" id="button_margin_top" 
                                                   class="form-control" placeholder="20px" 
                                                   value="{{ $styles['form_buttons']['button_margin_top'] ?? '20px' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Settings -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title">Responsive Design</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="mobile_breakpoint">Mobile Breakpoint</label>
                                    <input type="text" name="responsive_breakpoints[mobile]" id="mobile_breakpoint" 
                                           class="form-control" placeholder="768px" 
                                           value="{{ $styles['responsive_breakpoints']['mobile'] ?? '768px' }}">
                                </div>

                                <div class="form-group">
                                    <label for="tablet_breakpoint">Tablet Breakpoint</label>
                                    <input type="text" name="responsive_breakpoints[tablet]" id="tablet_breakpoint" 
                                           class="form-control" placeholder="992px" 
                                           value="{{ $styles['responsive_breakpoints']['tablet'] ?? '992px' }}">
                                </div>

                                <div class="form-group">
                                    <label for="desktop_breakpoint">Desktop Breakpoint</label>
                                    <input type="text" name="responsive_breakpoints[desktop]" id="desktop_breakpoint" 
                                           class="form-control" placeholder="1200px" 
                                           value="{{ $styles['responsive_breakpoints']['desktop'] ?? '1200px' }}">
                                </div>

                                <hr>
                                <h6>Responsive Padding</h6>
                                
                                <div class="form-group">
                                    <label for="mobile_padding">Mobile Padding</label>
                                    <input type="text" name="responsive_breakpoints[mobile_padding]" id="mobile_padding" 
                                           class="form-control" placeholder="15px" 
                                           value="{{ $styles['responsive_breakpoints']['mobile_padding'] ?? '15px' }}">
                                </div>

                                <div class="form-group">
                                    <label for="tablet_padding">Tablet Padding</label>
                                    <input type="text" name="responsive_breakpoints[tablet_padding]" id="tablet_padding" 
                                           class="form-control" placeholder="25px" 
                                           value="{{ $styles['responsive_breakpoints']['tablet_padding'] ?? '25px' }}">
                                </div>

                                <div class="form-group">
                                    <label for="desktop_padding">Desktop Padding</label>
                                    <input type="text" name="responsive_breakpoints[desktop_padding]" id="desktop_padding" 
                                           class="form-control" placeholder="30px" 
                                           value="{{ $styles['responsive_breakpoints']['desktop_padding'] ?? '30px' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fa fa-save"></i> Save Style Settings
                        </button>
                        <a href="{{ route('admin.ecommerce.quote-form-builder.preview') }}" class="btn btn-success btn-lg ml-2" target="_blank">
                            <i class="fa fa-eye"></i> Preview Changes
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync color inputs
    document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
        const textInput = colorInput.nextElementSibling;
        
        colorInput.addEventListener('change', function() {
            textInput.value = this.value;
        });
        
        textInput.addEventListener('change', function() {
            colorInput.value = this.value;
        });
    });
});
</script>
@endpush