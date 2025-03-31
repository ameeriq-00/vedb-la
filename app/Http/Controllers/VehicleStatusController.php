<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use App\Models\Attachment;
use App\Notifications\VehicleStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class VehicleStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:update vehicle status');
    }
    
    public function updateSeizureStatus(Request $request, Vehicle $vehicle)
    {
        // Validate the request
        $validatedData = $request->validate([
            'seizure_status' => 'required|in:محجوزة,مفرج عنها,مصادرة',
            'letter_number' => 'required|string|max:255',
            'letter_date' => 'required|date',
            'notes' => 'nullable|string',
            'attachment' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // Check if the current status is final and trying to go back
        if ($vehicle->seizure_status === 'مصادرة' && $validatedData['seizure_status'] !== 'مصادرة') {
            if (!$user->hasRole(['admin', 'verifier'])) {
                return redirect()->back()->with('error', 'لا يمكن تغيير الحالة من مصادرة إلى أي حالة أخرى إلا بموافقة المدقق أو المشرف');
            }
        }
        
        // Check if going from released to seized needs admin/verifier approval
        if ($vehicle->seizure_status === 'مفرج عنها' && $validatedData['seizure_status'] === 'محجوزة') {
            if (!$user->hasRole(['admin', 'verifier'])) {
                return redirect()->back()->with('error', 'لا يمكن تغيير الحالة من مفرج عنها إلى محجوزة إلا بموافقة المدقق أو المشرف');
            }
        }
        
        // Store the old status for history
        $oldStatus = $vehicle->seizure_status;
        
        // Update vehicle status
        $vehicle->seizure_status = $validatedData['seizure_status'];
        
        // Reset dependent statuses when moving backwards
        if ($oldStatus === 'مصادرة' && $validatedData['seizure_status'] !== 'مصادرة') {
            $vehicle->final_degree_status = 'غير مكتسبة';
            $vehicle->valuation_status = 'غير مثمنة';
            $vehicle->authentication_status = 'غير مصادق عليها';
            $vehicle->donation_status = 'غير مهداة';
            $vehicle->government_registration_status = 'غير مرقمة';
            
            // Clear related fields
            $vehicle->decision_number = null;
            $vehicle->decision_date = null;
            $vehicle->valuation_amount = null;
            $vehicle->authentication_number = null;
            $vehicle->authentication_date = null;
            $vehicle->donation_letter_number = null;
            $vehicle->donation_letter_date = null;
            $vehicle->donation_entity = null;
            $vehicle->registration_letter_number = null;
            $vehicle->registration_letter_date = null;
            $vehicle->government_registration_number = null;
        }
        
        // Update relevant letter information based on status
        if ($validatedData['seizure_status'] == 'محجوزة') {
            $vehicle->seizure_letter_number = $validatedData['letter_number'];
            $vehicle->seizure_letter_date = $validatedData['letter_date'];
        } elseif ($validatedData['seizure_status'] == 'مفرج عنها') {
            $vehicle->release_decision_number = $validatedData['letter_number'];
            $vehicle->release_decision_date = $validatedData['letter_date'];
        } elseif ($validatedData['seizure_status'] == 'مصادرة') {
            $vehicle->confiscation_letter_number = $validatedData['letter_number'];
            $vehicle->confiscation_letter_date = $validatedData['letter_date'];
        }
        
        $vehicle->save();
        
        // Create status history record
        $status = VehicleStatus::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'status_type' => 'seizure_status',
            'old_status' => $oldStatus,
            'new_status' => $validatedData['seizure_status'],
            'letter_number' => $validatedData['letter_number'],
            'letter_date' => $validatedData['letter_date'],
            'notes' => $validatedData['notes'],
        ]);
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('status_documents', $filename, 'public');
            
            $attachmentType = '';
            if ($validatedData['seizure_status'] == 'محجوزة') {
                $attachmentType = 'seizure_letter';
            } elseif ($validatedData['seizure_status'] == 'مفرج عنها') {
                $attachmentType = 'release_decision';
            } elseif ($validatedData['seizure_status'] == 'مصادرة') {
                $attachmentType = 'confiscation_letter';
            }
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleStatus',
                'attachable_id' => $status->id,
                'type' => $attachmentType,
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // Notify relevant users
        $this->notifyStatusUpdate($vehicle, 'seizure_status', $oldStatus, $validatedData['seizure_status']);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تحديث حالة العجلة بنجاح');
    }
    
    public function updateFinalDegreeStatus(Request $request, Vehicle $vehicle)
    {
        // Validate the request
        $validatedData = $request->validate([
            'final_degree_status' => 'required|in:غير مكتسبة,مكتسبة',
            'decision_number' => 'required_if:final_degree_status,مكتسبة|nullable|string|max:255',
            'decision_date' => 'required_if:final_degree_status,مكتسبة|nullable|date',
            'notes' => 'nullable|string',
            'attachment' => 'required_if:final_degree_status,مكتسبة|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // Check pre-conditions
        if ($vehicle->seizure_status != 'مصادرة') {
            return redirect()->back()->with('error', 'لا يمكن تحديث حالة اكتساب الدرجة القطعية إلا بعد مصادرة العجلة');
        }
        
        // Going back from acquired to not acquired requires admin/verifier
        if ($vehicle->final_degree_status === 'مكتسبة' && $validatedData['final_degree_status'] === 'غير مكتسبة') {
            if (!$user->hasRole(['admin', 'verifier'])) {
                return redirect()->back()->with('error', 'لا يمكن إلغاء اكتساب الدرجة القطعية إلا بموافقة المدقق أو المشرف');
            }
            
            // Reset dependent statuses
            $vehicle->valuation_status = 'غير مثمنة';
            $vehicle->authentication_status = 'غير مصادق عليها';
            $vehicle->donation_status = 'غير مهداة';
            $vehicle->government_registration_status = 'غير مرقمة';
            
            // Clear related fields
            $vehicle->valuation_amount = null;
            $vehicle->authentication_number = null;
            $vehicle->authentication_date = null;
            $vehicle->donation_letter_number = null;
            $vehicle->donation_letter_date = null;
            $vehicle->donation_entity = null;
            $vehicle->registration_letter_number = null;
            $vehicle->registration_letter_date = null;
            $vehicle->government_registration_number = null;
        }
        
        // Store the old status for history
        $oldStatus = $vehicle->final_degree_status;
        
        // Update vehicle status
        $vehicle->final_degree_status = $validatedData['final_degree_status'];
        
        if ($validatedData['final_degree_status'] == 'مكتسبة') {
            $vehicle->decision_number = $validatedData['decision_number'];
            $vehicle->decision_date = $validatedData['decision_date'];
        } else {
            $vehicle->decision_number = null;
            $vehicle->decision_date = null;
        }
        
        $vehicle->save();
        
        // Create status history record
        $status = VehicleStatus::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'status_type' => 'final_degree_status',
            'old_status' => $oldStatus,
            'new_status' => $validatedData['final_degree_status'],
            'letter_number' => $validatedData['decision_number'] ?? null,
            'letter_date' => $validatedData['decision_date'] ?? null,
            'notes' => $validatedData['notes'],
        ]);
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('status_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleStatus',
                'attachable_id' => $status->id,
                'type' => 'final_degree_decision',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // Notify relevant users
        $this->notifyStatusUpdate($vehicle, 'final_degree_status', $oldStatus, $validatedData['final_degree_status']);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تحديث حالة اكتساب الدرجة القطعية بنجاح');
    }
    
    public function updateValuationStatus(Request $request, Vehicle $vehicle)
    {
        // Validate the request
        $validatedData = $request->validate([
            'valuation_status' => 'required|in:غير مثمنة,مثمنة',
            'valuation_amount' => 'required_if:valuation_status,مثمنة|nullable|numeric',
            'notes' => 'nullable|string',
            'attachment' => 'required_if:valuation_status,مثمنة|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // Check pre-conditions - تم تغيير التسلسل ليكون التثمين بعد الدرجة القطعية وقبل المصادقة
        if ($vehicle->final_degree_status != 'مكتسبة') {
            return redirect()->back()->with('error', 'لا يمكن تثمين العجلة إلا بعد اكتساب الدرجة القطعية');
        }
        
        // Going back from valued to not valued requires admin/verifier
        if ($vehicle->valuation_status === 'مثمنة' && $validatedData['valuation_status'] === 'غير مثمنة') {
            if (!$user->hasRole(['admin', 'verifier'])) {
                return redirect()->back()->with('error', 'لا يمكن إلغاء التثمين إلا بموافقة المدقق أو المشرف');
            }
            
            // Reset dependent statuses
            $vehicle->authentication_status = 'غير مصادق عليها';
            $vehicle->donation_status = 'غير مهداة';
            $vehicle->government_registration_status = 'غير مرقمة';
            
            // Clear related fields
            $vehicle->authentication_number = null;
            $vehicle->authentication_date = null;
            $vehicle->donation_letter_number = null;
            $vehicle->donation_letter_date = null;
            $vehicle->donation_entity = null;
            $vehicle->registration_letter_number = null;
            $vehicle->registration_letter_date = null;
            $vehicle->government_registration_number = null;
        }
        
        // Store the old status for history
        $oldStatus = $vehicle->valuation_status;
        
        // Update vehicle status
        $vehicle->valuation_status = $validatedData['valuation_status'];
        
        if ($validatedData['valuation_status'] == 'مثمنة') {
            $vehicle->valuation_amount = $validatedData['valuation_amount'];
        } else {
            $vehicle->valuation_amount = null;
        }
        
        $vehicle->save();
        
        // Create status history record
        $status = VehicleStatus::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'status_type' => 'valuation_status',
            'old_status' => $oldStatus,
            'new_status' => $validatedData['valuation_status'],
            'notes' => $validatedData['notes'],
        ]);
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('status_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleStatus',
                'attachable_id' => $status->id,
                'type' => 'valuation_document',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // Notify relevant users
        $this->notifyStatusUpdate($vehicle, 'valuation_status', $oldStatus, $validatedData['valuation_status']);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تحديث حالة التثمين بنجاح');
    }
    
    public function updateAuthenticationStatus(Request $request, Vehicle $vehicle)
    {
        // Validate the request
        $validatedData = $request->validate([
            'authentication_status' => 'required|in:غير مصادق عليها,تمت المصادقة عليها',
            'authentication_number' => 'required_if:authentication_status,تمت المصادقة عليها|nullable|string|max:255',
            'authentication_date' => 'required_if:authentication_status,تمت المصادقة عليها|nullable|date',
            'notes' => 'nullable|string',
            'attachment' => 'required_if:authentication_status,تمت المصادقة عليها|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // Check pre-conditions - تم تغيير التسلسل ليكون المصادقة بعد التثمين
        if ($vehicle->valuation_status != 'مثمنة') {
            return redirect()->back()->with('error', 'لا يمكن تحديث حالة المصادقة إلا بعد تثمين العجلة');
        }
        
        // Going back from authenticated to not authenticated requires admin/verifier
        if ($vehicle->authentication_status === 'تمت المصادقة عليها' && $validatedData['authentication_status'] === 'غير مصادق عليها') {
            if (!$user->hasRole(['admin', 'verifier'])) {
                return redirect()->back()->with('error', 'لا يمكن إلغاء المصادقة إلا بموافقة المدقق أو المشرف');
            }
            
            // Reset dependent statuses
            $vehicle->donation_status = 'غير مهداة';
            $vehicle->government_registration_status = 'غير مرقمة';
            
            // Clear related fields
            $vehicle->donation_letter_number = null;
            $vehicle->donation_letter_date = null;
            $vehicle->donation_entity = null;
            $vehicle->registration_letter_number = null;
            $vehicle->registration_letter_date = null;
            $vehicle->government_registration_number = null;
        }
        
        // Store the old status for history
        $oldStatus = $vehicle->authentication_status;
        
        // Update vehicle status
        $vehicle->authentication_status = $validatedData['authentication_status'];
        
        if ($validatedData['authentication_status'] == 'تمت المصادقة عليها') {
            $vehicle->authentication_number = $validatedData['authentication_number'];
            $vehicle->authentication_date = $validatedData['authentication_date'];
        } else {
            $vehicle->authentication_number = null;
            $vehicle->authentication_date = null;
        }
        
        $vehicle->save();
        
        // Create status history record
        $status = VehicleStatus::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'status_type' => 'authentication_status',
            'old_status' => $oldStatus,
            'new_status' => $validatedData['authentication_status'],
            'letter_number' => $validatedData['authentication_number'] ?? null,
            'letter_date' => $validatedData['authentication_date'] ?? null,
            'notes' => $validatedData['notes'],
        ]);
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('status_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleStatus',
                'attachable_id' => $status->id,
                'type' => 'authentication_letter',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // Notify relevant users
        $this->notifyStatusUpdate($vehicle, 'authentication_status', $oldStatus, $validatedData['authentication_status']);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تحديث حالة المصادقة بنجاح');
    }
    
    public function updateDonationStatus(Request $request, Vehicle $vehicle)
    {
        // Validate the request
        $validatedData = $request->validate([
            'donation_status' => 'required|in:غير مهداة,مهداة',
            'donation_letter_number' => 'required_if:donation_status,مهداة|nullable|string|max:255',
            'donation_letter_date' => 'required_if:donation_status,مهداة|nullable|date',
            'donation_entity' => 'required_if:donation_status,مهداة|nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'required_if:donation_status,مهداة|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // Check pre-conditions
        if ($vehicle->authentication_status != 'تمت المصادقة عليها') {
            return redirect()->back()->with('error', 'لا يمكن تحديث حالة الإهداء إلا بعد المصادقة على العجلة');
        }
        
        // Going back from donated to not donated requires admin/verifier
        if ($vehicle->donation_status === 'مهداة' && $validatedData['donation_status'] === 'غير مهداة') {
            if (!$user->hasRole(['admin', 'verifier'])) {
                return redirect()->back()->with('error', 'لا يمكن إلغاء الإهداء إلا بموافقة المدقق أو المشرف');
            }
            
            // Reset dependent statuses
            $vehicle->government_registration_status = 'غير مرقمة';
            
            // Clear related fields
            $vehicle->registration_letter_number = null;
            $vehicle->registration_letter_date = null;
            $vehicle->government_registration_number = null;
        }
        
        // Store the old status for history
        $oldStatus = $vehicle->donation_status;
        
        // Update vehicle status
        $vehicle->donation_status = $validatedData['donation_status'];
        
        if ($validatedData['donation_status'] == 'مهداة') {
            $vehicle->donation_letter_number = $validatedData['donation_letter_number'];
            $vehicle->donation_letter_date = $validatedData['donation_letter_date'];
            $vehicle->donation_entity = $validatedData['donation_entity'];
        } else {
            $vehicle->donation_letter_number = null;
            $vehicle->donation_letter_date = null;
            $vehicle->donation_entity = null;
        }
        
        $vehicle->save();
        
        // Create status history record
        $status = VehicleStatus::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'status_type' => 'donation_status',
            'old_status' => $oldStatus,
            'new_status' => $validatedData['donation_status'],
            'letter_number' => $validatedData['donation_letter_number'] ?? null,
            'letter_date' => $validatedData['donation_letter_date'] ?? null,
            'notes' => $validatedData['notes'],
        ]);
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('status_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleStatus',
                'attachable_id' => $status->id,
                'type' => 'donation_letter',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // Notify relevant users
        $this->notifyStatusUpdate($vehicle, 'donation_status', $oldStatus, $validatedData['donation_status']);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تحديث حالة الإهداء بنجاح');
    }
    
    public function updateRegistrationStatus(Request $request, Vehicle $vehicle)
    {
        // Validate the request
        $validatedData = $request->validate([
            'government_registration_status' => 'required|in:غير مرقمة,مرقمة',
            'registration_letter_number' => 'required_if:government_registration_status,مرقمة|nullable|string|max:255',
            'registration_letter_date' => 'required_if:government_registration_status,مرقمة|nullable|date',
            'government_registration_number' => 'required_if:government_registration_status,مرقمة|nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'required_if:government_registration_status,مرقمة|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // Check pre-conditions
        if ($vehicle->donation_status != 'مهداة') {
            return redirect()->back()->with('error', 'لا يمكن تحديث حالة الترقيم الحكومي إلا بعد إهداء العجلة');
        }
        if (!auth()->user()->hasRole(['admin', 'verifier', 'vehicles_dept'])) {
            return redirect()->back()->with('error', 'غير مصرح لك بتحديث حالة الترقيم الحكومي');
        }
        // Going back from registered to not registered requires admin/verifier
        if ($vehicle->government_registration_status === 'مرقمة' && $validatedData['government_registration_status'] === 'غير مرقمة') {
            if (!$user->hasRole(['admin', 'verifier'])) {
                return redirect()->back()->with('error', 'لا يمكن إلغاء الترقيم الحكومي إلا بموافقة المدقق أو المشرف');
            }
        }
        
        // Store the old status for history
        $oldStatus = $vehicle->government_registration_status;
        
        // Update vehicle status
        $vehicle->government_registration_status = $validatedData['government_registration_status'];
        
        if ($validatedData['government_registration_status'] == 'مرقمة') {
            $vehicle->registration_letter_number = $validatedData['registration_letter_number'];
            $vehicle->registration_letter_date = $validatedData['registration_letter_date'];
            $vehicle->government_registration_number = $validatedData['government_registration_number'];
        } else {
            $vehicle->registration_letter_number = null;
            $vehicle->registration_letter_date = null;
            $vehicle->government_registration_number = null;
        }
        
        $vehicle->save();
        
        // Create status history record
        $status = VehicleStatus::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'status_type' => 'government_registration_status',
            'old_status' => $oldStatus,
            'new_status' => $validatedData['government_registration_status'],
            'letter_number' => $validatedData['registration_letter_number'] ?? null,
            'letter_date' => $validatedData['registration_letter_date'] ?? null,
            'notes' => $validatedData['notes'],
        ]);
        
        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('status_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleStatus',
                'attachable_id' => $status->id,
                'type' => 'registration_document',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // Notify relevant users
        $this->notifyStatusUpdate($vehicle, 'government_registration_status', $oldStatus, $validatedData['government_registration_status']);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تحديث حالة الترقيم الحكومي بنجاح');
    }
    
    // Helper method to send notifications
    private function notifyStatusUpdate($vehicle, $statusType, $oldStatus, $newStatus)
    {
        // Find users who should be notified
        $usersToNotify = [];
        
        // Add admin and verifier users
        $adminUsers = User::role(['admin', 'verifier'])->get();
        foreach ($adminUsers as $adminUser) {
            $usersToNotify[] = $adminUser;
        }
        
        // Add users from the same directorate
        $directorate = $vehicle->directorate;
        if ($directorate) {
            $directoryUsers = User::where('directorate_id', $directorate->id)
                                  ->whereNotIn('id', $adminUsers->pluck('id')->toArray())
                                  ->get();
            foreach ($directoryUsers as $dirUser) {
                $usersToNotify[] = $dirUser;
            }
        }
        
        // Add vehicles department users for specific statuses
        if (in_array($statusType, ['final_degree_status', 'authentication_status', 'valuation_status', 'donation_status', 'government_registration_status'])) {
            $vehiclesDeptUsers = User::role('vehicles_dept')
                                     ->whereNotIn('id', collect($usersToNotify)->pluck('id')->toArray())
                                     ->get();
            foreach ($vehiclesDeptUsers as $vdUser) {
                $usersToNotify[] = $vdUser;
            }
        }
        
        // Send notifications
        foreach ($usersToNotify as $user) {
            $user->notify(new VehicleStatusUpdated($vehicle, $statusType, $oldStatus, $newStatus, Auth::user()));
        }
    }
}