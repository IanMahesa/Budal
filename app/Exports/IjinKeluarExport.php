<?php

namespace App\Exports;

use App\Models\IjinKeluar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class IjinKeluarExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{

    public function __construct($jenis, $tanggalMulai = null, $tanggalAkhir = null)
    {
        $this->jenis = $jenis;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }
    
    public function collection()
    {
        $query = IjinKeluar::with([
            'pegawai',
            'subbag',
            'perijinan',
            'user'
        ]);

    if ($this->jenis) {
        $query->whereHas('perijinan', function ($q) {
            $q->where('jenis', $this->jenis);
        });
    }

    if ($this->tanggalMulai) {
        $query->whereDate('tanggal_keluar', '>=', $this->tanggalMulai);
    }

    if ($this->tanggalAkhir) {
        $query->whereDate('tanggal_keluar', '<=', $this->tanggalAkhir);
    }

    return $query->get()->map(function ($row) {
            return [
                optional($row->tanggal_keluar)->format('d-m-Y'),
                optional($row->pegawai)->nama,
                optional($row->subbag)->sub_bag,
                optional($row->perijinan)->izin,
                $row->jam_keluar,
                $row->jam_masuk,
                $row->durasi_menit
                    ? floor($row->durasi_menit / 60) . ' Jam ' . ($row->durasi_menit % 60) . ' Menit'
                    : '-',
                $row->status,
                optional($row->user)->name,
            ];

        });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pegawai',
            'Sub Bagian',
            'Ijin',
            'Jam Keluar',
            'Jam Masuk',
            'Durasi',
            'Status',
            'Dibuat Oleh'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '198754'
                    ]
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Sisipkan 2 baris
                $sheet->insertNewRowBefore(1,2);

                // Judul
                $sheet->mergeCells('A1:I1');
                $judul = 'LAPORAN IJIN KELUAR PEGAWAI';
                if ($this->jenis == 'DINAS') {
                    $judul .= ' (DINAS)';
                } elseif ($this->jenis == 'PRIBADI') {
                    $judul .= ' (PRIBADI)';
                }

                $sheet->setCellValue('A1', $judul);

                // Tanggal cetak
                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue(
                    'A2',
                    'Tanggal Cetak : '.now()->format('d-m-Y H:i')
                );

                // Style Judul
                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet->getStyle('A1:A2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Header
                $sheet->getStyle('A3:I3')->applyFromArray([
                    'font'=>[
                        'bold'=>true,
                        'color'=>['rgb'=>'FFFFFF']
                    ],
                    'fill'=>[
                        'fillType'=>Fill::FILL_SOLID,
                        'startColor'=>['rgb'=>'198754']
                    ]
                ]);

                // Border
                $sheet->getStyle('A3:'.$highestColumn.($highestRow+2))
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Tengah
                $sheet->getStyle('A3:I'.($highestRow+2))
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A3:A'.($highestRow+2))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('C3:I'.($highestRow+2))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            }

        ];
    }

}