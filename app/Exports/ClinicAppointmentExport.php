<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Appointment\Models\Appointment;
use App\Models\Setting;

class ClinicAppointmentExport implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents
{
    public array $columns;
    public array $dateRange;
    public $exportDoctorId;

    public function __construct($columns, $dateRange, $exportDoctorId)
    {
        $this->columns = $columns;
        $this->dateRange = $dateRange;
        $this->exportDoctorId = $exportDoctorId;
    }

    /**
     * Define the starting cell for the data rows.
     */
    public function startCell(): string
    {
        return 'A3'; // Data starts from row 3
    }

    /**
     * Define the headings for the data table.
     */
    public function headings(): array
    {
        return array_map(function ($column) {
            return ucwords(str_replace('_', ' ', $column));
        }, $this->columns);
    }

    /**
     * Collect the data to be exported.
     */
    public function collection()
    {
        $query = Appointment::SetRole(auth()->user())
            ->with('payment', 'commissionsdata', 'patientEncounter', 'cliniccenter')
            ->whereRaw(
                'CAST(CONCAT(appointment_date, " ", appointment_time) AS DATETIME) >= ?',
                [$this->dateRange[0]]
            )
            ->whereRaw(
                'CAST(CONCAT(appointment_date, " ", appointment_time) AS DATETIME) <= ?',
                [$this->dateRange[1]]
            )
            ->orderBy('id', 'asc')
            ->get();

        return $query->map(function ($row) {
            $selectedData = [];

            foreach ($this->columns as $column) {
                switch ($column) {
                    case 'Patient Name':
                        $selectedData[$column] = optional($row->user)->full_name;
                        break;

                    case 'doctor':
                        $selectedData[$column] = optional($row->doctor)->full_name;
                        break;

                    case 'services':
                        $selectedData[$column] = optional($row->clinicservice)->name;
                        break;

                    case 'start_date_time':
                        $setting = Setting::where('name', 'date_formate')->first();
                        $dateformate = $setting ? $setting->val : 'Y-m-d';
                        $setting = Setting::where('name', 'time_formate')->first();
                        $timeformate = $setting ? $setting->val : 'h:i A';
                        $date = date($dateformate, strtotime($row->appointment_date ?? '--'));
                        $time = date($timeformate, strtotime($row->appointment_time ?? '--'));

                        $selectedData[$column] = $date . ' ' . $time;
                        break;

                    default:
                        $selectedData[$column] = $row[$column];
                        break;
                }
            }

            return $selectedData;
        });
    }

    /**
     * Customize the sheet using events.
     */
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
