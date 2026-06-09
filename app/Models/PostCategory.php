<?php

namespace App\Models;

use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PostCategory extends Model
{
    /** @use HasFactory<PostCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (PostCategory $category): void {
            if (blank($category->slug)) {
                $category->slug = static::uniqueSlug($category->name);
            }
        });

        static::updating(function (PostCategory $category): void {
            if ($category->isDirty('name')) {
                $category->slug = static::uniqueSlug($category->name, $category->id);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (
            static::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $originalSlug.'-'.$counter++;
        }

        return $slug;
    }

    public function postsCount(): int
    {
        return Post::query()->where('category', $this->name)->count();
    }
}
