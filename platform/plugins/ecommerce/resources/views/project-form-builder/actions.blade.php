<div class="btn-group">
    <a href="{{ route('project-form-builder.edit', $item->id) }}" 
       class="btn btn-icon btn-sm btn-primary" 
       data-bs-toggle="tooltip" 
       title="Edit">
        <i class="fa fa-edit"></i>
    </a>
    
    <button class="btn btn-icon btn-sm btn-info" 
            onclick="toggleStatus({{ $item->id }})" 
            data-bs-toggle="tooltip" 
            title="Toggle Status">
        <i class="fa fa-toggle-{{ $item->enabled ? 'on' : 'off' }}"></i>
    </button>
    
    <a href="{{ route('project-form-builder.duplicate', $item->id) }}" 
       class="btn btn-icon btn-sm btn-warning" 
       data-bs-toggle="tooltip" 
       title="Duplicate">
        <i class="fa fa-copy"></i>
    </a>
    
    <button class="btn btn-icon btn-sm btn-danger" 
            onclick="deleteField({{ $item->id }})" 
            data-bs-toggle="tooltip" 
            title="Delete">
        <i class="fa fa-trash"></i>
    </button>
</div>

<script>
function toggleStatus(id) {
    $.ajax({
        url: `/admin/ecommerce/project-form-builder/${id}/toggle`,
        method: 'GET',
        success: function(response) {
            if (response.status) {
                Botble.showSuccess(response.message);
                window.LaravelDataTables['dataTableBuilder'].ajax.reload();
            } else {
                Botble.showError('Error updating status');
            }
        },
        error: function() {
            Botble.showError('Error updating status');
        }
    });
}

function deleteField(id) {
    if (confirm('Are you sure you want to delete this field?')) {
        $.ajax({
            url: `/admin/ecommerce/project-form-builder/${id}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Botble.showSuccess('Field deleted successfully');
                window.LaravelDataTables['dataTableBuilder'].ajax.reload();
            },
            error: function() {
                Botble.showError('Error deleting field');
            }
        });
    }
}
</script>