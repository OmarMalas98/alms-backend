<?php

namespace App\Models\Components\Question\MultiChoice;

use App\Models\Components\Question\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultiChoiceQuestion extends Model
{
    use HasFactory;

    protected $fillable=['question_id','text'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function correctOption()
    {
        return $this->hasOne(Option::class)->where('is_correct', true);
    }

}
