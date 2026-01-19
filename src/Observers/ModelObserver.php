<?php

namespace FrancescoPrisco\NovaMongoDB\Observers;

use FrancescoPrisco\NovaMongoDB\Models\ActionEvent;
use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logAction('Create', $model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->logAction('Update', $model);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logAction('Delete', $model);
    }

    /**
     * Log the action to MongoDB.
     */
    protected function logAction(string $action, Model $model): void
    {
        try {
            $user = auth()->user();
            
            ActionEvent::create([
                'batch_id' => (string) \Illuminate\Support\Str::orderedUuid(),
                'user_id' => $user ? (string) $user->getKey() : null,
                'name' => $action,
                'actionable_type' => get_class($model),
                'actionable_id' => (string) $model->getKey(),
                'target_type' => get_class($model),
                'target_id' => (string) $model->getKey(),
                'model_type' => get_class($model),
                'model_id' => (string) $model->getKey(),
                'fields' => json_encode($model->getDirty()),
                'status' => 'finished',
                'exception' => '',
                'original' => $model->wasRecentlyCreated ? [] : $model->getOriginal(),
                'changes' => $model->getChanges(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking the main operation
            \Log::warning('Failed to log action event: ' . $e->getMessage());
        }
    }
}
