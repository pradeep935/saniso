<?php

namespace Botble\MultiBranchInventory\Policies;

use Botble\ACL\Models\User;
use Botble\MultiBranchInventory\Models\Branch;

class BranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('branches.index');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Branch $branch): bool
    {
        // Users with full access can view any branch.
        if ($user->hasPermission('branches.index')) {
            return true;
        }

        // Store managers can only view their own branch.
        return $user->branch_id === $branch->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('branches.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $branch): bool
    {
        // Super users can edit any branch
        if ($user->hasPermission('branches.edit_all')) {
            return true;
        }
        
        // Store managers can only edit their own branch if they have the base permission.
        if ($user->hasPermission('branches.edit')) {
            return $user->branch_id === $branch->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $branch): bool
    {
        // Super users can delete any branch
        if ($user->hasPermission('branches.destroy_all')) {
            return true;
        }

        // Store managers can only delete their own branch if they have the base permission.
        if ($user->hasPermission('branches.destroy')) {
            return $user->branch_id === $branch->id;
        }

        return false;
    }
}
