<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\EditRequest;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create edit requests')->only(['create', 'store']);
        $this->middleware('permission:view edit requests')->only(['index', 'show']);
        $this->middleware('permission:approve edit requests')->only(['approve', 'reject']);
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = EditRequest::with(['vehicle', 'user', 'approver']);
        
        // Data entry users see only their requests
        if ($user->hasRole('data_entry')) {
            $query->where('user_id', $user->id);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by directorate
        if ($request->has('directorate_id') && $user->hasRole(['admin', 'verifier'])) {
            $query->whereHas('vehicle', function($q) use ($request) {
                $q->where('directorate_id', $request->directorate_id);
            });
        } elseif (!$user->hasRole(['admin', 'verifier'])) {
            // Other users only see their directorate's requests
            $query->whereHas('vehicle', function($q) use ($user) {
                $q->where('directorate_id', $user->directorate_id);
            });
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('field_name', 'like', "%{$search}%")
                  ->orWhere('new_value', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function($q2) use ($search) {
                      $q2->where('vehicle_type', 'like', "%{$search}%")
                        ->orWhere('vehicle_name', 'like', "%{$search}%")
                        ->orWhere('vehicle_number', 'like', "%{$search}%");
                  });
            });
        }
        
        $editRequests = $query->latest()->paginate(10)->withQueryString();
        
        return view('edit_requests.index', compact('editRequests'));
    }
    
    public function create(Vehicle $vehicle, $field)
    {
        // قائمة الحقول القابلة للتعديل (الحقول الأساسية فقط)
        $editableFields = [
            // معلومات أساسية
            'vehicle_type', 'vehicle_name', 'model', 'chassis_number', 'vehicle_number', 
            'province', 'color', 'vehicle_condition', 'missing_parts',
            
            // معلومات العجلة المصادرة
            'defendant_name', 'legal_article',
            
            // معلومات العجلة الحكومية
            'source', 'import_letter_number', 'import_letter_date',
            
            // معلومات أخرى
            'notes',
        ];
        
        if (!in_array($field, $editableFields)) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'هذا الحقل غير قابل للتعديل من خلال طلب تعديل');
        }
        
        $fieldValue = $vehicle->$field;
        
        // تحديد نوع الإدخال المناسب للحقل
        $inputType = 'text'; // افتراضي
        
        if (in_array($field, ['import_letter_date'])) {
            $inputType = 'date';
        } elseif ($field == 'vehicle_condition') {
            $inputType = 'select';
        } elseif (in_array($field, ['notes', 'missing_parts'])) {
            $inputType = 'textarea';
        }
        
        return view('edit_requests.create', compact('vehicle', 'field', 'fieldValue', 'inputType'));
    }
    
    public function store(Request $request, Vehicle $vehicle)
    {
        // Validate the request
        $validatedData = $request->validate([
            'field_name' => 'required|string',
            'old_value' => 'nullable|string',
            'new_value' => 'required|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // Create edit request
        $editRequest = EditRequest::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'field_name' => $validatedData['field_name'],
            'old_value' => $validatedData['old_value'],
            'new_value' => $validatedData['new_value'],
            'status' => 'pending',
            'notes' => $validatedData['notes'],
        ]);
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('edit_request_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\EditRequest',
                'attachable_id' => $editRequest->id,
                'type' => 'edit_request_document',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم إرسال طلب التعديل بنجاح');
    }
    
    public function show(EditRequest $editRequest)
    {
        $editRequest->load(['vehicle', 'user', 'approver', 'attachments']);
        
        return view('edit_requests.show', compact('editRequest'));
    }
    
    public function approve(Request $request, EditRequest $editRequest)
    {
        // Validate the request
        $validatedData = $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        $user = Auth::user();
        $vehicle = $editRequest->vehicle;
        
        // Update edit request status
        $editRequest->status = 'approved';
        $editRequest->approved_by = $user->id;
        $editRequest->approval_date = now();
        $editRequest->notes = $editRequest->notes . "\n" . ($validatedData['notes'] ?? 'تمت الموافقة على الطلب');
        $editRequest->save();
        
        // Update vehicle field
        $fieldName = $editRequest->field_name;
        $newValue = $editRequest->new_value;
        
        $vehicle->$fieldName = $newValue;
        $vehicle->save();
        
        // هنا تكمن المشكلة: يجب استخدام المسار الصحيح
        return redirect()->route('edit-requests.show', $editRequest)
            ->with('success', 'تمت الموافقة على طلب التعديل بنجاح');
    }
    
    public function reject(Request $request, EditRequest $editRequest)
    {
        // Validate the request
        $validatedData = $request->validate([
            'notes' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        // Update edit request status
        $editRequest->status = 'rejected';
        $editRequest->approved_by = $user->id;
        $editRequest->approval_date = now();
        $editRequest->notes = $editRequest->notes . "\n" . $validatedData['notes'];
        $editRequest->save();
        
        // هنا أيضًا تكمن المشكلة: يجب استخدام المسار الصحيح
        return redirect()->route('edit-requests.show', $editRequest)
            ->with('success', 'تم رفض طلب التعديل');
    }
}