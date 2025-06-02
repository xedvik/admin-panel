<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;
    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender', 'addresses', 'accepts_marketing', 'email_verified_at', 'is_active'];
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
