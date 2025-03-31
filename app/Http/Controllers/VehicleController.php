<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Directorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachment;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view vehicles')->only(['index', 'show']);
        $this->middleware('permission:create vehicles')->only(['create', 'store']);
        $this->middleware('permission:edit vehicles')->only(['edit', 'update']);
        $this->middleware('permission:delete vehicles')->only('destroy');
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Vehicle::with(['directorate', 'user']);
        
        // Apply filtering
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('status')) {
            $status = $request->status;
            if ($status == 'محجوزة' || $status == 'مصادرة' || $status == 'مفرج عنها') {
                $query->where('seizure_status', $status);
            } elseif ($status == 'مكتسبة') {
                $query->where('final_degree_status', $status);
            } elseif ($status == 'مصادق عليها') {
                $query->where('authentication_status', $status);
            } elseif ($status == 'مثمنة') {
                $query->where('valuation_status', $status);
            }
        }
        
        if ($request->has('directorate_id') && $user->hasRole(['admin', 'verifier'])) {
            $query->where('directorate_id', $request->directorate_id);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle_type', 'like', "%{$search}%")
                  ->orWhere('vehicle_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('chassis_number', 'like', "%{$search}%")
                  ->orWhere('defendant_name', 'like', "%{$search}%");
            });
        }
        
        // Apply directorate restrictions based on user role
        $vehicles = $query->forUserDirectorate($user)
            ->latest()
            ->paginate(10)
            ->withQueryString();
        
        $directorates = [];
        if ($user->hasRole(['admin', 'verifier'])) {
            $directorates = Directorate::all();
        }
        
        return view('vehicles.index', compact('vehicles', 'directorates'));
    }

    public function create()
    {
        $user = Auth::user();
        $directorates = [];
        
        // If admin or verifier, show all directorates
        if ($user->hasRole(['admin', 'verifier'])) {
            $directorates = Directorate::all();
        }
        
        // تمكين مستخدمي الآليات من إضافة العجلات الحكومية
        $canAddGovernmentVehicle = $user->hasRole(['admin', 'verifier', 'vehicles_dept']);
        
        return view('vehicles.create', compact('directorates', 'canAddGovernmentVehicle'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'type' => 'required|in:confiscated,government',
            'directorate_id' => 'required|exists:directorates,id',
            'vehicle_type' => 'required|string|max:255',
            'vehicle_name' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'chassis_number' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'vehicle_condition' => 'required|in:صالحة,غير صالحة',
            'accessories' => 'nullable|array',
            'defects' => 'nullable|array',
            'missing_parts' => 'nullable|string',
            
            // Confiscated vehicle fields
            'defendant_name' => 'required_if:type,confiscated|nullable|string|max:255',
            'legal_article' => 'required_if:type,confiscated|nullable|string|max:255',
            'seizure_status' => 'required_if:type,confiscated|nullable|in:محجوزة,مفرج عنها,مصادرة',
            'seizure_letter_number' => 'nullable|string|max:255',
            'seizure_letter_date' => 'nullable|date',
            
            // Government vehicle fields
            'source' => 'required_if:type,government|nullable|string|max:255',
            'import_letter_number' => 'nullable|string|max:255',
            'import_letter_date' => 'nullable|date',
            
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);
        
        // Create the vehicle
        $user = Auth::user();
        
        // التحقق من صلاحية إضافة عجلات حكومية
        if ($validatedData['type'] == 'government' && !$user->hasRole(['admin', 'verifier', 'vehicles_dept'])) {
            return redirect()->route('vehicles.index')
                ->with('error', 'ليس لديك صلاحية لإضافة العجلات الحكومية');
        }
        
        // Set directorate_id to user's directorate if not admin/verifier
        if (!$user->hasRole(['admin', 'verifier'])) {
            $validatedData['directorate_id'] = $user->directorate_id;
        }
        
        $validatedData['user_id'] = $user->id;
        
        // Set default status for confiscated vehicles
        if ($validatedData['type'] == 'confiscated') {
            $validatedData['final_degree_status'] = 'غير مكتسبة';
            $validatedData['authentication_status'] = 'غير مصادق عليها';
            $validatedData['valuation_status'] = 'غير مثمنة';
            $validatedData['donation_status'] = 'غير مهداة';
            $validatedData['government_registration_status'] = 'غير مرقمة';
        }
        
        $vehicle = Vehicle::create($validatedData);
        
        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('vehicle_images', $filename, 'public');
                
                Attachment::create([
                    'attachable_type' => 'App\Models\Vehicle',
                    'attachable_id' => $vehicle->id,
                    'type' => 'vehicle_image',
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_type' => $image->getMimeType(),
                    'file_size' => $image->getSize(),
                    'user_id' => $user->id
                ]);
            }
        }
        
        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('vehicle_documents', $filename, 'public');
                
                Attachment::create([
                    'attachable_type' => 'App\Models\Vehicle',
                    'attachable_id' => $vehicle->id,
                    'type' => 'vehicle_document',
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'user_id' => $user->id
                ]);
            }
        }
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم إضافة العجلة بنجاح');
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);
        
        $vehicle->load(['directorate', 'user', 'statuses.user', 'transfers.user', 'editRequests.user', 'attachments']);
        
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        
        $user = Auth::user();
        $directorates = [];
        
        // If admin or verifier, show all directorates
        if ($user->hasRole(['admin', 'verifier'])) {
            $directorates = Directorate::all();
        }
        
        return view('vehicles.edit', compact('vehicle', 'directorates'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);
        
        // Validate the request
        $validatedData = $request->validate([
            'type' => 'required|in:confiscated,government',
            'directorate_id' => 'required|exists:directorates,id',
            'vehicle_type' => 'required|string|max:255',
            'vehicle_name' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'chassis_number' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'vehicle_condition' => 'required|in:صالحة,غير صالحة',
            'accessories' => 'nullable|array',
            'defects' => 'nullable|array',
            'missing_parts' => 'nullable|string',
            
            // Confiscated vehicle fields
            'defendant_name' => 'required_if:type,confiscated|nullable|string|max:255',
            'legal_article' => 'required_if:type,confiscated|nullable|string|max:255',
            
            // Don't allow updating status through this form
            
            // Government vehicle fields
            'source' => 'required_if:type,government|nullable|string|max:255',
            
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);
        
        $vehicle->update($validatedData);
        
        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('vehicle_images', $filename, 'public');
                
                Attachment::create([
                    'attachable_type' => 'App\Models\Vehicle',
                    'attachable_id' => $vehicle->id,
                    'type' => 'vehicle_image',
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_type' => $image->getMimeType(),
                    'file_size' => $image->getSize(),
                    'user_id' => Auth::id()
                ]);
            }
        }
        
        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('vehicle_documents', $filename, 'public');
                
                Attachment::create([
                    'attachable_type' => 'App\Models\Vehicle',
                    'attachable_id' => $vehicle->id,
                    'type' => 'vehicle_document',
                    'file_name' => $filename,
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'user_id' => Auth::id()
                ]);
            }
        }
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تحديث العجلة بنجاح');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);
        
        // Delete associated files
        foreach ($vehicle->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        $vehicle->delete();
        
        return redirect()->route('vehicles.index')
            ->with('success', 'تم حذف العجلة بنجاح');
    }
}