<?php

namespace App\Http\Controllers;

use App\EditableLayerDef;
use App\Intervention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\InterventionsExport;
use App\Exports\InterventionsSummaryExport;
use App\Helpers\Helpers;



class ReportController extends Controller
{
    protected function getFormVariables($user) {
        $all_layers = EditableLayerDef::enabled()->get();
        $user->load(['departments']);
        $user_departments = ['UY'=>'Uruguay'];
        foreach ($user->departments as $current_dep) {
            $user_departments[$current_dep->code] = $current_dep->code.' - '.$current_dep->name;
        }
        $inventory_layers = ['any'=>'Cualquiera'];
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
                $tareaSelect = Helpers::domainDefToSelectArray($fieldDef['domain'], [''=>'']);
            }
            elseif ($fieldDef['name']=='financiacion') {
                $financiacionSelect = Helpers::domainDefToSelectArray($fieldDef['domain'], [''=>'']);
            }
            elseif ($fieldDef['name']=='forma_ejecucion') {
                $formaEjecucionSelect = Helpers::domainDefToSelectArray($fieldDef['domain'], [''=>'']);
            }
        }
        return [
            'user_departments'=>$user_departments,
            'inventory_layers'=>$inventory_layers,
            'tareaSelect'=>$tareaSelect,
            'financiacionSelect'=>$financiacionSelect,
            'formaEjecucionSelect'=>$formaEjecucionSelect
        ];
    }
    
    public function query(Request $request)
    {
        $variables = $this->getFormVariables($request->user());
        return view('reports.query', $variables);
    }

    public function export(Request $request) {
        Log::debug(json_encode($request->all()));
        $format = $request->input('format');
        $tipoReporte = $request->input('tipo_reporte', 'detalle');
        $ambitoGeografico = $request->input('ambito', 'UY');
        $tipoElem = $request->input('tipo_elem', 'any');
        $codigo_camino = $request->input('codigo_camino');
        $id_elem = $request->input('id_elem');
        $tarea = $request->input('tarea');
        $forma_ejecucion = $request->input('forma_ejecucion');
        $financiacion = $request->input('financiacion');
        $from_year = $request->input('from_year');
        $to_year = $request->input('to_year');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        

        if ($format=='pdf') {
            return (new InterventionsExport())->download($fileName = 'intervenciones.'.$format, \Maatwebsite\Excel\Excel::TCPDF);
        }
        elseif ($format=='ods' || $format=='csv'|| $format=='tsv') {
            $fileName = 'intervenciones.'.$format;
        }
        else {
            $fileName = 'intervenciones.xlsx';
        }
        if ($tipoReporte=='detalle') {
            $interventionsExport = new InterventionsExport(
                $ambitoGeografico,
                $tipoElem,
                $codigo_camino,
                $id_elem,
                $tarea,
                $forma_ejecucion,
                $financiacion,
                $from_year,
                $to_year);
        }
        else {
            $interventionsExport = new InterventionsSummaryExport(
                $ambitoGeografico,
                $tipoElem,
                $codigo_camino,
                $id_elem,
                $tarea,
                $forma_ejecucion,
                $financiacion,
                $from_year,
                $to_year);
        }
        return $interventionsExport->download($fileName);
    }
    
    public function download(Request $request)
    {
        $interventions = Intervention::all();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'DEPARTAMENTO');
        $sheet->setCellValue('C1', 'AÑO INTERVENCIÓN');
        $sheet->setCellValue('D1', 'CAMINO');
        $sheet->setCellValue('E1', 'LONGITUD (KM)');
        $sheet->setCellValue('F1', 'MONTO');
        $sheet->setCellValue('G1', 'TAREA');
        $sheet->setCellValue('H1', 'FINANCIACIÓN');
        $sheet->setCellValue('I1', 'FORMA EJECUCIÓN');

        $row = 1;
        foreach ($interventions as $intervention) {
            $row = $row + 1;
            $sheet->setCellValue('A'.$row, $intervention->id);
            $sheet->setCellValue('B'.$row, $intervention->departamento);
            $sheet->setCellValue('C'.$row, $intervention->anyo_interv);
            $sheet->setCellValue('D'.$row, $intervention->codigo_camino);
            $sheet->setCellValue('E'.$row, $intervention->longitud);
            $sheet->setCellValue('F'.$row, $intervention->monto);
            $sheet->setCellValue('G'.$row, $intervention->tarea);
            $sheet->setCellValue('H'.$row, $intervention->financiacion);
            $sheet->setCellValue('I'.$row, $intervention->forma_ejecucion);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('/tmp/helloworld.xlsx');
        return response()->file('/tmp/helloworld.xlsx');
            //->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');;
    }
}
