<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportReport implements FromView
{
     /**
    * @return \Illuminate\Support\Collection
    */
    protected $walkin_pmi;
    protected $rider_pmi;
    protected $walkin_subcon;
    protected $rider_subcon;
    protected $total_pmi_all;
    protected $total_subcon_all;
    protected $walkin_all;
    protected $rider_all;
    protected $start;
    protected $end;

    function __construct($walkin_pmi,$rider_pmi,$walkin_subcon,$rider_subcon,$total_pmi_all,$total_subcon_all,$walkin_all,$rider_all,$start,$end)
    {
        // $walkin_pmi;
        // $rider_pmi;
        // $walkin_subcon;
        // $rider_subcon;
        // $total_pmi_all;
        // $total_subcon_all;
        // $walkin_all;
        // $rider_all;
        // $start;
        // $end;

        $this->walkin_pmi = $walkin_pmi;
        $this->rider_pmi = $rider_pmi;
        $this->total_pmi_all = $total_pmi_all;

        $this->walkin_subcon = $walkin_subcon;
        $this->rider_subcon = $rider_subcon;
        $this->total_subcon_all = $total_subcon_all;

        $this->walkin_all = $walkin_all;
        $this->rider_all = $rider_all;
        $this->start = $start;
        // $end = $this->end;
        $this->end = $end;
    }

    public function view(): View
    {

        return view('reporttotal',
        [
            'walkin_pmi' => $this->walkin_pmi,
            'rider_pmi' => $this->rider_pmi,
            'walkin_subcon' => $this->walkin_subcon,
            'rider_subcon' => $this->rider_subcon,
            'total_pmi_all' => $this->total_pmi_all,
            'total_subcon_all' => $this->total_subcon_all,
            'walkin_all' => $this->walkin_all,
            'rider_all' => $this->rider_all,
            'start' => $this->start,
            'end' => $this->end
        ]);
    }
}
