<?php

use Illuminate\Database\Seeder;
USE App\example;

class ExamplesTableSeeder  extends Seeder
{
    public function run()
    {
        $faker = Faker\Factory::create();

        foreach(range(1,30) as $index)
        {
            Example::create([
                'note' => $faker->paragraph($nbSentences = 3),
                'user_id' =>$faker->numberBetween($min = 1, $max = 5)
            ]);
        }
    }
}
