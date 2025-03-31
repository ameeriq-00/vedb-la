<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EditRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class EditRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view edit requests');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EditRequest $editRequest)
    {
        // Admin and verifier can view all requests
        if ($user->hasRole(['admin', 'verifier'])) {
            return true;
        }
        
        // Users can view their own requests
        if ($editRequest->user_id === $user->id) {
            return true;
        }
        
        // Users can view requests for their directorate's vehicles
        return $editRequest->vehicle->directorate_id === $user->directorate_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create edit requests');
    }

    /**
     * Determine whether the user can approve/reject requests.
     */
    public function approve(User $user, EditRequest $editRequest)
    {
        // Only admin and verifier can approve/reject
        if (!$user->hasPermissionTo('approve edit requests')) {
            return false;
        }
        
        // Can only approve pending requests
        if ($editRequest->status !== 'pending') {
            return false;
        }
        
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EditRequest $editRequest)
    {
        // Only admin can delete edit requests
        return $user->hasRole('admin');
    }
}