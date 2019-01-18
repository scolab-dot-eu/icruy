<?php

namespace App\Http\Controllers;

use App\SupportLayerDef;
use App\Http\Requests\SupportLayerDefFormRequest;
use App\Helpers\Helpers;

class SupportLayerDefController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = SupportLayerDef::all();
        return view('supportlayerdef.index', ['supportlayerdefs' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = new SupportLayerDef();
        return view('supportlayerdef.create', ['supportlayerdef'=>$data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SupportLayerDefFormRequest $request)
    {
        $validated = $request->validated();
        $validated['url'] = explode('?', $validated['url'])[0];
        Helpers::set_boolean_value($validated, 'isbaselayer');
        Helpers::set_boolean_value($validated, 'visible');
        SupportLayerDef::create($validated);
        return redirect()->route('supportlayerdefs.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SupportLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function show(SupportLayerDef $supportlayerdef)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SupportLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function edit(SupportLayerDef $supportlayerdef)
    {
        return view('supportlayerdef.edit', ['supportlayerdef'=>$supportlayerdef]);
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SupportLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function update(SupportLayerDefFormRequest $request, SupportLayerDef $supportlayerdef)
    {
        $validated = $request->validated();
        $validated['url'] = explode('?', $validated['url'])[0];
        Helpers::set_boolean_value($validated, 'isbaselayer');
        Helpers::set_boolean_value($validated, 'visible');
        $supportlayerdef->update($validated);
        return redirect()->route('supportlayerdefs.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SupportLayerDef  $editableLayer
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupportLayerDef $supportlayerdef)
    {
        $supportlayerdef->delete();
        return redirect()->route('supportlayerdefs.index');
    }
}
