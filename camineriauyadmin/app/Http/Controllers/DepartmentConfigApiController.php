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

class DepartmentConfigApiController extends Controller
{
    // TODO: esto debería obtenerse de la configuración
    const WFS_CAMINERIA_LAYERNAME = [
        "UYAR" => "caminerias_intendencias:v_camineria_artigas",
        "UYCA" => "caminerias_intendencias:v_camineria_canelones",
        "UYCL" => "caminerias_intendencias:v_camineria_cerro_largo",
        "UYCO" => "caminerias_intendencias:v_camineria_colonia",
        "UYDU" => "caminerias_intendencias:v_camineria_durazno",
        "UYFS" => "caminerias_intendencias:v_camineria_flores",
        "UYFD" => "caminerias_intendencias:v_camineria_florida",
        "UYLA" => "caminerias_intendencias:v_camineria_lavalleja",
        "UYMA" => "caminerias_intendencias:v_camineria_maldonado",
        "UYMO" => "caminerias_intendencias:v_camineria_montevideo",
        "UYPA" => "caminerias_intendencias:v_camineria_paysandu",
        "UYRN" => "caminerias_intendencias:v_camineria_rio_negro",
        "UYRV" => "caminerias_intendencias:v_camineria_rivera",
        "UYRO" => "caminerias_intendencias:v_camineria_rocha",
        "UYSA" => "caminerias_intendencias:v_camineria_salto",
        "UYSJ" => "caminerias_intendencias:v_camineria_san_jose",
        "UYSO" => "caminerias_intendencias:v_camineria_soriano",
        "UYTA" => "caminerias_intendencias:v_camineria_tacuarembo",
        "UYTT" => "caminerias_intendencias:v_camineria_treinta_y_tres"
        
    ];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDepartmentConfig(Request $request, $department_code)
    {
        $dep = Department::where('code', $department_code)->first();
        if (empty($dep)) {
            return response()->json(['status'=> 'error',
                'error'=>'El código de departamento no es válido'], 400);
        }
        $baselayers = [];
        $baselayers[] = ['type'=>'empty', 'name'=>'empty', 'title'=>'Capa vacía', 'visible'=>false];
        $overlays = [];
        // FIXME: añadimos a piñón las capas de caminería
        $overlays['Capas de apoyo'][] = [
            'type'=>'wfs',
            'url'=> 'http://geoservicios.mtop.gub.uy/geoserver/caminerias_intendencias/wfs',
            'wms_url'=> 'http://geoservicios.mtop.gub.uy/geoserver/caminerias_intendencias/wms',
            'name'=> DepartmentConfigApiController::WFS_CAMINERIA_LAYERNAME[$department_code],
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
                'color'=> '#848484',
                'weight'=> 2,
                'opacity'=> 0.65
             ]
        ];
        Log::error($overlays);
        foreach (SupportLayerDef::all() as $lyr) {
            if ($lyr->isbaselayer) {
                $baselayers[] = [
                    'type'=>$lyr->protocol,
                    //'url'=>$lyr->url,
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
                //$conf['api_key'] = $lyr, 'api_key', null);
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
        
        foreach (EditableLayerDef::where('enabled', 1)->get() as $lyr) {
            $conf = json_decode($lyr->conf, true);
            $conf['name'] = $default_workspace.":".$lyr->name;
            $conf['abrev'] = $lyr->abrev;
            $conf['title'] = $lyr->title;
            $inventory_layers[] = $lyr->name;
            $conf['type'] = $lyr->protocol;
            $conf['url'] = $lyr->url;
            $conf['wms_url'] = $wms_url;
            $conf['history_layer_name'] = $default_workspace.":".EditableLayerDef::getHistoricTableName($lyr->name);
            $conf['fields'] = json_decode($lyr->fields);
            $conf['style'] = json_decode($lyr->style);
            $conf['geom_style'] = $lyr->geom_style;
            
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
        $overlaysConf = ['groups'=> $overlaysGroups];
        /*
        $overlaysConf = ['groups'=> [
            ['title'=>'Capas de apoyo', 'expanded'=>true, 'layers'=>$supportOverlays],
            ['title'=>'Inventario caminería rural', 'expanded'=>true, 'layers'=>$supportOverlays],
            ['title'=>'Caminería MTOP', 'expanded'=>true, 'layers'=>$supportOverlays]
        ]];*/
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
        return response()->json($result);
    }
    
    public function getGlobalConfig(Request $request)
    {
        return response()->file('testconfigglobal.json');
        /*return response()->json([
         'name' => 'Abigail',
         'state' => 'CA'
         ]);*/
    }

}
