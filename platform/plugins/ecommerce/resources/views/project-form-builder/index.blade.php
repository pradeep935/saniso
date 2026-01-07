@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="widget meta-boxes">
                <div class="widget-title">
                    <h4>{{ trans('plugins/ecommerce::ecommerce.project_form_builder') }}</h4>
                    <div class="widget-controls">
                        <a href="{{ route('project-form-builder.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> {{ trans('plugins/ecommerce::ecommerce.add_new_field') }}
                        </a>
                        <a href="{{ route('project-form-builder.preview') }}" class="btn btn-info" target="_blank">
                            <i class="fa fa-eye"></i> {{ trans('plugins/ecommerce::ecommerce.preview_form') }}
                        </a>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="table-responsive">
                        {!! $dataTable->table(['class' => 'table table-striped table-hover']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shortcode-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/ecommerce::ecommerce.project_form_shortcode') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ trans('plugins/ecommerce::ecommerce.project_form_shortcode_description') }}</p>
                    <div class="form-group">
                        <label>{{ trans('plugins/ecommerce::ecommerce.shortcode') }}:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="[project-request-form]" readonly id="shortcode-input">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyShortcode()">
                                <i class="fa fa-copy"></i> {{ trans('core/base::forms.copy') }}
                            </button>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label>{{ trans('plugins/ecommerce::ecommerce.shortcode_with_button') }}:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value='[project-request-cta title="Request Project Quote" class="btn btn-primary"]' readonly id="cta-shortcode-input">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyCtaShortcode()">
                                <i class="fa fa-copy"></i> {{ trans('core/base::forms.copy') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    {!! $dataTable->scripts() !!}
    
    <script>
        $(document).ready(function() {
            // Add shortcode button to toolbar
            $('.widget-controls').append(
                '<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#shortcode-modal">' +
                '<i class="fa fa-code"></i> Shortcode' +
                '</button>'
            );
        });

        function copyShortcode() {
            const input = document.getElementById('shortcode-input');
            input.select();
            document.execCommand('copy');
            Botble.showSuccess('Shortcode copied to clipboard!');
        }

        function copyCtaShortcode() {
            const input = document.getElementById('cta-shortcode-input');
            input.select();
            document.execCommand('copy');
            Botble.showSuccess('CTA Shortcode copied to clipboard!');
        }

        // Make table sortable
        if (typeof Sortable !== 'undefined') {
            const tbody = document.querySelector('#dataTableBuilder tbody');
            if (tbody) {
                new Sortable(tbody, {
                    animation: 150,
                    onEnd: function(evt) {
                        const ids = Array.from(tbody.children).map(row => 
                            row.getAttribute('data-id') || row.querySelector('[data-id]')?.getAttribute('data-id')
                        ).filter(Boolean);
                        
                        if (ids.length > 0) {
                            $.ajax({
                                url: '{{ route("project-form-builder.reorder") }}',
                                method: 'POST',
                                data: {
                                    ids: ids,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    Botble.showSuccess(response.message || 'Order updated successfully');
                                },
                                error: function() {
                                    Botble.showError('Error updating order');
                                }
                            });
                        }
                    }
                });
            }
        }
    </script>
@endpush