<?php

namespace App\Models\Components\Question\MultiChoice;

use App\Models\Question\MultiChoiceQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = ['multi_choice_question_id', 'text', 'is_correct'];

    public function multiChoiceQuestion()
    {
        return $this->belongsTo(MultiChoiceQuestion::class);
    }
}
