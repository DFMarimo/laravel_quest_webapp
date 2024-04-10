<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Answer extends Model
{
    use HasFactory, HasSlug;

    protected $table = 'answers';
    protected $fillable = ['title', 'body', 'quest_id', 'is_active', 'author', 'slug', 'deleted_at'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author', 'uuid');
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class, 'quest_id', 'uuid');
    }
}
