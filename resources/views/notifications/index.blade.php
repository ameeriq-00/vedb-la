@extends('layouts.app')

@section('title', 'الإشعارات')

@section('actions')
    @if($unreadCount > 0)
    <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-all"></i> تحديد الكل كمقروء
        </button>
    </form>
    @endif
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">قائمة الإشعارات</h5>
        <span class="badge bg-primary">{{ $unreadCount }} غير مقروء</span>
    </div>
    <div class="card-body">
        @if($notifications->count() > 0)
            <div class="list-group">
                @foreach($notifications as $notification)
                <div class="list-group-item list-group-item-action {{ is_null($notification->read_at) ? 'list-group-item-primary' : '' }}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">{{ $notification->data['title'] ?? 'إشعار' }}</h6>
                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                
                    <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>
                
                    @if(isset($notification->data['vehicle_type']))
                    <p class="mb-1">
                        <strong>العجلة:</strong> {{ $notification->data['vehicle_type'] }}
                        @if(isset($notification->data['vehicle_number']) && $notification->data['vehicle_number'])
                         - {{ $notification->data['vehicle_number'] }}
                        @endif
                        @if(isset($notification->data['vehicle_name']) && $notification->data['vehicle_name'])
                         ({{ $notification->data['vehicle_name'] }})
                        @endif
                    </p>
                    @endif
                
                    @if(isset($notification->data['old_status']) && isset($notification->data['new_status']))
                    <p class="mb-1">
                        <strong>التغيير:</strong> من {{ $notification->data['old_status'] }}
                        <i class="bi bi-arrow-right"></i>
                        إلى {{ $notification->data['new_status'] }}
                    </p>
                    @endif
                
                    @if(isset($notification->data['causer_name']))
                    <p class="mb-1">
                        <strong>تم بواسطة:</strong> {{ $notification->data['causer_name'] }}
                    </p>
                    @endif
                
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <a href="{{
                            isset($notification->data['action_url']) ? $notification->data['action_url'] :
                            (isset($notification->data['vehicle_id']) ? route('vehicles.show', $notification->data['vehicle_id']) :
                            (isset($notification->data['transfer_id']) ? route('transfers.show', $notification->data['transfer_id']) :
                            (isset($notification->data['edit_request_id']) ? route('edit-requests.show', $notification->data['edit_request_id']) :
                            '#')))
                        }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> {{
                            isset($notification->data['vehicle_id']) ? 'عرض العجلة' :
                            (isset($notification->data['transfer_id']) ? 'عرض المناقلة' :
                            (isset($notification->data['edit_request_id']) ? 'عرض طلب التعديل' :
                            'عرض التفاصيل'))
                            }}
                        </a>
                    
                        @if(is_null($notification->read_at))
                        <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-secondary">
                                <i class="bi bi-check"></i> تحديد كمقروء
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <h4>لا توجد إشعارات</h4>
                <p class="text-muted">ستظهر هنا الإشعارات المتعلقة بتحديثات النظام</p>
            </div>
        @endif
    </div>
</div>
@endsection
