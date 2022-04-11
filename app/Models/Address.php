<?php

namespace App\Models;

use App\Models\Traits\SanitizeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;
use \OwenIt\Auditing\Auditable;

class Address extends Model implements AuditableContracts
{
    use HasFactory, SoftDeletes, SanitizeTrait, Auditable;

    protected $fillable = [
        'client_id',
        'zip_code',
        'street',
        'house_number',
        'neighborhood',
        'complement',
        'observation',
        'phone',
        'state_id',
        'city_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
