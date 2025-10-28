<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Tag;

class PivotArticleTagSeeder extends Seeder
{
    public function run(): void
    {
        $tagIds = Tag::pluck('id');
        Article::all()->each(function ($article) use ($tagIds) {
            $article->tags()->sync($tagIds->random(rand(1, 4))->all());
        });
    }
}