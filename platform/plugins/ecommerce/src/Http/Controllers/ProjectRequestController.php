<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\ProjectRequest;
use Botble\Ecommerce\Tables\ProjectRequestTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ProjectRequestController extends BaseController
{
    public function index(ProjectRequestTable $dataTable)
    {
        $this->pageTitle(trans('plugins/ecommerce::ecommerce.project_requests'));

        return $dataTable->renderTable();
    }

    public function show(ProjectRequest $projectRequest)
    {
        $projectRequest->load(['quotedBy']);
        
        return view('plugins/ecommerce::project-requests.show', compact('projectRequest'));
    }

    public function update(Request $request, ProjectRequest $projectRequest)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,quoted,accepted,rejected,completed',
            'admin_notes' => 'nullable|string',
            'quoted_price' => 'nullable|numeric|min:0',
            'quote_details' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldStatus = $projectRequest->status;
        $data = $request->only(['status', 'admin_notes', 'quoted_price', 'quote_details']);
        
        // If status is being changed to quoted, set quoted_at and quoted_by
        if ($request->status === 'quoted' && $projectRequest->status !== 'quoted') {
            $data['quoted_at'] = now();
            $data['quoted_by'] = auth()->id();
        }

        $projectRequest->update($data);

        // Send email notification if quote is sent
        if ($request->status === 'quoted' && $projectRequest->wasChanged('status')) {
            $this->sendQuoteEmail($projectRequest);
        }

        return back()->with('success', 'Project request updated successfully.');
    }

    public function destroy(ProjectRequest $projectRequest)
    {
        try {
            // Delete uploaded files if any
            if ($projectRequest->uploaded_files) {
                foreach ($projectRequest->uploaded_files as $file) {
                    if (file_exists(public_path($file))) {
                        unlink(public_path($file));
                    }
                }
            }
            
            // Delete the project request
            $projectRequest->delete();
            
            return back()->with('success', 'Project request deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete project request: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete project request. Please try again.');
        }
    }

    public function export(Request $request)
    {
        $query = ProjectRequest::with(['quotedBy']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_company', 'like', "%{$search}%");
            });
        }

        $projectRequests = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="project-requests-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($projectRequests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID',
                'Customer Name',
                'Email',
                'Phone',
                'Company',
                'Budget Range',
                'Deadline',
                'Status',
                'Quoted Price',
                'Created At',
                'Quoted At'
            ]);

            foreach ($projectRequests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->customer_name,
                    $request->customer_email,
                    $request->customer_phone ?? 'N/A',
                    $request->customer_company ?? 'N/A',
                    $request->budget_range_label ?? 'N/A',
                    $request->deadline_label ?? 'N/A',
                    $request->status_label,
                    $request->quoted_price ?? 'N/A',
                    $request->created_at->format('Y-m-d H:i:s'),
                    $request->quoted_at ? $request->quoted_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function sendQuoteEmail(ProjectRequest $projectRequest)
    {
        try {
            // Send email to customer with quote details
            Mail::send('emails.project-quote-sent', ['projectRequest' => $projectRequest], function ($message) use ($projectRequest) {
                $message->to($projectRequest->customer_email, $projectRequest->customer_name)
                        ->subject('Your Project Quote');
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send project quote email: ' . $e->getMessage());
        }
    }
}