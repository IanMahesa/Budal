<?php

namespace App\Imports;

use App\Models\Pegawai;
use App\Models\SubBagians;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class PegawaiImport implements ToCollection
{
    private $importedCount = 0;

    public function collection(Collection $rows)
    {
        $data = [];
        $errors = [];
        $niks = [];
        $headerIndex = null;
        $headers = [];
        $candidateIndex = null;

        foreach ($rows as $index => $row) {
            $cells = array_values($this->cells($row));
            $normalizedHeaders = array_map([$this, 'normalizeHeader'], $cells);

            if ($candidateIndex === null && count(array_filter($cells, function ($cell) {
                return trim((string) $cell) !== '';
            })) >= 5) {
                $candidateIndex = $index;
            }

            if (in_array('nama', $normalizedHeaders, true)
                && in_array('nik', $normalizedHeaders, true)) {
                $headerIndex = $index;
                $headers = $normalizedHeaders;
                break;
            }
        }

        if ($headerIndex === null && $candidateIndex !== null) {
            $headerIndex = $candidateIndex;
            $headers = ['nama', 'nik', 'jenis_kelamin', 'jabatan', 'sub_bagian', 'status'];
        }

        if ($headerIndex === null) {
            return;
        }

        foreach ($rows as $index => $row) {
            if ($index <= $headerIndex) {
                continue;
            }

            $rowNumber = $index + 1;
            $row = $this->mapRow($this->cells($row), $headers);

            if (count(array_filter($row, function ($value) {
                return trim((string) $value) !== '';
            })) === 0) {
                continue;
            }

            $values = [
                'nama' => $this->value($row, ['nama', 'name']),
                'nik' => $this->value($row, ['nik']),
                'jenis_kelamin' => strtoupper($this->value($row, ['jenis_kelamin', 'jenis kelamin'])),
                'jabatan' => $this->value($row, ['jabatan', 'posisi']),
                'sub_bagian' => $this->value($row, ['sub_bagian', 'subbagian', 'sub bagian']),
                'status' => $this->value($row, ['status']) ?: 'Aktif',
            ];

            $validator = Validator::make($values, [
                'nama' => 'required|string|max:100',
                'nik' => 'required|digits_between:5,8|unique:pegawai,nik',
                'jenis_kelamin' => 'required|in:L,P',
                'jabatan' => 'required|in:Direktur,Manajer,A.Manajer,Staff',
                'sub_bagian' => 'required|string',
                'status' => 'required|in:Aktif,Tidak Aktif',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$rowNumber}: {$message}";
                }
                continue;
            }

            if (isset($niks[$values['nik']])) {
                $errors[] = "Baris {$rowNumber}: NIK '{$values['nik']}' duplikat di dalam file.";
                continue;
            }

            $niks[$values['nik']] = true;

            $subbag = SubBagians::whereRaw('LOWER(TRIM(sub_bag)) = ?', [strtolower($values['sub_bagian'])])->first();

            if (!$subbag) {
                $errors[] = "Baris {$rowNumber}: Sub bagian '{$values['sub_bagian']}' tidak ditemukan.";
                continue;
            }

            $data[] = [
                'nama' => $values['nama'],
                'nik' => $values['nik'],
                'jenis_kelamin' => $values['jenis_kelamin'],
                'jabatan' => $values['jabatan'],
                'id_subag' => $subbag->id_subag,
                'status' => $values['status'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages(['file' => $errors]);
        }

        DB::transaction(function () use ($data) {
            foreach ($data as $pegawai) {
                Pegawai::create($pegawai);
            }
        });

        $this->importedCount += count($data);
    }

    public function hasImportedRows(): bool
    {
        return $this->importedCount > 0;
    }

    private function value($row, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($row[$key])) {
                return trim((string) $row[$key]);
            }
        }

        return '';
    }

    private function cells($row): array
    {
        return method_exists($row, 'toArray') ? $row->toArray() : (array) $row;
    }

    private function mapRow(array $cells, array $headers): array
    {
        $mapped = [];

        foreach ($headers as $index => $header) {
            if ($header !== '') {
                $mapped[$header] = $cells[$index] ?? '';
            }
        }

        return $mapped;
    }

    private function normalizeHeader($value): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim((string) $value))), '_');
    }
}
