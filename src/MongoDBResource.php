<?php

namespace FrancescoPrisco\NovaMongoDB;

use Laravel\Nova\Resource as NovaResource;
use Laravel\Nova\Http\Requests\NovaRequest;
use MongoDB\Laravel\Eloquent\Model;

abstract class MongoDBResource extends NovaResource
{
    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query;
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function detailQuery(NovaRequest $request, $query)
    {
        return parent::detailQuery($request, $query);
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return parent::relatableQuery($request, $query);
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return parent::label();
    }

    /**
     * Determine if this resource is searchable.
     *
     * @return bool
     */
    public static function searchable()
    {
        return !empty(static::$search);
    }

    /**
     * Apply the search query to the query.
     *
     * @param  \Illuminate\Contracts\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Contracts\Database\Eloquent\Builder
     */
    protected static function applySearch(\Illuminate\Contracts\Database\Eloquent\Builder $query, string $search): \Illuminate\Contracts\Database\Eloquent\Builder
    {
        if (empty(static::$search)) {
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            foreach (static::$search as $column) {
                // MongoDB uses regex for text search
                $query->orWhere($column, 'regexp', "/{$search}/i");
            }
        });
    }

    /**
     * Build an associatable query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function associatableQuery(NovaRequest $request, $query)
    {
        return static::relatableQuery($request, $query);
    }
}
