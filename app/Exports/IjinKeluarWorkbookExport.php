<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class IjinKeluarWorkbookExport implements WithMultipleSheets
{
    public function __construct($jenis = null, $tanggalMulai = null, $tanggalAkhir = null)
    {
        $this->jenis = $jenis;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function sheets(): array
    {
        $jenis = $this->jenis
            ? [$this->jenis]
            : ['PRIBADI', 'DINAS'];

        return array_map(function ($jenis) {
            return new IjinKeluarExport(
                $jenis,
                $this->tanggalMulai,
                $this->tanggalAkhir
            );
        }, $jenis);
    }
}
