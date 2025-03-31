@extends('layouts.app')

@section('title', 'طلب تعديل بيانات العجلة #' . $vehicle->id)

@section('actions')
<a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-primary">
    <i class="bi bi-arrow-right"></i> عودة للتفاصيل
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">طلب تعديل حقل "{{ $field }}"</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('edit-requests.store', $vehicle) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <input type="hidden" name="field_name" value="{{ $field }}">
            <input type="hidden" name="old_value" value="{{ $fieldValue }}">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="old_value_display" class="form-label">القيمة الحالية</label>
                        @if($inputType == 'select')
                        <input type="text" id="old_value_display" class="form-control" value="{{ $fieldValue }}" readonly>
                        @elseif($inputType == 'textarea')
                        <textarea id="old_value_display" class="form-control" rows="3" readonly>{{ $fieldValue }}</textarea>
                        @elseif($inputType == 'date' && $fieldValue)
                        <input type="text" id="old_value_display" class="form-control" value="{{ \Carbon\Carbon::parse($fieldValue)->format('Y-m-d') }}" readonly>
                        @else
                        <input type="text" id="old_value_display" class="form-control" value="{{ $fieldValue }}" readonly>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="new_value" class="form-label required-field">القيمة الجديدة</label>
                        @if($inputType == 'select')
                        <select name="new_value" id="new_value" class="form-select @error('new_value') is-invalid @enderror" required>
                            <option value="صالحة" {{ $fieldValue == 'صالحة' ? '' : 'selected' }}>صالحة</option>
                            <option value="غير صالحة" {{ $fieldValue == 'غير صالحة' ? '' : 'selected' }}>غير صالحة</option>
                        </select>
                        @elseif($inputType == 'textarea')
                        <textarea name="new_value" id="new_value" class="form-control @error('new_value') is-invalid @enderror" 
                                rows="3" required>{{ old('new_value', $fieldValue) }}</textarea>
                        @elseif($inputType == 'date')
                        <input type="date" name="new_value" id="new_value" class="form-control @error('new_value') is-invalid @enderror" 
                               value="{{ old('new_value', $fieldValue ? \Carbon\Carbon::parse($fieldValue)->format('Y-m-d') : '') }}" required>
                        @elseif($inputType == 'number')
                        <input type="number" name="new_value" id="new_value" class="form-control @error('new_value') is-invalid @enderror" 
                               value="{{ old('new_value', $fieldValue) }}" step="0.01" required>
                        @else
                        <input type="text" name="new_value" id="new_value" class="form-control @error('new_value') is-invalid @enderror" 
                               value="{{ old('new_value', $fieldValue) }}" required>
                        @endif
                        @error('new_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="notes" class="form-label">سبب التعديل</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="attachment" class="form-label">مرفق داعم (اختياري)</label>
                        <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">يمكنك إرفاق ملف داعم لطلب التعديل (PDF, DOC, DOCX, JPG, PNG)</small>
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> إرسال الطلب
                </button>
                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-secondary">
                    <i class="bi bi-x"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection