<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapPegawaiExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->mergeCells('A1:J1');
                $event->sheet->mergeCells('A2:J2');
                $event->sheet->mergeCells('A3:J3');

                $event->sheet->setCellValue('A1', 'PERUMDA AIR MINUM KOTA MAGELANG');
                $event->sheet->setCellValue('A2', 'REKAP DURASI IZIN KELUAR PEGAWAI');
                $event->sheet->setCellValue('A3', 'Periode : ' . $this->periodLabel());

                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => 'center'],
                ]);
                $event->sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => 'center'],
                ]);
                $event->sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $lastRow = $event->sheet->getHighestRow();
                $totalRow = $lastRow + 1;

                $event->sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                $event->sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $event->sheet->setCellValue("F{$totalRow}", "=SUM(F6:F{$lastRow})");
                $event->sheet->setCellValue("G{$totalRow}", "=SUM(G6:G{$lastRow})");
                $event->sheet->setCellValue("H{$totalRow}", "=SUM(H6:H{$lastRow})");
                $event->sheet->setCellValue("I{$totalRow}", "=SUM(I6:I{$lastRow})");
                $event->sheet->setCellValue("J{$totalRow}", "=SUM(J6:J{$lastRow})");

                $event->sheet->getStyle("A{$totalRow}:J{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                        'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);
                $event->sheet->getStyle("A{$totalRow}:E{$totalRow}")
                    ->getAlignment()->setHorizontal('right');
                $event->sheet->getStyle("F{$totalRow}:J{$totalRow}")
                    ->getAlignment()->setHorizontal('center');
            },
        ];
    }

    protected function periodLabel(): string
    {
        $request = $this->request;
        $periode = $request->input('periode', 'harian');

        if ($periode === 'harian' && $request->filled('tanggal')) {
            return Carbon::parse($request->input('tanggal'))->translatedFormat('d F Y');
        }

        if ($periode === 'bulanan' && $request->filled('bulan') && $request->filled('tahun')) {
            return Carbon::createFromDate($request->input('tahun'), $request->input('bulan'), 1)
                ->translatedFormat('F Y');
        }

        if ($periode === 'tahunan' && $request->filled('tahun')) {
            return (string) $request->input('tahun');
        }

        if ($periode === 'range' && $request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            return Carbon::parse($request->input('tanggal_awal'))->translatedFormat('d F Y')
                . ' s/d ' . Carbon::parse($request->input('tanggal_akhir'))->translatedFormat('d F Y');
        }

        return 'Semua Periode';
    }

    public function collection()
    {
        $request = $this->request;

        $query = DB::table('ijin_keluar as ik')
            ->join('pegawai as p', 'ik.id_peg', '=', 'p.id_peg')
            ->join('subag as sb', 'ik.id_subag', '=', 'sb.id_subag')
            ->join('bagian as b', 'ik.id_bag', '=', 'b.id_bag')
            ->join('perijinan as i', 'ik.id_ijin', '=', 'i.id_ijin')

            ->select(
                'p.nik',
                'p.nama',
                'b.bag',
                'sb.sub_bag',

                DB::raw("COUNT(CASE WHEN i.jenis='PRIBADI' THEN 1 END) as jumlah_pribadi"),
                DB::raw("SUM(CASE WHEN i.jenis='PRIBADI' THEN ik.durasi_menit ELSE 0 END) as durasi_pribadi"),

                DB::raw("COUNT(CASE WHEN i.jenis='DINAS' THEN 1 END) as jumlah_dinas"),
                DB::raw("SUM(CASE WHEN i.jenis='DINAS' THEN ik.durasi_menit ELSE 0 END) as durasi_dinas"),

                DB::raw("SUM(ik.durasi_menit) as total_durasi")
            );

        /*
        |--------------------------------------------------------------------------
        | FILTER PERIODE
        |--------------------------------------------------------------------------
        */

        $applyFilter = filter_var($request->input('filter_applied', false), FILTER_VALIDATE_BOOLEAN);
        $periode = $request->input('periode', 'harian');
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        if ($applyFilter) {
            if ($periode == 'harian') {
                if (!empty($tanggal)) {
                    $query->whereDate('ik.tanggal_keluar', $tanggal);
                }
            } elseif ($periode == 'bulanan') {
                if (!empty($bulan)) {
                    $query->whereMonth('ik.tanggal_keluar', $bulan);
                }
                if (!empty($tahun)) {
                    $query->whereYear('ik.tanggal_keluar', $tahun);
                }
            } elseif ($periode == 'tahunan') {
                if (!empty($tahun)) {
                    $query->whereYear('ik.tanggal_keluar', $tahun);
                }
            } elseif ($periode == 'range') {
                if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
                    $query->whereBetween('ik.tanggal_keluar', [$tanggalAwal, $tanggalAkhir]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BAGIAN
        |--------------------------------------------------------------------------
        */

        if ($request->filled('id_bag')) {
            $query->where('ik.id_bag', $request->id_bag);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER SUB BAGIAN
        |--------------------------------------------------------------------------
        */

        if ($request->filled('id_subag')) {
            $query->where('ik.id_subag', $request->id_subag);
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER PEGAWAI
        |--------------------------------------------------------------------------
        */

        if ($request->filled('id_peg')) {
            $query->where('ik.id_peg', $request->id_peg);
        }

        $query->whereNotNull('ik.jam_masuk');

        return $query
            ->groupBy(
                'p.id_peg',
                'p.nik',
                'p.nama',
                'b.bag',
                'sb.sub_bag'
            )
            ->orderBy('p.nama')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    'No' => $index + 1,
                    'NIK' => $row->nik,
                    'Nama Pegawai' => $row->nama,
                    'Bagian' => $row->bag,
                    'Sub Bagian' => $row->sub_bag,
                    'Jumlah Izin Pribadi' => $row->jumlah_pribadi,
                    'Durasi Pribadi (Menit)' => $row->durasi_pribadi,
                    'Jumlah Izin Kedinasan' => $row->jumlah_dinas,
                    'Durasi Kedinasan (Menit)' => $row->durasi_dinas,
                    'Total Durasi (Menit)' => $row->total_durasi,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Pegawai',
            'Bagian',
            'Sub Bagian',
            'Jumlah Izin Pribadi',
            'Durasi Pribadi (Menit)',
            'Jumlah Izin Kedinasan',
            'Durasi Kedinasan (Menit)',
            'Total Durasi (Menit)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = max(5, $sheet->getHighestRow());

        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '0D6EFD'],
            ],
        ]);

        $sheet->getStyle("A5:J{$lastRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle("A5:J{$lastRow}")
            ->getAlignment()->setVertical('center');
        $sheet->getStyle("A5:A{$lastRow}")
            ->getAlignment()->setHorizontal('center');
        $sheet->getStyle("F5:J{$lastRow}")
            ->getAlignment()->setHorizontal('center');

        return [];
    }

    public function title(): string
    {
        return 'Rekap Pegawai';
    }
}