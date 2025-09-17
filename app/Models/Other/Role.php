<?php

namespace App\Models\Other;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Define the fields that can be mass-assigned
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
