<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderingItem extends Model
{
    use HasFactory;
    protected $fillable = ['reordering_question_id','order','text'];
    // protected $hidden = ['order'];
    /**
     * Get the question that owns the ReorderingItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ReorderingQuestion::class);
    }
}
