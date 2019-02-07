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
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_artigas";
        $dptmn->color = '#ffcccc';
        $dptmn->save();

        $dptmn = new Department();
        $dptmn->code = 'UYCA';
        $dptmn->name = 'CANELONES';
        $dptmn->minx = '-56.49392';
        $dptmn->maxx = '-55.39073';
        $dptmn->miny = '-34.88022';
        $dptmn->maxy = '-34.20552';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_canelones";
        $dptmn->color = '#ffcccc';
        $dptmn->save();

        $dptmn = new Department();
        $dptmn->code = 'UYCL';
        $dptmn->name = 'CERRO LARGO';
        $dptmn->minx = '-55.35582';
        $dptmn->maxx = '-53.18105';
        $dptmn->miny = '-33.02976';
        $dptmn->maxy = '-31.65128';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_cerro_largo";
        $dptmn->color = '#f6f507';
        $dptmn->save();

        $dptmn = new Department();
        $dptmn->code = 'UYCO';
        $dptmn->name = 'COLONIA';
        $dptmn->minx = '-58.42193';
        $dptmn->maxx = '-56.98186';
        $dptmn->miny = '-34.47643';
        $dptmn->maxy = '-33.76547';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_colonia";
        $dptmn->color = '#ffdea9';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYDU';
        $dptmn->name = 'DURAZNO';
        $dptmn->minx = '-57.19647';
        $dptmn->maxx = '-55.00568';
        $dptmn->miny = '-33.50727';
        $dptmn->maxy = '-32.40946';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_durazno";
        $dptmn->color = '#ffdea9';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYFS';
        $dptmn->name = 'FLORES';
        $dptmn->minx = '-57.36210';
        $dptmn->maxx = '-56.39653';
        $dptmn->miny = '-33.98477';
        $dptmn->maxy = '-33.10669';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_flores";
        $dptmn->color = '#ffcccc';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYFD';
        $dptmn->name = 'FLORIDA';
        $dptmn->minx = '-56.53636';
        $dptmn->maxx = '-55.09820';
        $dptmn->miny = '-34.42403';
        $dptmn->maxy = '-33.10234';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_florida";
        $dptmn->color = '#f6f507';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYLA';
        $dptmn->name = 'LAVALLEJA';
        $dptmn->minx = '-55.62435';
        $dptmn->maxx = '-54.12959';
        $dptmn->miny = '-34.63581';
        $dptmn->maxy = '-33.31899';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_lavalleja";
        $dptmn->color = '#98dd4a';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYMA';
        $dptmn->name = 'MALDONADO';
        $dptmn->minx = '-55.48511';
        $dptmn->maxx = '-54.46325';
        $dptmn->miny = '-34.97398';
        $dptmn->maxy = '-33.92774';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_maldonado";
        $dptmn->color = '#ffdea9';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYMO';
        $dptmn->name = 'MONTEVIDEO';
        $dptmn->minx = '-56.43173';
        $dptmn->maxx = '-56.02699';
        $dptmn->miny = '-34.93572';
        $dptmn->maxy = '-34.70156';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_montevideo";
        $dptmn->color = '#98dd4a';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYPA';
        $dptmn->name = 'PAYSANDÚ';
        $dptmn->minx = '-58.19583';
        $dptmn->maxx = '-56.23884';
        $dptmn->miny = '-32.59594';
        $dptmn->maxy = '-31.45239';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_paysandu";
        $dptmn->color = '#ebcef2';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYRN';
        $dptmn->name = 'RÍO NEGRO';
        $dptmn->minx = '-58.42332';
        $dptmn->maxx = '-56.54710';
        $dptmn->miny = '-33.40496';
        $dptmn->maxy = '-32.34250';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_rio_negro";
        $dptmn->color = '#f6f507';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYRV';
        $dptmn->name = 'RIVERA';
        $dptmn->minx = '-56.18520';
        $dptmn->maxx = '-54.45152';
        $dptmn->miny = '-32.11036';
        $dptmn->maxy = '-30.83294';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_rivera";
        $dptmn->color = '#ffdea9';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYRO';
        $dptmn->name = 'ROCHA';
        $dptmn->minx = '-54.57667';
        $dptmn->maxx = '-53.36981';
        $dptmn->miny = '-33.14703';
        $dptmn->maxy = '-34.80383';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_rocha";
        $dptmn->color = '#f6f507';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYSA';
        $dptmn->name = 'SALTO';
        $dptmn->minx = '-58.07885';
        $dptmn->maxx = '-56.00949';
        $dptmn->miny = '-31.87124';
        $dptmn->maxy = '-30.74526';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_salto";
        $dptmn->color = '#f6f507';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYSJ';
        $dptmn->name = 'SAN JOSÉ';
        $dptmn->minx = '-57.15394';
        $dptmn->maxx = '-56.34173';
        $dptmn->miny = '-34.78844';
        $dptmn->maxy = '-33.83214';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_san_jose";
        $dptmn->color = '#ebcef2';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYSO';
        $dptmn->name = 'SORIANO';
        $dptmn->minx = '-58.43935';
        $dptmn->maxx = '-57.08949';
        $dptmn->miny = '-33.95656';
        $dptmn->maxy = '-33.00021';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_soriano";
        $dptmn->color = '#98dd4a';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYTA';
        $dptmn->name = 'TACUAREMBÓ';
        $dptmn->minx = '-56.68357';
        $dptmn->maxx = '-54.66728';
        $dptmn->miny = '-32.86662';
        $dptmn->maxy = '-31.23060';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_tacuarembo";
        $dptmn->color = '#98dd4a';
        $dptmn->save();
        
        $dptmn = new Department();
        $dptmn->code = 'UYTT';
        $dptmn->name = 'TREINTA Y TRES';
        $dptmn->minx = '-55.15936';
        $dptmn->maxx = '-53.29060';
        $dptmn->miny = '-33.46417';
        $dptmn->maxy = '-32.69748';
        $dptmn->layer_name = "caminerias_intendencias:v_camineria_treinta_y_tres";
        $dptmn->color = '#ebcef2';
        $dptmn->save();
    }
}
