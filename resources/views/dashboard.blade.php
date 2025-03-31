<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card border-primary h-100">
            <div class="card-body text-center">
                <h5 class="card-title">العجلات المصادرة</h5>
                <p class="card-text display-4">{{ $confiscatedCount }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card border-success h-100">
            <div class="card-body text-center">
                <h5 class="card-title">العجلات الحكومية</h5>
                <p class="card-text display-4">{{ $governmentCount }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card border-warning h-100">
            <div class="card-body text-center">
                <h5 class="card-title">طلبات التعديل</h5>
                <p class="card-text display-4">{{ $pendingRequests }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card border-info h-100">
            <div class="card-body text-center">
                <h5 class="card-title">المناقلات الجارية</h5>
                <p class="card-text display-4">{{ $pendingTransfers }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">إحصائيات العجلات حسب الحالة</h5>
            </div>
            <div class="card-body">
                @if(count($vehiclesByStatus) > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>الحالة</th>
                                <th>العدد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehiclesByStatus as $status => $count)
                            <tr>
                                <td>{{ $status }}</td>
                                <td>{{ $count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center">لا توجد بيانات</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">آخر العجلات المضافة</h5>
            </div>
            <div class="card-body">
                @if($recentVehicles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>الرقم</th>
                                <th>النوع</th>
                                <th>المديرية</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentVehicles as $vehicle)
                            <tr onclick="window.location='{{ route('vehicles.show', $vehicle) }}'">
                                <td>{{ $vehicle->id }}</td>
                                <td>{{ $vehicle->vehicle_type }}</td>
                                <td>{{ $vehicle->directorate->name }}</td>
                                <td>{{ $vehicle->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center">لا توجد عجلات حديثة</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($recentRequests->count() > 0 || $recentTransfers->count() > 0)
<div class="row mt-4">
    @if($recentRequests->count() > 0)
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">آخر طلبات التعديل</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>الحقل</th>
                                <th>العجلة</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRequests as $request)
                            <tr onclick="window.location='{{ route('edit-requests.show', $request) }}'">
                                <td>{{ $request->field_name }}</td>
                                <td>{{ $request->vehicle->vehicle_type }}</td>
                                <td>
                                    @if($request->status == 'pending')
                                    <span class="badge bg-warning">قيد الانتظار</span>
                                    @elseif($request->status == 'approved')
                                    <span class="badge bg-success">تمت الموافقة</span>
                                    @else
                                    <span class="badge bg-danger">مرفوض</span>
                                    @endif
                                </td>
                                <td>{{ $request->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if($recentTransfers->count() > 0)
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">آخر المناقلات</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>العجلة</th>
                                <th>المستلم</th>
                                <th>تاريخ الاستلام</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransfers as $transfer)
                            <tr onclick="window.location='{{ route('transfers.show', $transfer) }}'">
                                <td>{{ $transfer->vehicle->vehicle_type }}</td>
                                <td>{{ $transfer->recipient_name }}</td>
                                <td>{{ $transfer->receive_date->format('Y-m-d') }}</td>
                                <td>
                                    @if($transfer->return_date)
                                    <span class="badge bg-success">مكتملة</span>
                                    @else
                                    <span class="badge bg-primary">جارية</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
@endsection