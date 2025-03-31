<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleTransfer;
use App\Models\Directorate;
use App\Models\Attachment;
use App\Models\User;
use App\Notifications\VehicleTransferred;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create transfers')->only(['create', 'store']);
        $this->middleware('permission:view transfers')->only(['index', 'show']);
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = VehicleTransfer::with(['vehicle', 'user', 'destinationDirectorate']);
        
        // Filter by directorate
        if ($user->hasRole('recipient')) {
            $query->where('destination_directorate_id', $user->directorate_id);
        } elseif ($request->has('directorate_id') && $user->hasRole(['admin', 'verifier', 'vehicles_dept'])) {
            $query->where('destination_directorate_id', $request->directorate_id);
        }
        
        // Filter by vehicle type
        if ($request->has('vehicle_type')) {
            $query->whereHas('vehicle', function($q) use ($request) {
                $q->where('type', $request->vehicle_type);
            });
        }

        // Filter by status (active/completed)
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNull('return_date');
            } elseif ($request->status === 'completed') {
                $query->whereNotNull('return_date');
            }
        }
        
        // Filter by transfer type
        if ($request->has('transfer_type')) {
            if ($request->transfer_type === 'regular') {
                $query->where('is_ownership_transfer', false)->where('is_referral', false);
            } elseif ($request->transfer_type === 'ownership') {
                $query->where('is_ownership_transfer', true);
            } elseif ($request->transfer_type === 'referral') {
                $query->where('is_referral', true);
            }
        }
        
        // Filter by recipient ID number
        if ($request->has('recipient_id_number') && !empty($request->recipient_id_number)) {
            $query->where('recipient_id_number', 'like', '%' . $request->recipient_id_number . '%');
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                  ->orWhere('recipient_entity', 'like', "%{$search}%")
                  ->orWhere('assigned_to', 'like', "%{$search}%")
                  ->orWhere('recipient_id_number', 'like', "%{$search}%")
                  ->orWhere('recipient_phone', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function($q2) use ($search) {
                      $q2->where('vehicle_type', 'like', "%{$search}%")
                        ->orWhere('vehicle_name', 'like', "%{$search}%")
                        ->orWhere('vehicle_number', 'like', "%{$search}%");
                  });
            });
        }
        
        $transfers = $query->latest()->paginate(10)->withQueryString();
        
        $directorates = [];
        if ($user->hasRole(['admin', 'verifier', 'vehicles_dept'])) {
            $directorates = Directorate::all();
        }
        
        // للتحذير من الأشخاص الذين لديهم أكثر من عجلة مستلمة
        $activeTransferCountsByRecipient = [];
        if ($user->hasRole(['admin', 'verifier', 'vehicles_dept'])) {
            $activeTransferCountsByRecipient = VehicleTransfer::whereNull('return_date')
                ->where('is_ownership_transfer', false)
                ->where('is_referral', false)
                ->select('recipient_id_number', DB::raw('count(*) as total'))
                ->whereNotNull('recipient_id_number')
                ->groupBy('recipient_id_number')
                ->having('total', '>=', 1) // تغيير هنا لإظهار كل المستلمين وعدد العجلات
                ->pluck('total', 'recipient_id_number')
                ->toArray();
        }
        
        return view('transfers.index', compact('transfers', 'directorates', 'activeTransferCountsByRecipient'));
    }
    
    public function create(Vehicle $vehicle)
    {
        $user = Auth::user();
        
        // التحقق مما إذا كانت العجلة قابلة للمناقلة
        if (!$vehicle->isTransferable()) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'لا يمكن مناقلة هذه العجلة حتى تكتسب الدرجة القطعية أو تتم المصادقة عليها');
        }
        
        // التحقق مما إذا كانت هناك مناقلة نشطة بالفعل
        if ($vehicle->getActiveTransfers()->count() > 0) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'لا يمكن إنشاء مناقلة جديدة حتى يتم إنهاء المناقلة الحالية');
        }

        // التحقق مما إذا كانت العجلة محالة لجهة خارجية
        if ($vehicle->is_externally_referred) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'لا يمكن مناقلة هذه العجلة لأنها محالة لجهة خارجية: ' . $vehicle->external_entity);
        }
        
        $directorates = Directorate::all();
        
        return view('transfers.create', compact('vehicle', 'directorates'));
    }
    
    public function store(Request $request, Vehicle $vehicle)
    {
        // التحقق مما إذا كانت العجلة قابلة للمناقلة
        if (!$vehicle->isTransferable()) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'لا يمكن مناقلة هذه العجلة حتى تكتسب الدرجة القطعية أو تتم المصادقة عليها');
        }
        
        // التحقق مما إذا كانت هناك مناقلة نشطة بالفعل
        if ($vehicle->getActiveTransfers()->count() > 0) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'لا يمكن إنشاء مناقلة جديدة حتى يتم إنهاء المناقلة الحالية');
        }
    
        // التحقق مما إذا كانت العجلة محالة لجهة خارجية
        if ($vehicle->is_externally_referred) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'لا يمكن مناقلة هذه العجلة لأنها محالة لجهة خارجية: ' . $vehicle->external_entity);
        }
        
        // التحقق من البيانات
        $validatedData = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_id_number' => 'required|string|max:255',
            'recipient_phone' => 'nullable|string|max:255',
            'recipient_entity' => 'required|string|max:255',
            'assigned_to' => 'nullable|string|max:255',
            'receive_date' => 'required|date',
            'destination_directorate_id' => 'required|exists:directorates,id',
            'notes' => 'nullable|string',
            'attachment' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $user = Auth::user();
        
        // التحقق مما إذا كان المستلم لديه عجلات نشطة أخرى
        if (!empty($validatedData['recipient_id_number'])) {
            $activeTransfersCount = VehicleTransfer::whereNull('return_date')
                ->where('recipient_id_number', $validatedData['recipient_id_number'])
                ->where('is_ownership_transfer', false)
                ->where('is_referral', false)
                ->count();
                
            if ($activeTransfersCount > 0) {
                // نضيف رسالة تحذيرية وليس منع
                session()->flash('warning', 'تنبيه: المستلم لديه ' . $activeTransfersCount . ' مناقلات نشطة أخرى.');
            }
        }
        
        // إنشاء سجل المناقلة
        $transfer = VehicleTransfer::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'recipient_name' => $validatedData['recipient_name'],
            'recipient_id_number' => $validatedData['recipient_id_number'],
            'recipient_phone' => $validatedData['recipient_phone'],
            'recipient_entity' => $validatedData['recipient_entity'],
            'assigned_to' => $validatedData['assigned_to'],
            'receive_date' => $validatedData['receive_date'],
            'is_external' => false, // المناقلة الاعتيادية هي دائما داخلية
            'destination_directorate_id' => $validatedData['destination_directorate_id'],
            'notes' => $validatedData['notes'],
            'is_ownership_transfer' => false, // مناقلة عادية
            'is_referral' => false, // ليست إحالة خارجية
        ]);
        
        // التعامل مع المرفقات
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('transfer_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleTransfer',
                'attachable_id' => $transfer->id,
                'type' => 'transfer_document',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // إرسال الإشعارات
        $this->notifyTransfer($transfer);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم تسجيل المناقلة بنجاح');
    }
    
    public function show(VehicleTransfer $transfer)
    {
        // التحقق من أن المستخدم يمكنه عرض هذه المناقلة
        $user = Auth::user();
        
        if (!$user->hasRole(['admin', 'verifier', 'vehicles_dept']) && 
            !($user->hasRole('recipient') && $transfer->destination_directorate_id === $user->directorate_id) &&
            !($user->hasRole('data_entry') && $transfer->vehicle->directorate_id === $user->directorate_id)) {
            abort(403, 'غير مصرح لك بعرض هذه المناقلة');
        }
        
        $transfer->load(['vehicle', 'user', 'destinationDirectorate', 'attachments', 'completer']);
        
        return view('transfers.show', compact('transfer'));
    }
    
    public function completeTransfer(Request $request, VehicleTransfer $transfer)
    {
        // التحقق من أن المستخدم يمكنه إكمال هذه المناقلة
        $user = Auth::user();
        
        if (!$user->hasRole(['admin', 'verifier', 'vehicles_dept'])) {
            abort(403, 'غير مصرح لك بإكمال هذه المناقلة');
        }
        
        // التحقق مما إذا كانت المناقلة مكتملة بالفعل
        if ($transfer->return_date !== null) {
            return redirect()->route('transfers.show', $transfer)
                ->with('error', 'تم إكمال هذه المناقلة مسبقاً');
        }

        // لا تكمل المناقلات التي هي من نوع نقل ملكية أو إحالة خارجية
        if ($transfer->is_ownership_transfer || $transfer->is_referral) {
            return redirect()->route('transfers.show', $transfer)
                ->with('error', 'لا يمكن إكمال هذه المناقلة لأنها ' . ($transfer->is_ownership_transfer ? 'نقل ملكية' : 'إحالة خارجية'));
        }
        
        // التحقق من البيانات
        $validatedData = $request->validate([
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
            'attachment' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        // تحديث سجل المناقلة
        $transfer->return_date = $validatedData['return_date'];
        $transfer->notes = $validatedData['notes'] ? $transfer->notes . "\n" . $validatedData['notes'] : $transfer->notes;
        $transfer->completed_by = $user->id;
        $transfer->save();
        
        // التعامل مع المرفقات
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('transfer_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleTransfer',
                'attachable_id' => $transfer->id,
                'type' => 'return_document',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        return redirect()->route('transfers.show', $transfer)
            ->with('success', 'تم إكمال المناقلة بنجاح');
    }
    
    // دالة لنقل ملكية عجلة إلى مديرية أخرى
    public function transferOwnership(Request $request, Vehicle $vehicle)
    {
        // التحقق من أن المستخدم يمكنه نقل الملكية
        $user = Auth::user();
        
        if (!$user->hasRole(['admin', 'verifier'])) {
            abort(403, 'غير مصرح لك بنقل ملكية العجلة');
        }

        // التحقق مما إذا كانت العجلة محالة لجهة خارجية
        if ($vehicle->is_externally_referred) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'لا يمكن نقل ملكية هذه العجلة لأنها محالة لجهة خارجية: ' . $vehicle->external_entity);
        }
        
        // التحقق من البيانات
        $validatedData = $request->validate([
            'directorate_id' => 'required|exists:directorates,id',
            'reason' => 'required|string',
            'attachment' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        // التحقق من أن الوجهة مختلفة عن المديرية الحالية
        if ($vehicle->directorate_id == $validatedData['directorate_id']) {
            return redirect()->back()->with('error', 'المديرية المحددة هي نفسها المديرية الحالية للعجلة');
        }
        
        // تخزين المديرية القديمة للسجل
        $oldDirectorate = $vehicle->directorate;
        
        // تحديث مديرية العجلة
        $vehicle->directorate_id = $validatedData['directorate_id'];
        $vehicle->save();
        
        // إنشاء سجل مناقلة خاص لنقل الملكية
        $transfer = VehicleTransfer::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'recipient_name' => 'نقل ملكية',
            'recipient_id_number' => null, // لا حاجة لرقم هوية في نقل الملكية
            'recipient_entity' => Directorate::find($validatedData['directorate_id'])->name,
            'assigned_to' => null,
            'receive_date' => now(),
            'return_date' => null, // لن تعود أبدًا لأنها نقل ملكية
            'is_external' => false,
            'destination_directorate_id' => $validatedData['directorate_id'],
            'notes' => 'تم نقل ملكية العجلة من ' . $oldDirectorate->name . ' إلى ' . 
                      Directorate::find($validatedData['directorate_id'])->name . "\n" . 
                      'السبب: ' . $validatedData['reason'],
            'is_ownership_transfer' => true, // حقل جديد للتمييز عن المناقلات العادية
            'is_referral' => false,
        ]);
        
        // التعامل مع المرفقات
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('transfer_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleTransfer',
                'attachable_id' => $transfer->id,
                'type' => 'ownership_transfer_document',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // إرسال إشعارات حول نقل الملكية
        $this->notifyTransfer($transfer);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم نقل ملكية العجلة بنجاح إلى ' . Directorate::find($validatedData['directorate_id'])->name);
    }
    
    // دالة لإحالة العجلة خارجيًا
    public function externalReferral(Request $request, Vehicle $vehicle)
    {
        // التحقق من أن المستخدم يمكنه عمل إحالة خارجية
        $user = Auth::user();
        
        if (!$user->hasRole(['admin', 'verifier'])) {
            abort(403, 'غير مصرح لك بإحالة العجلة لجهة خارجية');
        }

        // التحقق مما إذا كانت العجلة محالة لجهة خارجية مسبقًا
        if ($vehicle->is_externally_referred) {
            return redirect()->route('vehicles.show', $vehicle)
                ->with('error', 'هذه العجلة محالة بالفعل لجهة خارجية: ' . $vehicle->external_entity);
        }
        
        // التحقق من البيانات
        $validatedData = $request->validate([
            'external_entity' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_id_number' => 'required|string|max:255',
            'recipient_phone' => 'nullable|string|max:255',
            'receive_date' => 'required|date',
            'reason' => 'required|string',
            'attachment' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        // إنشاء سجل مناقلة خاص للإحالة الخارجية
        $transfer = VehicleTransfer::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'recipient_name' => $validatedData['recipient_name'],
            'recipient_id_number' => $validatedData['recipient_id_number'],
            'recipient_phone' => $validatedData['recipient_phone'],
            'recipient_entity' => $validatedData['external_entity'],
            'assigned_to' => null,
            'receive_date' => $validatedData['receive_date'],
            'return_date' => null, // لن تعود من الإحالة الخارجية
            'is_external' => true,
            'destination_directorate_id' => null, // لا توجد مديرية داخلية
            'notes' => 'تم إحالة العجلة لجهة خارجية: ' . $validatedData['external_entity'] . "\n" . 
                      'السبب: ' . $validatedData['reason'],
            'is_ownership_transfer' => false,
            'is_referral' => true, // تحديد كإحالة
        ]);
        
        // تحديث حالة العجلة كمحالة خارجيًا
        $vehicle->is_externally_referred = true;
        $vehicle->external_entity = $validatedData['external_entity'];
        $vehicle->save();
        
        // التعامل مع المرفقات
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('transfer_documents', $filename, 'public');
            
            Attachment::create([
                'attachable_type' => 'App\Models\VehicleTransfer',
                'attachable_id' => $transfer->id,
                'type' => 'external_referral_document',
                'file_name' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'user_id' => $user->id
            ]);
        }
        
        // إرسال إشعارات حول الإحالة الخارجية
        $this->notifyTransfer($transfer);
        
        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'تم إحالة العجلة بنجاح إلى ' . $validatedData['external_entity']);
    }
    
    // دالة مساعدة لإرسال الإشعارات
    private function notifyTransfer($transfer)
    {
        // العثور على المستخدمين الذين يجب إخطارهم
        $usersToNotify = [];
        
        // إضافة المشرفين والمدققين
        $adminUsers = User::role(['admin', 'verifier'])->get();
        foreach ($adminUsers as $adminUser) {
            $usersToNotify[] = $adminUser;
        }
        
        // إضافة مستخدمي قسم العجلات
        $vehiclesDeptUsers = User::role('vehicles_dept')->get();
        foreach ($vehiclesDeptUsers as $vdUser) {
            $usersToNotify[] = $vdUser;
        }
        
        // إضافة المستلمين إذا كانت مناقلة داخلية
        if ($transfer->destination_directorate_id) {
            $recipientUsers = User::role('recipient')
                ->where('directorate_id', $transfer->destination_directorate_id)
                ->get();
            foreach ($recipientUsers as $recipientUser) {
                $usersToNotify[] = $recipientUser;
            }
        }
        
        // إرسال الإشعارات
        foreach ($usersToNotify as $user) {
            $user->notify(new VehicleTransferred($transfer, Auth::user()));
        }
    }
}