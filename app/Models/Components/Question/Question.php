<?php

namespace App\Models\Components\Question;

use App\Models\Components\Component;
use App\Models\Components\Question\MultiChoice\MultiChoiceQuestion;
use App\Models\Content\Assessment;
use App\Models\BlankQuestion;
use App\Models\CrossQuestion;
use App\Models\ReorderingQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable=['type','component_id'];

    public function toArray()
    {
        $data = parent::toArray();
        unset($data['multi_choice_question']);
        unset($data['cross_question']);
        return $data;
    }

    public function multiChoiceQuestion()
    {
        return $this->hasOne(MultiChoiceQuestion::class)->with('options');
    }

    public function blankQuestion() {
        return $this->hasOne(BlankQuestion::class);
    }
    public function reorderQuestion(){
        return $this->hasOne(ReorderingQuestion::class)->with('items');
    }
    public function crossQuestion(){
        return $this->hasOne(CrossQuestion::class);
    }
    public function getTypeRelation()
    {

        switch ($this->type) {
            case 'multi-choice':
                return $this->multiChoiceQuestion();
            case 'cross-question':
                return $this->crossQuestion();
            case 'blank-question':
                return $this->blankQuestion();
            case 'reorder-question':
                return $this->reorderQuestion();
            // Add more cases for other types if needed

        }
    }
    public function component(){
        return $this->belongsTo(Component::class);
    }
}
