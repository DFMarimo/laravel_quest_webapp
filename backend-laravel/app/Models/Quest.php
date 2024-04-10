<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Quest extends Model
{
    use HasFactory, HasSlug;

    protected $table = 'quests';
    protected $fillable = [
        'uuid',
        'slug',
        'best_answer_id',
        'author_id',
        'channel_id',
        'title',
        'body',
        'status',
        'is_active',
        'deleted_at'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'quest_id', 'uuid');
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'uuid');
    }

    public function bestAnswer()
    {
        return $this->hasOne(Answer::class, 'quest_id', 'uuid');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            Tag::class,
            'taggable',
            'taggables',
            'tag_id',
            'taggable_id',
            'uuid',
            'uuid'
        );
//        return $this->morphToMany(
//            Tag::class,
//            'taggable',
//            'taggables',
//            'taggable_id',
//            'tag_id',
//            'uuid',
//            'uuid');
    }
}
