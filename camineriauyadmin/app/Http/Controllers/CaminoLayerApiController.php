<?php

namespace App\Http\Controllers;

use App\Camino;
use App\ChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CaminoLayerApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $department_code = $request->query('dep', null);
        if ($department_code) {
            $data = Camino::where('departamento', $department_code);
            if (!$this->checkDepartment($department_code)) {
                $data = $data->where('status',
                    '!=', ChangeRequest::FEATURE_STATUS_PENDING_CREATE);
            }
        }
        else {
            $data = Camino::where('status',
                '!=', ChangeRequest::FEATURE_STATUS_PENDING_CREATE);
        }
        return response()->json($data->get());
    }
    
    protected function checkDepartment($department_code) {
        if (!$this->user()->isAdmin()) {
            if (!$this->user()->isManager()) {
                return false;
            }
            if ($this->user()->departments()->where('code', $department_code)->count()==0) {
                return false;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Camino  $camino
     * @return \Illuminate\Http\Response
     */
    public function show($camino_id)
    {
        
        $camino = Camino::findOrFail($camino_id); // Workaround. Why type-hinting fails?
        if ($camino->status == ChangeRequest::FEATURE_STATUS_PENDING_CREATE) {
            if (!$this->checkDepartment($camino->departamento)) {
                return response()->json(null); // TODO: should we fail?
            }
        }
        return response()->json($camino);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Camino  $camino
     * @return \Illuminate\Http\Response
     */
    public function edit(Camino $camino)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Camino  $camino
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Camino $camino)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Camino  $camino
     * @return \Illuminate\Http\Response
     */
    public function destroy(Camino $camino)
    {
        //
    }
}
