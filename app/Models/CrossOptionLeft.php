<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CrossOptionLeft extends Model
{
    use HasFactory;

    protected $fillable = ['cross_question_id','text','right_option_id'];
    protected $table = 'cross_options_left';
    // protected $hidden = "right_option_id";
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    /**
     * Get the answer associated with the CrossOptions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(CrossOptionRight::class,'right_option_id');
    }
}
