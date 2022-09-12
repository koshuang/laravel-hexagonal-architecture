<?php

namespace Modules\Shared\Infrastructure\Adapter\Out\Persistence\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SharedDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");
    }
}
