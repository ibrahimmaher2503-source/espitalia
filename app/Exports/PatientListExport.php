<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Category\Models\Category;
use Modules\Appointment\Models\Appointment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
class PatientListExport implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell
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
        $query = Appointment::query()->with('cliniccenter');
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
            $query;
        } else {
            $query->where('doctor_id', auth()->id());
        }
        $status = config('appointment.STATUS');
        $payment_status = config('appointment.PAYMENT_STATUS');

        $query->orderBy('created_at', 'desc');

        $query->whereDate('created_at', '>=', $this->dateRange[0]);

        $query->whereDate('created_at', '<=', $this->dateRange[1]);

        $query = $query->get();

        $newQuery = $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'varification_status':
                        $selectedData[$column] = __('customer.msg_unverified');
                        if ($row['email_verified_at']) {
                            $selectedData[$column] = __('customer.msg_verified');
                        }
                        break;

                    case 'Patient Name':
                        $selectedData[$column] = $row->user->full_name;

                        break;
                    case 'doctor':

                        $selectedData[$column] = $row->employee->full_name;
                        break;
                    case 'Clinic_Name':

                        $selectedData[$column] = $row->cliniccenter->clinic_name;
                        break;

                    case 'services':
                        $selectedData[$column] = $row->clinicservice->name;
                        break;
                    case 'start_date':
                        $startDate = Carbon::parse($row->start_date_time);
                        $selectedData[$column] = $startDate->toDateString();
                        break;

                    case 'start_time':
                        $startTime = Carbon::parse($row->start_date_time);
                        $selectedData[$column] = $startTime->toTimeString();
                        break;
                    case 'payment_status':
                        if ($row->appointmenttransaction) {
                            if ($row->appointmenttransaction->payment_status != 1) {
                                $selectedData[$column] = __('Pending');
                            } else {
                                $selectedData[$column] = __('Paid');
                            }
                        } else {
                            $selectedData[$column] = __('Unknown');
                        }
                        break;
                    case 'is_banned':
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
