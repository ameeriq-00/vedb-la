<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiclePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view vehicles');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vehicle $vehicle)
    {
        // Admin and verifier can view all vehicles
        if ($user->hasRole(['admin', 'verifier'])) {
            return true;
        }
        
        // Vehicles department can view government vehicles and confiscated vehicles with final degree or authentication
        if ($user->hasRole('vehicles_dept')) {
            if ($vehicle->type === 'government') {
                return true;
            }
            
            if ($vehicle->type === 'confiscated' && 
                ($vehicle->final_degree_status === 'مكتسبة' || 
                 $vehicle->authentication_status === 'تمت المصادقة عليها')) {
                return true;
            }
            
            return false;
        }
        
        // Recipients can only view vehicles transferred to their directorate
        if ($user->hasRole('recipient')) {
            return $vehicle->transfers()
                ->where('destination_directorate_id', $user->directorate_id)
                ->exists();
        }
        
        // Data entry users can only view their directorate's vehicles
        return $vehicle->directorate_id === $user->directorate_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create vehicles');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vehicle $vehicle)
    {
        // Only admin and verifier can edit directly
        if (!$user->hasPermissionTo('edit vehicles')) {
            return false;
        }
        
        // Admin can edit all vehicles
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Verifier can edit all vehicles
        if ($user->hasRole('verifier')) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vehicle $vehicle)
    {
        // Only admin can delete
        return $user->hasRole('admin');
    }
}