<?php

namespace App\Observers;

use App\Models\StatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StatusTrackingObserver
{
    /**
     * Handle the model "updating" event.
     */
    public function updating(Model $model): void
    {
        // Check if status field has changed
        if ($model->isDirty('status')) {
            StatusHistory::create([
                'statusable_type' => get_class($model),
                'statusable_id' => $model->id,
                'field_name' => 'status',
                'old_value' => $model->getOriginal('status'),
                'new_value' => $model->status,
                'changed_by' => Auth::id(),
            ]);
        }
    }
}
