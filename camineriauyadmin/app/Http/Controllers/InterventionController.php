<?php

namespace App\Http\Controllers;

use App\Intervention;
use Illuminate\Support\Facades\Auth;
use App\EditableLayerDef;
use App\Http\Requests\InterventionFormRequest;

class InterventionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Intervention::all();
        return view('intervention.index', ['interventions' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $intervention = new Intervention();
        $all_layers = EditableLayerDef::where('enabled', True)->get();
        $user = Auth::user();
        $user->load(['departments']);
        $user_departments = [];
        foreach ($user->departments as $current_dep) {
            $user_departments[$current_dep->code] = $current_dep->code.' - '.$current_dep->name;
        }
        $inventory_layers = [];
        foreach ($all_layers as $current_lyr) {
            $inventory_layers[$current_lyr->name] = $current_lyr->title;
        }
        return view('intervention.create', ['intervention'=>$intervention,
            'user_departments'=>$user_departments,
            'inventory_layers'=>$inventory_layers]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InterventionFormRequest $request)
    {
        $intervention = Intervention::create($request->validated());
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
        $all_layers = EditableLayerDef::where('enabled', True)->get();
        $user = Auth::user();
        $user->load(['departments']);
        $user_departments = [];
        foreach ($user->departments as $current_dep) {
            $user_departments[$current_dep->code] = $current_dep->code.' - '.$current_dep->name;
        }
        $inventory_layers = [];
        foreach ($all_layers as $current_lyr) {
            $inventory_layers[$current_lyr->name] = $current_lyr->title;
        }
        return view('intervention.edit', ['intervention'=>$intervention,
            'user_departments'=>$user_departments,
            'inventory_layers'=>$inventory_layers
        ]);
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
        $intervention->update($validated);
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
