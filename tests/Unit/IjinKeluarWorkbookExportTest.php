<?php

namespace Tests\Unit;

use App\Exports\IjinKeluarExport;
use App\Exports\IjinKeluarWorkbookExport;
use PHPUnit\Framework\TestCase;

class IjinKeluarWorkbookExportTest extends TestCase
{
    public function test_exporting_all_data_creates_separate_named_sheets(): void
    {
        $sheets = (new IjinKeluarWorkbookExport())->sheets();

        $this->assertCount(2, $sheets);
        $this->assertSame(
            ['Ijin Dinas', 'Ijin Pribadi'],
            array_map(function (IjinKeluarExport $sheet) {
                return $sheet->title();
            }, $sheets)
        );
    }

    public function test_exporting_one_type_creates_only_its_sheet(): void
    {
        $sheets = (new IjinKeluarWorkbookExport('PRIBADI'))->sheets();

        $this->assertCount(1, $sheets);
        $this->assertSame('Ijin Pribadi', $sheets[0]->title());
    }
}
