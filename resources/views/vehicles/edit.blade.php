<!-- resources/views/vehicles/edit.blade.php -->
@extends('layouts.app')

@section('title', 'تعديل العجلة #' . $vehicle->id)

@section('actions')
<a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-primary">
    <i class="bi bi-arrow-right"></i> عودة للتفاصيل
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">تعديل معلومات العجلة</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type" class="form-label required-field">نوع السجل</label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required disabled>
                            <option value="confiscated" {{ $vehicle->type == 'confiscated' ? 'selected' : '' }}>مصادرة</option>
                            <option value="government" {{ $vehicle->type == 'government' ? 'selected' : '' }}>حكومية</option>
                        </select>
                        <input type="hidden" name="type" value="{{ $vehicle->type }}">
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="directorate_id" class="form-label required-field">المديرية</label>
                        <select name="directorate_id" id="directorate_id" class="form-select @error('directorate_id') is-invalid @enderror" 
                                {{ auth()->user()->hasRole(['admin', 'verifier']) ? '' : 'disabled' }} required>
                            @if(auth()->user()->hasRole(['admin', 'verifier']) && isset($directorates))
                                @foreach($directorates as $directorate)
                                    <option value="{{ $directorate->id }}" {{ $vehicle->directorate_id == $directorate->id ? 'selected' : '' }}>
                                        {{ $directorate->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="{{ $vehicle->directorate_id }}" selected>
                                    {{ $vehicle->directorate->name }}
                                </option>
                                <input type="hidden" name="directorate_id" value="{{ $vehicle->directorate_id }}">
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
                               value="{{ old('vehicle_type', $vehicle->vehicle_type) }}" required>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="vehicle_name" class="form-label">اسم العجلة</label>
                        <input type="text" name="vehicle_name" id="vehicle_name" class="form-control @error('vehicle_name') is-invalid @enderror" 
                               value="{{ old('vehicle_name', $vehicle->vehicle_name) }}">
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
                               value="{{ old('model', $vehicle->model) }}">
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vehicle_number" class="form-label">رقم العجلة</label>
                        <input type="text" name="vehicle_number" id="vehicle_number" class="form-control @error('vehicle_number') is-invalid @enderror" 
                               value="{{ old('vehicle_number', $vehicle->vehicle_number) }}">
                        @error('vehicle_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="province" class="form-label">المحافظة</label>
                        <input type="text" name="province" id="province" class="form-control @error('province') is-invalid @enderror" 
                               value="{{ old('province', $vehicle->province) }}">
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
                               value="{{ old('color', $vehicle->color) }}">
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="chassis_number" class="form-label">رقم الشاصي</label>
                        <input type="text" name="chassis_number" id="chassis_number" class="form-control @error('chassis_number') is-invalid @enderror" 
                               value="{{ old('chassis_number', $vehicle->chassis_number) }}">
                        @error('chassis_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="vehicle_condition" class="form-label required-field">حالة العجلة</label>
                        <select name="vehicle_condition" id="vehicle_condition" class="form-select @error('vehicle_condition') is-invalid @enderror" required>
                            <option value="صالحة" {{ $vehicle->vehicle_condition == 'صالحة' ? 'selected' : '' }}>صالحة</option>
                            <option value="غير صالحة" {{ $vehicle->vehicle_condition == 'غير صالحة' ? 'selected' : '' }}>غير صالحة</option>
                        </select>
                        @error('vehicle_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Confiscated Vehicle Fields -->
            @if($vehicle->type == 'confiscated')
            <h5 class="mt-4 mb-3">معلومات العجلة المصادرة</h5>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="defendant_name" class="form-label required-field">اسم المتهم</label>
                        <input type="text" name="defendant_name" id="defendant_name" class="form-control @error('defendant_name') is-invalid @enderror" 
                               value="{{ old('defendant_name', $vehicle->defendant_name) }}" required>
                        @error('defendant_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="legal_article" class="form-label required-field">المادة القانونية</label>
                        <input type="text" name="legal_article" id="legal_article" class="form-control @error('legal_article') is-invalid @enderror" 
                               value="{{ old('legal_article', $vehicle->legal_article) }}" required>
                        @error('legal_article')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Government Vehicle Fields -->
            @if($vehicle->type == 'government')
            <h5 class="mt-4 mb-3">معلومات العجلة الحكومية</h5>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="source" class="form-label required-field">وردت من</label>
                        <input type="text" name="source" id="source" class="form-control @error('source') is-invalid @enderror" 
                               value="{{ old('source', $vehicle->source) }}" required>
                        @error('source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Common Fields -->
            <h5 class="mt-4 mb-3">الملحقات والعوارض</h5>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">الملحقات</label>
                        <div class="border rounded p-3">
                            <div class="row accessories-container">
                                @php
                                    $accessories = $vehicle->accessories ?? [];
                                @endphp
                                
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="طفاية" id="acc_fire"
                                            {{ in_array('طفاية', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_fire">طفاية</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="سبير" id="acc_spare"
                                            {{ in_array('سبير', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_spare">سبير</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="عدة" id="acc_kit"
                                            {{ in_array('عدة', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_kit">عدة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="جك" id="acc_jack"
                                            {{ in_array('جك', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_jack">جك</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="ويل سبانة" id="acc_wheel"
                                            {{ in_array('ويل سبانة', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_wheel">ويل سبانة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="ارضيات" id="acc_floors"
                                            {{ in_array('ارضيات', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_floors">ارضيات</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="جداحة" id="acc_lighter"
                                            {{ in_array('جداحة', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_lighter">جداحة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="بصمة" id="acc_key"
                                            {{ in_array('بصمة', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_key">بصمة</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="accessories[]" value="سويج" id="acc_switch"
                                            {{ in_array('سويج', $accessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_switch">سويج</label>
                                    </div>
                                </div>
                                
                                @foreach($accessories as $accessory)
                                    @if(!in_array($accessory, ['طفاية', 'سبير', 'عدة', 'جك', 'ويل سبانة', 'ارضيات', 'جداحة', 'بصمة', 'سويج']))
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="accessories[]" value="{{ $accessory }}" 
                                                   id="acc_custom_{{ $loop->index }}" checked>
                                            <label class="form-check-label" for="acc_custom_{{ $loop->index }}">{{ $accessory }}</label>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
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
                            <div class="row defects-container">
                                @php
                                    $defects = $vehicle->defects ?? [];
                                @endphp
                                
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="دعامية امامية" id="def_front"
                                            {{ in_array('دعامية امامية', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_front">دعامية امامية</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="دعامية خلفية" id="def_back"
                                            {{ in_array('دعامية خلفية', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_back">دعامية خلفية</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب امامي يمين" id="def_door_fr"
                                            {{ in_array('باب امامي يمين', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_door_fr">باب امامي يمين</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب امامي يسار" id="def_door_fl"
                                            {{ in_array('باب امامي يسار', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_door_fl">باب امامي يسار</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب خلفي يمين" id="def_door_br"
                                            {{ in_array('باب خلفي يمين', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_door_br">باب خلفي يمين</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="باب خلفي يسار" id="def_door_bl"
                                            {{ in_array('باب خلفي يسار', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_door_bl">باب خلفي يسار</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="محرك" id="def_engine"
                                            {{ in_array('محرك', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_engine">محرك</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="كير" id="def_gear"
                                            {{ in_array('كير', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_gear">كير</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="داينمو" id="def_dynamo"
                                            {{ in_array('داينمو', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_dynamo">داينمو</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="defects[]" value="متهالكة" id="def_worn"
                                            {{ in_array('متهالكة', $defects) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="def_worn">متهالكة</label>
                                    </div>
                                </div>
                                
                                @foreach($defects as $defect)
                                    @if(!in_array($defect, ['دعامية امامية', 'دعامية خلفية', 'باب امامي يمين', 'باب امامي يسار', 'باب خلفي يمين', 'باب خلفي يسار', 'محرك', 'كير', 'داينمو', 'متهالكة']))
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="defects[]" value="{{ $defect }}" 
                                                   id="def_custom_{{ $loop->index }}" checked>
                                            <label class="form-check-label" for="def_custom_{{ $loop->index }}">{{ $defect }}</label>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
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
                                  rows="3">{{ old('missing_parts', $vehicle->missing_parts) }}</textarea>
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
                                  rows="3">{{ old('notes', $vehicle->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <h5 class="mt-4 mb-3">إضافة صور ومرفقات جديدة</h5>
            
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
                    <i class="bi bi-save"></i> حفظ التغييرات
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
        // Add custom accessory
        const addAccessoryBtn = document.getElementById('add_accessory');
        const otherAccessoriesInput = document.getElementById('other_accessories');
        
        addAccessoryBtn.addEventListener('click', function() {
            const accessoryValue = otherAccessoriesInput.value.trim();
            
            if (accessoryValue) {
                const row = document.createElement('div');
                row.className = 'col-md-4 mb-2';
                row.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="accessories[]" value="${accessoryValue}" id="acc_custom_${Date.now()}" checked>
                        <label class="form-check-label" for="acc_custom_${Date.now()}">${accessoryValue}</label>
                    </div>
                `;
                
                document.querySelector('.accessories-container').appendChild(row);
                otherAccessoriesInput.value = '';
            }
        });
        
        // Add custom defect
        const addDefectBtn = document.getElementById('add_defect');
        const otherDefectsInput = document.getElementById('other_defects');
        
        addDefectBtn.addEventListener('click', function() {
            const defectValue = otherDefectsInput.value.trim();
            
            if (defectValue) {
                const row = document.createElement('div');
                row.className = 'col-md-6 mb-2';
                row.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="defects[]" value="${defectValue}" id="def_custom_${Date.now()}" checked>
                        <label class="form-check-label" for="def_custom_${Date.now()}">${defectValue}</label>
                    </div>
                `;
                
                document.querySelector('.defects-container').appendChild(row);
                otherDefectsInput.value = '';
            }
        });
    });
</script>
@endpush