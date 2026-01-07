<button type="button" 
        class="btn {{ $class ?? 'btn-primary' }} project-request-btn" 
        data-bs-toggle="modal" 
        data-bs-target="#projectRequestModal">
    {{ $title ?? 'Request Project Quote' }}
</button>

<!-- Project Request Modal -->
<div class="modal fade" id="projectRequestModal" tabindex="-1" aria-labelledby="projectRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectRequestModalLabel">{{ $title ?? 'Request Project Quote' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="project-request-form-container">
                    {!! do_shortcode('[project-request-form]') !!}
                </div>
            </div>
        </div>
    </div>
</div>