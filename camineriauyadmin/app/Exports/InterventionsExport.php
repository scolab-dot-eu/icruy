<?php

namespace App\Exports;

use App\Intervention;
use App\EditableLayerDef;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Camino;

class InterventionsExport 
    implements FromQuery, WithMapping, WithHeadings, WithTitle, WithEvents
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
        
        if ($tipoElem != 'any') {
            $lyr = EditableLayerDef::where('name', $tipoElem)->get()->first();
            $this->tipoElemLabel = $lyr->title;
        }
        else {
            $this->tipoElemLabel = 'Cualquiera';
        }
        $interventionsDef = EditableLayerDef::where('name', Intervention::LAYER_NAME)->get()->first();
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
            Log::error("InterventionsExport: algo falló");
        }
    }
    
    public function query()
    {
        $query = Intervention::query()->validated();
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
            if ($domainElemCode) {
                return $this->fieldDefs[$fieldName][$domainElemCode];
            }
        }
        catch (\Exception $ex) {
            Log::error("algo falló: ".$fieldName. " - ". $domainElemCode);
            Log::error($this->fieldDefs);
        }
        return "";
    }

    public function map($intervention): array
    {
        if ($this->id_elem!==null) {
            return [
                $intervention->id,
                $intervention->departamento,
                $intervention->fecha_interv,
                $intervention->codigo_camino,
                $intervention->id_elem,
                $intervention->longitud,
                $intervention->monto,
                $this->getDomainDef('tarea', $intervention->tarea),
                $this->getDomainDef('financiacion', $intervention->financiacion),
                $this->getDomainDef('forma_ejecucion', $intervention->forma_ejecucion)
            ];
        }
        return [
            $intervention->id,
            $intervention->departamento,
            $intervention->fecha_interv,
            $intervention->codigo_camino,
            $intervention->longitud,
            $intervention->monto,
            $this->getDomainDef('tarea', $intervention->tarea),
            $this->getDomainDef('financiacion', $intervention->financiacion),
            $this->getDomainDef('forma_ejecucion', $intervention->forma_ejecucion)
        ];
    }
    public function headings(): array
    {
        $titleFromYearFilter = "Desde: ";
        $titleToYearFilter = "Hasta: ";
        if ($this->from_year) {
            $titleFromYearFilter = $titleFromYearFilter . $this->from_year." ";
        }
        if ($this->to_year) {
            $titleToYearFilter = $titleToYearFilter . $this->to_year;
        }
        $tipoElemFilterTitle = 'Tipo de elemento: '.$this->tipoElemLabel;
        if ($this->id_elem!==null) {
            $header = ['ID', 'DEPARTAMENTO', 'FECHA INTERVENCIÓN', 'CAMINO', 'ID ELEMENTO','LONGITUD (KM)', 'MONTO', 'TAREA', 'FINANCIACIÓN', 'FORMA EJECUCIÓN'];
        }
        else {
            $header = ['ID', 'DEPARTAMENTO', 'FECHA INTERVENCIÓN', 'CAMINO', 'LONGITUD (KM)', 'MONTO', 'TAREA', 'FINANCIACIÓN', 'FORMA EJECUCIÓN'];
        }
    
        return [
            ['Detalle de Intervenciones - Inventario de Caminería Rural'],
            [$titleFromYearFilter, $titleToYearFilter, $tipoElemFilterTitle],
            [''],
            $header
        ];
    }
    public function title(): string
    {
        return "Intervenciones - ICR";
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->autoSize();
                $columnA = $sheet->getColumnDimension('A');
                $columnA->setAutoSize(false);
                $columnA->setWidth(12);
            }
            ];
    }
}
