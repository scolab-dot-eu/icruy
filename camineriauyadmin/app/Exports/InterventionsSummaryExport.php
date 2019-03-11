<?php

namespace App\Exports;

use App\Camino;
use App\Intervention;
use App\EditableLayerDef;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class InterventionsSummaryExport 
    implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithTitle
{
    use Exportable;
    
    public function __construct(
        $ambitoGeografico,
        $tipoElem,
        $codigo_camino = null,
        $id_elem = null,
        $tarea = null,
        $forma_ejecucion = null,
        $financiacion = null,
        $from_year=null,
        $to_year=null,
        $from_date = null,
        $to_date = null)
    {
        $this->ambitoGeografico = $ambitoGeografico;
        $this->tipoElem = $tipoElem;
        $this->codigo_camino = $codigo_camino;
        if ($id_elem && $tipoElem != Camino::LAYER_NAME) {
            $this->id_elem = $id_elem;
        }
        else {
            $this->id_elem = null;
        }
        $this->tarea = $tarea;
        $this->forma_ejecucion = $forma_ejecucion;
        $this->financiacion = $financiacion;
        $this->from_year = $from_year;
        $this->to_year = $to_year;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        
        $interventionsDef = EditableLayerDef::where('name', 'interventions')->get()->first();
        if ($interventionsDef) {
            $fields = json_decode($interventionsDef->fields, true);
            $this->fieldDefs = [];
            foreach ($fields as $field) {
                $domain = array_get($field, 'domain');
                $domainDict = [];
                if ($domain!=null) {
                    foreach ($domain as $domainElement) {
                        $domainDict[$domainElement['code']] = $domainElement['definition'];
                    }
                }
                $this->fieldDefs[$field['name']] = $domainDict;
            }
        }
        else {
            Log::error("algo falló");
        }
    }
    
    public function query()
    {
        $query = DB::table(Intervention::LAYER_NAME)
            ->select(DB::raw('count(*) as num, sum(monto) as monto_total, sum(longitud) as longitud_total'))
            ->orderBy('id', 'asc');
        //$query = Intervention::query()->validated();
        if ($this->ambitoGeografico != 'UY') {
            $query->where('departamento', $this->ambitoGeografico);
        }
        if ($this->tipoElem != 'any') {
            $query->where('tipo_elem', $this->tipoElem);
        }
        if ($this->codigo_camino) {
            $query->where('codigo_camino', $this->codigo_camino);
        }
        if ($this->id_elem) {
            $query->where('id_elem', $this->id_elem);
        }
        if ($this->tarea) {
            $query->where('tarea', $this->tarea);
        }
        if ($this->forma_ejecucion) {
            $query->where('forma_ejecucion', $this->forma_ejecucion);
        }
        if ($this->financiacion) {
            $query->where('financiacion', $this->financiacion);
        }
        if ($this->from_year) {
            $from_date = $this->from_year.'-01-01';
            $query->where('fecha_interv', '>=', $from_date);
        }
        if ($this->to_year) {
            $to_date = $this->to_year.'-12-31';
            $query->where('fecha_interv', '<=', $to_date);
        }
        if ($this->from_date) {
            //$query->where('codigo_camino', $this->codigo_camino);
        }
        if ($this->to_date) {
            //$query->where('codigo_camino', $this->codigo_camino);
        }
        return $query;
    }
    
    public function getDomainDef($fieldName, $domainElemCode) {
        try {
            return $this->fieldDefs[$fieldName][$domainElemCode];
        }
        catch (\Exception $ex) {
            Log::error("algo falló: ".$fieldName. " - ". $domainElemCode);
        }
    }

    public function map($summaryRow): array
    {
        $row = [$this->ambitoGeografico, $summaryRow->monto_total, $summaryRow->longitud_total, $summaryRow->num];
        if ($this->codigo_camino) {
            $row[] = $this->codigo_camino;
        }
        if ($this->id_elem) {
            $row[] = $this->id_elem;
        }
        if ($this->tarea) {
            $row[] = $this->getDomainDef('tarea', $this->tarea);
        }
        if ($this->financiacion) {
            $row[] = $this->getDomainDef('financiacion', $this->financiacion);
        }
        if ($this->forma_ejecucion) {
            $row[] = $this->getDomainDef('forma_ejecucion', $this->forma_ejecucion);
        }
        if ($this->from_year) {
            $row[] = $this->from_year;
        }
        if ($this->to_year) {
            $row[] = $this->to_year;
        }
        return $row;
    }
    public function headings(): array
    {
        $heading1 = ['Resumen de Intervenciones - Inventario de Caminería Rural'];
        $heading2 = ['ÁMBITO', 'MONTO TOTAL', 'LONGITUD TOTAL (KM)', 'NÚMERO INTERVENCIONES'];
        if ($this->codigo_camino) {
            $heading2[] = 'CAMINO';
        }
        if ($this->id_elem) {
            $heading2[] = 'ID ELEMENTO';
        }
        if ($this->tarea) {
            $heading2[] = 'TAREA';
        }
        if ($this->financiacion) {
            $heading2[] = 'FINANCIACIÓN';
        }
        if ($this->forma_ejecucion) {
            $heading2[] = 'FORMA EJECUCIÓN';
        }
        if ($this->from_year) {
            $heading2[] = 'HASTA AÑO';
        }
        if ($this->to_year) {
            $heading2[] = 'DESDE AÑO';
        }
        return [$heading1, $heading2];
    }
    public function title(): string
    {
        return "Resumen Intervenciones - ICR";
    }



}
