<?php

namespace Database\Factories;
use Illuminate\Support\Str;

use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;

class UrlFactory extends Factory
{
    protected $model = Url::class;

    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'url_key' => Str::random(8), // Ajusta la longitud según lo necesites
        ];
    }
}
