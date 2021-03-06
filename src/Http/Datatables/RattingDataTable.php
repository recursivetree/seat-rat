<?php
namespace RecursiveTree\Seat\RattingMonitor\Http\Datatables;

use RecursiveTree\Seat\RattingMonitor\Http\Datatables\Exports\RattingDataExport;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Http\Request;

abstract class RattingDataTable extends DataTable
{
    protected $exportClass = RattingDataExport::class;

    abstract public function query($system,$days);

    public function getColumns()
    {
        return [
            ['data' => 'character_name', 'title' => 'Character'],
            ['data' => 'ratted_money', 'title' => 'Ratted Money'],
        ];
    }

    public function html()
    {
        $days = intval(request()->query("days")) ?: 30;
        $system = intval(request()->query("system")) ?: 30000142;

        return $this->builder()
            ->postAjax()
            ->parameters([
                'dom'          => '<"row"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                'buttons' => ['postCsv', 'postExcel'],
                'drawCallback' => "function(d) { d.system = $system; d.days = $days; }",
            ])
            ->columns($this->getColumns())
            ->orderBy(1, 'desc');
    }

    public function ajax()
    {
        $days = intval(request()->query("days")) ?: 30;
        $system = intval(request()->query("system")) ?: 30000142;

        $ajax = datatables()
            ->of($this->query($system,$days))
            ->editColumn('character_name', function ($row) {
                return view("rattingmonitor::charactername",[
                    "character_id"=>$row->character_id,
                    "name"=>$row->character_name
                ])->render();
            })
            ->editColumn('ratted_money', function ($row) {
                return number($row->ratted_money) . " ISK";
            })
            ->rawColumns(["character_name"]);

        return $ajax->make(true);
    }
}