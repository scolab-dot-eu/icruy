<?php

namespace App\Http\Controllers;

use App\EditableLayerDef;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\SearchApiFormRequest;
use App\Department;


class DepartamentosController extends Controller
{

    public function list(Request $request) {
        $all_departments = Department::all('code', 'name', 'layer_name');
        return response()->json($all_departments);
    }
}
