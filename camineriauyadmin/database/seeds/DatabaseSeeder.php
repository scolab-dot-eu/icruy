<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // La creación de datos de roles y departamentos debe ejecutarse primero
        $this->call(RoleTableSeeder::class);
        $this->call(DepartmentTableSeeder::class);
        // Los usuarios necesitarán los roles previamente generados
        $this->call(UserTableSeeder::class);
        
        $this->call(SupportLayerDefTableSeeder::class);
        $this->call(EditableLayerDefTableSeeder::class);
    }
}
