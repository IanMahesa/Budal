<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapHarianMultiExport implements WithMultipleSheets
{
    protected $data;
    protected $tanggalAwal;
    protected $tanggalAkhir;

    public function __construct(
        Collection $data,
        $tanggalAwal = null,
        $tanggalAkhir = null
    ) {
        $this->data = $data;
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function sheets(): array
    {
        $pribadi = $this->data
            ->filter(function ($row) {
                return strtoupper(optional($row->perijinan)->jenis) === 'PRIBADI';
            })
            ->values();

        $dinas = $this->data
            ->filter(function ($row) {
                return strtoupper(optional($row->perijinan)->jenis) === 'DINAS';
            })
            ->values();

        return [
            new RekapHarianExport(
                $pribadi,
                'PRIBADI',
                $this->tanggalAwal,
                $this->tanggalAkhir
            ),

            new RekapHarianExport(
                $dinas,
                'DINAS',
                $this->tanggalAwal,
                $this->tanggalAkhir
            ),
        ];
    }
}