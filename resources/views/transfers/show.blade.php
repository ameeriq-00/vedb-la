@extends('layouts.app')

@section('title', 'تفاصيل المناقلة #' . $transfer->id)

@section('actions')
<div class="btn-group" role="group">
    @if(!$transfer->return_date && !$transfer->is_ownership_transfer && !$transfer->is_referral && auth()->user()->hasRole(['admin', 'vehicles_dept']))
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal">
        <i class="bi bi-check-lg"></i> إكمال المناقلة
    </button>
    @endif
    
    <a href="{{ route('vehicles.show', $transfer->vehicle) }}" class="btn btn-primary">
        <i class="bi bi-truck"></i> تفاصيل العجلة
    </a>
    
    <a href="{{ route('transfers.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> عودة للقائمة
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات المناقلة</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th width="30%">نوع المناقلة</th>
                            <td>
                                @if($transfer->is_ownership_transfer)
                                <span class="badge bg-dark">نقل ملكية</span>
                                @elseif($transfer->is_referral)
                                <span class="badge bg-info">إحالة خارجية</span>
                                @else
                                <span class="badge bg-primary">مناقلة اعتيادية</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>الحالة</th>
                            <td>
                                @if($transfer->is_ownership_transfer)
                                <span class="badge bg-dark">نقل ملكية</span>
                                @elseif($transfer->is_referral)
                                <span class="badge bg-info">إحالة خارجية</span>
                                @elseif($transfer->return_date)
                                <span class="badge bg-success">مكتملة</span>
                                @else
                                <span class="badge bg-primary">جارية</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>العجلة</th>
                            <td>
                                <a href="{{ route('vehicles.show', $transfer->vehicle) }}">
                                    {{ $transfer->vehicle->vehicle_type }} - {{ $transfer->vehicle->vehicle_number ?: 'بلا رقم' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>اسم المستلم</th>
                            <td>{{ $transfer->recipient_name }}</td>
                        </tr>
                        @if(!$transfer->is_ownership_transfer)
                        <tr>
                            <th>رقم هوية المستلم</th>
                            <td>{{ $transfer->recipient_id_number }}</td>
                        </tr>
                        @if($transfer->recipient_phone)
                        <tr>
                            <th>رقم هاتف المستلم</th>
                            <td>{{ $transfer->recipient_phone }}</td>
                        </tr>
                        @endif
                        @endif
                        <tr>
                            <th>الجهة المستلمة</th>
                            <td>{{ $transfer->recipient_entity }}</td>
                        </tr>
                        @if($transfer->destinationDirectorate)
                        <tr>
                            <th>المديرية</th>
                            <td>{{ $transfer->destinationDirectorate->name }}</td>
                        </tr>
                        @endif
                        @if($transfer->assigned_to)
                        <tr>
                            <th>منسب إلى</th>
                            <td>{{ $transfer->assigned_to }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>تاريخ الاستلام</th>
                            <td>{{ $transfer->receive_date->format('Y-m-d') }}</td>
                        </tr>
                        @if(!$transfer->is_ownership_transfer && !$transfer->is_referral)
                            @if($transfer->return_date)
                            <tr>
                                <th>تاريخ الإعادة</th>
                                <td>{{ $transfer->return_date->format('Y-m-d') }}</td>
                            </tr>
                            @endif
                        @endif
                        <tr>
                            <th>ملاحظات</th>
                            <td>{!! nl2br(e($transfer->notes ?: 'لا توجد ملاحظات')) !!}</td>
                        </tr>
                        <tr>
                            <th>تاريخ التسجيل</th>
                            <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        <tr>
                            <th>المستخدم</th>
                            <td>{{ $transfer->user->name }}</td>
                        </tr>
                        @if($transfer->return_date && $transfer->completer)
                        <tr>
                            <th>تمت الإعادة بواسطة</th>
                            <td>{{ $transfer->completer->name }}</td>
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
                @if($transfer->attachments->count() > 0)
                <div class="list-group">
                    @foreach($transfer->attachments as $attachment)
                    <a href="{{ route('attachments.download', $attachment) }}" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark"></i> {{ $attachment->file_name }}
                        @if($attachment->type == 'transfer_document')
                        <span class="badge bg-primary">مستند المناقلة</span>
                        @elseif($attachment->type == 'return_document')
                        <span class="badge bg-success">مستند الإعادة</span>
                        @elseif($attachment->type == 'ownership_transfer_document')
                        <span class="badge bg-dark">مستند نقل الملكية</span>
                        @elseif($attachment->type == 'external_referral_document')
                        <span class="badge bg-info">مستند الإحالة الخارجية</span>
                        @endif
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

<!-- Complete Transfer Modal -->
@if(!$transfer->return_date && !$transfer->is_ownership_transfer && !$transfer->is_referral && auth()->user()->hasRole(['admin', 'vehicles_dept']))
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('transfers.complete', $transfer) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalLabel">إكمال المناقلة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="return_date" class="form-label required-field">تاريخ الإعادة</label>
                        <input type="date" name="return_date" id="return_date" class="form-control" 
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="notes" class="form-label">ملاحظات</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="attachment" class="form-label required-field">مستند الإعادة</label>
                        <input type="file" name="attachment" id="attachment" class="form-control" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small class="form-text text-muted">يجب إرفاق مستند الإعادة (PDF, DOC, DOCX, JPG, PNG)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">تأكيد الإكمال</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection