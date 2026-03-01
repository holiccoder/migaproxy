<?php

namespace Database\Factories;

use App\Models\HelpArticle;
use App\Models\HelpArticleSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HelpArticleSection>
 */
class HelpArticleSectionFactory extends Factory
{
    protected $model = HelpArticleSection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'help_article_id' => HelpArticle::factory(),
            'section_key' => Str::slug($title),
            'title' => $title,
            'paragraphs' => [fake()->paragraph(), fake()->paragraph()],
            'callout_type' => null,
            'callout_title' => null,
            'callout_content' => null,
            'code_language' => null,
            'code' => null,
            'sort_order' => 1,
        ];
    }
}
