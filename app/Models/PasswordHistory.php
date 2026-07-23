<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['customer_id', 'password'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
