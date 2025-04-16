<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'link',
        'user_id',
    ];
       
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags() 
    {
        return $this->belongsToMany(Tag::class, 'tool_tag');
    }

    public static function filterByRequest(Request $request)
    {
        $query = self::query();

        if ($request->filled('tag')) {
            $tag = $request->query('tag');
        
            $query->whereHas('tags', function ($query) use ($tag) {
                $query->where('name', $tag);
            });
        }

        return $query->with('tags')->latest();
    }

    public function syncTags(array $tagNames)
    {
        $tagIds = collect($tagNames)
            ->map(fn ($name) => Tag::firstOrCreate(['name' => strtolower($name)])->id)
            ->toArray();

        $this->tags()->sync($tagIds);
        $this->load('tags');
    }
}
