@extends('layouts.app')

@section('title', 'تفاصيل العجلة #' . $vehicle->id)

@section('actions')
<div class="btn-group" role="group">
    @can('update', $vehicle)
    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-warning">
        <i class="bi bi-pencil"></i> تعديل
    </a>
    @endcan
    
    @can('create edit requests')
    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editRequestModal">
        <i class="bi bi-file-earmark-text"></i> طلب تعديل
    </button>
    @endcan
    
    @if(!$vehicle->is_externally_referred)
        @if($vehicle->type == 'confiscated' && 
            ($vehicle->final_degree_status == 'مكتسبة' || $vehicle->authentication_status == 'تمت المصادقة عليها') ||
            $vehicle->type == 'government')
            @can('create transfers')
            <a href="{{ route('transfers.create', $vehicle) }}" class="btn btn-info">
                <i class="bi bi-arrow-left-right"></i> مناقلة جديدة
            </a>
            @endcan
        @endif
        
        @if(auth()->user()->hasRole(['admin', 'verifier']))
        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#transferOwnershipModal">
            <i class="bi bi-shuffle"></i> نقل ملكية
        </button>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#externalReferralModal">
            <i class="bi bi-box-arrow-up-right"></i> إحالة خارجية
        </button>
        @endif
    @else
        <button type="button" class="btn btn-danger" disabled>
            <i class="bi bi-exclamation-triangle"></i> محالة إلى {{ $vehicle->external_entity }}
        </button>
    @endif
    
    <a href="{{ route('vehicles.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-right"></i> عودة للقائمة
    </a>
</div>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات أساسية</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">نوع السجل</th>
                            <td>
                                @if($vehicle->type == 'confiscated')
                                <span class="badge bg-danger">مصادرة</span>
                                @else
                                <span class="badge bg-success">حكومية</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>نوع العجلة</th>
                            <td>{{ $vehicle->vehicle_type }}</td>
                        </tr>
                        <tr>
                            <th>اسم العجلة</th>
                            <td>{{ $vehicle->vehicle_name ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>الموديل</th>
                            <td>{{ $vehicle->model ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>رقم العجلة</th>
                            <td>{{ $vehicle->vehicle_number ?: 'بلا' }}</td>
                        </tr>
                        <tr>
                            <th>المحافظة</th>
                            <td>{{ $vehicle->province ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>اللون</th>
                            <td>{{ $vehicle->color ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>رقم الشاصي</th>
                            <td>{{ $vehicle->chassis_number ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>حالة العجلة</th>
                            <td>{{ $vehicle->vehicle_condition }}</td>
                        </tr>
                        <tr>
                            <th>المديرية</th>
                            <td>{{ $vehicle->directorate->name }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ الإضافة</th>
                            <td>{{ $vehicle->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>المضيف</th>
                            <td>{{ $vehicle->user->name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">الملحقات والعوارض</h5>
            </div>
            <div class="card-body">
                <h6>الملحقات:</h6>
                @if(!empty($vehicle->accessories))
                <ul>
                    @foreach($vehicle->accessories as $accessory)
                    <li>{{ $accessory }}</li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted">لا توجد ملحقات مسجلة</p>
                @endif
                
                <h6 class="mt-3">العوارض:</h6>
                @if(!empty($vehicle->defects))
                <ul>
                    @foreach($vehicle->defects as $defect)
                    <li>{{ $defect }}</li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted">لا توجد عوارض مسجلة</p>
                @endif
                
                <h6 class="mt-3">النواقص:</h6>
                @if($vehicle->missing_parts)
                <p>{{ $vehicle->missing_parts }}</p>
                @else
                <p class="text-muted">لا توجد نواقص مسجلة</p>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">الصور والمرفقات</h5>
            </div>
            <div class="card-body">
                <h6>صور العجلة:</h6>
                <div class="row">
                    @forelse($vehicle->attachments->where('type', 'vehicle_image') as $attachment)
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('attachments.show', $attachment) }}" target="_blank">
                            <img src="{{ route('attachments.show', $attachment) }}" 
                                class="img-thumbnail" alt="صورة العجلة">
                        </a>
                    </div>
                    @empty
                    <p class="text-muted">لا توجد صور مرفقة</p>
                    @endforelse
                </div>
                
                <h6 class="mt-3">المستندات المرفقة:</h6>
                <div class="list-group">
                    @forelse($vehicle->attachments->where('type', 'vehicle_document') as $attachment)
                    <a href="{{ route('attachments.download', $attachment) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark"></i> {{ $attachment->file_name }}
                        <span class="badge bg-secondary float-end">
                            {{ number_format($attachment->file_size / 1024, 2) }} KB
                        </span>
                    </a>
                    @empty
                    <p class="text-muted">لا توجد مستندات مرفقة</p>
                    @endforelse
                </div>
            </div>
        </div>
        
    </div>
</div>

@if($vehicle->type == 'confiscated')
<!-- Confiscated Vehicle Specific Information -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات المصادرة</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">اسم المتهم</th>
                            <td>{{ $vehicle->defendant_name }}</td>
                        </tr>
                        <tr>
                            <th>المادة القانونية</th>
                            <td>{{ $vehicle->legal_article }}</td>
                        </tr>
                        <tr>
                            <th>حالة العجلة</th>
                            <td>
                                @if($vehicle->seizure_status == 'محجوزة')
                                <span class="badge bg-warning">محجوزة</span>
                                @elseif($vehicle->seizure_status == 'مفرج عنها')
                                <span class="badge bg-success">مفرج عنها</span>
                                @elseif($vehicle->seizure_status == 'مصادرة')
                                <span class="badge bg-danger">مصادرة</span>
                                @else
                                <span class="badge bg-secondary">غير محدد</span>
                                @endif
                            </td>
                        </tr>
                        
                        @if($vehicle->seizure_status == 'محجوزة')
                        <tr>
                            <th>عدد كتاب الحجز</th>
                            <td>{{ $vehicle->seizure_letter_number ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ كتاب الحجز</th>
                            <td>{{ $vehicle->seizure_letter_date ? $vehicle->seizure_letter_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                        @elseif($vehicle->seizure_status == 'مفرج عنها')
                        <tr>
                            <th>عدد قرار الافراج</th>
                            <td>{{ $vehicle->release_decision_number ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ قرار الافراج</th>
                            <td>{{ $vehicle->release_decision_date ? $vehicle->release_decision_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                        @elseif($vehicle->seizure_status == 'مصادرة')
                        <tr>
                            <th>عدد كتاب المصادرة</th>
                            <td>{{ $vehicle->confiscation_letter_number ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ كتاب المصادرة</th>
                            <td>{{ $vehicle->confiscation_letter_date ? $vehicle->confiscation_letter_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                
                @can('update vehicle status')
                <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal" data-bs-target="#seizureStatusModal">
                    <i class="bi bi-pencil-square"></i> تحديث حالة المصادرة
                </button>
                @endcan
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">اكتساب الدرجة القطعية</h5>
                <span class="badge {{ $vehicle->final_degree_status == 'مكتسبة' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $vehicle->final_degree_status }}
                </span>
            </div>
            <div class="card-body">
                @if($vehicle->final_degree_status == 'مكتسبة')
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">عدد القرار</th>
                            <td>{{ $vehicle->decision_number }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ القرار</th>
                            <td>{{ $vehicle->decision_date ? $vehicle->decision_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p class="text-muted">العجلة لم تكتسب الدرجة القطعية بعد</p>
                @endif
                
                @if($vehicle->seizure_status == 'مصادرة')
                @can('update vehicle status')
                <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal" data-bs-target="#finalDegreeStatusModal">
                    <i class="bi bi-pencil-square"></i> تحديث حالة اكتساب الدرجة القطعية
                </button>
                @endcan
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">التثمين</h5>
                <span class="badge {{ $vehicle->valuation_status == 'مثمنة' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $vehicle->valuation_status }}
                </span>
            </div>
            <div class="card-body">
                @if($vehicle->valuation_status == 'مثمنة')
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">مبلغ التثمين</th>
                            <td>@money($vehicle->valuation_amount)</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p class="text-muted">لم يتم تثمين العجلة بعد</p>
                @endif
                
                @if($vehicle->final_degree_status == 'مكتسبة')
                @can('update vehicle status')
                <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal" data-bs-target="#valuationStatusModal">
                    <i class="bi bi-pencil-square"></i> تحديث حالة التثمين
                </button>
                @endcan
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">المصادقة</h5>
                <span class="badge {{ $vehicle->authentication_status == 'تمت المصادقة عليها' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $vehicle->authentication_status }}
                </span>
            </div>
            <div class="card-body">
                @if($vehicle->authentication_status == 'تمت المصادقة عليها')
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">عدد المصادقة</th>
                            <td>{{ $vehicle->authentication_number }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ المصادقة</th>
                            <td>{{ $vehicle->authentication_date ? $vehicle->authentication_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p class="text-muted">لم تتم المصادقة على العجلة بعد</p>
                @endif
                
                @if($vehicle->valuation_status == 'مثمنة')
                @can('update vehicle status')
                <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal" data-bs-target="#authenticationStatusModal">
                    <i class="bi bi-pencil-square"></i> تحديث حالة المصادقة
                </button>
                @endcan
                @endif
            </div>
        </div>
        
        <!-- إضافة قسم الإهداء -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">الإهداء</h5>
                <span class="badge {{ $vehicle->donation_status == 'مهداة' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $vehicle->donation_status }}
                </span>
            </div>
            <div class="card-body">
                @if($vehicle->donation_status == 'مهداة')
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">رقم كتاب الإهداء</th>
                            <td>{{ $vehicle->donation_letter_number }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ كتاب الإهداء</th>
                            <td>{{ $vehicle->donation_letter_date ? $vehicle->donation_letter_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>الجهة المهداة لها</th>
                            <td>{{ $vehicle->donation_entity }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p class="text-muted">لم يتم إهداء العجلة بعد</p>
                @endif
                
                @if($vehicle->authentication_status == 'تمت المصادقة عليها')
                @can('update vehicle status')
                <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal" data-bs-target="#donationStatusModal">
                    <i class="bi bi-pencil-square"></i> تحديث حالة الإهداء
                </button>
                @endcan
                @endif
            </div>
        </div>
        
        <!-- إضافة قسم الترقيم الحكومي -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">الترقيم الحكومي</h5>
                <span class="badge {{ $vehicle->government_registration_status == 'مرقمة' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $vehicle->government_registration_status }}
                </span>
            </div>
            <div class="card-body">
                @if($vehicle->government_registration_status == 'مرقمة')
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">رقم كتاب الترقيم</th>
                            <td>{{ $vehicle->registration_letter_number }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ كتاب الترقيم</th>
                            <td>{{ $vehicle->registration_letter_date ? $vehicle->registration_letter_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>رقم اللوحة الحكومية</th>
                            <td>{{ $vehicle->government_registration_number }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p class="text-muted">لم يتم ترقيم العجلة بعد</p>
                @endif
                
                @if($vehicle->donation_status == 'مهداة')
                    @can('update vehicle status')
                        @if(auth()->user()->hasRole(['admin', 'verifier', 'vehicles_dept']))
                        <button type="button" class="btn btn-primary mt-3 w-100" data-bs-toggle="modal" data-bs-target="#registrationStatusModal">
                            <i class="bi bi-pencil-square"></i> تحديث حالة الترقيم الحكومي
                        </button>
                        @endif
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>
@elseif($vehicle->type == 'government')
<!-- Government Vehicle Specific Information -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات العجلة الحكومية</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="40%">وردت من</th>
                            <td>{{ $vehicle->source ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>عدد الوارد</th>
                            <td>{{ $vehicle->import_letter_number ?: 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ الوارد</th>
                            <td>{{ $vehicle->import_letter_date ? $vehicle->import_letter_date->format('Y-m-d') : 'غير محدد' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Vehicle Transfers Section -->
@if($vehicle->transfers->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">سجل المناقلات</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المستلم</th>
                        <th>الجهة المستلمة</th>
                        <th>تاريخ الاستلام</th>
                        <th>تاريخ الإعادة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicle->transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->id }}</td>
                        <td>
                            {{ $transfer->recipient_name }}
                            @if($transfer->recipient_id_number)
                            <br><small>{{ $transfer->recipient_id_number }}</small>
                            @endif
                        </td>
                        <td>{{ $transfer->recipient_entity }}</td>
                        <td>{{ $transfer->receive_date->format('Y-m-d') }}</td>
                        <td>{{ $transfer->return_date ? $transfer->return_date->format('Y-m-d') : 'مستمرة' }}</td>
                        <td>
                            @if($transfer->is_ownership_transfer)
                            <span class="badge bg-dark">نقل ملكية</span>
                            @elseif($transfer->is_referral)
                            <span class="badge bg-info">إحالة خارجية</span>
                            @elseif($transfer->return_date)
                            <span class="badge bg-success">مكتملة</span>
                            @else
                            <span class="badge bg-primary">جارية</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('transfers.show', $transfer) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> عرض
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Status History Section -->
@if($vehicle->statuses->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">سجل تغييرات الحالة</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>نوع التغيير</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>المستخدم</th>
                        <th>الملاحظات</th>
                        <th>المرفقات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicle->statuses->sortByDesc('created_at') as $status)
                    <tr>
                        <td>{{ $status->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            @if($status->status_type == 'seizure_status')
                            <span class="badge bg-warning">حالة المصادرة</span>
                            @elseif($status->status_type == 'final_degree_status')
                            <span class="badge bg-primary">الدرجة القطعية</span>
                            @elseif($status->status_type == 'valuation_status')
                            <span class="badge bg-dark">التثمين</span>
                            @elseif($status->status_type == 'authentication_status')
                            <span class="badge bg-info">المصادقة</span>
                            @elseif($status->status_type == 'donation_status')
                            <span class="badge bg-success">الإهداء</span>
                            @elseif($status->status_type == 'government_registration_status')
                            <span class="badge bg-secondary">الترقيم الحكومي</span>
                            @endif
                        </td>
                        <td>{{ $status->old_status ?: 'غير محدد' }}</td>
                        <td>{{ $status->new_status }}</td>
                        <td>{{ $status->user->name }}</td>
                        <td>{{ $status->notes ?: 'لا توجد ملاحظات' }}</td>
                        <td>
                            @php
                                $statusAttachments = $status->attachments;
                            @endphp
                            @if($statusAttachments->count() > 0)
                                @foreach($statusAttachments as $attachment)
                                <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary mb-1" title="{{ $attachment->file_name }}">
                                    <i class="bi bi-file-earmark"></i>
                                    @if($attachment->type == 'seizure_letter')
                                        كتاب الحجز
                                    @elseif($attachment->type == 'release_decision')
                                        قرار الإفراج
                                    @elseif($attachment->type == 'confiscation_letter')
                                        كتاب المصادرة
                                    @elseif($attachment->type == 'final_degree_decision')
                                        قرار اكتساب الدرجة
                                    @elseif($attachment->type == 'valuation_document')
                                        وثيقة التثمين
                                    @elseif($attachment->type == 'authentication_letter')
                                        كتاب المصادقة
                                    @elseif($attachment->type == 'donation_letter')
                                        كتاب الإهداء
                                    @elseif($attachment->type == 'registration_document')
                                        وثيقة الترقيم
                                    @else
                                        مرفق
                                    @endif
                                </a>
                                @endforeach
                            @else
                                <span class="text-muted">لا توجد مرفقات</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Edit Request History Section -->
@if($vehicle->editRequests->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">سجل طلبات التعديل</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الحقل</th>
                        <th>القيمة القديمة</th>
                        <th>القيمة الجديدة</th>
                        <th>المستخدم</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicle->editRequests->sortByDesc('created_at') as $request)
                    <tr>
                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $request->field_name }}</td>
                        <td>{{ Str::limit($request->old_value, 30) ?: 'غير محدد' }}</td>
                        <td>{{ Str::limit($request->new_value, 30) }}</td>
                        <td>{{ $request->user->name }}</td>
                        <td>
                            @if($request->status == 'pending')
                            <span class="badge bg-warning">قيد الانتظار</span>
                            @elseif($request->status == 'approved')
                            <span class="badge bg-success">تمت الموافقة</span>
                            @else
                            <span class="badge bg-danger">مرفوض</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('edit-requests.show', $request) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> عرض
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Notes Section -->
@if($vehicle->notes)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">ملاحظات</h5>
    </div>
    <div class="card-body">
        <p>{{ $vehicle->notes }}</p>
    </div>
</div>
@endif

<!-- Modals -->
@can('update vehicle status')
@if($vehicle->type == 'confiscated')
<!-- Seizure Status Modal -->
<div class="modal fade" id="seizureStatusModal" tabindex="-1" aria-labelledby="seizureStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('vehicles.update-seizure-status', $vehicle) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="seizureStatusModalLabel">تحديث حالة المصادرة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="seizure_status" class="col-sm-3 col-form-label required-field">الحالة</label>
                        <div class="col-sm-9">
                            <select name="seizure_status" id="seizure_status" class="form-select" required>
                                <option value="محجوزة" {{ $vehicle->seizure_status == 'محجوزة' ? 'selected' : '' }}>محجوزة</option>
                                <option value="مفرج عنها" {{ $vehicle->seizure_status == 'مفرج عنها' ? 'selected' : '' }}>مفرج عنها</option>
                                <option value="مصادرة" {{ $vehicle->seizure_status == 'مصادرة' ? 'selected' : '' }}>مصادرة</option>
                           </select>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="letter_number" class="col-sm-3 col-form-label required-field">رقم الكتاب/القرار</label>
                       <div class="col-sm-9">
                           <input type="text" class="form-control" id="letter_number" name="letter_number" required>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="letter_date" class="col-sm-3 col-form-label required-field">تاريخ الكتاب/القرار</label>
                       <div class="col-sm-9">
                           <input type="date" class="form-control" id="letter_date" name="letter_date" required>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="attachment" class="col-sm-3 col-form-label required-field">صورة الكتاب/القرار</label>
                       <div class="col-sm-9">
                           <input type="file" class="form-control" id="attachment" name="attachment" required>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="notes" class="col-sm-3 col-form-label">ملاحظات</label>
                       <div class="col-sm-9">
                           <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                   <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
               </div>
           </form>
       </div>
   </div>
</div>

<!-- Final Degree Status Modal -->
<div class="modal fade" id="finalDegreeStatusModal" tabindex="-1" aria-labelledby="finalDegreeStatusModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <form action="{{ route('vehicles.update-final-degree-status', $vehicle) }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-header">
                   <h5 class="modal-title" id="finalDegreeStatusModalLabel">تحديث حالة اكتساب الدرجة القطعية</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div class="row mb-3">
                       <label for="final_degree_status" class="col-sm-3 col-form-label required-field">الحالة</label>
                       <div class="col-sm-9">
                           <select name="final_degree_status" id="final_degree_status" class="form-select" required>
                               <option value="غير مكتسبة" {{ $vehicle->final_degree_status == 'غير مكتسبة' ? 'selected' : '' }}>غير مكتسبة</option>
                               <option value="مكتسبة" {{ $vehicle->final_degree_status == 'مكتسبة' ? 'selected' : '' }}>مكتسبة</option>
                           </select>
                       </div>
                   </div>
                   <div class="final-degree-fields" style="display: none;">
                       <div class="row mb-3">
                           <label for="decision_number" class="col-sm-3 col-form-label required-field">رقم القرار</label>
                           <div class="col-sm-9">
                               <input type="text" class="form-control" id="decision_number" name="decision_number">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="decision_date" class="col-sm-3 col-form-label required-field">تاريخ القرار</label>
                           <div class="col-sm-9">
                               <input type="date" class="form-control" id="decision_date" name="decision_date">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="attachment" class="col-sm-3 col-form-label required-field">صورة القرار</label>
                           <div class="col-sm-9">
                               <input type="file" class="form-control" id="attachment" name="attachment">
                           </div>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="notes" class="col-sm-3 col-form-label">ملاحظات</label>
                       <div class="col-sm-9">
                           <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                   <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
               </div>
           </form>
       </div>
   </div>
</div>

<!-- Valuation Status Modal -->
<div class="modal fade" id="valuationStatusModal" tabindex="-1" aria-labelledby="valuationStatusModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <form action="{{ route('vehicles.update-valuation-status', $vehicle) }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-header">
                   <h5 class="modal-title" id="valuationStatusModalLabel">تحديث حالة التثمين</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div class="row mb-3">
                       <label for="valuation_status" class="col-sm-3 col-form-label required-field">الحالة</label>
                       <div class="col-sm-9">
                           <select name="valuation_status" id="valuation_status" class="form-select" required>
                               <option value="غير مثمنة" {{ $vehicle->valuation_status == 'غير مثمنة' ? 'selected' : '' }}>غير مثمنة</option>
                               <option value="مثمنة" {{ $vehicle->valuation_status == 'مثمنة' ? 'selected' : '' }}>مثمنة</option>
                           </select>
                       </div>
                   </div>
                   <div class="valuation-fields" style="display: none;">
                       <div class="row mb-3">
                           <label for="valuation_amount" class="col-sm-3 col-form-label required-field">مبلغ التثمين</label>
                           <div class="col-sm-9">
                               <div class="input-group">
                                   <input type="number" class="form-control" id="valuation_amount" name="valuation_amount" step="0.01">
                                   <span class="input-group-text">د.ع</span>
                               </div>
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="attachment" class="col-sm-3 col-form-label required-field">تقرير التثمين</label>
                           <div class="col-sm-9">
                               <input type="file" class="form-control" id="attachment" name="attachment">
                           </div>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="notes" class="col-sm-3 col-form-label">ملاحظات</label>
                       <div class="col-sm-9">
                           <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                   <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
               </div>
           </form>
       </div>
   </div>
</div>

<!-- Authentication Status Modal -->
<div class="modal fade" id="authenticationStatusModal" tabindex="-1" aria-labelledby="authenticationStatusModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <form action="{{ route('vehicles.update-authentication-status', $vehicle) }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-header">
                   <h5 class="modal-title" id="authenticationStatusModalLabel">تحديث حالة المصادقة</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div class="row mb-3">
                       <label for="authentication_status" class="col-sm-3 col-form-label required-field">الحالة</label>
                       <div class="col-sm-9">
                           <select name="authentication_status" id="authentication_status" class="form-select" required>
                               <option value="غير مصادق عليها" {{ $vehicle->authentication_status == 'غير مصادق عليها' ? 'selected' : '' }}>غير مصادق عليها</option>
                               <option value="تمت المصادقة عليها" {{ $vehicle->authentication_status == 'تمت المصادقة عليها' ? 'selected' : '' }}>تمت المصادقة عليها</option>
                           </select>
                       </div>
                   </div>
                   <div class="authentication-fields" style="display: none;">
                       <div class="row mb-3">
                           <label for="authentication_number" class="col-sm-3 col-form-label required-field">رقم المصادقة</label>
                           <div class="col-sm-9">
                               <input type="text" class="form-control" id="authentication_number" name="authentication_number">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="authentication_date" class="col-sm-3 col-form-label required-field">تاريخ المصادقة</label>
                           <div class="col-sm-9">
                               <input type="date" class="form-control" id="authentication_date" name="authentication_date">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="attachment" class="col-sm-3 col-form-label required-field">صورة كتاب المصادقة</label>
                           <div class="col-sm-9">
                               <input type="file" class="form-control" id="attachment" name="attachment">
                           </div>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="notes" class="col-sm-3 col-form-label">ملاحظات</label>
                       <div class="col-sm-9">
                           <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                   <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
               </div>
           </form>
       </div>
   </div>
</div>

<!-- Donation Status Modal -->
<div class="modal fade" id="donationStatusModal" tabindex="-1" aria-labelledby="donationStatusModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <form action="{{ route('vehicles.update-donation-status', $vehicle) }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-header">
                   <h5 class="modal-title" id="donationStatusModalLabel">تحديث حالة الإهداء</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div class="row mb-3">
                       <label for="donation_status" class="col-sm-3 col-form-label required-field">الحالة</label>
                       <div class="col-sm-9">
                           <select name="donation_status" id="donation_status" class="form-select" required>
                               <option value="غير مهداة" {{ $vehicle->donation_status == 'غير مهداة' ? 'selected' : '' }}>غير مهداة</option>
                               <option value="مهداة" {{ $vehicle->donation_status == 'مهداة' ? 'selected' : '' }}>مهداة</option>
                           </select>
                       </div>
                   </div>
                   <div class="donation-fields" style="display: none;">
                       <div class="row mb-3">
                           <label for="donation_letter_number" class="col-sm-3 col-form-label required-field">رقم كتاب الإهداء</label>
                           <div class="col-sm-9">
                               <input type="text" class="form-control" id="donation_letter_number" name="donation_letter_number">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="donation_letter_date" class="col-sm-3 col-form-label required-field">تاريخ كتاب الإهداء</label>
                           <div class="col-sm-9">
                               <input type="date" class="form-control" id="donation_letter_date" name="donation_letter_date">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="donation_entity" class="col-sm-3 col-form-label required-field">الجهة المهداة لها</label>
                           <div class="col-sm-9">
                               <input type="text" class="form-control" id="donation_entity" name="donation_entity">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="attachment" class="col-sm-3 col-form-label required-field">صورة كتاب الإهداء</label>
                           <div class="col-sm-9">
                               <input type="file" class="form-control" id="attachment" name="attachment">
                           </div>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="notes" class="col-sm-3 col-form-label">ملاحظات</label>
                       <div class="col-sm-9">
                           <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                   <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
               </div>
           </form>
       </div>
   </div>
</div>

<!-- Registration Status Modal -->
<div class="modal fade" id="registrationStatusModal" tabindex="-1" aria-labelledby="registrationStatusModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <form action="{{ route('vehicles.update-registration-status', $vehicle) }}" method="POST" enctype="multipart/form-data">
               @csrf
               <div class="modal-header">
                   <h5 class="modal-title" id="registrationStatusModalLabel">تحديث حالة الترقيم الحكومي</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <div class="row mb-3">
                       <label for="government_registration_status" class="col-sm-3 col-form-label required-field">الحالة</label>
                       <div class="col-sm-9">
                           <select name="government_registration_status" id="government_registration_status" class="form-select" required>
                               <option value="غير مرقمة" {{ $vehicle->government_registration_status == 'غير مرقمة' ? 'selected' : '' }}>غير مرقمة</option>
                               <option value="مرقمة" {{ $vehicle->government_registration_status == 'مرقمة' ? 'selected' : '' }}>مرقمة</option>
                           </select>
                       </div>
                   </div>
                   <div class="registration-fields" style="display: none;">
                       <div class="row mb-3">
                           <label for="registration_letter_number" class="col-sm-3 col-form-label required-field">رقم كتاب الترقيم</label>
                           <div class="col-sm-9">
                               <input type="text" class="form-control" id="registration_letter_number" name="registration_letter_number">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="registration_letter_date" class="col-sm-3 col-form-label required-field">تاريخ كتاب الترقيم</label>
                           <div class="col-sm-9">
                               <input type="date" class="form-control" id="registration_letter_date" name="registration_letter_date">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="government_registration_number" class="col-sm-3 col-form-label required-field">رقم اللوحة الحكومية</label>
                           <div class="col-sm-9">
                               <input type="text" class="form-control" id="government_registration_number" name="government_registration_number">
                           </div>
                       </div>
                       <div class="row mb-3">
                           <label for="attachment" class="col-sm-3 col-form-label required-field">صورة كتاب الترقيم</label>
                           <div class="col-sm-9">
                               <input type="file" class="form-control" id="attachment" name="attachment">
                           </div>
                       </div>
                   </div>
                   <div class="row mb-3">
                       <label for="notes" class="col-sm-3 col-form-label">ملاحظات</label>
                       <div class="col-sm-9">
                           <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                       </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                   <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
               </div>
           </form>
       </div>
   </div>
</div>
@endif
@endcan
<!-- نافذة نقل الملكية -->
@if(auth()->user()->hasRole(['admin', 'verifier']))
<div class="modal fade" id="transferOwnershipModal" tabindex="-1" aria-labelledby="transferOwnershipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vehicles.transfer-ownership', $vehicle) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="transferOwnershipModalLabel">نقل ملكية العجلة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>تنبيه:</strong> سيتم نقل ملكية العجلة بشكل دائم إلى المديرية المحددة ولن يمكن العودة عن ذلك.
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="directorate_id" class="form-label required-field">المديرية المستلمة</label>
                        <select name="directorate_id" id="directorate_id" class="form-select" required>
                            <option value="">-- اختر المديرية --</option>
                            @foreach(\App\Models\Directorate::all() as $directorate)
                                @if($directorate->id != $vehicle->directorate_id)
                                <option value="{{ $directorate->id }}">{{ $directorate->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="reason" class="form-label required-field">سبب نقل الملكية</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="attachment" class="form-label required-field">مستند نقل الملكية</label>
                        <input type="file" name="attachment" id="attachment" class="form-control" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">مطلوب كتاب أو أمر نقل الملكية</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تأكيد نقل الملكية</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- نافذة الإحالة الخارجية -->
<div class="modal fade" id="externalReferralModal" tabindex="-1" aria-labelledby="externalReferralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vehicles.external-referral', $vehicle) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="externalReferralModalLabel">إحالة العجلة لجهة خارجية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>تنبيه:</strong> بعد الإحالة الخارجية، لن يمكن مناقلة العجلة أو نقل ملكيتها.
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="external_entity" class="form-label required-field">الجهة الخارجية</label>
                        <input type="text" name="external_entity" id="external_entity" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="recipient_name" class="form-label required-field">اسم المستلم</label>
                        <input type="text" name="recipient_name" id="recipient_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="recipient_id_number" class="form-label required-field">رقم هوية المستلم</label>
                        <input type="text" name="recipient_id_number" id="recipient_id_number" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="recipient_phone" class="form-label">رقم هاتف المستلم</label>
                        <input type="text" name="recipient_phone" id="recipient_phone" class="form-control">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="receive_date" class="form-label required-field">تاريخ الاستلام</label>
                        <input type="date" name="receive_date" id="receive_date" class="form-control" 
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="reason" class="form-label required-field">سبب الإحالة</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="attachment" class="form-label required-field">مستند الإحالة</label>
                        <input type="file" name="attachment" id="attachment" class="form-control" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">مطلوب كتاب أو أمر الإحالة</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد الإحالة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Request Modal -->
@can('create edit requests')
<div class="modal fade" id="editRequestModal" tabindex="-1" aria-labelledby="editRequestModalLabel" aria-hidden="true">
   <div class="modal-dialog">
       <div class="modal-content">
           <div class="modal-header">
               <h5 class="modal-title" id="editRequestModalLabel">طلب تعديل</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body">
               <p>اختر الحقل الذي ترغب في طلب تعديله:</p>
               <div class="list-group">
                   <h6>معلومات أساسية</h6>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'vehicle_type']) }}" class="list-group-item list-group-item-action">نوع العجلة</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'vehicle_name']) }}" class="list-group-item list-group-item-action">اسم العجلة</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'model']) }}" class="list-group-item list-group-item-action">الموديل</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'chassis_number']) }}" class="list-group-item list-group-item-action">رقم الشاصي</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'vehicle_number']) }}" class="list-group-item list-group-item-action">رقم العجلة</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'province']) }}" class="list-group-item list-group-item-action">المحافظة</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'color']) }}" class="list-group-item list-group-item-action">اللون</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'vehicle_condition']) }}" class="list-group-item list-group-item-action">حالة العجلة</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'missing_parts']) }}" class="list-group-item list-group-item-action">النواقص</a>
                   
                   @if($vehicle->type == 'confiscated')
                   <h6 class="mt-3">معلومات العجلة المصادرة</h6>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'defendant_name']) }}" class="list-group-item list-group-item-action">اسم المتهم</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'legal_article']) }}" class="list-group-item list-group-item-action">المادة القانونية</a>
                   @elseif($vehicle->type == 'government')
                   <h6 class="mt-3">معلومات العجلة الحكومية</h6>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'source']) }}" class="list-group-item list-group-item-action">وردت من</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'import_letter_number']) }}" class="list-group-item list-group-item-action">رقم كتاب الوارد</a>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'import_letter_date']) }}" class="list-group-item list-group-item-action">تاريخ كتاب الوارد</a>
                   @endif
                   
                   <h6 class="mt-3">معلومات أخرى</h6>
                   <a href="{{ route('edit-requests.create', [$vehicle, 'notes']) }}" class="list-group-item list-group-item-action">الملاحظات</a>
               </div>
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
           </div>
       </div>
   </div>
</div>
@endcan

@endsection

@push('scripts')
<script>
   document.addEventListener('DOMContentLoaded', function() {
       // تشخيص Bootstrap
       console.log('Bootstrap:', typeof bootstrap);
       console.log('Modal elements:', {
           seizure: document.getElementById('seizureStatusModal'),
           finalDegree: document.getElementById('finalDegreeStatusModal'),
           valuation: document.getElementById('valuationStatusModal'),
           authentication: document.getElementById('authenticationStatusModal'),
           donation: document.getElementById('donationStatusModal'),
           registration: document.getElementById('registrationStatusModal')
       });
       
       // For Final Degree Status
       const finalDegreeStatus = document.getElementById('final_degree_status');
       const finalDegreeFields = document.querySelector('.final-degree-fields');
       
       if (finalDegreeStatus) {
           finalDegreeStatus.addEventListener('change', function() {
               if (this.value === 'مكتسبة') {
                   finalDegreeFields.style.display = 'block';
                   document.getElementById('decision_number').setAttribute('required', 'required');
                   document.getElementById('decision_date').setAttribute('required', 'required');
                   document.querySelector('#finalDegreeStatusModal #attachment').setAttribute('required', 'required');
               } else {
                   finalDegreeFields.style.display = 'none';
                   document.getElementById('decision_number').removeAttribute('required');
                   document.getElementById('decision_date').removeAttribute('required');
                   document.querySelector('#finalDegreeStatusModal #attachment').removeAttribute('required');
               }
           });
           
           // Trigger on load
           if (finalDegreeStatus.value === 'مكتسبة') {
               finalDegreeFields.style.display = 'block';
               document.getElementById('decision_number').setAttribute('required', 'required');
               document.getElementById('decision_date').setAttribute('required', 'required');
               document.querySelector('#finalDegreeStatusModal #attachment').setAttribute('required', 'required');
           }
       }
       
       // For Valuation Status
       const valuationStatus = document.getElementById('valuation_status');
       const valuationFields = document.querySelector('.valuation-fields');
       
       if (valuationStatus) {
           valuationStatus.addEventListener('change', function() {
               if (this.value === 'مثمنة') {
                   valuationFields.style.display = 'block';
                   document.getElementById('valuation_amount').setAttribute('required', 'required');
                   document.querySelector('#valuationStatusModal #attachment').setAttribute('required', 'required');
               } else {
                   valuationFields.style.display = 'none';
                   document.getElementById('valuation_amount').removeAttribute('required');
                   document.querySelector('#valuationStatusModal #attachment').removeAttribute('required');
               }
           });
           
           // Trigger on load
           if (valuationStatus.value === 'مثمنة') {
               valuationFields.style.display = 'block';
               document.getElementById('valuation_amount').setAttribute('required', 'required');
               document.querySelector('#valuationStatusModal #attachment').setAttribute('required', 'required');
           }
       }
       
       // For Authentication Status
       const authenticationStatus = document.getElementById('authentication_status');
       const authenticationFields = document.querySelector('.authentication-fields');
       
       if (authenticationStatus) {
           authenticationStatus.addEventListener('change', function() {
               if (this.value === 'تمت المصادقة عليها') {
                   authenticationFields.style.display = 'block';
                   document.getElementById('authentication_number').setAttribute('required', 'required');
                   document.getElementById('authentication_date').setAttribute('required', 'required');
                   document.querySelector('#authenticationStatusModal #attachment').setAttribute('required', 'required');
               } else {
                   authenticationFields.style.display = 'none';
                   document.getElementById('authentication_number').removeAttribute('required');
                   document.getElementById('authentication_date').removeAttribute('required');
                   document.querySelector('#authenticationStatusModal #attachment').removeAttribute('required');
               }
           });
           
           // Trigger on load
           if (authenticationStatus.value === 'تمت المصادقة عليها') {
               authenticationFields.style.display = 'block';
               document.getElementById('authentication_number').setAttribute('required', 'required');
               document.getElementById('authentication_date').setAttribute('required', 'required');
               document.querySelector('#authenticationStatusModal #attachment').setAttribute('required', 'required');
           }
       }
       
       // For Donation Status
       const donationStatus = document.getElementById('donation_status');
       const donationFields = document.querySelector('.donation-fields');
       
       if (donationStatus) {
           donationStatus.addEventListener('change', function() {
               if (this.value === 'مهداة') {
                   donationFields.style.display = 'block';
                   document.getElementById('donation_letter_number').setAttribute('required', 'required');
                   document.getElementById('donation_letter_date').setAttribute('required', 'required');
                   document.getElementById('donation_entity').setAttribute('required', 'required');
                   document.querySelector('#donationStatusModal #attachment').setAttribute('required', 'required');
               } else {
                   donationFields.style.display = 'none';
                   document.getElementById('donation_letter_number').removeAttribute('required');
                   document.getElementById('donation_letter_date').removeAttribute('required');
                   document.getElementById('donation_entity').removeAttribute('required');
                   document.querySelector('#donationStatusModal #attachment').removeAttribute('required');
               }
           });
           // Trigger on load
           if (donationStatus.value === 'مهداة') {
               donationFields.style.display = 'block';
               document.getElementById('donation_letter_number').setAttribute('required', 'required');
               document.getElementById('donation_letter_date').setAttribute('required', 'required');
               document.getElementById('donation_entity').setAttribute('required', 'required');
               document.querySelector('#donationStatusModal #attachment').setAttribute('required', 'required');
           }
       }
       
       // For Registration Status
       const registrationStatus = document.getElementById('government_registration_status');
       const registrationFields = document.querySelector('.registration-fields');
       
       if (registrationStatus) {
           registrationStatus.addEventListener('change', function() {
               if (this.value === 'مرقمة') {
                   registrationFields.style.display = 'block';
                   document.getElementById('registration_letter_number').setAttribute('required', 'required');
                   document.getElementById('registration_letter_date').setAttribute('required', 'required');
                   document.getElementById('government_registration_number').setAttribute('required', 'required');
                   document.querySelector('#registrationStatusModal #attachment').setAttribute('required', 'required');
               } else {
                   registrationFields.style.display = 'none';
                   document.getElementById('registration_letter_number').removeAttribute('required');
                   document.getElementById('registration_letter_date').removeAttribute('required');
                   document.getElementById('government_registration_number').removeAttribute('required');
                   document.querySelector('#registrationStatusModal #attachment').removeAttribute('required');
               }
           });
           
           // Trigger on load
           if (registrationStatus.value === 'مرقمة') {
               registrationFields.style.display = 'block';
               document.getElementById('registration_letter_number').setAttribute('required', 'required');
               document.getElementById('registration_letter_date').setAttribute('required', 'required');
               document.getElementById('government_registration_number').setAttribute('required', 'required');
               document.querySelector('#registrationStatusModal #attachment').setAttribute('required', 'required');
           }
       }
       
       // تحقق من عمل الأزرار المشغلة للـ modals
       const modalButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
       if (modalButtons) {
           modalButtons.forEach(button => {
               console.log('Modal button:', button.dataset.bsTarget);
               button.addEventListener('click', function() {
                   console.log('Modal button clicked:', this.dataset.bsTarget);
                   try {
                       const modalId = this.dataset.bsTarget;
                       const modalElement = document.querySelector(modalId);
                       if (modalElement) {
                           const modal = new bootstrap.Modal(modalElement);
                           modal.show();
                       } else {
                           console.error('Modal element not found:', modalId);
                       }
                   } catch (e) {
                       console.error('Error showing modal:', e);
                   }
               });
           });
       }
   });
</script>
@endpush