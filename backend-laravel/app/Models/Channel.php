<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Channel extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    protected $fillable = [
        'uuid', 'name', 'slug', 'parent_id', 'is_active'
    ];

    public function quest()
    {
        return $this->hasMany(Quest::class, 'channel_id', 'uuid');
    }

    public function child()
    {
        return $this->hasMany(Channel::class, 'parent_id', 'uuid');
    }

    public function parent()
    {
        return $this->belongsTo(Channel::class, 'parent_id', 'uuid');
    }
}
