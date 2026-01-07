@extends('core/base::layouts.master')

@section('title', 'Quote Request #' . $quoteRequest->id)

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Quote Request Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $quoteRequest->customer_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $quoteRequest->customer_email }}</td>
                                </tr>
                                @if($quoteRequest->customer_phone)
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $quoteRequest->customer_phone }}</td>
                                </tr>
                                @endif
                                @if($quoteRequest->customer_company)
                                <tr>
                                    <td><strong>Company:</strong></td>
                                    <td>{{ $quoteRequest->customer_company }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Product Information</h6>
                            @if($quoteRequest->product)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $quoteRequest->product->image ? RvMedia::getImageUrl($quoteRequest->product->image, 'thumb') : RvMedia::getDefaultImage() }}" 
                                         alt="{{ $quoteRequest->product->name }}" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;" 
                                         class="me-3">
                                    <div>
                                        <div class="fw-bold">{{ $quoteRequest->product->name }}</div>
                                        <small class="text-muted">SKU: {{ $quoteRequest->product->sku ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            @endif
                            
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Quantity:</strong></td>
                                    <td>{{ $quoteRequest->quantity }}</td>
                                </tr>
                                @if($quoteRequest->area_size)
                                <tr>
                                    <td><strong>Area Size:</strong></td>
                                    <td>{{ $quoteRequest->area_size }}</td>
                                </tr>
                                @endif
                                @if($quoteRequest->budget_range_label)
                                <tr>
                                    <td><strong>Budget Range:</strong></td>
                                    <td>{{ $quoteRequest->budget_range_label }}</td>
                                </tr>
                                @endif
                                @if($quoteRequest->timeline_label)
                                <tr>
                                    <td><strong>Timeline:</strong></td>
                                    <td>{{ $quoteRequest->timeline_label }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($quoteRequest->project_description)
                        <div class="mb-4">
                            <h6>Project Description</h6>
                            <p class="text-muted">{{ $quoteRequest->project_description }}</p>
                        </div>
                    @endif
                    
                    @if($quoteRequest->special_requirements)
                        <div class="mb-4">
                            <h6>Special Requirements</h6>
                            <ul class="list-unstyled">
                                @foreach($quoteRequest->special_requirements as $requirement)
                                    <li><i class="ti ti-check text-success me-2"></i>{{ ucfirst(str_replace('_', ' ', $requirement)) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Update Quote</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quote-requests.update', $quoteRequest) }}">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                @foreach(\Botble\Ecommerce\Models\QuoteRequest::STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ $quoteRequest->status === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quoted_price" class="form-label">Quoted Price</label>
                            <input type="number" step="0.01" name="quoted_price" id="quoted_price" 
                                   class="form-control" value="{{ $quoteRequest->quoted_price }}" placeholder="0.00">
                        </div>
                        
                        <div class="mb-3">
                            <label for="quote_details" class="form-label">Quote Details</label>
                            <textarea name="quote_details" id="quote_details" rows="4" class="form-control" 
                                      placeholder="Enter quote details...">{{ $quoteRequest->quote_details }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" id="admin_notes" rows="3" class="form-control" 
                                      placeholder="Internal notes...">{{ $quoteRequest->admin_notes }}</textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Update Quote</button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title">Request Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>#{{ $quoteRequest->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'quoted' => 'primary',
                                        'accepted' => 'success',
                                        'rejected' => 'danger',
                                        'completed' => 'success'
                                    ];
                                    $color = $statusColors[$quoteRequest->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $quoteRequest->status_label }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Submitted:</strong></td>
                            <td>{{ $quoteRequest->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($quoteRequest->quoted_at)
                        <tr>
                            <td><strong>Quoted:</strong></td>
                            <td>{{ $quoteRequest->quoted_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($quoteRequest->quotedBy)
                        <tr>
                            <td><strong>Quoted By:</strong></td>
                            <td>{{ $quoteRequest->quotedBy->name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection