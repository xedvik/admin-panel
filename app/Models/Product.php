<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['date_of_birth', 'name', 'owner_id', 'type'];
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }
}
