<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'destination_directorate_id',
        'recipient_name',
        'recipient_id_number',
        'recipient_phone',
        'recipient_entity',
        'assigned_to',
        'receive_date',
        'return_date',
        'is_external',
        'is_ownership_transfer',
        'is_referral',
        'notes',
        'completed_by'
    ];

    protected $casts = [
        'receive_date' => 'date',
        'return_date' => 'date',
        'is_external' => 'boolean',
        'is_ownership_transfer' => 'boolean',
        'is_referral' => 'boolean',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function destinationDirectorate()
    {
        return $this->belongsTo(Directorate::class, 'destination_directorate_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Scope for active transfers
    public function scopeActive($query)
    {
        return $query->whereNull('return_date');
    }

    // Scope for completed transfers
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('return_date');
    }

    // Scope for ownership transfers
    public function scopeOwnershipTransfer($query)
    {
        return $query->where('is_ownership_transfer', true);
    }

    // Scope for external referrals
    public function scopeExternalReferral($query)
    {
        return $query->where('is_referral', true);
    }

    // Scope for regular transfers (not ownership transfers or referrals)
    public function scopeRegular($query)
    {
        return $query->where('is_ownership_transfer', false)
                     ->where('is_referral', false);
    }

    // Helper method to get status as text
    public function getStatusAttribute()
    {
        if ($this->is_ownership_transfer) {
            return 'نقل ملكية';
        }
        
        if ($this->is_referral) {
            return 'إحالة خارجية';
        }
        
        if ($this->return_date) {
            return 'مكتملة';
        }
        
        return 'جارية';
    }

    // Helper method to check if transfer is active
    public function isActive()
    {
        return $this->return_date === null;
    }

    // Get appropriate attachment for display
    public function getMainDocument()
    {
        if ($this->is_ownership_transfer) {
            return $this->attachments->where('type', 'ownership_transfer_document')->first();
        }
        
        if ($this->is_referral) {
            return $this->attachments->where('type', 'external_referral_document')->first();
        }
        
        return $this->attachments->where('type', 'transfer_document')->first();
    }

    // Get return document if exists
    public function getReturnDocument()
    {
        return $this->attachments->where('type', 'return_document')->first();
    }
}