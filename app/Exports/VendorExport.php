<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
class VendorExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    public array $columns;

    public array $dateRange;

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
            $modifiedHeadings[] = ucwords(str_replace('_', ' ', $column));
        }

        return $modifiedHeadings;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = User::role('vendor');

        $query->whereDate('users.created_at', '>=', $this->dateRange[0]);

        $query->whereDate('users.created_at', '<=', $this->dateRange[1]);

        $query->orderBy('users.updated_at', 'desc');

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'Name':
                        $selectedData[$column] = $row->first_name . ' ' . $row->last_name;
                        break;

                    case 'mobile':
                        $selectedData[$column] = $row->mobile;
                        break;

                    case 'varification_status':
                        $selectedData[$column] = __('customer.msg_unverified');
                        if ($row['email_verified_at']) {
                            $selectedData[$column] = __('customer.msg_verified');
                        }
                        break;

                    case 'status':
                        $selectedData[$column] = 'no';
                        if ($row[$column]) {
                            $selectedData[$column] = 'yes';
                        }
                        break;


                    default:
                        $selectedData[$column] = $row[$column];
                        break;
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
