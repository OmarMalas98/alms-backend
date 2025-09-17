<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievedObjective extends Model
{
    use HasFactory;
    protected $fillable=['user_id','learning_objective_id','score'];
    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
