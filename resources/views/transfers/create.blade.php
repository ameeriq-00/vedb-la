@extends('layouts.app')

@section('title', 'إنشاء مناقلة جديدة للعجلة #' . $vehicle->id)

@section('actions')
<a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-primary">
    <i class="bi bi-arrow-right"></i> عودة للتفاصيل
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">مناقلة عجلة: {{ $vehicle->vehicle_type }} {{ $vehicle->vehicle_name }} {{ $vehicle->vehicle_number }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('transfers.store', $vehicle) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="recipient_name" class="form-label required-field">اسم المستلم</label>
                        <input type="text" name="recipient_name" id="recipient_name" class="form-control @error('recipient_name') is-invalid @enderror" 
                               value="{{ old('recipient_name') }}" required>
                        @error('recipient_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="recipient_id_number" class="form-label required-field">رقم هوية المستلم</label>
                        <input type="text" name="recipient_id_number" id="recipient_id_number" class="form-control @error('recipient_id_number') is-invalid @enderror" 
                               value="{{ old('recipient_id_number') }}" required>
                        <small class="text-muted" id="recipient_id_warning"></small>
                        @error('recipient_id_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="recipient_phone" class="form-label">رقم هاتف المستلم</label>
                        <input type="text" name="recipient_phone" id="recipient_phone" class="form-control @error('recipient_phone') is-invalid @enderror" 
                               value="{{ old('recipient_phone') }}">
                        @error('recipient_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="recipient_entity" class="form-label required-field">الجهة المستلمة</label>
                        <input type="text" name="recipient_entity" id="recipient_entity" class="form-control @error('recipient_entity') is-invalid @enderror" 
                               value="{{ old('recipient_entity') }}" required>
                        @error('recipient_entity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="destination_directorate_id" class="form-label required-field">المديرية المستلمة</label>
                        <select name="destination_directorate_id" id="destination_directorate_id" 
                                class="form-select @error('destination_directorate_id') is-invalid @enderror" required>
                            <option value="">-- اختر المديرية --</option>
                            @foreach($directorates as $directorate)
                            <option value="{{ $directorate->id }}" {{ old('destination_directorate_id') == $directorate->id ? 'selected' : '' }}>
                                {{ $directorate->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('destination_directorate_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="assigned_to" class="form-label">منسب إلى</label>
                        <input type="text" name="assigned_to" id="assigned_to" class="form-control @error('assigned_to') is-invalid @enderror" 
                               value="{{ old('assigned_to') }}">
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="receive_date" class="form-label required-field">تاريخ الاستلام</label>
                        <input type="date" name="receive_date" id="receive_date" class="form-control @error('receive_date') is-invalid @enderror" 
                               value="{{ old('receive_date', now()->format('Y-m-d')) }}" required>
                        @error('receive_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notes" class="form-label">ملاحظات</label>
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
                        <label for="attachment" class="form-label required-field">مستند المناقلة</label>
                        <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" 
                              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">أرفق مستند المناقلة (PDF, DOC, DOCX, JPG, PNG)</small>
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> حفظ المناقلة
                </button>
                <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-secondary">
                    <i class="bi bi-x"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
   document.addEventListener('DOMContentLoaded', function() {
       const recipientIdInput = document.getElementById('recipient_id_number');
       const recipientIdWarning = document.getElementById('recipient_id_warning');
       
       // التحقق من الشخص المستلم إذا كان لديه عجلات أخرى
       function checkRecipientId(idNumber) {
           if (!idNumber) return;
           
           fetch(`/api/check-recipient?id_number=${encodeURIComponent(idNumber)}`)
               .then(response => response.json())
               .then(data => {
                   if (data.count > 0) {
                       recipientIdWarning.innerHTML = `<span class="text-danger">تحذير: هذا الشخص مستلم لـ ${data.count} عجلة أخرى</span>`;
                   } else {
                       recipientIdWarning.textContent = '';
                   }
               })
               .catch(error => {
                   console.error('Error checking recipient:', error);
               });
       }
       
       recipientIdInput.addEventListener('blur', function() {
           checkRecipientId(this.value);
       });
   });
</script>
@endpush