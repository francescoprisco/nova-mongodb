<?php

namespace FrancescoPrisco\NovaMongoDB;

use MongoDB\Laravel\Connection as BaseConnection;

class MongoDBConnection extends BaseConnection
{
    /**
     * Override transaction method to prevent nested transaction errors.
     * MongoDB doesn't handle nested transactions well, so we execute
     * callbacks directly without transaction wrapping.
     *
     * @param  \Closure  $callback
     * @param  int  $attempts
     * @param  array  $options
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(\Closure $callback, $attempts = 1, array $options = []): mixed
    {
        // Check if we're already in a transaction
        // If so, just execute the callback without starting a new transaction
        try {
            // Try to execute with transaction for top-level calls
            return parent::transaction($callback, $attempts, $options);
        } catch (\MongoDB\Driver\Exception\RuntimeException $e) {
            // If we get "Transaction already in progress", just execute the callback
            if (str_contains($e->getMessage(), 'Transaction already in progress')) {
                return $callback();
            }
            throw $e;
        }
    }
}
