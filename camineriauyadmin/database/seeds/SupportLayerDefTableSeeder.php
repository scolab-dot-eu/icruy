<?php

use Illuminate\Database\Seeder;
use App\Department;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;
use App\SupportLayerDef;

class SupportLayerDefTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lyr = new SupportLayerDef();
        $lyr->name = 'osm';
        $lyr->title = 'OSM';
        $lyr->protocol = 'tilelayer';
        $lyr->url = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        $lyr->isbaselayer = true;
        $lyr->visible = true;
        $lyr->layergroup = 'Capas base';
        $lyr->api_key = '';
        $lyr->save();

        $lyr = new SupportLayerDef();
        $lyr->name = 'AerialWithLabels';
        $lyr->title = 'Bing Satélite con etiquetas';
        $lyr->protocol = 'bing';
        $lyr->url = '';
        $lyr->isbaselayer = true;
        $lyr->visible = false;
        $lyr->layergroup = 'Capas base';
        $lyr->api_key = env('BING_API_KEY', '');
        $lyr->save();
        
        $lyr = new SupportLayerDef();
        $lyr->name = 'Road';
        $lyr->title = 'Bing Roads';
        $lyr->protocol = 'bing';
        $lyr->url = '';
        $lyr->isbaselayer = true;
        $lyr->visible = false;
        $lyr->layergroup = 'Capas base';
        $lyr->api_key = env('BING_API_KEY', '');
        $lyr->save();
        
        $lyr = new SupportLayerDef();
        //$lyr->name = 'u19600217:c004';
        $lyr->name = 'departamentos';
        $lyr->title = 'Departamentos';
        $lyr->protocol = 'wms';
        $lyr->url = 'http://geoservicios.mtop.gub.uy/geoserver/geoportal_capas_base/wms';
        //$lyr->url = 'https://www.dinama.gub.uy/geoserver/u19600217/wms?';
        $lyr->isbaselayer = false;
        $lyr->visible = false;
        $lyr->layergroup = 'Capas de apoyo';
        $lyr->api_key = '';
        $lyr->conf = '{"metadata": "", "showTable": false, "hasMetadata": false, "showInSearch": false}';
        $lyr->save();

        $lyr = new SupportLayerDef();
        $lyr->name = 'u19600217:c258';
        $lyr->title = 'Espejos de agua';
        $lyr->protocol = 'wms';
        $lyr->url = 'http://www.dinama.gub.uy/geoserver/u19600217/wms';
        $lyr->isbaselayer = false;
        $lyr->visible = false;
        $lyr->layergroup = 'Capas de apoyo';
        $lyr->api_key = '';
        $lyr->conf = '{"metadata": "", "showTable": false, "hasMetadata": false, "showInSearch": false}';
        $lyr->save();
        
        $lyr = new SupportLayerDef();
        $lyr->name = 'u19600217:c257';
        $lyr->title = 'Cursos de agua';
        $lyr->protocol = 'wms';
        $lyr->url = 'http://www.dinama.gub.uy/geoserver/u19600217/wms';
        $lyr->isbaselayer = false;
        $lyr->visible = false;
        $lyr->layergroup = 'Capas de apoyo';
        $lyr->api_key = '';
        $lyr->conf = '{"metadata": "", "showTable": false, "hasMetadata": false, "showInSearch": false}';
        $lyr->save();
        
    }
}
