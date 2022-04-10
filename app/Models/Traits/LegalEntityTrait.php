<?php

namespace App\Models\Traits;

use App\Utils\Helpers;

trait LegalEntityTrait
{
    protected static function bootLegalEntityTrait()
    {
        static::saving(function ($model) {
            $model->legal_entity = Helpers::legalEntity($model->document);
        });
    }
}
