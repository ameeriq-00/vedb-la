<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'status_type',
        'old_status',
        'new_status',
        'letter_number',
        'letter_date',
        'notes',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    
    // دالة مساعدة للحصول على نوع المرفق بالعربية
    public function getAttachmentTypeName($type)
    {
        $types = [
            'seizure_letter' => 'كتاب الحجز',
            'release_decision' => 'قرار الإفراج',
            'confiscation_letter' => 'كتاب المصادرة',
            'final_degree_decision' => 'قرار اكتساب الدرجة',
            'valuation_document' => 'وثيقة التثمين',
            'authentication_letter' => 'كتاب المصادقة',
            'donation_letter' => 'كتاب الإهداء',
            'registration_document' => 'وثيقة الترقيم',
        ];
        
        return $types[$type] ?? 'مرفق';
    }
}