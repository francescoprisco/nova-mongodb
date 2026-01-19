<?php

namespace FrancescoPrisco\NovaMongoDB\Traits;

trait HandlesMorphRelations
{
    /**
     * Convert SQL-style morph relations to MongoDB format.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function convertMorphAttributes(array $attributes): array
    {
        $converted = [];

        foreach ($attributes as $key => $value) {
            // Handle morphs - convert {name}_type and {name}_id to proper format
            if (preg_match('/^(.+)_type$/', $key, $matches)) {
                $baseKey = $matches[1];
                $idKey = $baseKey . '_id';
                
                if (isset($attributes[$idKey])) {
                    $converted[$baseKey] = [
                        'type' => $value,
                        'id' => $attributes[$idKey],
                    ];
                    continue;
                }
            }

            // Skip _id keys if they're part of a morph
            if (preg_match('/^(.+)_id$/', $key, $matches)) {
                $typeKey = $matches[1] . '_type';
                if (isset($attributes[$typeKey])) {
                    continue;
                }
            }

            $converted[$key] = $value;
        }

        return $converted;
    }

    /**
     * Expand MongoDB morph format to SQL-style attributes.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function expandMorphAttributes(array $attributes): array
    {
        $expanded = [];

        foreach ($attributes as $key => $value) {
            if (is_array($value) && isset($value['type']) && isset($value['id'])) {
                // Expand morph format to SQL-style
                $expanded[$key . '_type'] = $value['type'];
                $expanded[$key . '_id'] = $value['id'];
            } else {
                $expanded[$key] = $value;
            }
        }

        return $expanded;
    }

    /**
     * Get morph attribute value.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getMorphAttribute($key)
    {
        $value = $this->getAttribute($key);

        if (is_array($value) && isset($value['type']) && isset($value['id'])) {
            // Return in SQL-style format for Nova compatibility
            return [
                $key . '_type' => $value['type'],
                $key . '_id' => $value['id'],
            ];
        }

        return $value;
    }
}
