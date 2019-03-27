<?php

namespace App\Http\Controllers;

use App\Intervention;
use App\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\EditableLayerDef;
use App\ChangeRequests\ChangeRequestProcessor;
use App\Helpers\Helpers;
use App\Http\Requests\InterventionFormRequest;
use App\ChangeRequest;
use App\ChangeRequests\InterventionChangeRequestProcessor;
use function GuzzleHttp\json_encode;
use App\Department;
use App\User;

class InterventionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            $data = Intervention::consolidated()->get();
        }
        else {
            // get the interventions opened by the user
            $changeRequests = ChangeRequest::open()
            ->where('layer', Intervention::LAYER_NAME)
            ->where('requested_by_id', $user->id)->get();
            $interventionIds = $changeRequests->pluck('feature_id')->all();

            // get user departments
            $user->load('departments');
            $userDepartmentIds = $user->departments->pluck('code');
            $consolidatedData = Intervention::consolidated()->whereIn('departamento', $userDepartmentIds);
            $data = Intervention::whereIn('id', $interventionIds)
                ->union($consolidatedData)->get();
        }
        return view('intervention.index', ['interventions' => $data]);
    }
    
    protected function isEditable(Intervention $intervention, User $user): bool {
        if ($intervention->status==ChangeRequest::FEATURE_STATUS_VALIDATED) {
            return true;
        }
        return false;
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
        $formVariables = $this->getFormVariables($intervention, $user);
        return view('intervention.create', $formVariables);
    }
    
    protected function getFormVariables(Intervention $intervention, $user) {
        $all_layers = EditableLayerDef::enabled()->get();
        $user->load(['departments']);
        $user_departments = [];
        foreach ($user->departments as $current_dep) {
            $user_departments[$current_dep->code] = $current_dep->code.' - '.$current_dep->name;
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
            'editable' => $this->isEditable($intervention, $user),
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
        /* 
        if ($user->isAdmin()) {
            $validated['status'] = ChangeRequest::FEATURE_STATUS_VALIDATED;
        }
        else {
            $validated['status'] = ChangeRequest::FEATURE_STATUS_PENDING_CREATE;
        }*/
        /*$intervention = Intervention::create($validated);
        $properties = $intervention->toArray();
        Log::debug('$intervention->toArray():');
        Log::debug(json_encode($properties));*/
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
        Log::debug('$intervention->toArray():');
        Log::debug(json_encode($properties));
        
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
        $intervention->delete();
        return redirect()->route('interventions.index');
    }
}
