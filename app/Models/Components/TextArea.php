<?php

namespace App\Models\Components;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextArea extends Model
{
    use HasFactory;
    protected $fillable = ['component_id','body'];
    protected $hidden = ['id'];
    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
