<!-- resources/views/edit_requests/show.blade.php -->
@extends('layouts.app')

@section('title', 'تفاصيل طلب التعديل #' . $editRequest->id)

@section('actions')
<div class="btn-group" role="group">
    @if($editRequest->status == 'pending' && auth()->user()->can('approve edit requests'))
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
        <i class="bi bi-check-lg"></i> موافقة
    </button>
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
        <i class="bi bi-x-lg"></i> رفض
    </button>
    @endif
    
    <a href="{{ route('edit-requests.index') }}" class="btn btn-primary">
        <i class="bi bi-arrow-right"></i> عودة للقائمة
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات الطلب</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="30%">الحالة</th>
                            <td>
                                @if($editRequest->status == 'pending')
                                <span class="badge bg-warning">قيد الانتظار</span>
                                @elseif($editRequest->status == 'approved')
                                <span class="badge bg-success">تمت الموافقة</span>
                                @else
                                <span class="badge bg-danger">مرفوض</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>رقم العجلة</th>
                            <td>
                                <a href="{{ route('vehicles.show', $editRequest->vehicle) }}">
                                    {{ $editRequest->vehicle->vehicle_type }} - {{ $editRequest->vehicle->vehicle_number ?: 'بلا رقم' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>الحقل المراد تعديله</th>
                            <td>{{ $editRequest->field_name }}</td>
                        </tr>
                        <tr>
                            <th>القيمة الحالية</th>
                            <td>
                                @if(is_array(json_decode($editRequest->old_value)))
                                {{ implode(', ', json_decode($editRequest->old_value)) }}
                                @else
                                {{ $editRequest->old_value ?: 'غير محدد' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>القيمة الجديدة</th>
                            <td>
                                @if(is_array(json_decode($editRequest->new_value)))
                                {{ implode(', ', json_decode($editRequest->new_value)) }}
                                @else
                                {{ $editRequest->new_value }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>سبب التعديل</th>
                            <td>{{ $editRequest->notes ?: 'لم يتم تحديد سبب' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ الطلب</th>
                            <td>{{ $editRequest->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>مقدم الطلب</th>
                            <td>{{ $editRequest->user->name }}</td>
                        </tr>
                        @if($editRequest->status != 'pending')
                        <tr>
                            <th>قام بالمراجعة</th>
                            <td>{{ $editRequest->approver->name ?? 'غير محدد' }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ المراجعة</th>
                            <td>{{ $editRequest->approval_date ? $editRequest->approval_date->format('Y-m-d H:i') : 'غير محدد' }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">المرفقات</h5>
            </div>
            <div class="card-body">
                @if($editRequest->attachments->count() > 0)
                <div class="list-group">
                    @foreach($editRequest->attachments as $attachment)
                    <a href="{{ route('attachments.download', $attachment) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark"></i> {{ $attachment->file_name }}
                        <span class="badge bg-secondary float-end">
                            {{ number_format($attachment->file_size / 1024, 2) }} KB
                        </span>
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-muted">لا توجد مرفقات</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
@if($editRequest->status == 'pending' && auth()->user()->can('approve edit requests'))
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('edit-requests.approve', $editRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">تأكيد الموافقة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من الموافقة على طلب تعديل الحقل <strong>{{ $editRequest->field_name }}</strong>؟</p>
                    <p>سيتم تغيير القيمة من <strong>{{ $editRequest->old_value ?: 'غير محدد' }}</strong> إلى <strong>{{ $editRequest->new_value }}</strong>.</p>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">ملاحظات (اختياري)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تأكيد الموافقة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('edit-requests.reject', $editRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">تأكيد الرفض</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من رفض طلب تعديل الحقل <strong>{{ $editRequest->field_name }}</strong>؟</p>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label required-field">سبب الرفض</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection