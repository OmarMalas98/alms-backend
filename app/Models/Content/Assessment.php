<?php

namespace App\Models\Content;

use App\Models\LearningObjective;
use App\Models\Other\Status;
use App\Models\Question\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable=['title','description','duration','status_id','content_id','creator_id','learning_objective_id'];
    protected $hidden = ['id'];

    public function status(){
        return $this->belongsTo(Status::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    public function content()
    {
        return $this->belongsTo(Content::class,'content_id');
    }

    public function learningObjective()
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function questions(){
        return $this->hasMany(Question::class);
    }
    public function assessmentAttempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }
}
