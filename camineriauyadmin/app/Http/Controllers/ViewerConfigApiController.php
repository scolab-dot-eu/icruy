<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Department;
use App\SupportLayerDef;
use App\EditableLayerDef;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\ChangeRequest;

class ViewerConfigApiController extends Controller
{
    const CAMINERIA_DEFAULT_WFS_URL = 'http://geoservicios.mtop.gub.uy/geoserver/caminerias_intendencias/wfs';
    const CAMINERIA_DEFAULT_WMS_URL = 'http://geoservicios.mtop.gub.uy/geoserver/caminerias_intendencias/wms';

    public function getViewerConfig(Request $request, $department_code=null) {
        $baselayers = [];
        $baselayers[] = ['type'=>'empty', 'name'=>'empty', 'title'=>'Capa vacía', 'visible'=>false];
        $overlays = [];
        
        $camineria_wfs_url = env('CAMINERIA_WMS_URL', ViewerConfigApiController::CAMINERIA_DEFAULT_WFS_URL);
        $camineria_wms_url = env('CAMINERIA_WFS_URL', ViewerConfigApiController::CAMINERIA_DEFAULT_WMS_URL);
        
        if ($department_code!=null) {
            $dep = Department::where('code', $department_code)->first();
            if (empty($dep)) {
                return response()->json(['status'=> 'error',
                    'error'=>'El código de departamento no es válido'], 400);
            }
            /*
            $overlays['Capas de apoyo'][] = [
                'type'=>'wfs',
                'url'=> $camineria_wfs_url,
                'wms_url'=> $camineria_wms_url,
                'name'=> $dep->layer_name,
                'title'=> 'Caminería '.$dep->name,
                'visible'=> false,
                'editable'=> false, //true,
                'showTable'=> true,
                'showInSearch'=> true,
                'download'=> true,
                'hasMetadata'=> false,
                'metadata'=> '',
                'geom_type'=> 'line',
                'geom_style'=> 'line',
                'style'=> [
                    'color'=> $dep->color,
                    'weight'=> 2,
                    'opacity'=> 1
                ]
            ];*/
        }
        else {
            $layersCamineria = [];
            foreach (Department::orderBy('name', 'ASC')->get() as $dep) {
                $layersCamineria[] = [
                    'type'=>'wfs',
                    'url'=> $camineria_wfs_url,
                    'wms_url'=> $camineria_wms_url,
                    'name'=> $dep->layer_name,
                    'title'=> 'Caminería '.$dep->name,
                    'visible'=> false,
                    'editable'=> true,
                    'showTable'=> true,
                    'showInSearch'=> true,
                    'download'=> true,
                    'hasMetadata'=> false,
                    'metadata'=> '',
                    'geom_type'=> 'line',
                    'geom_style'=> 'line',
                    'style'=> [
                        'color'=> $dep->color,
                        'weight'=> 2,
                        'opacity'=> 1
                    ]
                ];
            }
        }

        foreach (SupportLayerDef::all() as $lyr) {
            if ($lyr->isbaselayer) {
                $baselayers[] = [
                    'type'=>$lyr->protocol,
                    'url'=>array_get($lyr, 'url', ''),
                    'name'=>$lyr->name,
                    'title'=>$lyr->title,
                    'visible'=>$lyr->visible,
                    'api_key'=>array_get($lyr, 'api_key', '')
                ];
            }
            else {
                if (empty($lyr->conf)) {
                    $conf = [];
                }
                else {
                    $conf = json_decode($lyr->conf, true);
                }
                $conf['type'] = $lyr->protocol;
                $conf['url'] = array_get($lyr, 'url', '');
                $conf['name'] = $lyr->name;
                $conf['title'] = $lyr->title;
                $conf['visible'] = $lyr->visible;
                $conf['api_key'] = array_get($lyr, 'api_key', '');
                $conf['editable'] = false;
                if (empty($lyr->metadata)) {
                    $conf['hasMetadata'] = false;
                    $conf['metadata'] = '';
                }
                else {
                    $conf['hasMetadata'] = true;
                    $conf['metadata'] = $lyr->metadata;
                }
                $overlays[$lyr->layergroup][] = $conf;
            }
        }
        $baselayersConf = ['groups'=> [
            ['title'=>'Capas base', 'expanded'=>true, 'layers'=>$baselayers]
        ]];
        
        $overlaysGroups = [];
        foreach ($overlays as $key=>$value) {
            $overlaysGroups[] = ['title'=>$key, 'expanded'=>false, 'layers'=>$value];
        }
        $editableLayers = [];
        $inventory_layers = [];
        $default_workspace = env('DEFAULT_GS_WORKSPACE','camineria');
        $wms_url = env('WMS_URL','');
        $wfs_url = env('WFS_URL','');
        $protocol = 'wfs';
        
        foreach (EditableLayerDef::where('enabled', 1)->get() as $lyr) {
            $conf = json_decode($lyr->conf, true);
            if (($lyr->name == 'cr_caminos') && ($department_code==null)) {
                continue;
            }
            $conf['title'] = $lyr->title;
            
            $conf['type'] = $protocol;
            if ($lyr->name == 'cr_caminos') {
                $inventory_layers[] = $dep->layer_name;
                $conf['name'] = $dep->layer_name;
                $conf['internal_name'] = $lyr->name;
                $conf['url'] = $camineria_wfs_url;
                $conf['wms_url'] = $camineria_wms_url;
                $conf['style'] = [
                    'color'=> $dep->color,
                    'weight'=> 2,
                    'opacity'=> 1
                ];
                if ($lyr->geom_style) {
                    $conf['geom_style'] = $lyr->geom_style;
                }
                else {
                    $conf['geom_style'] = 'line';
                }
            }
            else {
                $inventory_layers[] = $default_workspace.":".$lyr->name;
                $conf['name'] = $default_workspace.":".$lyr->name;
                $conf['url'] = $wfs_url;
                $conf['wms_url'] = $wms_url;
                $conf['geom_style'] = $lyr->geom_style;
                if ($lyr->geom_type == 'point') {
                    $conf['style'] = json_decode($lyr->style);
                }
            }
            $conf['history_layer_name'] = $default_workspace.":".EditableLayerDef::getHistoricTableName($lyr->name);
            $conf['fields'] = json_decode($lyr->fields);
            $conf['geom_type'] = $lyr->geom_type;
            $conf['editable'] = true;
            /*
            if ($department_code!=null) {
                $conf['editable'] = true;
            }
            else {
                $conf['editable'] = false;
            }
            */
            $conf['visible'] = $lyr->visible;
            $conf['showInSearch'] = $lyr->showInSearch;
            $conf['showTable'] = $lyr->showTable;
            $conf['download'] = $lyr->download;
            
            if (empty($lyr->metadata)) {
                $conf['hasMetadata'] = false;
                $conf['metadata'] = '';
            }
            else {
                $conf['hasMetadata'] = true;
                $conf['metadata'] = $lyr->metadata;
            }
            $editableLayers[] = $conf;
        }
        $overlaysGroups[] = ['title'=>'Inventario caminería Rural', 'expanded'=>true, 'layers'=>$editableLayers];
        
        if ($department_code==null) {
            $overlaysGroups[] = ['title'=>'Caminería MTOP', 'expanded'=>true, 'layers'=>$layersCamineria];
        }
        $overlaysConf = ['groups'=> $overlaysGroups];
        if ($department_code!=null) {
            $result = [
                'baselayers'=>
                $baselayersConf,
                'overlays'=>$overlaysConf,
                'department' => $dep,
                'inventory_layers' => $inventory_layers
            ];
            if (Auth::check()) {
                $user = $request->user();
                $departments = [];
                foreach($user->departments as $dep) {
                    $departments[] = $dep->code;
                }
                $result['user'] = [
                    'name'=>$user->name,
                    'email'=>$user->email,
                    'isadmin'=>$user->isAdmin(),
                    'ismanager'=>$user->isManager(),
                    'ismtopmanager'=>$user->isMtopManager(),
                    'departments'=>$departments
                ];
            }
        }
        else {
            $result = [
                "map"=> [
                    "center"=> [-32.805745, -56.018085],
                    "zoom"=> 7
                ],
                'baselayers'=>
                $baselayersConf,
                'overlays'=>$overlaysConf,
                'inventory_layers' => $inventory_layers
            ];
        }
        return response()->json($result);
        
    }

}
