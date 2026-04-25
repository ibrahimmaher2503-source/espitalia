<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Category\Models\Category;
use Modules\Promotion\Models\Coupon;
use Modules\Promotion\Models\Promotion;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
class couponsExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    public array $columns;

    public array $dateRange;
    public $id;

    public function __construct($columns, $dateRange)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;


    }

    public function headings(): array
    {
        $modifiedHeadings = [];

        foreach ($this->columns as $column) {
            // Capitalize each word and replace underscores with spaces
            if ($column != $this->columns[1]) {
                $modifiedHeadings[] = ucwords(str_replace('_', ' ', $column));
            }
        }

        return $modifiedHeadings;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        $Promotion_id = $this->columns[1];
        $query = Coupon::where('promotion_id', $Promotion_id);

        $query->whereDate('created_at', '>=', $this->dateRange[0]);

        $query->whereDate('created_at', '<=', $this->dateRange[1]);

        $query = $query->get();



        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                if ($column != $this->columns[1]) {
                    switch ($column) {
                        case 'status':
                            $selectedData[$column] = 'inactive';
                            if ($row[$column]) {
                                $selectedData[$column] = 'active';
                            }
                            break;
                        default:
                            $selectedData[$column] = $row[$column];
                            break;
                    }
                }
            }

            return $selectedData;
        });

        return $newQuery;
    }
    public function startCell(): string
    {
        return 'A3'; // Data starts from row 3
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->columns));

                // Add "From Date" and "To Date" at the top
                $sheet->setCellValue('A1', "From Date: {$this->dateRange[0]}");
                $sheet->setCellValue('A2', "To Date: {$this->dateRange[1]}");

                // Merge cells for a cleaner header
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");

                // Style the headers (optional)
                $sheet->getStyle('A1:A2')->getFont()->setBold(true);
                $sheet->getStyle('A1:A2')->getFont()->setSize(12);
            },
        ];
    }
}
