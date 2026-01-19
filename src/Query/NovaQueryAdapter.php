<?php

namespace FrancescoPrisco\NovaMongoDB\Query;

use Illuminate\Database\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Builder as MongoBuilder;

class NovaQueryAdapter
{
    /**
     * Convert Nova's SQL-style where clauses to MongoDB format.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyFilters($query, array $filters)
    {
        foreach ($filters as $filter) {
            $method = $filter['operator'] ?? '=';
            $column = $filter['column'];
            $value = $filter['value'];

            match ($method) {
                '=' => $query->where($column, '=', $value),
                '!=' => $query->where($column, '!=', $value),
                '>' => $query->where($column, '>', $value),
                '>=' => $query->where($column, '>=', $value),
                '<' => $query->where($column, '<', $value),
                '<=' => $query->where($column, '<=', $value),
                'like' => $query->where($column, 'regexp', self::convertLikeToRegex($value)),
                'in' => $query->whereIn($column, (array) $value),
                'not in' => $query->whereNotIn($column, (array) $value),
                'between' => $query->whereBetween($column, (array) $value),
                'null' => $query->whereNull($column),
                'not null' => $query->whereNotNull($column),
                default => $query->where($column, $value),
            };
        }

        return $query;
    }

    /**
     * Convert SQL LIKE pattern to MongoDB regex.
     *
     * @param  string  $pattern
     * @return string
     */
    protected static function convertLikeToRegex(string $pattern): string
    {
        // Convert SQL wildcards to regex
        $pattern = str_replace(['%', '_'], ['.*', '.'], $pattern);
        
        return "/^{$pattern}$/i";
    }

    /**
     * Apply ordering to MongoDB query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $column
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyOrdering($query, string $column, string $direction = 'asc')
    {
        return $query->orderBy($column, $direction);
    }

    /**
     * Handle relationships for MongoDB.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $relations
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function loadRelations($query, array $relations)
    {
        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query;
    }

    /**
     * Convert Nova's search query to MongoDB regex search.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $searchColumns
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applySearch($query, array $searchColumns, string $search)
    {
        if (empty($searchColumns) || empty($search)) {
            return $query;
        }

        return $query->where(function ($query) use ($searchColumns, $search) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'regexp', "/{$search}/i");
            }
        });
    }

    /**
     * Handle aggregate queries for MongoDB.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $function
     * @param  string|null  $column
     * @return mixed
     */
    public static function aggregate($query, string $function, ?string $column = null)
    {
        return match ($function) {
            'count' => $query->count(),
            'sum' => $query->sum($column),
            'avg' => $query->avg($column),
            'min' => $query->min($column),
            'max' => $query->max($column),
            default => $query->count(),
        };
    }
}
