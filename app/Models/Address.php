<?php

namespace App\Models;

use App\Models\Traits\SanitizeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes, SanitizeTrait;

    protected $table = 'des_customer_address';

    protected $fillable = [
        'customer_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
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
