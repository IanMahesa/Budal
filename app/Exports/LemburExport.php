<?php

namespace App\Exports;

use App\Models\DetailLembur;
use App\Models\Lembur;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LemburExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithEvents,
    WithTitle
{
    protected $jenis;
    protected $tanggalMulai;
    protected $tanggalAkhir;

    public function __construct($jenis, $tanggalMulai = null, $tanggalAkhir = null)
    {
        $this->jenis = $jenis;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
    }

    public function title(): string
    {
        return $this->jenis === 'TERENCANA' ? 'Lembur Terencana' : 'Lembur Dadakan';
    }

    public function collection()
    {
        $query = Lembur::with([
            'subag',
            'pembuat',
            'detailLembur' => function ($query) {
                $query->where('is_delete', false)->with('pegawai');
            },
        ])->where('is_delete', false);

        if ($this->jenis) {
            $query->where('jenis_lembur', $this->jenis);
        }

        if ($this->tanggalMulai) {
            $query->whereDate('tanggal_lembur', '>=', $this->tanggalMulai);
        }

        if ($this->tanggalAkhir) {
            $query->whereDate('tanggal_lembur', '<=', $this->tanggalAkhir);
        }

        $lemburList = $query
            ->orderBy('tanggal_lembur')
            ->orderBy('id_lembur')
            ->get();

        $rows = collect();

        foreach ($lemburList as $lembur) {
            if ($lembur->detailLembur->isEmpty()) {
                $rows->push($this->row($lembur));
                continue;
            }

            foreach ($lembur->detailLembur as $detail) {
                $rows->push($this->row($lembur, $detail));
            }
        }

        return $rows;
    }

    protected function row(Lembur $lembur, ?DetailLembur $detail = null): array
    {
        $durasiAktual = null;
        if ($detail) {
            $durasiAktual = $detail->total_menit;
            if ($detail->jam_mulai && $detail->jam_selesai) {
                $durasiAktual = Carbon::parse($detail->jam_mulai)
                    ->diffInMinutes(Carbon::parse($detail->jam_selesai));
            }
        }

        return [
            $lembur->tanggal_lembur
                ? $lembur->tanggal_lembur->format('d-m-Y')
                : '-',
            $lembur->jenis_lembur,
            optional($detail ? $detail->pegawai : null)->nama ?? '-',
            optional($lembur->subag)->sub_bag ?? '-',
            $this->formatTime($lembur->jam_mulai),
            $this->formatTime($lembur->jam_selesai),
            $this->formatDuration($lembur->durasi_menit),
            $this->formatDateTime($detail ? $detail->jam_mulai : null),
            $this->formatDateTime($detail ? $detail->jam_selesai : null),
            $this->formatDuration($durasiAktual),
            $lembur->keterangan ?? '-',
            optional($lembur->pembuat)->name ?? '-',
            $this->formatDuration($durasiAktual - $lembur->durasi_menit),
            $detail ? $detail->status_kehadiran : '-',
            $lembur->status,
        ];
    }

    protected function formatTime($time): string
    {
        return $time ? substr((string) $time, 0, 5) : '-';
    }

    protected function formatDateTime($dateTime): string
    {
        return $dateTime ? Carbon::parse($dateTime)->format('d-m-Y H:i') : '-';
    }

    protected function formatDuration($minutes): string
    {
        if ($minutes === null) {
            return '-';
        }

        $minutes = (int) $minutes;

        return floor($minutes / 60) . ' Jam ' . ($minutes % 60) . ' Menit';
    }

    public function headings(): array
    {
        return [
            'Tanggal Lembur',
            'Pegawai',
            'Sub Bagian',
            'Jenis Lembur',
            'Jadwal Mulai',
            'Jadwal Selesai',
            'Durasi Jadwal',
            'Scan Mulai',
            'Scan Selesai',
            'Durasi Aktual',
            'Keterangan',
            'Dibuat Oleh',
            'Total Durasi Aktual',
            'Status Kehadiran',
            'Status Lembur',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '198754',
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();

                $sheet->insertNewRowBefore(1, 2);

                $jenisLabel = $this->jenis === 'TERENCANA' ? 'TERENCANA' : 'DADAKAN';
                $sheet->mergeCells('A1:O1');
                $sheet->setCellValue('A1', 'LAPORAN LEMBUR (' . $jenisLabel . ')');

                $sheet->mergeCells('A2:O2');
                $sheet->setCellValue(
                    'A2',
                    'Tanggal Cetak : ' . now()->format('d-m-Y H:i')
                );

                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(16);
                $sheet->getStyle('A1:A2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A3:O3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '198754'],
                    ],
                ]);

                $lastRow = $highestRow + 2;
                $sheet->getStyle('A3:O' . $lastRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A3:O' . $lastRow)
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A4:B' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E4:J' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('M4:O' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
