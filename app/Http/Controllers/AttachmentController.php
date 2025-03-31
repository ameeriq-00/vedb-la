<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Attachment $attachment)
    {
        // Check if user has permission to view this attachment
        if (!$this->canAccessAttachment($attachment)) {
            abort(403, 'غير مصرح لك بعرض هذا المرفق.');
        }
        
        $path = storage_path('app/public/' . $attachment->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'المرفق غير موجود.');
        }
        
        $content = file_get_contents($path);
        $type = $attachment->file_type;
        
        return Response::make($content, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="' . $attachment->file_name . '"'
        ]);
    }
    
    public function download(Attachment $attachment)
    {
        // Check if user has permission to download this attachment
        if (!$this->canAccessAttachment($attachment)) {
            abort(403, 'غير مصرح لك بتنزيل هذا المرفق.');
        }
        
        $path = storage_path('app/public/' . $attachment->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'المرفق غير موجود.');
        }
        
        return response()->download($path, $attachment->file_name);
    }
    
    public function destroy(Attachment $attachment)
    {
        // Check if user has permission to delete this attachment
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'verifier']) && $attachment->user_id !== $user->id) {
            abort(403, 'غير مصرح لك بحذف هذا المرفق.');
        }
        
        // Delete the file
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        
        // Delete the record
        $attachment->delete();
        
        return redirect()->back()->with('success', 'تم حذف المرفق بنجاح');
    }

    // Helper method to check if user can access attachment
    private function canAccessAttachment(Attachment $attachment)
    {
        $user = Auth::user();
        
        // Admin and verifier can access all attachments
        if ($user->hasRole(['admin', 'verifier'])) {
            return true;
        }
        
        // If it's a vehicle attachment
        if ($attachment->attachable_type === 'App\Models\Vehicle') {
            $vehicle = $attachment->attachable;
            
            // Data entry can access own directorate's vehicles
            if ($user->hasRole('data_entry') && $vehicle->directorate_id === $user->directorate_id) {
                return true;
            }
            
            // Vehicles department can access final degree or authenticated vehicles
            if ($user->hasRole('vehicles_dept') && 
                ($vehicle->type === 'government' || 
                 $vehicle->final_degree_status === 'مكتسبة' || 
                 $vehicle->authentication_status === 'تمت المصادقة عليها')) {
                return true;
            }
            
            // Recipients can access vehicles transferred to them
            if ($user->hasRole('recipient')) {
                return $vehicle->transfers()
                    ->where('destination_directorate_id', $user->directorate_id)
                    ->exists();
            }
        }
        
        // If it's a transfer attachment
        if ($attachment->attachable_type === 'App\Models\VehicleTransfer') {
            $transfer = $attachment->attachable;
            
            // Vehicles department can access all transfers
            if ($user->hasRole('vehicles_dept')) {
                return true;
            }
            
            // Data entry can access transfers for own directorate's vehicles
            if ($user->hasRole('data_entry') && $transfer->vehicle->directorate_id === $user->directorate_id) {
                return true;
            }
            
            // Recipients can access transfers to their directorate
            if ($user->hasRole('recipient') && $transfer->destination_directorate_id === $user->directorate_id) {
                return true;
            }
        }
        
        // If it's an edit request attachment
        if ($attachment->attachable_type === 'App\Models\EditRequest') {
            $editRequest = $attachment->attachable;
            
            // Creator can access their own edit request attachments
            if ($editRequest->user_id === $user->id) {
                return true;
            }
            
            // Data entry can access edit requests for own directorate's vehicles
            if ($user->hasRole('data_entry') && $editRequest->vehicle->directorate_id === $user->directorate_id) {
                return true;
            }
        }
        
        // If it's a vehicle status attachment
        if ($attachment->attachable_type === 'App\Models\VehicleStatus') {
            $status = $attachment->attachable;
            
            // Data entry can access status updates for own directorate's vehicles
            if ($user->hasRole('data_entry') && $status->vehicle->directorate_id === $user->directorate_id) {
                return true;
            }
            
            // Vehicles department can access status updates for final degree or authenticated vehicles
            if ($user->hasRole('vehicles_dept') && 
                ($status->vehicle->type === 'government' || 
                 $status->vehicle->final_degree_status === 'مكتسبة' || 
                 $status->vehicle->authentication_status === 'تمت المصادقة عليها')) {
                return true;
            }
        }
        
        // User owns the attachment
        return $attachment->user_id === $user->id;
    }
}