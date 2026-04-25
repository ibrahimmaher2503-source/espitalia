<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\World\Models\City;
use Modules\World\Models\Country;
use Modules\World\Models\State;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;

class Address extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'postal_code',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'address_line_1',
        'address_line_2',
        'first_name',
        'last_name',
        'is_primary',
        'governorate_id',
        'city_id',
        'label',
        'phone',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'governorate_id' => 'integer',
        'city_id' => 'integer',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

    public function city_data()
    {
        return $this->belongsTo(City::class, 'city');
    }

    public function state_data()
    {
        return $this->belongsTo(State::class, 'state');
    }

    public function country_data()
    {
        return $this->belongsTo(Country::class, 'country');
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function governorateCity()
    {
        return $this->belongsTo(GovernorateCity::class, 'city_id');
    }
}
