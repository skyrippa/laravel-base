<?php

namespace App\Models\Traits;

use App\Utils\Helpers;

trait SanitizeTrait
{
    protected static function bootSanitizeTrait()
    {
        static::saving(function ($model) {
            self::sanitizeParams($model);
        });
    }

    private static function sanitizeParams($model)
    {
        isset($model->phone) ? $model->phone = Helpers::sanitizeString($model->phone) : null;

        isset($model->cel_phone) ? $model->cel_phone = Helpers::sanitizeString($model->cel_phone) : null;

        isset($model->zip_code) ? $model->zip_code = Helpers::sanitizeString($model->zip_code) : null;

        isset($model->document) ? $model->document = Helpers::sanitizeString($model->document) : null;

        isset($model->pix_key) ? $model->pix_key = $model->pix_key : null;

        isset($model->whatsapp) ? $model->whatsapp = Helpers::sanitizeString($model->whatsapp) : null;

        isset($model->plate) ? $model->plate = Helpers::sanitizeStringWithLetters($model->plate) : null;
    }
}
