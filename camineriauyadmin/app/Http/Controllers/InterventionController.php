<?php

namespace App\Http\Controllers;

use App\Intervention;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\EditableLayerDef;
use App\Helpers\Helpers;
use App\Http\Requests\InterventionFormRequest;
use App\ChangeRequest;
use App\ChangeRequests\InterventionChangeRequestProcessor;
use function GuzzleHttp\json_encode;
use App\Department;
use App\User;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    const CREATE_MODE = "CREATE";
    const UPDATE_MODE = "UPDATE";
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all_departments = [''=>''];
        $departments = Department::all()->sortBy('name');
        foreach ($departments as $current_dep) {
            $all_departments[$current_dep->code] = $current_dep->name;
        }
        
        
        return view('intervention.index', ['all_departments' => $all_departments]);
    }
    
    protected function isEditable(Intervention $intervention, User $user): bool {
        if ($intervention->status!=ChangeRequest::FEATURE_STATUS_VALIDATED) {
            return false;
        }
        if (!$user->isAdmin()) {
            if (!$user->isManager()) {
                return false;
            }
            if (isset($intervention->departamento)) {
                if ($user->departments()->where('code', $intervention->departamento)->count()==0) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $intervention = new Intervention();
        $user = Auth::user();
        $formVariables = $this->getFormVariables($intervention, $user, InterventionController::CREATE_MODE);
        return view('intervention.create', $formVariables);
    }
    
    protected function getFormVariables(Intervention $intervention, User $user, string $mode=InterventionController::UPDATE_MODE) {
        $all_layers = EditableLayerDef::enabled()->get();
        if ($mode==InterventionController::CREATE_MODE) {
            $editable = true;
        }
        else {
            $editable = $this->isEditable($intervention, $user);
        }
        if ($editable) {
            $user->load(['departments']);
            $user_departments = [];
            foreach ($user->departments as $current_dep) {
                $user_departments[$current_dep->code] = $current_dep->code.' - '.$current_dep->name;
            }
        }
        else {
            $departments = Department::all()->sortBy('name');
            foreach ($departments as $current_dep) {
                $user_departments[$current_dep->code] = $current_dep->name;
            }
        }
        $inventory_layers = [];
        $inventoryDef = [];
        foreach ($all_layers as $current_lyr) {
            if ($current_lyr->name!=Intervention::LAYER_NAME) {
                $inventory_layers[$current_lyr->name] = $current_lyr->title;
            }
            else {
                $inventoryDef = json_decode($current_lyr->fields, true);
            }
        }
        foreach ($inventoryDef as $fieldDef) {
            if ($fieldDef['name']=='tarea') {
                $tareaSelect = Helpers::domainDefToSelectArray($fieldDef['domain']);
            }
            elseif ($fieldDef['name']=='financiacion') {
                $financiacionSelect = Helpers::domainDefToSelectArray($fieldDef['domain']);
            }
            elseif ($fieldDef['name']=='forma_ejecucion') {
                $formaEjecucionSelect = Helpers::domainDefToSelectArray($fieldDef['domain']);
            }
        }
        $changeRequestUrl = null;
        if (isset($intervention->id) 
                && $intervention->status != ChangeRequest::FEATURE_STATUS_VALIDATED) {
            if ($user->isAdmin()) {
                $changeRequest = ChangeRequest::open()
                ->where('layer', Intervention::LAYER_NAME)
                ->where('feature_id', $intervention->id)->get()->first();
                if (isset($changeRequest->id)) {
                    $changeRequestUrl = route('changerequests.edit', $changeRequest->id);
                }
            }
            else {
                $changeRequest = ChangeRequest::open()
                ->where('layer', Intervention::LAYER_NAME)
                ->where('feature_id', $intervention->id)
                ->where('requested_by_id', $user->id)->get()->first();
                if (isset($changeRequest->id)) {
                    $changeRequestUrl = route('changerequests.edit', $changeRequest->id);
                }
            }
        }
        return ['intervention'=>$intervention,
            'user_departments'=>$user_departments,
            'inventory_layers'=>$inventory_layers,
            'tareaSelect'=>$tareaSelect,
            'financiacionSelect'=>$financiacionSelect,
            'formaEjecucionSelect'=>$formaEjecucionSelect,
            'editable' => $editable,
            'changeRequestUrl' => $changeRequestUrl
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InterventionFormRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $changeRequestProcessor = new InterventionChangeRequestProcessor();
        $changeRequestProcessor->createChangeRequest(Intervention::LAYER_NAME,
            ChangeRequest::OPERATION_CREATE, $validated, $user);
        return redirect()->route('interventions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Intervention  $intervention
     * @return \Illuminate\Http\Response
     */
    public function show(Intervention $intervention)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Intervention  $intervention
     * @return \Illuminate\Http\Response
     */
    public function edit(Intervention $intervention)
    {
        $user = Auth::user();
        $formVariables = $this->getFormVariables($intervention, $user);
        return view('intervention.edit', $formVariables);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Intervention  $intervention
     * @return \Illuminate\Http\Response
     */
    public function update(InterventionFormRequest $request, Intervention $intervention)
    {
        $validated = $request->validated();
        $properties = $intervention->fill($validated)->toArray(); 
        $user = $request->user();
        /*
         * Will be done on the change request if needed
         * $intervention->update($validated);
         */
        
        $changeRequestProcessor = new InterventionChangeRequestProcessor();
        $changeRequestProcessor->createChangeRequest(Intervention::LAYER_NAME,
            ChangeRequest::OPERATION_UPDATE, $properties, $user);
        
        return redirect()->route('interventions.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Intervention  $intervention
     * @return \Illuminate\Http\Response
     */
    public function destroy(Intervention $intervention)
    {
        $user = Auth::user();
        if ($this->isEditable($intervention, $user)) {
            $intervention->delete();
            return redirect()->route('interventions.index');
        }
        else {
            $message = 'No tienes permisos para borrar la intervención: '.$user->email;
            Log::error($message);
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'user' => [$message],
            ]);
            throw $error;
        }
    }
    
    
    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function anyData(Request $request)
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $query = Intervention::consolidated();
        }
        else {
            // get the interventions opened by the user
            $changeRequests = ChangeRequest::open()
            ->where('layer', Intervention::LAYER_NAME)
            ->where('requested_by_id', $user->id)->get();
            $interventionIds = $changeRequests->pluck('feature_id')->all();
            
            $query = Intervention::where(function($groupedQuery) use ($interventionIds, $user) {
                $groupedQuery->where('status', '!=', ChangeRequest::FEATURE_STATUS_PENDING_CREATE)
                    ->orWhereIn('id', $interventionIds);
            });
        }
        $codigo_camino = $request->query('codigo_camino');
        if (!empty($codigo_camino)) {
            $query = $query->where('codigo_camino', $codigo_camino);
        }
        $id_elem = $request->query('id_elem');
        if (!empty($id_elem)) {
            $query = $query->where('id_elem', $id_elem);
        }
        $tipo_elem = $request->query('tipo_elem');
        if (!empty($tipo_elem)) {
            $query = $query->where('tipo_elem', $tipo_elem);
        }
        
        return Datatables::make($query)->toJson();
    }
}
