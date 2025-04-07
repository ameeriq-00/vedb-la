<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EditRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'approved_by',
        'field_name',
        'old_value',
        'new_value',
        'status',
        'notes',
        'approval_date',
    ];

    protected $casts = [
        'approval_date' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['vehicle_id', 'field_name', 'old_value', 'new_value', 'status'])
            ->setDescriptionForEvent(function(string $eventName) {
                if ($eventName == 'created') {
                    return 'تم إنشاء طلب تعديل جديد';
                } elseif ($eventName == 'updated' && $this->isDirty('status')) {
                    if ($this->status == 'approved') {
                        return 'تمت الموافقة على طلب تعديل';
                    } elseif ($this->status == 'rejected') {
                        return 'تم رفض طلب تعديل';
                    }
                }

                return 'تم ' . $this->getEventArabicName($eventName) . ' طلب تعديل';
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    private function getEventArabicName(string $eventName): string
    {
        $events = [
            'created' => 'إنشاء',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
        ];

        return $events[$eventName] ?? $eventName;
    }
}
