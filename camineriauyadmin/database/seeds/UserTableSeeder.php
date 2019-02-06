<?php

use Illuminate\Database\Seeder;
use App\Department;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_admin = Role::where('name', Role::getAdminRoleName())->first();
        $role_manager = Role::where('name', Role::getManagerRoleName())->first();
        $role_mtopmanager = Role::where('name', Role::getMtopManagerRoleName())->first();
        
        $user = new User();
        $user->name = 'Administrador de prueba';
        $user->email = 'admin.cruy@yopmail.com';
        $user->password = bcrypt('4Testpurposes123');
        $user->save();
        $user->roles()->attach($role_admin);
        $user->roles()->attach($role_manager);
        $department = Department::where('code', 'UYCL')->first();
        $user->departments()->attach($department);
        $department = Department::where('code', 'UYCA')->first();
        $user->departments()->attach($department);
        
        $user = new User();
        $user->name = 'Gestor de prueba';
        $user->email = 'ges1.cruy@yopmail.com';
        $user->password = Hash::make('4Testpurposes123');
        $user->save();
        $user->roles()->attach($role_manager);
        
        $department = Department::where('code', 'UYCL')->first();
        $user->departments()->attach($department);
        $department = Department::where('code', 'UYCA')->first();
        $user->departments()->attach($department);

        $user = new User();
        $user->name = 'Gestor de prueba';
        $user->email = 'mges1.cruy@yopmail.com';
        $user->password = Hash::make('4Testpurposes123');
        $user->save();
        
        $user->roles()->attach($role_mtopmanager);
        $department = Department::where('code', 'UYCL')->first();
        $user->departments()->attach($department);
        $department = Department::where('code', 'UYCA')->first();
        $user->departments()->attach($department);
        
    }
}
