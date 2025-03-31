@extends('layouts.app')

@section('title', 'المناقلات')

@section('content')
<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">فلترة البيانات</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('transfers.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="vehicle_type" class="form-label">نوع العجلة</label>
                <select name="vehicle_type" id="vehicle_type" class="form-select">
                    <option value="">الكل</option>
                    <option value="confiscated" {{ request('vehicle_type') == 'confiscated' ? 'selected' : '' }}>مصادرة</option>
                    <option value="government" {{ request('vehicle_type') == 'government' ? 'selected' : '' }}>حكومية</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="transfer_type" class="form-label">نوع المناقلة</label>
                <select name="transfer_type" id="transfer_type" class="form-select">
                    <option value="">الكل</option>
                    <option value="regular" {{ request('transfer_type') == 'regular' ? 'selected' : '' }}>مناقلة اعتيادية</option>
                    <option value="ownership" {{ request('transfer_type') == 'ownership' ? 'selected' : '' }}>نقل ملكية</option>
                    <option value="referral" {{ request('transfer_type') == 'referral' ? 'selected' : '' }}>إحالة خارجية</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">الحالة</label>
                <select name="status" id="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>جارية</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                </select>
            </div>
            
            @if(auth()->user()->hasRole(['admin', 'verifier', 'vehicles_dept']))
            <div class="col-md-3">
                <label for="directorate_id" class="form-label">المديرية المستلمة</label>
                <select name="directorate_id" id="directorate_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($directorates as $directorate)
                    <option value="{{ $directorate->id }}" {{ request('directorate_id') == $directorate->id ? 'selected' : '' }}>
                        {{ $directorate->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div class="col-md-3">
                <label for="recipient_id_number" class="form-label">رقم هوية المستلم</label>
                <input type="text" name="recipient_id_number" id="recipient_id_number" class="form-control" 
                       value="{{ request('recipient_id_number') }}" placeholder="رقم الهوية">
            </div>
            
            <div class="col-md-3">
                <label for="search" class="form-label">بحث</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="المستلم، العجلة...">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
                <a href="{{ route('transfers.index') }}" class="btn btn-secondary">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<!-- تحذير المستلمين المتعددين -->
@if(count($activeTransferCountsByRecipient) > 0)
<div class="card mb-4">
    <div class="card-header bg-warning">
        <h5 class="mb-0">تنبيه: أشخاص لديهم أكثر من عجلة مستلمة</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>رقم الهوية</th>
                        <th>عدد العجلات المستلمة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeTransferCountsByRecipient as $idNumber => $count)
                    <tr>
                        <td>{{ $idNumber }}</td>
                        <td><span class="badge {{ $count > 1 ? 'bg-danger' : 'bg-warning' }}">{{ $count }}</span></td>
                        <td>
                            <a href="{{ route('transfers.index', ['recipient_id_number' => $idNumber]) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-search"></i> عرض المناقلات
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

<!-- Transfers Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">قائمة المناقلات</h5>
        <span>العدد الكلي: {{ $transfers->total() }}</span>
    </div>
    <div class="card-body">
        @if($transfers->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العجلة</th>
                        <th>المستلم</th>
                        <th>الجهة المستلمة</th>
                        <th>نوع المناقلة</th>
                        <th>تاريخ الاستلام</th>
                        <th>تاريخ الإعادة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->id }}</td>
                        <td>
                            <a href="{{ route('vehicles.show', $transfer->vehicle) }}">
                                {{ $transfer->vehicle->vehicle_type }} - {{ $transfer->vehicle->vehicle_number ?: 'بلا رقم' }}
                            </a>
                        </td>
                        <td>
                            {{ $transfer->recipient_name }}
                            @if($transfer->recipient_id_number)
                            <div class="small text-muted">{{ $transfer->recipient_id_number }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $transfer->recipient_entity }}
                            @if($transfer->destinationDirectorate)
                            <div class="small text-muted">{{ $transfer->destinationDirectorate->name }}</div>
                            @endif
                        </td>
                        <td>
                            @if($transfer->is_ownership_transfer)
                            <span class="badge bg-dark">نقل ملكية</span>
                            @elseif($transfer->is_referral)
                            <span class="badge bg-info">إحالة خارجية</span>
                            @else
                            <span class="badge bg-primary">اعتيادية</span>
                            @endif
                        </td>
                        <td>{{ $transfer->receive_date->format('Y-m-d') }}</td>
                        <td>
                            @if($transfer->is_ownership_transfer || $transfer->is_referral)
                                <span class="text-muted">-</span>
                            @else
                                {{ $transfer->return_date ? $transfer->return_date->format('Y-m-d') : 'مستمرة' }}
                            @endif
                        </td>
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
        
        <div class="mt-4">
            {{ $transfers->withQueryString()->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <h4>لا توجد مناقلات</h4>
            <p class="text-muted">لم يتم العثور على مناقلات مطابقة لمعايير البحث</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection