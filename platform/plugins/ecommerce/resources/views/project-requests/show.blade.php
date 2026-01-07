@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="widget meta-boxes">
                <div class="widget-title">
                    <h4>{{ trans('plugins/ecommerce::ecommerce.project_request') }} #{{ $projectRequest->id }}</h4>
                    <div class="widget-controls">
                        <a href="{{ route('project-requests.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ trans('core/base::forms.back') }}
                        </a>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ trans('plugins/ecommerce::ecommerce.project_request_details') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="fw-bold">{{ trans('plugins/ecommerce::ecommerce.customer_name') }}:</label>
                                                <p>{{ $projectRequest->customer_name ?: 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="fw-bold">{{ trans('plugins/ecommerce::ecommerce.customer_email') }}:</label>
                                                <p>{{ $projectRequest->customer_email ?: 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="fw-bold">{{ trans('plugins/ecommerce::ecommerce.customer_phone') }}:</label>
                                                <p>{{ $projectRequest->customer_phone ?: 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="fw-bold">{{ trans('core/base::tables.created_at') }}:</label>
                                                <p>{{ $projectRequest->created_at->format('F j, Y \a\t g:i A') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($projectRequest->form_data)
                                        <div class="mt-4">
                                            <h6>{{ trans('plugins/ecommerce::ecommerce.form_data') }}:</h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('plugins/ecommerce::ecommerce.field') }}</th>
                                                            <th>{{ trans('plugins/ecommerce::ecommerce.value') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach(json_decode($projectRequest->form_data, true) ?? [] as $field => $value)
                                                            <tr>
                                                                <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                                                <td>
                                                                    @if(is_array($value))
                                                                        {{ implode(', ', $value) }}
                                                                    @else
                                                                        {{ $value }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    @if($projectRequest->attachments && count($projectRequest->attachments) > 0)
                                        <div class="mt-4">
                                            <h6>{{ trans('plugins/ecommerce::ecommerce.attachments') }}:</h6>
                                            <div class="list-group">
                                                @foreach($projectRequest->attachments as $attachment)
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fa fa-file me-2"></i>
                                                            <strong>{{ $attachment['name'] ?? 'Attachment' }}</strong>
                                                            @if(isset($attachment['size']))
                                                                <span class="text-muted">({{ number_format($attachment['size'] / 1024, 1) }} KB)</span>
                                                            @endif
                                                        </div>
                                                        @if(isset($attachment['path']))
                                                            <a href="{{ $attachment['path'] }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fa fa-download"></i> {{ trans('core/base::forms.download') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ trans('plugins/ecommerce::ecommerce.actions') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        @if($projectRequest->customer_email)
                                            <a href="mailto:{{ $projectRequest->customer_email }}" class="btn btn-primary">
                                                <i class="fa fa-envelope"></i> {{ trans('plugins/ecommerce::ecommerce.send_email') }}
                                            </a>
                                        @endif

                                        @if($projectRequest->customer_phone)
                                            <a href="tel:{{ $projectRequest->customer_phone }}" class="btn btn-success">
                                                <i class="fa fa-phone"></i> {{ trans('plugins/ecommerce::ecommerce.call_customer') }}
                                            </a>
                                        @endif

                                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#notesModal">
                                            <i class="fa fa-sticky-note"></i> {{ trans('plugins/ecommerce::ecommerce.add_notes') }}
                                        </button>

                                        <form method="POST" action="{{ route('project-requests.destroy', $projectRequest) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('{{ trans('core/base::forms.are_you_sure') }}')">
                                                <i class="fa fa-trash"></i> {{ trans('core/base::forms.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5>{{ trans('plugins/ecommerce::ecommerce.request_info') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="fw-bold">{{ trans('plugins/ecommerce::ecommerce.status') }}:</label>
                                        <select class="form-select" onchange="updateStatus({{ $projectRequest->id }}, this.value)">
                                            <option value="pending" {{ $projectRequest->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="in_progress" {{ $projectRequest->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="quoted" {{ $projectRequest->status === 'quoted' ? 'selected' : '' }}>Quoted</option>
                                            <option value="completed" {{ $projectRequest->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $projectRequest->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">{{ trans('plugins/ecommerce::ecommerce.priority') }}:</label>
                                        <select class="form-select" onchange="updatePriority({{ $projectRequest->id }}, this.value)">
                                            <option value="low" {{ ($projectRequest->priority ?? 'normal') === 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="normal" {{ ($projectRequest->priority ?? 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                            <option value="high" {{ ($projectRequest->priority ?? 'normal') === 'high' ? 'selected' : '' }}>High</option>
                                            <option value="urgent" {{ ($projectRequest->priority ?? 'normal') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/ecommerce::ecommerce.add_notes') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('project-requests.update', $projectRequest) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="notes">{{ trans('plugins/ecommerce::ecommerce.notes') }}:</label>
                            <textarea class="form-control" id="notes" name="notes" rows="5">{{ $projectRequest->notes ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('core/base::forms.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('core/base::forms.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    <script>
        function updateStatus(id, status) {
            $.ajax({
                url: `{{ route('project-requests.index') }}/${id}`,
                method: 'PATCH',
                data: {
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Botble.showSuccess('Status updated successfully');
                },
                error: function() {
                    Botble.showError('Error updating status');
                }
            });
        }

        function updatePriority(id, priority) {
            $.ajax({
                url: `{{ route('project-requests.index') }}/${id}`,
                method: 'PATCH',
                data: {
                    priority: priority,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Botble.showSuccess('Priority updated successfully');
                },
                error: function() {
                    Botble.showError('Error updating priority');
                }
            });
        }
    </script>
@endpush