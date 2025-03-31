<!-- resources/views/users/show.blade.php -->
@extends('layouts.app')

@section('title', 'تفاصيل المستخدم: ' . $user->name)

@section('actions')
<div class="btn-group" role="group">
    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
        <i class="bi bi-pencil"></i> تعديل
    </a>
    
    @if($user->id != auth()->id())
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
        <i class="bi bi-trash"></i> حذف
    </button>
    @endif
    
    <a href="{{ route('users.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-right"></i> عودة للقائمة
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات المستخدم</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="30%">الاسم</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>البريد الإلكتروني</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>الدور</th>
                            <td>
                                @foreach($user->roles as $role)
                                    @if($role->name == 'admin')
                                    <span class="badge bg-danger">المشرف</span>
                                    @elseif($role->name == 'verifier')
                                    <span class="badge bg-warning">المدقق</span>
                                    @elseif($role->name == 'data_entry')
                                    <span class="badge bg-primary">مدخل البيانات</span>
                                    @elseif($role->name == 'vehicles_dept')
                                    <span class="badge bg-success">الآليات</span>
                                    @elseif($role->name == 'recipient')
                                    <span class="badge bg-info">المستلم</span>
                                    @else
                                    <span class="badge bg-secondary">{{ $role->name }}</span>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>المديرية</th>
                            <td>{{ $user->directorate->name ?? 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ الإنشاء</th>
                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>آخر تحديث</th>
                            <td>{{ $user->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">الصلاحيات</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($user->getAllPermissions() as $permission)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>{{ $permission->name }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">النشاطات</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ $user->vehicles()->count() }}</h5>
                                <div class="text-muted">العجلات المضافة</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ $user->editRequests()->count() }}</h5>
                                <div class="text-muted">طلبات التعديل</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ $user->vehicleTransfers()->count() }}</h5>
                                <div class="text-muted">المناقلات</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5 class="mb-0">{{ $user->approvedRequests()->count() }}</h5>
                                <div class="text-muted">الطلبات المراجعة</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
@if($user->id != auth()->id())
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('users.destroy', $user) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <p>هل أنت متأكد من حذف المستخدم <strong>{{ $user->name }}</strong>؟</p>
                   <p class="text-danger">تحذير: لا يمكن التراجع عن هذا الإجراء. سيتم حذف حساب المستخدم نهائياً.</p>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                   <button type="submit" class="btn btn-danger">تأكيد الحذف</button>
               </div>
           </form>
       </div>
   </div>
</div>
@endif
@endsection