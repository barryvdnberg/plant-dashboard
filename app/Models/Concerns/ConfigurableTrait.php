<?php

namespace App\Models\Concerns;

use RuntimeException;

/**
 * Provides configuration capabilities to models.
 * 
 * This trait allows traits
 * to safely read static or instance config from models with
 * fallback defaults and optional merging.
 */
trait ConfigurableTrait
{
    /**
     * Get a configuration value from the model or trait with fallback.
     *
     * @param  string  $property   The property name on the model (e.g. 'localizationDefaults')
     * @param  mixed   $fallback   Default value if not found
     * @param  bool    $merge      Whether to merge arrays instead of overwrite
     * @param  bool    $staticOnly Whether to only check static properties
     * @return mixed
     */
    protected static function getModelConfig(
        string $property,
        mixed $fallback = null,
        bool $merge = false,
        bool $staticOnly = false
    ): mixed
    {
        $model = static::class;

        if (property_exists($model, $property)) {
            $value = $model::$$property ?? null;
        }
        elseif (!$staticOnly && property_exists(new $model, $property)) {
            $instance = new $model;
            $value = $instance->$property ?? null;
        }
        else {
            $value = null;
        }

        if ($merge && is_array($fallback)) {
            return array_merge($fallback, $value ?? []);
        }

        return $value ?? $fallback;
    }

    /**
     * Require a static config value or throw.
     *
     * @param  string  $property
     * @param  string|null  $message
     * @return mixed
     * @throws \RuntimeException
     */
    protected static function requireStaticConfig(string $property, ?string $message = null): mixed
    {
        $model = static::class;

        if (!property_exists($model, $property) || !isset($model::$$property)) {
            $message ??= "Required static configuration '{$property}' not found on model '{$model}'.";
            throw new RuntimeException($message);
        }   

        return $model::$$property;
    }
}