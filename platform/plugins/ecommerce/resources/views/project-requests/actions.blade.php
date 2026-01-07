{{-- Action buttons for project requests --}}
<div class="btn-group" role="group">
    <a href="{{ route('project-requests.show', $item->id) }}" 
       class="btn btn-sm btn-primary" 
       title="{{ trans('core/base::forms.view') }}">
        <i class="fa fa-eye"></i>
    </a>
    
    <button type="button" 
            class="btn btn-sm btn-danger deleteDialog" 
            data-section="{{ route('project-requests.destroy', $item->id) }}"
            title="{{ trans('core/base::forms.delete') }}">
        <i class="fa fa-trash"></i>
    </button>
</div>