@extends('core/base::layouts.master')

@section('content')
    <div class="page-content">
        <div class="page-header">
            <h1>{{ trans('Quote Form Builder') }}</h1>
            <div class="page-header-actions">
                <a href="{{ route('admin.ecommerce.quote-form-builder.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add New Field
                </a>
                <a href="{{ route('admin.ecommerce.quote-form-builder.styles') }}" class="btn btn-info">
                    <i class="fa fa-paint-brush"></i> Customize Styles
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

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Form Fields</h4>
                    <p class="card-subtitle">Drag and drop to reorder fields. Click toggle to enable/disable.</p>
                </div>
                <div class="card-body">
                    @if($fields->count() > 0)
                        <div id="sortable-fields" class="sortable-list">
                            @foreach($fields as $field)
                                <div class="field-item card mb-3" data-field-id="{{ $field->id }}">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-1">
                                                <i class="fa fa-grip-vertical drag-handle text-muted" style="cursor: move;"></i>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>{{ $field->label }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $field->name }} ({{ $fieldTypes[$field->type] }})</small>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-{{ $field->required ? 'danger' : 'secondary' }}">
                                                    {{ $field->required ? 'Required' : 'Optional' }}
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge badge-{{ $field->enabled ? 'success' : 'warning' }}">
                                                    {{ $field->enabled ? 'Enabled' : 'Disabled' }}
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">{{ $field->field_width }}</small>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.ecommerce.quote-form-builder.edit', $field->id) }}" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('admin.ecommerce.quote-form-builder.duplicate', $field->id) }}" 
                                                       class="btn btn-outline-secondary" title="Duplicate">
                                                        <i class="fa fa-copy"></i>
                                                    </a>
                                                    <a href="{{ route('admin.ecommerce.quote-form-builder.toggle-status', $field->id) }}" 
                                                       class="btn btn-outline-{{ $field->enabled ? 'warning' : 'success' }}" 
                                                       title="{{ $field->enabled ? 'Disable' : 'Enable' }}">
                                                        <i class="fa fa-{{ $field->enabled ? 'eye-slash' : 'eye' }}"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.ecommerce.quote-form-builder.destroy', $field->id) }}" 
                                                          style="display: inline-block;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this field?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-file-text fa-3x text-muted mb-3"></i>
                            <h4>No Fields Created Yet</h4>
                            <p class="text-muted">Start building your quote form by adding fields.</p>
                            <a href="{{ route('admin.ecommerce.quote-form-builder.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Add Your First Field
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('sortable-fields')) {
        var sortable = Sortable.create(document.getElementById('sortable-fields'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function (evt) {
                var fieldIds = [];
                var items = document.querySelectorAll('.field-item');
                items.forEach(function(item) {
                    fieldIds.push(item.getAttribute('data-field-id'));
                });

                // Send AJAX request to update order
                fetch('{{ route("admin.ecommerce.quote-form-builder.update-order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        fields: fieldIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        console.log('Order updated successfully');
                    }
                })
                .catch(error => {
                    console.error('Error updating order:', error);
                });
            }
        });
    }
});
</script>
@endpush