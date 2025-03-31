<!-- resources/views/vehicles/create.blade.php -->
@extends('layouts.app')

@section('title', 'إضافة عجلة جديدة')

@section('actions')
<a href="{{ route('vehicles.index') }}" class="btn btn-primary">
    <i class="bi bi-arrow-right"></i> عودة للقائمة
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">معلومات العجلة الجديدة</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="type" class="form-label required-field">نوع السجل</label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">-- اختر نوع السجل --</option>
                        <option value="confiscated" {{ old('type') == 'confiscated' ? 'selected' : '' }}>مصادرة</option>
                        @if(auth()->user()->hasRole(['admin', 'verifier', 'vehicles_dept']))
                        <option value="government" {{ old('type') == 'government' ? 'selected' : '' }}>حكومية</option>
                        @endif
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="directorate_id" class="form-label required-field">المديرية</label>
                    <select name="directorate_id" id="directorate_id" class="form-select @error('directorate_id') is-invalid @enderror" required>
                        <option value="">-- اختر المديرية --</option>
                        @if(auth()->user()->hasRole(['admin', 'verifier']) && isset($directorates))
                            @foreach($directorates as $directorate)
                                <option value="{{ $directorate->id }}" {{ old('directorate_id') == $directorate->id ? 'selected' : '' }}>
                                    {{ $directorate->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="{{ auth()->user()->directorate_id }}" selected>
                                {{ auth()->user()->directorate->name }}
                            </option>
                        @endif
                    </select>
                    @error('directorate_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vehicle_type" class="form-label required-field">نوع العجلة</label>
                        <input type="text" name="vehicle_type" id="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror" 
                               value="{{ old('vehicle_type') }}" required>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vehicle_name" class="form-label">اسم العجلة</label>
                        <input type="text" name="vehicle_name" id="vehicle_name" class="form-control @error('vehicle_name') is-invalid @enderror" 
                               value="{{ old('vehicle_name') }}">
                        @error('vehicle_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="model" class="form-label">الموديل</label>
                        <input type="text" name="model" id="model" class="form-control @error('model') is-invalid @enderror" 
                               value="{{ old('model') }}">
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vehicle_number" class="form-label">رقم العجلة</label>
                        <input type="text" name="vehicle_number" id="vehicle_number" class="form-control @error('vehicle_number') is-invalid @enderror" 
                               value="{{ old('vehicle_number') }}">
                        @error('vehicle_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="province" class="form-label">المحافظة</label>
                        <input type="text" name="province" id="province" class="form-control @error('province') is-invalid @enderror" 
                               value="{{ old('province') }}">
                        @error('province')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="color" class="form-label">اللون</label>
                        <input type="text" name="color" id="color" class="form-control @error('color') is-invalid @enderror" 
                               value="{{ old('color') }}">
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="chassis_number" class="form-label">رقم الشاصي</label>
                        <input type="text" name="chassis_number" id="chassis_number" class="form-control @error('chassis_number') is-invalid @enderror" 
                               value="{{ old('chassis_number') }}">
                        @error('chassis_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vehicle_condition" class="form-label required-field">حالة العجلة</label>
                        <select name="vehicle_condition" id="vehicle_condition" class="form-select @error('vehicle_condition') is-invalid @enderror" required>
                            <option value="صالحة" {{ old('vehicle_condition') == 'صالحة' ? 'selected' : '' }}>صالحة</option>
                            <option value="غير صالحة" {{ old('vehicle_condition') == 'غير صالحة' ? 'selected' : '' }}>غير صالحة</option>
                        </select>
                        @error('vehicle_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Confiscated Vehicle Fields -->
            <div id="confiscated-fields" style="display: none;">
                <h5 class="mt-4 mb-3">معلومات العجلة المصادرة</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="defendant_name" class="form-label required-field">اسم المتهم</label>
                            <input type="text" name="defendant_name" id="defendant_name" class="form-control @error('defendant_name') is-invalid @enderror" 
                                   value="{{ old('defendant_name') }}">
                            @error('defendant_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="legal_article" class="form-label required-field">المادة القانونية</label>
                            <input type="text" name="legal_article" id="legal_article" class="form-control @error('legal_article') is-invalid @enderror" 
                                   value="{{ old('legal_article') }}">
                            @error('legal_article')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="seizure_status" class="form-label required-field">حالة العجلة</label>
                            <select name="seizure_status" id="seizure_status" class="form-select @error('seizure_status') is-invalid @enderror">
                                <option value="محجوزة" {{ old('seizure_status') == 'محجوزة' ? 'selected' : '' }}>محجوزة</option>
                                <option value="مفرج عنها" {{ old('seizure_status') == 'مفرج عنها' ? 'selected' : '' }}>مفرج عنها</option>
                                <option value="مصادرة" {{ old('seizure_status') == 'مصادرة' ? 'selected' : '' }}>مصادرة</option>
                            </select>
                            @error('seizure_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="seizure_letter_number" class="form-label">عدد كتاب الحجز</label>
                            <input type="text" name="seizure_letter_number" id="seizure_letter_number" 
                                   class="form-control @error('seizure_letter_number') is-invalid @enderror" 
                                   value="{{ old('seizure_letter_number') }}">
                            @error('seizure_letter_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="seizure_letter_date" class="form-label">تاريخ كتاب الحجز</label>
                            <input type="date" name="seizure_letter_date" id="seizure_letter_date" 
                                   class="form-control @error('seizure_letter_date') is-invalid @enderror" 
                                   value="{{ old('seizure_letter_date') }}">
                            @error('seizure_letter_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Government Vehicle Fields -->
            <div id="government-fields" style="display: none;">
                <h5 class="mt-4 mb-3">معلومات العجلة الحكومية</h5>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="source" class="form-label required-field">وردت من</label>
                            <input type="text" name="source" id="source" class="form-control @error('source') is-invalid @enderror" 
                                   value="{{ old('source') }}">
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="import_letter_number" class="form-label">عدد الوارد</label>
                            <input type="text" name="import_letter_number" id="import_letter_number" 
                                   class="form-control @error('import_letter_number') is-invalid @enderror" 
                                   value="{{ old('import_letter_number') }}">
                            @error('import_letter_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="import_letter_date" class="form-label">تاريخ الوارد</label>
                            <input type="date" name="import_letter_date" id="import_letter_date" 
                                   class="form-control @error('import_letter_date') is-invalid @enderror" 
                                   value="{{ old('import_letter_date') }}">
                            @error('import_letter_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Common Fields -->
            <h5 class="mt-4 mb-3">الملحقات والعوارض</h5>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">الملحقات</label>
                        <div class="border rounded p-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="طفاية" id="acc_fire">
                                        <label class="form-check-label" for="acc_fire">طفاية</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="سبير" id="acc_spare">
                                        <label class="form-check-label" for="acc_spare">سبير</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="عدة" id="acc_kit">
                                        <label class="form-check-label" for="acc_kit">عدة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="جك" id="acc_jack">
                                        <label class="form-check-label" for="acc_jack">جك</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="ويل سبانة" id="acc_wheel">
                                        <label class="form-check-label" for="acc_wheel">ويل سبانة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="ارضيات" id="acc_floors">
                                        <label class="form-check-label" for="acc_floors">ارضيات</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="جداحة" id="acc_lighter">
                                        <label class="form-check-label" for="acc_lighter">جداحة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="بصمة" id="acc_key">
                                        <label class="form-check-label" for="acc_key">بصمة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="سويج" id="acc_switch">
                                        <label class="form-check-label" for="acc_switch">سويج</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <label for="other_accessories" class="form-label">ملحقات أخرى</label>
                                <input type="text" id="other_accessories" class="form-control mb-2">
                                <button type="button" class="btn btn-sm btn-secondary" id="add_accessory">
                                    <i class="bi bi-plus"></i> إضافة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">العوارض</label>
                        <div class="border rounded p-3">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="دعامية امامية" id="def_front">
                                        <label class="form-check-label" for="def_front">دعامية امامية</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="دعامية خلفية" id="def_back">
                                        <label class="form-check-label" for="def_back">دعامية خلفية</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب امامي يمين" id="def_door_fr">
                                        <label class="form-check-label" for="def_door_fr">باب امامي يمين</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب امامي يسار" id="def_door_fl">
                                        <label class="form-check-label" for="def_door_fl">باب امامي يسار</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب خلفي يمين" id="def_door_br">
                                        <label class="form-check-label" for="def_door_br">باب خلفي يمين</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب خلفي يسار" id="def_door_bl">
                                        <label class="form-check-label" for="def_door_bl">باب خلفي يسار</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="محرك" id="def_engine">
                                        <label class="form-check-label" for="def_engine">محرك</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="كير" id="def_gear">
                                        <label class="form-check-label" for="def_gear">كير</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="داينمو" id="def_dynamo">
                                        <label class="form-check-label" for="def_dynamo">داينمو</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="متهالكة" id="def_worn">
                                        <label class="form-check-label" for="def_worn">متهالكة</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <label for="other_defects" class="form-label">عوارض أخرى</label>
                                <input type="text" id="other_defects" class="form-control mb-2">
                                <button type="button" class="btn btn-sm btn-secondary" id="add_defect">
                                    <i class="bi bi-plus"></i> إضافة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="missing_parts" class="form-label">النواقص</label>
                        <textarea name="missing_parts" id="missing_parts" class="form-control @error('missing_parts') is-invalid @enderror" 
                                  rows="3">{{ old('missing_parts') }}</textarea>
                        @error('missing_parts')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
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
            
            <h5 class="mt-4 mb-3">الصور والمرفقات</h5>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="images" class="form-label">صور العجلة</label>
                        <input type="file" name="images[]" id="images" class="form-control @error('images.*') is-invalid @enderror" 
                               multiple accept="image/*">
                        <small class="form-text text-muted">يمكنك اختيار أكثر من صورة</small>
                        @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="attachments" class="form-label">المستندات المرفقة</label>
                        <input type="file" name="attachments[]" id="attachments" class="form-control @error('attachments.*') is-invalid @enderror" 
                               multiple accept=".pdf,.doc,.docx">
                        <small class="form-text text-muted">يمكنك اختيار أكثر من مستند (PDF, DOC, DOCX)</small>
                        @error('attachments.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> حفظ العجلة
                </button>
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
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
        // Show/hide fields based on vehicle type
        const typeSelect = document.getElementById('type');
        const confiscatedFields = document.getElementById('confiscated-fields');
        const governmentFields = document.getElementById('government-fields');
        
        // Handle type change
        function handleTypeChange() {
            if (typeSelect.value === 'confiscated') {
                confiscatedFields.style.display = 'block';
                governmentFields.style.display = 'none';
                
                // Make confiscated-specific fields required
                document.getElementById('defendant_name').setAttribute('required', 'required');
                document.getElementById('legal_article').setAttribute('required', 'required');
                document.getElementById('seizure_status').setAttribute('required', 'required');
                
                // Remove required from government-specific fields
                document.getElementById('source').removeAttribute('required');
            } else if (typeSelect.value === 'government') {
                confiscatedFields.style.display = 'none';
                governmentFields.style.display = 'block';
                
                // Make government-specific fields required
                document.getElementById('source').setAttribute('required', 'required');
                
                // Remove required from confiscated-specific fields
                document.getElementById('defendant_name').removeAttribute('required');
                document.getElementById('legal_article').removeAttribute('required');
                document.getElementById('seizure_status').removeAttribute('required');
            } else {
                confiscatedFields.style.display = 'none';
                governmentFields.style.display = 'none';
            }
        }
        
        typeSelect.addEventListener('change', handleTypeChange);
        
        // Initial check
        handleTypeChange();
        
        // Add custom accessory
        const addAccessoryBtn = document.getElementById('add_accessory');
        const otherAccessoriesInput = document.getElementById('other_accessories');
        
        addAccessoryBtn.addEventListener('click', function() {
            const accessoryValue = otherAccessoriesInput.value.trim();
            
            if (accessoryValue) {
                const accessoriesContainer = document.querySelector('.accessories-container');
                const row = document.createElement('div');
                row.className = 'col-md-4 mb-2';
                row.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="accessories[]" value="${accessoryValue}" id="acc_custom_${Date.now()}" checked>
                        <label class="form-check-label" for="acc_custom_${Date.now()}">${accessoryValue}</label>
                    </div>
                `;
                
                document.querySelector('.accessories-container .row').appendChild(row);
                otherAccessoriesInput.value = '';
            }
        });
        
        // Add custom defect
        const addDefectBtn = document.getElementById('add_defect');
        const otherDefectsInput = document.getElementById('other_defects');
        
        addDefectBtn.addEventListener('click', function() {
            const defectValue = otherDefectsInput.value.trim();
            
            if (defectValue) {
                const defectsContainer = document.querySelector('.defects-container');
                const row = document.createElement('div');
                row.className = 'col-md-6 mb-2';
                row.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="defects[]" value="${defectValue}" id="def_custom_${Date.now()}" checked>
                        <label class="form-check-label" for="def_custom_${Date.now()}">${defectValue}</label>
                    </div>
                       `;
                
                document.querySelector('.defects-container .row').appendChild(row);
                otherDefectsInput.value = '';
            }
        });
    });
</script>
@endpush