<?php

namespace FrancescoPrisco\NovaMongoDB\Models;

use MongoDB\Laravel\Eloquent\Model;

class ActionEvent extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mongodb';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'action_events';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fields' => 'array',
        'original' => 'array',
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'batch_id',
        'user_id',
        'name',
        'actionable_type',
        'actionable_id',
        'target_type',
        'target_id',
        'model_type',
        'model_id',
        'fields',
        'status',
        'exception',
        'original',
        'changes',
        'created_at',
        'updated_at',
    ];
}
