<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleTransfer;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleTransferPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view transfers');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VehicleTransfer $vehicleTransfer)
    {
        // Admin and verifier can view all transfers
        if ($user->hasRole(['admin', 'verifier'])) {
            return true;
        }
        
        // Vehicles department can view all transfers
        if ($user->hasRole('vehicles_dept')) {
            return true;
        }
        
        // Recipients can only view transfers to their directorate
        if ($user->hasRole('recipient')) {
            return $vehicleTransfer->destination_directorate_id === $user->directorate_id;
        }
        
        // Data entry users can only view their directorate's transfers
        return $vehicleTransfer->vehicle->directorate_id === $user->directorate_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, VehicleTransfer $vehicleTransfer)
    {
        // Check if user has permission to create transfers
        if (!$user->hasPermissionTo('create transfers')) {
            return false;
        }
        
        // Admin can create all transfers
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Vehicles department can create transfers for authenticated or final degree vehicles
        if ($user->hasRole('vehicles_dept')) {
            $vehicle = $vehicleTransfer->vehicle;
            
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
        
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VehicleTransfer $vehicleTransfer)
    {
        // Only admin and vehicles department can update transfers
        return $user->hasRole(['admin', 'vehicles_dept']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VehicleTransfer $vehicleTransfer)
    {
        // Only admin can delete transfers
        return $user->hasRole('admin');
    }
}