<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CrossOptionRight extends Model
{
    use HasFactory;

    protected $fillable = ['text',"cross_question_id"];
    protected $table = 'cross_options_right';

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    /**
     * Get the answer associated with the CrossOptions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function left(): HasOne
    {
        return $this->hasOne(CrossOptionLeft::class,'right_option_id');
    }
}
