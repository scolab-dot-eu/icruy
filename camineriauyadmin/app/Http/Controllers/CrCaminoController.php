<?php

namespace App\Http\Controllers;

use App\Camino;
use Illuminate\Http\Request;

class CrCaminoController extends Controller
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
            $data = Camino::where('departamento', $department_code)->get();
        }
        else {
            $data = Camino::all();
        }
        return response()->json($data);
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
    public function show(Camino $camino)
    {
        //
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
