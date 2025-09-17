<?php

namespace App\Models;

use App\Models\Components\Question\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAttempt extends Model
{
    use HasFactory;

    protected $fillable=['user_id','question_id','learning_objective_id','pass'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
