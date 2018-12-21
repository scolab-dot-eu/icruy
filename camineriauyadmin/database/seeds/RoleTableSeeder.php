<?php

use Illuminate\Database\Seeder;
use App\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new Role();
        $role->name = Role::getAdminRoleName();
        $role->desc = 'Administradores';
        $role->save();
        
        $role = new Role();
        $role->name = Role::getManagerRoleName();
        $role->desc = 'Gestores departamentales';
        $role->save();
        
        $role = new Role();
        $role->name = Role::getMtopManagerRoleName();
        $role->desc = 'Gestores MTOP';
        $role->save();
    }
}
