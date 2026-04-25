<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Appointment\Models\BillingRecord;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
class BillingExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
{
    public array $columns;

    public array $dateRange;

    public function __construct($columns, $dateRange)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;
    }
    public function startCell(): string
    {
        return 'A3'; // Data starts from row 3
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
        $query = BillingRecord::SetRole(auth()->user());

        $query->whereDate('created_at', '>=', $this->dateRange[0]);

        $query->whereDate('created_at', '<=', $this->dateRange[1]);

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'encounter_id':
                        $selectedData[$column] = $row->encounter_id;

                        break;

                    case 'user_id':
                        $selectedData[$column] = optional($row->user)->full_name;

                        break;
                    case 'doctor_id':

                        $selectedData[$column] = optional($row->doctor)->full_name;
                        break;

                    case 'clinic_id':
                        $selectedData[$column] = optional($row->clinic)->name;
                        break;

                    case 'service_id':
                        $selectedData[$column] = optional($row->clinicservice)->name;
                        break;

                    case 'total_amount':
                        $selectedData[$column] = $row->total_amount;
                        break;

                    case 'date':
                        $selectedData[$column] = $row->date;
                        break;

                    case 'payment_status':
                        if ($row->payment_status) {
                            $payment_status = 'Paid';
                        } else {
                            $payment_status = 'Unpaid';
                        }
                        $selectedData[$column] = $payment_status;
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
