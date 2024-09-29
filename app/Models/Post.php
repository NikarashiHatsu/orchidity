<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Screen\AsSource;

class Post extends Model
{
    use HasFactory, AsSource, Attachable, Filterable;

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected $fillable = [
        'author_id',
        'hero',
        'title',
        'description',
        'body',
    ];

    protected $allowedSorts = [
        'title',
        'created_at',
        'updated_at',
    ];

    protected $allowedFilters = [
        'title' => Like::class,
    ];
}
