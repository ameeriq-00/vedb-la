<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'directorate_id',
        'user_id',
        'type',
        'vehicle_type',
        'vehicle_name',
        'model',
        'chassis_number',
        'vehicle_number',
        'province',
        'color',
        'vehicle_condition',
        'accessories',
        'defects',
        'missing_parts',
        'defendant_name',
        'legal_article',
        'seizure_status',
        'seizure_letter_number',
        'seizure_letter_date',
        'release_decision_number',
        'release_decision_date',
        'confiscation_letter_number',
        'confiscation_letter_date',
        'final_degree_status',
        'decision_number',
        'decision_date',
        'valuation_status',
        'valuation_amount',
        'authentication_status',
        'authentication_number',
        'authentication_date',
        'donation_status',
        'donation_letter_number',
        'donation_letter_date',
        'donation_entity',
        'government_registration_status',
        'registration_letter_number',
        'registration_letter_date',
        'government_registration_number',
        'source',
        'import_letter_number',
        'import_letter_date',
        'notes',
        'is_externally_referred',
        'external_entity',
    ];

    protected $casts = [
        'accessories' => 'array',
        'defects' => 'array',
        'seizure_letter_date' => 'date',
        'release_decision_date' => 'date',
        'confiscation_letter_date' => 'date',
        'decision_date' => 'date',
        'authentication_date' => 'date',
        'import_letter_date' => 'date',
        'valuation_amount' => 'decimal:2',
        'donation_letter_date' => 'date',
        'registration_letter_date' => 'date',
        'is_externally_referred' => 'boolean',
    ];

    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statuses()
    {
        return $this->hasMany(VehicleStatus::class);
    }

    public function transfers()
    {
        return $this->hasMany(VehicleTransfer::class);
    }

    public function editRequests()
    {
        return $this->hasMany(EditRequest::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function getAccessoriesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setAccessoriesAttribute($value)
    {
        $this->attributes['accessories'] = $value ? json_encode($value) : null;
    }

    public function getDefectsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setDefectsAttribute($value)
    {
        $this->attributes['defects'] = $value ? json_encode($value) : null;
    }

    // Scope for confiscated vehicles
    public function scopeConfiscated($query)
    {
        return $query->where('type', 'confiscated');
    }

    // Scope for government vehicles
    public function scopeGovernment($query)
    {
        return $query->where('type', 'government');
    }

    // Scope for user's directorate
    public function scopeForUserDirectorate($query, $user)
    {
        if ($user->hasRole(['admin', 'verifier'])) {
            return $query; // Can see all vehicles
        }
        
        if ($user->hasRole('vehicles_dept')) {
            // Vehicles department can see government vehicles and confiscated vehicles that have final degree or authenticated
            return $query->where(function($q) {
                $q->where('type', 'government')
                  ->orWhere(function($q2) {
                      $q2->where('type', 'confiscated')
                         ->where(function($q3) {
                             $q3->where('final_degree_status', 'مكتسبة')
                                ->orWhere('authentication_status', 'تمت المصادقة عليها')
                                ->orWhere('valuation_status', 'مثمنة');
                         });
                  });
            });
        }
        
        if ($user->hasRole('recipient')) {
            // Recipients can only see vehicles transferred to their directorate
            return $query->whereHas('transfers', function($q) use ($user) {
                $q->where('destination_directorate_id', $user->directorate_id);
            });
        }
        
        // Default for data entry - only see their directorate's vehicles
        return $query->where('directorate_id', $user->directorate_id);
    }

    // Helper method to check if vehicle can proceed to the next stage
    public function canProceedToStage($stage)
    {
        switch ($stage) {
            case 'final_degree':
                return $this->type === 'confiscated' && $this->seizure_status === 'مصادرة';
            
            case 'valuation':
                return $this->type === 'confiscated' && $this->final_degree_status === 'مكتسبة';
            
            case 'authentication':
                return $this->type === 'confiscated' && $this->valuation_status === 'مثمنة';
            
            case 'donation':
                return $this->type === 'confiscated' && $this->authentication_status === 'تمت المصادقة عليها';
            
            case 'government_registration':
                return $this->type === 'confiscated' && $this->donation_status === 'مهداة';
            
            case 'transfer':
                return ($this->type === 'government') || 
                       ($this->type === 'confiscated' && 
                        ($this->final_degree_status === 'مكتسبة' || 
                         $this->authentication_status === 'تمت المصادقة عليها'));
            
            default:
                return false;
        }
    }

    // Get vehicle images
    public function getImages()
    {
        return $this->attachments->where('type', 'vehicle_image');
    }

    // Get vehicle documents
    public function getDocuments()
    {
        return $this->attachments->where('type', 'vehicle_document');
    }

    // Get latest transfers
    public function getActiveTransfers()
    {
        return $this->transfers()
            ->whereNull('return_date')
            ->where('is_ownership_transfer', false)
            ->where('is_referral', false)
            ->get();
    }

    // Get pending edit requests
    public function getPendingEditRequests()
    {
        return $this->editRequests()->where('status', 'pending')->get();
    }

    // Get current stage of vehicle workflow
    public function getCurrentStage()
    {
        if ($this->type === 'government') {
            return 'government';
        }
        
        if ($this->type === 'confiscated') {
            if ($this->is_externally_referred) {
                return 'externally_referred';
            }
            
            if ($this->government_registration_status === 'مرقمة') {
                return 'registered';
            }
            
            if ($this->donation_status === 'مهداة') {
                return 'donated';
            }
            
            if ($this->authentication_status === 'تمت المصادقة عليها') {
                return 'authenticated';
            }
            
            if ($this->valuation_status === 'مثمنة') {
                return 'valued';
            }
            
            if ($this->final_degree_status === 'مكتسبة') {
                return 'final_degree';
            }
            
            if ($this->seizure_status === 'مصادرة') {
                return 'confiscated';
            }
            
            if ($this->seizure_status === 'مفرج عنها') {
                return 'released';
            }
            
            return 'seized';
        }
        
        return 'unknown';
    }

    // Check if vehicle is transferable
    public function isTransferable()
    {
        // العجلات المحالة لجهات خارجية لا يمكن نقلها
        if ($this->is_externally_referred) {
            return false;
        }
        
        return $this->canProceedToStage('transfer');
    }

    // Check if user can update this vehicle
    public function canBeUpdatedBy($user)
    {
        // Admin and verifier can update any vehicle
        if ($user->hasRole(['admin', 'verifier'])) {
            return true;
        }
        
        // Data entry can only update vehicles in their directorate
        if ($user->hasRole('data_entry') && $this->directorate_id === $user->directorate_id) {
            return true;
        }
        
        // Vehicles department can update vehicles in the final stages
        if ($user->hasRole('vehicles_dept') && $this->isTransferable()) {
            return true;
        }
        
        return false;
    }

    // Get status updates for a specific status type
    public function getStatusUpdates($statusType)
    {
        return $this->statuses()
            ->where('status_type', $statusType)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    // Get all attachments from statuses
    public function getAllStatusAttachments()
    {
        $statusIds = $this->statuses->pluck('id')->toArray();
        
        if (empty($statusIds)) {
            return collect([]);
        }
        
        return Attachment::where('attachable_type', 'App\Models\VehicleStatus')
            ->whereIn('attachable_id', $statusIds)
            ->get();
    }
}