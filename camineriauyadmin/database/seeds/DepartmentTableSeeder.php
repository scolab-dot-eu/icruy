<?php

use Illuminate\Database\Seeder;
use App\Department;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dptmn = new Department();
        $dptmn->code = 'UYAR';
        $dptmn->name = 'ARTIGAS';
        $dptmn->minx = '-57.87759';
        $dptmn->maxx = '-55.98959';
        $dptmn->miny = '-31.08256';
        $dptmn->maxy = '-30.08550';
        $dptmn->save();

        $dptmn = new Department();
        $dptmn->code = 'UYCA';
        $dptmn->name = 'CANELONES';
        $dptmn->minx = '-56.49392';
        $dptmn->maxx = '-55.39073';
        $dptmn->miny = '-34.88022';
        $dptmn->maxy = '-34.20552';
        $dptmn->save();

        $dptmn = new Department();
        $dptmn->code = 'UYCL';
        $dptmn->name = 'CERRO LARGO';
        $dptmn->minx = '-55.35582';
        $dptmn->maxx = '-53.18105';
        $dptmn->miny = '-33.02976';
        $dptmn->maxy = '-31.65128';
        $dptmn->save();

        $dptmn = new Department();
        $dptmn->code = 'UYCO';
        $dptmn->name = 'COLONIA';
        $dptmn->minx = '-58.42193';
        $dptmn->maxx = '-56.98186';
        $dptmn->miny = '-34.47643';
        $dptmn->maxy = '-33.76547';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYDU';
        $dptmn->name = 'DURAZNO';
        $dptmn->minx = '-57.19647';
        $dptmn->maxx = '-55.00568';
        $dptmn->miny = '-33.50727';
        $dptmn->maxy = '-32.40946';
        $dptmn->save();
    }
}
