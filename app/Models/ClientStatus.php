<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
