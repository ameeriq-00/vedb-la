<!-- resources/views/vehicles/index.blade.php -->
@extends('layouts.app')

@section('title', 'العجلات')

@section('actions')
@if(auth()->user()->can('create vehicles') || auth()->user()->hasRole('vehicles_dept'))
<a href="{{ route('vehicles.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> إضافة عجلة جديدة
</a>
@endif
@endsection

@section('content')
<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">فلترة البيانات</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('vehicles.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="type" class="form-label">نوع العجلة</label>
                <select name="type" id="type" class="form-select">
                    <option value="">الكل</option>
                    <option value="confiscated" {{ request('type') == 'confiscated' ? 'selected' : '' }}>مصادرة</option>
                    <option value="government" {{ request('type') == 'government' ? 'selected' : '' }}>حكومية</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">الحالة</label>
                <select name="status" id="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="محجوزة" {{ request('status') == 'محجوزة' ? 'selected' : '' }}>محجوزة</option>
                    <option value="مصادرة" {{ request('status') == 'مصادرة' ? 'selected' : '' }}>مصادرة</option>
                    <option value="مفرج عنها" {{ request('status') == 'مفرج عنها' ? 'selected' : '' }}>مفرج عنها</option>
                    <option value="مكتسبة" {{ request('status') == 'مكتسبة' ? 'selected' : '' }}>مكتسبة</option>
                    <option value="مصادق عليها" {{ request('status') == 'مصادق عليها' ? 'selected' : '' }}>مصادق عليها</option>
                    <option value="مثمنة" {{ request('status') == 'مثمنة' ? 'selected' : '' }}>مثمنة</option>
                </select>
            </div>
            
            @if(auth()->user()->hasRole(['admin', 'verifier']) && count($directorates) > 0)
            <div class="col-md-3">
                <label for="directorate_id" class="form-label">المديرية</label>
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
                <label for="search" class="form-label">بحث</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="رقم، نوع، اسم متهم...">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<!-- Vehicles Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">قائمة العجلات</h5>
        <span>العدد الكلي: {{ $vehicles->total() }}</span>
    </div>
    <div class="card-body">
        @if($vehicles->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>النوع</th>
                        <th>الاسم/الموديل</th>
                        <th>الرقم</th>
                        <th>المديرية</th>
                        <th>الحالة</th>
                        <th>المضيف</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                   @foreach($vehicles as $vehicle)
                   <tr>
                       <td>{{ $vehicle->id }}</td>
                       <td>
                           @if($vehicle->type == 'confiscated')
                           <span class="badge bg-danger">مصادرة</span>
                           @else
                           <span class="badge bg-success">حكومية</span>
                           @endif
                           {{ $vehicle->vehicle_type }}
                       </td>
                       <td>{{ $vehicle->vehicle_name }} {{ $vehicle->model }}</td>
                       <td>{{ $vehicle->vehicle_number }}</td>
                       <td>{{ $vehicle->directorate->name }}</td>
                       <td>
                           @if($vehicle->type == 'confiscated')
                               @if($vehicle->valuation_status == 'مثمنة')
                               <span class="badge bg-dark">مثمنة</span>
                               @elseif($vehicle->authentication_status == 'تمت المصادقة عليها')
                               <span class="badge bg-info">مصادق عليها</span>
                               @elseif($vehicle->final_degree_status == 'مكتسبة')
                               <span class="badge bg-primary">مكتسبة</span>
                               @elseif($vehicle->seizure_status == 'مصادرة')
                               <span class="badge bg-danger">مصادرة</span>
                               @elseif($vehicle->seizure_status == 'مفرج عنها')
                               <span class="badge bg-success">مفرج عنها</span>
                               @else
                               <span class="badge bg-warning">محجوزة</span>
                               @endif
                           @else
                               <span class="badge bg-secondary">{{ $vehicle->vehicle_condition }}</span>
                           @endif
                       </td>
                       <td>{{ $vehicle->user->name }}</td>
                       <td>{{ $vehicle->created_at->format('Y-m-d') }}</td>
                       <td>
                           <div class="btn-group" role="group">
                               <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-primary">
                                   <i class="bi bi-eye"></i>
                               </a>
                               
                               @can('update', $vehicle)
                               <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-sm btn-warning">
                                   <i class="bi bi-pencil"></i>
                               </a>
                               @endcan
                               
                               @can('delete', $vehicle)
                               <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline">
                                   @csrf
                                   @method('DELETE')
                                   <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                       <i class="bi bi-trash"></i>
                                   </button>
                               </form>
                               @endcan
                           </div>
                       </td>
                   </tr>
                   @endforeach
               </tbody>
           </table>
       </div>
       
       <div class="mt-4">
           {{ $vehicles->withQueryString()->links() }}
       </div>
       @else
       <div class="text-center py-4">
           <h4>لا توجد عجلات متاحة</h4>
           <p class="text-muted">قم بإضافة عجلة جديدة أو تعديل معايير البحث</p>
           
           @if(auth()->user()->can('create vehicles') || auth()->user()->hasRole('vehicles_dept'))
            <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> إضافة عجلة جديدة
            </a>
            @endif
       </div>
       @endif
   </div>
</div>
@endsection