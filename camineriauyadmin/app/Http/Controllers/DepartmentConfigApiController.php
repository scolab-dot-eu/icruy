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
