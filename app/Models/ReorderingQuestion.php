<?php

namespace App\Models;

use App\Models\Components\Question\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReorderingQuestion extends Model
{
    use HasFactory;
    protected $fillable = ['text','question_id'];
    /**
     * Get all of the items for the ReorderingQuestion
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(ReorderingItem::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
