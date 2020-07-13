<?php

namespace App\Http\Controllers;

use App\EditableLayerDef;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\SearchApiFormRequest;


class SearchController extends Controller
{
    protected function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);
        
        $words = explode(' ', $term);
        
        foreach($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if(strlen($word) >= 3) {
                $words[$key] = '+' . $word . '*';
            }
        }
        
        $searchTerm = implode( ' ', $words);
        
        return $searchTerm;
    }
    
    public function search(SearchApiFormRequest $request) {
        //Log::debug(json_encode($request->all()));
        $searchString = $request->input('q');
        $departamento = $request->input('departamento', '');
        if ($departamento != '') {
            $searchString = $searchString . ' ' . $departamento;
            $searchString = $this->fullTextWildcards($searchString);
            $query = DB::table('globalsearch')
            ->whereRaw('MATCH (texto) AGAINST (? IN BOOLEAN MODE)' ,
                $searchString)->where('departamento', $departamento);
        }
        else {
            $searchString = $this->fullTextWildcards($searchString);
            $query = DB::table('globalsearch')
            ->whereRaw('MATCH (texto) AGAINST (? IN BOOLEAN MODE)' ,
                array($searchString));
        }
        $data = $query->get();
        return response()->json($data);
    }
}
