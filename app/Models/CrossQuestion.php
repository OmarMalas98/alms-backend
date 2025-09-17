<?php

namespace App\Models;

use App\Models\Components\Question\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrossQuestion extends Model
{
    use HasFactory;
    protected $fillable = ['question_id', 'text'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function leftOptions()
    {
        return $this->hasMany(CrossOptionLeft::class,'cross_question_id');
    }
    public function rightOptions()
    {
        return $this->hasMany(CrossOptionRight::class,'cross_question_id');
    }
    public function minOptions() {
        $lc = $this->leftOptions->count();
        $rc = $this->rightOptions->count();
        if ($lc < $rc) {
            return $lc;
        }
        else {
            return $rc;
        }
    }
}
