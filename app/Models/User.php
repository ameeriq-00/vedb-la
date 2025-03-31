<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'directorate_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function directorate()
    {
        return $this->belongsTo(Directorate::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function vehicleStatuses()
    {
        return $this->hasMany(VehicleStatus::class);
    }

    public function vehicleTransfers()
    {
        return $this->hasMany(VehicleTransfer::class);
    }

    public function editRequests()
    {
        return $this->hasMany(EditRequest::class);
    }

    public function approvedRequests()
    {
        return $this->hasMany(EditRequest::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}