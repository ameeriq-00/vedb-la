<!-- resources/views/users/edit.blade.php -->
@extends('layouts.app')

@section('title', 'تعديل المستخدم: ' . $user->name)

@section('actions')
<a href="{{ route('users.show', $user) }}" class="btn btn-primary">
    <i class="bi bi-arrow-right"></i> عودة للتفاصيل
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">تعديل معلومات المستخدم</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="form-label required-field">الاسم</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email" class="form-label required-field">البريد الإلكتروني</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="form-label">كلمة المرور (اتركها فارغة إذا لم ترغب بتغييرها)</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
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
                            <option value="{{ $role->name }}" 
                                {{ (old('role') == $role->name || $user->hasRole($role->name)) ? 'selected' : '' }}>
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
                            <option value="{{ $directorate->id }}" 
                                {{ (old('directorate_id') == $directorate->id || $user->directorate_id == $directorate->id) ? 'selected' : '' }}>
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
                    <i class="bi bi-save"></i> حفظ التغييرات
                </button>
                <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                    <i class="bi bi-x"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection