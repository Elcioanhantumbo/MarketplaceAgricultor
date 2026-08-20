<?php

namespace App\Models\Concerns;

use Illuminate\Database\Query\Expression;

/**
 * Mantém a coluna geography(Point,4326) `geo_location` em sincronia com as
 * colunas simples `latitude`/`longitude`, que continuam a ser a fonte de
 * verdade para exibição e edição nos formulários. `geo_location` existe só
 * para pesquisa espacial real via PostGIS (ST_DWithin/ST_Distance) — ver
 * migration 2026_08_18_090000_add_postgis_geography_columns.
 */
trait HasGeoLocation
{
    protected static function bootHasGeoLocation(): void
    {
        static::saving(function ($model): void {
            if (! $model->isDirty(['latitude', 'longitude'])) {
                return;
            }

            $model->geo_location = $model->latitude !== null && $model->longitude !== null
                ? new Expression(sprintf(
                    'ST_SetSRID(ST_MakePoint(%F, %F), 4326)::geography',
                    (float) $model->longitude,
                    (float) $model->latitude,
                ))
                : null;
        });
    }
}