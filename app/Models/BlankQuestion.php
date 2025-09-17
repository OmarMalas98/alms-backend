<?php

namespace App\Models;

use App\Models\Components\Question\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class BlankQuestion extends Model
{
    use HasFactory;
    protected $fillable = ['question_id','text'];

    /**
     * Get all of the answers for the BlankQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blanks(): HasMany
    {
        return $this->hasMany(BlankAnswer::class, 'question_id');
    }
    public function blanksCount(){
        $answers = $this->blanks;
        $blanks = array();
        foreach ($answers as $answer) {
            if(!Arr::has($blanks,$answer->blank_number)){
                array_push($blanks, $answer->blank_number);
            }
        }
        return count($blanks);
    }
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
