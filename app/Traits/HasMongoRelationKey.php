<?php

namespace App\Traits;

use MongoDB\BSON\ObjectId;

trait HasMongoRelationKey
{
    /**
     * MongoDB's Eloquent model exposes the primary key as a string through
     * getKey(), while relation foreign keys in this application are stored as
     * BSON ObjectIds. Relationships use this accessor as their local key so
     * both lazy and eager queries compare ObjectId values.
     */
    public function getMongoRelationIdAttribute(): ?ObjectId
    {
        $keyName = $this->getKeyName();
        $value = $this->getRawOriginal($keyName)
            ?? ($this->getAttributes()[$keyName] ?? null);

        if ($value instanceof ObjectId) {
            return $value;
        }

        if (is_string($value) && preg_match('/^[a-f0-9]{24}$/i', $value)) {
            return new ObjectId($value);
        }

        return null;
    }
}
