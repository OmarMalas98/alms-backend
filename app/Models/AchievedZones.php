<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievedZones extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','zone_id'];



    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }}
