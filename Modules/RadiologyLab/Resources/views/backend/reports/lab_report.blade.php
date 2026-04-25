<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laboratory Report - {{ $order->order_number }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #333333; line-height: 1.4;">

    {{-- HEADER --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td style="width: 70px; vertical-align: top; padding-right: 12px;">
                @php
                    $logo = setting('logo') ?? null;
                @endphp
                @if($logo)
                    <img src="{{ public_path($logo) }}" alt="Logo" style="max-width: 60px; max-height: 60px;">
                @endif
            </td>
            <td style="vertical-align: top;">
                <div style="font-size: 18px; font-weight: bold; color: #1a1a1a; margin-bottom: 2px;">
                    {{ config('app.name') }}
                </div>
                <div style="font-size: 10px; color: #666666;">
                    @if(setting('address'))
                        {{ setting('address') }}
                    @endif
                    @if(setting('phone'))
                        &nbsp;|&nbsp; Phone: {{ setting('phone') }}
                    @endif
                    @if(setting('email'))
                        &nbsp;|&nbsp; {{ setting('email') }}
                    @endif
                </div>
            </td>
            <td style="width: 200px; text-align: right; vertical-align: top;">
                <div style="font-size: 10px; color: #888888;">Order #</div>
                <div style="font-size: 14px; font-weight: bold; color: #1a1a1a;">{{ $order->order_number }}</div>
            </td>
        </tr>
    </table>

    <div style="background-color: #2c3e50; color: #ffffff; text-align: center; padding: 8px 0; font-size: 14px; font-weight: bold; letter-spacing: 1px; margin-bottom: 15px;">
        LABORATORY REPORT
    </div>

    {{-- PATIENT & ORDER INFORMATION --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #dddddd;">
                    <tr>
                        <td colspan="2" style="background-color: #f0f4f8; padding: 5px 8px; font-weight: bold; font-size: 11px; color: #2c3e50; border-bottom: 1px solid #dddddd;">
                            PATIENT INFORMATION
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; font-size: 10px; color: #888888; width: 35%; border-bottom: 1px solid #eeeeee;">Patient Name</td>
                        <td style="padding: 4px 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #eeeeee;">
                            {{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; font-size: 10px; color: #888888; border-bottom: 1px solid #eeeeee;">Age / Gender</td>
                        <td style="padding: 4px 8px; font-size: 11px; border-bottom: 1px solid #eeeeee;">
                            @if($patient->date_of_birth)
                                {{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} yrs
                            @else
                                N/A
                            @endif
                            /
                            {{ ucfirst($patient->gender ?? 'N/A') }}
                        </td>
                    </tr>
                    @if($patient->phone_number ?? $patient->mobile ?? null)
                    <tr>
                        <td style="padding: 4px 8px; font-size: 10px; color: #888888;">Phone</td>
                        <td style="padding: 4px 8px; font-size: 11px;">{{ $patient->phone_number ?? $patient->mobile ?? '' }}</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #dddddd;">
                    <tr>
                        <td colspan="2" style="background-color: #f0f4f8; padding: 5px 8px; font-weight: bold; font-size: 11px; color: #2c3e50; border-bottom: 1px solid #dddddd;">
                            ORDER DETAILS
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; font-size: 10px; color: #888888; width: 35%; border-bottom: 1px solid #eeeeee;">Order Number</td>
                        <td style="padding: 4px 8px; font-size: 11px; font-weight: bold; border-bottom: 1px solid #eeeeee;">{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; font-size: 10px; color: #888888; border-bottom: 1px solid #eeeeee;">Order Date</td>
                        <td style="padding: 4px 8px; font-size: 11px; border-bottom: 1px solid #eeeeee;">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 8px; font-size: 10px; color: #888888; border-bottom: 1px solid #eeeeee;">Report Date</td>
                        <td style="padding: 4px 8px; font-size: 11px; border-bottom: 1px solid #eeeeee;">{{ $reportDate }}</td>
                    </tr>
                    @if($doctor)
                    <tr>
                        <td style="padding: 4px 8px; font-size: 10px; color: #888888;">Referring Doctor</td>
                        <td style="padding: 4px 8px; font-size: 11px; font-weight: bold;">
                            Dr. {{ $doctor->first_name ?? '' }} {{ $doctor->last_name ?? '' }}
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- RESULTS GROUPED BY CATEGORY --}}
    @php
        $grouped = $items->groupBy(fn($item) => $item->labTest->category->name ?? 'Uncategorized');
        $normalCount = $items->where('result_status', 'normal')->count();
        $abnormalCount = $items->where('result_status', 'abnormal')->count();
        $criticalCount = $items->where('result_status', 'critical')->count();
    @endphp

    @foreach($grouped as $categoryName => $categoryItems)
        <div style="background-color: #f0f4f8; padding: 5px 8px; font-weight: bold; font-size: 11px; color: #2c3e50; border: 1px solid #dddddd; border-bottom: none; margin-top: 10px;">
            {{ $categoryName }}
        </div>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px; border: 1px solid #dddddd;">
            <thead>
                <tr>
                    <th style="background-color: #2c3e50; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 10px; font-weight: bold; width: 30%;">Test Name</th>
                    <th style="background-color: #2c3e50; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 10px; font-weight: bold; width: 18%;">Result</th>
                    <th style="background-color: #2c3e50; color: #ffffff; padding: 6px 8px; text-align: center; font-size: 10px; font-weight: bold; width: 10%;">Unit</th>
                    <th style="background-color: #2c3e50; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 10px; font-weight: bold; width: 22%;">Reference Range</th>
                    <th style="background-color: #2c3e50; color: #ffffff; padding: 6px 8px; text-align: center; font-size: 10px; font-weight: bold; width: 12%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryItems as $index => $item)
                    @php
                        $statusColor = '#27ae60';
                        $statusBg = '#eafaf1';
                        $statusLabel = 'Normal';

                        if (($item->result_status ?? '') === 'abnormal') {
                            $statusColor = '#e67e22';
                            $statusBg = '#fef5e7';
                            $statusLabel = 'Abnormal';
                        } elseif (($item->result_status ?? '') === 'critical') {
                            $statusColor = '#e74c3c';
                            $statusBg = '#fdedec';
                            $statusLabel = 'Critical';
                        }

                        $rowBg = ($index % 2 === 0) ? '#ffffff' : '#f9f9f9';
                    @endphp
                    <tr>
                        <td style="padding: 5px 8px; font-size: 11px; font-weight: bold; border: 1px solid #eeeeee; background-color: {{ $rowBg }};">
                            {{ $item->labTest->name ?? $item->test_name ?? 'N/A' }}
                        </td>
                        <td style="padding: 5px 8px; font-size: 11px; border: 1px solid #eeeeee; background-color: {{ $rowBg }};">
                            @if($item->result_value)
                                <span style="font-weight: bold;">{{ $item->result_value }}</span>
                            @else
                                <span style="color: #aaaaaa;">Pending</span>
                            @endif
                        </td>
                        <td style="padding: 5px 8px; font-size: 10px; color: #666666; text-align: center; border: 1px solid #eeeeee; background-color: {{ $rowBg }};">
                            {{ $item->result_unit ?? $item->labTest->unit ?? '-' }}
                        </td>
                        <td style="padding: 5px 8px; font-size: 10px; color: #666666; border: 1px solid #eeeeee; background-color: {{ $rowBg }};">
                            {{ $item->labTest->reference_range ?? $item->reference_range ?? '-' }}
                        </td>
                        <td style="padding: 5px 8px; text-align: center; border: 1px solid #eeeeee; background-color: {{ $rowBg }};">
                            @if($item->result_value)
                                <span style="display: inline-block; padding: 2px 8px; font-size: 9px; font-weight: bold; color: {{ $statusColor }}; background-color: {{ $statusBg }}; border: 1px solid {{ $statusColor }}; border-radius: 3px;">
                                    {{ $statusLabel }}
                                </span>
                            @else
                                <span style="font-size: 9px; color: #aaaaaa;">--</span>
                            @endif
                        </td>
                    </tr>
                    @if($item->result_notes)
                    <tr>
                        <td style="border: 1px solid #eeeeee; background-color: #fffef5;"></td>
                        <td colspan="4" style="padding: 3px 8px; font-size: 10px; color: #555555; border: 1px solid #eeeeee; background-color: #fffef5;">
                            <span style="font-weight: bold; color: #888888;">Note:</span> {{ $item->result_notes }}
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endforeach

    {{-- SUMMARY --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; border: 1px solid #dddddd;">
        <tr>
            <td style="background-color: #f0f4f8; padding: 5px 8px; font-weight: bold; font-size: 11px; color: #2c3e50; border-bottom: 1px solid #dddddd;" colspan="3">
                RESULTS SUMMARY
            </td>
        </tr>
        <tr>
            <td style="padding: 6px 8px; text-align: center; width: 33%; border: 1px solid #eeeeee;">
                <span style="display: inline-block; padding: 2px 12px; font-size: 10px; font-weight: bold; color: #27ae60; background-color: #eafaf1; border: 1px solid #27ae60; border-radius: 3px;">
                    Normal: {{ $normalCount }}
                </span>
            </td>
            <td style="padding: 6px 8px; text-align: center; width: 33%; border: 1px solid #eeeeee;">
                <span style="display: inline-block; padding: 2px 12px; font-size: 10px; font-weight: bold; color: #e67e22; background-color: #fef5e7; border: 1px solid #e67e22; border-radius: 3px;">
                    Abnormal: {{ $abnormalCount }}
                </span>
            </td>
            <td style="padding: 6px 8px; text-align: center; width: 33%; border: 1px solid #eeeeee;">
                <span style="display: inline-block; padding: 2px 12px; font-size: 10px; font-weight: bold; color: #e74c3c; background-color: #fdedec; border: 1px solid #e74c3c; border-radius: 3px;">
                    Critical: {{ $criticalCount }}
                </span>
            </td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div style="margin-top: 30px; border-top: 2px solid #2c3e50; padding-top: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    @if($technician)
                        <div style="margin-bottom: 20px;">
                            <div style="font-size: 10px; color: #888888; margin-bottom: 3px;">Verified by</div>
                            <div style="font-size: 12px; font-weight: bold; color: #1a1a1a; border-top: 1px solid #333333; display: inline-block; padding-top: 4px; min-width: 180px;">
                                {{ $technician->first_name ?? '' }} {{ $technician->last_name ?? '' }}
                            </div>
                            <div style="font-size: 9px; color: #888888;">Lab Technician</div>
                        </div>
                    @endif
                </td>
                <td style="width: 50%; text-align: right; vertical-align: bottom;">
                    <div style="font-size: 10px; color: #888888; margin-bottom: 3px;">Report Date</div>
                    <div style="font-size: 11px; font-weight: bold; color: #1a1a1a;">{{ $reportDate }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-top: 20px; padding-top: 8px; border-top: 1px solid #eeeeee;">
        <div style="font-size: 9px; color: #aaaaaa;">
            This is a computer-generated report and does not require a physical signature.
        </div>
        <div style="font-size: 9px; color: #cccccc; margin-top: 3px;">
            Generated by {{ config('app.name') }} &bull; {{ $reportDate }}
        </div>
    </div>

</body>
</html>
