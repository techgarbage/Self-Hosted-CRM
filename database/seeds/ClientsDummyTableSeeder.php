<?php

use Illuminate\Database\Seeder;

class ClientsDummyTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
         factory(App\Models\Client::class, 1000)->create()->each(function ($c) {
         });
    }
}
