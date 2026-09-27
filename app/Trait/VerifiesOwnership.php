<?php

namespace App\Trait;

use Illuminate\Database\Eloquent\Builder;

trait VerifiesOwnership
{
    protected function ownershipField(): string
    {
        return 'vendor_id';
    }

    protected function isAdminUser(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole(['admin', 'demo_admin']);
    }

    protected function applyOwnershipScope(Builder $query): Builder
    {
        if ($this->isAdminUser()) {
            return $query;
        }

        $user = auth()->user();
        $field = $this->ownershipField();

        if ($field === 'created_by') {
            return $query->where('created_by', $user->id);
        }

        if ($user->hasRole('vendor')) {
            return $query->where($field, $user->id);
        }

        if ($user->hasRole('doctor')) {
            return $query->where('doctor_id', $user->id);
        }

        return $query->where($field, $user->id);
    }

    protected function verifyOwnership($model): bool
    {
        if ($this->isAdminUser()) {
            return true;
        }

        $user = auth()->user();
        $field = $this->ownershipField();

        if ($field === 'created_by') {
            return (int) $model->created_by === (int) $user->id;
        }

        if ($user->hasRole('vendor')) {
            return (int) $model->{$field} === (int) $user->id;
        }

        if ($user->hasRole('doctor') && isset($model->doctor_id)) {
            return (int) $model->doctor_id === (int) $user->id;
        }

        return (int) $model->{$field} === (int) $user->id;
    }

    protected function abortUnlessOwner($model): void
    {
        if (! $this->verifyOwnership($model)) {
            abort(403, __('messages.permission_denied'));
        }
    }
}
