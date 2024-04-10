<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Tag extends Model
{
    use HasFactory, HasSlug;

    protected $table = 'tags';
    protected $fillable = ['uuid', 'deleted_at', 'name', 'slug'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            User::class,
            'taggable',
            'taggables',
            'tag_id',
            'taggable_id',
            'uuid',
            'uuid'
        );
    }

    public function quests(): MorphToMany
    {
        return $this->morphedByMany(
            Quest::class,
            'taggable',
            'taggables',
            'tag_id',
            'taggable_id',
            'uuid',
            'uuid'
        );
    }
}
