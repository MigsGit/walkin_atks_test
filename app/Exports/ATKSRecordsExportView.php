<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ATKSRecordsExportView implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $records;
    protected $start;
    protected $end;

    function __construct($records,$start,$end)
    {
        $this->records = $records;
        $this->start = $start;
        $this->end = $end;
    }

    public function view(): View
    {
    	$export_rec = $this->records;
        $start = $this->start;
        $end = $this->end;

        return view('recordsexp', compact('export_rec','start','end'));
    }
}
