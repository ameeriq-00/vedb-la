<!-- resources/views/users/index.blade.php -->
@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@section('actions')
<a href="{{ route('users.create') }}" class="btn btn-primary">
    <i class="bi bi-person-plus"></i> إضافة مستخدم جديد
</a>
@endsection

@section('content')
<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">فلترة البيانات</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="role" class="form-label">الدور</label>
                <select name="role" id="role" class="form-select">
                    <option value="">الكل</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        @if($role->name == 'admin')
                        المشرف
                        @elseif($role->name == 'verifier')
                        المدقق
                        @elseif($role->name == 'data_entry')
                        مدخل البيانات
                        @elseif($role->name == 'vehicles_dept')
                        الآليات
                        @elseif($role->name == 'recipient')
                        المستلم
                        @else
                        {{ $role->name }}
                        @endif
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-4">
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
            
            <div class="col-md-4">
                <label for="search" class="form-label">بحث</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="الاسم، البريد الإلكتروني...">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">تطبيق الفلتر</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">قائمة المستخدمين</h5>
        <span>العدد الكلي: {{ $users->total() }}</span>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور</th>
                        <th>المديرية</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
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
                        <td>{{ $user->directorate->name ?? 'غير محدد' }}</td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id != auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
        @else
        <div class="text-center py-4">
            <h4>لا يوجد مستخدمين</h4>
            <p class="text-muted">لم يتم العثور على مستخدمين مطابقين لمعايير البحث</p>
            
            <a href="{{ route('users.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-person-plus"></i> إضافة مستخدم جديد
            </a>
        </div>
        @endif
    </div>
</div>
@endsection