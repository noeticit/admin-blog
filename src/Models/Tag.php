<?php

namespace Noeticit\AdminBlog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('blog.database.table_prefix').'tags';
    }

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the posts for the tag.
     */
    public function posts(): BelongsToMany
    {
        $prefix = config('blog.database.table_prefix');
        return $this->belongsToMany(Post::class, $prefix.'post_tag', 'tag_id', 'post_id');
    }

    /**
     * Get the route key name for Laravel routing.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
