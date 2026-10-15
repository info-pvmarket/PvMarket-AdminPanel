<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Import template: branded header row, optional pre-filled rows, and optional
 * in-cell dropdowns keyed by heading label, e.g. ['Sell Type' => [...]].
 */
class ProductImportTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(
        private array $headings,
        private array $rows = [],
        private array $dropdowns = [],
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet): array
    {
        $count   = count($this->headings);
        $lastCol = Coordinate::stringFromColumnIndex($count);

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F15A2C']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);
        for ($i = 1; $i <= $count; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(22);
        }
        $sheet->freezePane('A2');

        foreach ($this->headings as $i => $heading) {
            $options = $this->dropdowns[$heading] ?? [];
            $list    = implode(',', $options);
            // Excel caps an in-cell list at 255 characters; longer lists stay free text.
            if ($options === [] || strlen($list) > 255) {
                continue;
            }
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $dv  = $sheet->getCell("{$col}2")->getDataValidation();
            $dv->setType(DataValidation::TYPE_LIST)
                ->setAllowBlank(true)
                ->setShowDropDown(true)
                ->setFormula1('"' . $list . '"');
            $dv->setSqref("{$col}2:{$col}1001");
        }

        return [];
    }
}