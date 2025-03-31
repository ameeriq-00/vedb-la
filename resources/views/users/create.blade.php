<!-- resources/views/users/create.blade.php -->
@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد')

@section('actions')
<a href="{{ route('users.index') }}" class="btn btn-primary">
    <i class="bi bi-arrow-right"></i> عودة للقائمة
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">معلومات المستخدم الجديد</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label required-field">الاسم</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="form-label required-field">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="form-label required-field">كلمة المرور</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label required-field">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role" class="form-label required-field">الدور</label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- اختر الدور --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
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
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="directorate_id" class="form-label required-field">المديرية</label>
                        <select name="directorate_id" id="directorate_id" class="form-select @error('directorate_id') is-invalid @enderror" required>
                            <option value="">-- اختر المديرية --</option>
                            @foreach($directorates as $directorate)
                            <option value="{{ $directorate->id }}" {{ old('directorate_id') == $directorate->id ? 'selected' : '' }}>
                                {{ $directorate->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('directorate_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> حفظ المستخدم
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection