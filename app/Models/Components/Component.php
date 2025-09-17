<?php

namespace App\Models\Components;

use App\Models\Components\Question\Question;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Component extends Model
{
    use HasFactory;
    protected $fillable = ['page_id','order','type','is_suggested'];

    public function video()
    {
        return $this->hasOne(Video::class);
    }

    public function textarea()
    {
        return $this->hasOne(TextArea::class);
    }
    public function title()
    {
        return $this->hasOne(Title::class);
    }
    public function question()
    {
        return $this->hasOne(Question::class);
    }
    public function page()
    {
        return $this->belongsTo(Page::class);
    }
    public function getTypeRelation()
    {
        switch ($this->type) {
            case 'video':
                return $this->video();
            case 'textarea':
                return $this->textarea();
            case 'title':
                return $this->title();
            case 'question':
                return $this->question();

            // Add more cases for other types if needed

        }
    }

    private function getComponentRelationName(string $type): ?string
    {
        switch ($type) {
            case 'video':
                return 'video';
            case 'textarea':
                return 'textarea';
            case 'title':
                return 'title';
            case 'question':
                return 'question';
            // Add more cases for other types if needed
            default:
                return null;
        }
    }
}
