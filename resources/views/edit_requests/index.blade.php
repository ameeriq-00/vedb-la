<!-- resources/views/edit_requests/index.blade.php -->
@extends('layouts.app')

@section('title', 'طلبات التعديل')

@section('content')
<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">فلترة البيانات</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('edit-requests.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">الحالة</label>
                <select name="status" id="status" class="form-select">
                   <option value="">الكل</option>
                   <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                   <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>تمت الموافقة</option>
                   <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
               </select>
           </div>
           
           @if(auth()->user()->hasRole(['admin', 'verifier']))
           <div class="col-md-4">
               <label for="directorate_id" class="form-label">المديرية</label>
               <select name="directorate_id" id="directorate_id" class="form-select">
                   <option value="">الكل</option>
                   @foreach(\App\Models\Directorate::all() as $directorate)
                   <option value="{{ $directorate->id }}" {{ request('directorate_id') == $directorate->id ? 'selected' : '' }}>
                       {{ $directorate->name }}
                   </option>
                   @endforeach
               </select>
           </div>
           @endif
           
           <div class="col-md-4">
               <label for="search" class="form-label">بحث</label>
               <input type="text" name="search" id="search" class="form-control" 
                      value="{{ request('search') }}" placeholder="نوع، حقل، قيمة...">
           </div>
           
           <div class="col-12">
               <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
               <a href="{{ route('edit-requests.index') }}" class="btn btn-secondary">إعادة تعيين</a>
           </div>
       </form>
   </div>
</div>

<!-- Edit Requests Table -->
<div class="card">
   <div class="card-header d-flex justify-content-between align-items-center">
       <h5 class="mb-0">قائمة طلبات التعديل</h5>
       <span>العدد الكلي: {{ $editRequests->total() }}</span>
   </div>
   <div class="card-body">
       @if($editRequests->count() > 0)
       <div class="table-responsive">
           <table class="table table-hover">
               <thead>
                   <tr>
                       <th>#</th>
                       <th>العجلة</th>
                       <th>الحقل</th>
                       <th>القيمة الجديدة</th>
                       <th>المستخدم</th>
                       <th>التاريخ</th>
                       <th>الحالة</th>
                       <th>الإجراءات</th>
                   </tr>
               </thead>
               <tbody>
                   @foreach($editRequests as $request)
                   <tr>
                       <td>{{ $request->id }}</td>
                       <td>
                           <a href="{{ route('vehicles.show', $request->vehicle) }}">
                               {{ $request->vehicle->vehicle_type }} - {{ $request->vehicle->vehicle_number }}
                           </a>
                       </td>
                       <td>{{ $request->field_name }}</td>
                       <td>{{ Str::limit($request->new_value, 30) }}</td>
                       <td>{{ $request->user->name }}</td>
                       <td>{{ $request->created_at->format('Y-m-d') }}</td>
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
       
       <div class="mt-4">
           {{ $editRequests->withQueryString()->links() }}
       </div>
       @else
       <div class="text-center py-4">
           <h4>لا توجد طلبات تعديل</h4>
           <p class="text-muted">لم يتم العثور على طلبات تعديل مطابقة لمعايير البحث</p>
       </div>
       @endif
   </div>
</div>
@endsection