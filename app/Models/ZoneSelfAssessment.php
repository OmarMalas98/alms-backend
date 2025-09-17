<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneSelfAssessment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','zone_id','rating'];



    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

}
