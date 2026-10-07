<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapHarianExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected $data;
    protected $jenis;
    protected $tanggalAwal;
    protected $tanggalAkhir;

    public function __construct(
        Collection $data,
        $jenis = null,
        $tanggalAwal = null,
        $tanggalAkhir = null
    ){
        $this->data = $data;
        $this->jenis = $jenis;
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {

            $judul = 'REKAP HARIAN IZIN KELUAR PEGAWAI';

            if ($this->jenis == 'DINAS') {
                $judul .= ' (DINAS)';
            } elseif ($this->jenis == 'PRIBADI') {
                $judul .= ' (PRIBADI)';
            }

            $tanggal = Carbon::parse($this->tanggalAwal)->translatedFormat('d F Y');

            $event->sheet->mergeCells('A1:K1');
            $event->sheet->mergeCells('A2:K2');
            $event->sheet->mergeCells('A3:K3');

            $event->sheet->setCellValue('A1', 'PERUMDA AIR MINUM KOTA MAGELANG');
            $event->sheet->setCellValue('A2', $judul);
            $event->sheet->setCellValue('A3', 'Tanggal : ' . $tanggal);

            // Style baris 1
            $event->sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // Style baris 2
            $event->sheet->getStyle('A2')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // Style baris 3
            $event->sheet->getStyle('A3')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        $lastRow = $event->sheet->getHighestRow();

            $totalMenit = $this->data->sum(function ($row) {
                return (int) ($row->durasi_menit ?? 0);
            });

            $totalJam = floor($totalMenit / 60);
            $sisaMenit = $totalMenit % 60;

            $totalRow = $lastRow + 1;

            // Gabungkan A sampai I
            $event->sheet->mergeCells(
                "A{$totalRow}:I{$totalRow}"
            );

            $event->sheet->setCellValue(
                "A{$totalRow}",
                'TOTAL DURASI'
            );

            $event->sheet->setCellValue(
                "J{$totalRow}",
                $totalMenit
            );

            $event->sheet->setCellValue(
                "K{$totalRow}",
                "{$totalJam} jam {$sisaMenit} menit"
            );

            // Style total
            $event->sheet->getStyle(
                "A{$totalRow}:K{$totalRow}"
            )->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' =>
                            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'bottom' => [
                        'borderStyle' =>
                            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'left' => [
                        'borderStyle' =>
                            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'right' => [
                        'borderStyle' =>
                            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ]);

            $event->sheet->getStyle(
                "A{$totalRow}:I{$totalRow}"
            )->getAlignment()->setHorizontal('right');

            $event->sheet->getStyle(
                "J{$totalRow}:K{$totalRow}"
            )->getAlignment()->setHorizontal('center');
        },
    ];
}

    public function collection()
    {
        return $this->data->map(function ($row, $index) {
            $pegawai = $row->pegawai ?? null;
            $subbag = $pegawai && $pegawai->subbag ? $pegawai->subbag : null;
            $perijinan = $row->perijinan ?? null;

            return [
                'No'              => $index + 1,
                'Tanggal'         => $row->tanggal_keluar,
                'NIK'             => $pegawai->nik ?? '-',
                'Nama Pegawai'    => $pegawai->nama ?? '-',
                'Sub Bagian'      => $subbag->sub_bag ?? '-',
                'Jenis Izin'      => $perijinan->jenis ?? '-',
                'Nama Izin'       => $perijinan->izin ?? '-',
                'Jam Keluar'      => $row->jam_keluar ?? '-',
                'Jam Masuk'       => $row->jam_masuk ?? '-',
                'Durasi (Menit)'  => $row->durasi_menit ?? 0,
                'Status'          => $row->status ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'NIK',
            'Nama Pegawai',
            'Sub Bagian',
            'Jenis Izin',
            'Nama Izin',
            'Jam Keluar',
            'Jam Masuk',
            'Durasi (Menit)',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header
        $sheet->getStyle('A5:K5')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => [
                    'rgb' => 'FFFFFF'
                ]
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'rgb' => '0D6EFD'
                ]
            ],
        ]);

        // Border seluruh tabel
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A5:K{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            );

        // Rata tengah
        $sheet->getStyle("A5:K{$lastRow}")
            ->getAlignment()
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            );

        // Kolom tertentu rata tengah
        $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("B5:B{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("G5:G{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("I5:K{$lastRow}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("L5:L{$lastRow}")->getAlignment()->setHorizontal('center');

        return [];
    }

    public function title(): string
{
    return $this->jenis === 'PRIBADI'
        ? 'Book1 - Pribadi'
        : 'Book2 - Dinas';
}
}