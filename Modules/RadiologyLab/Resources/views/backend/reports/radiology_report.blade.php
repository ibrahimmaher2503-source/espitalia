<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Radiology Report - {{ $order->order_number }}</title>
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
        RADIOLOGY REPORT
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

    {{-- EXAMINATION SECTIONS --}}
    @foreach($items as $index => $item)
        <div style="border: 1px solid #dddddd; margin-bottom: 15px;">
            {{-- Examination Header --}}
            <div style="background-color: #2c3e50; color: #ffffff; padding: 6px 10px; font-size: 12px; font-weight: bold;">
                {{ $item->labTest->name ?? $item->test_name ?? 'Examination' }}
                @if($item->labTest->category->name ?? null)
                    <span style="font-weight: normal; font-size: 10px; opacity: 0.8;">
                        &mdash; {{ $item->labTest->category->name }}
                    </span>
                @endif
            </div>

            {{-- Technique / Modality --}}
            @if($item->labTest->description ?? null)
            <div style="padding: 8px 10px; border-bottom: 1px solid #eeeeee;">
                <div style="font-size: 10px; font-weight: bold; color: #2c3e50; text-transform: uppercase; margin-bottom: 3px;">Technique / Modality</div>
                <div style="font-size: 11px; color: #555555;">{{ $item->labTest->description }}</div>
            </div>
            @endif

            {{-- Findings --}}
            <div style="padding: 8px 10px; border-bottom: 1px solid #eeeeee;">
                <div style="font-size: 10px; font-weight: bold; color: #2c3e50; text-transform: uppercase; margin-bottom: 3px;">Findings</div>
                <div style="font-size: 11px; color: #333333; line-height: 1.6;">
                    @if($item->result_notes)
                        {!! nl2br(e($item->result_notes)) !!}
                    @else
                        <span style="color: #aaaaaa;">No findings documented.</span>
                    @endif
                </div>
            </div>

            {{-- Impression / Conclusion --}}
            <div style="padding: 8px 10px; border-bottom: 1px solid #eeeeee; background-color: #fafbfc;">
                <div style="font-size: 10px; font-weight: bold; color: #2c3e50; text-transform: uppercase; margin-bottom: 3px;">Impression / Conclusion</div>
                <div style="font-size: 11px; color: #333333; line-height: 1.6; font-weight: bold;">
                    @if($item->result_value)
                        {!! nl2br(e($item->result_value)) !!}
                    @else
                        <span style="color: #aaaaaa; font-weight: normal;">Pending.</span>
                    @endif
                </div>
            </div>

            {{-- Status --}}
            @if($item->result_value)
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
                @endphp
                <div style="padding: 6px 10px; text-align: right;">
                    <span style="display: inline-block; padding: 2px 12px; font-size: 10px; font-weight: bold; color: {{ $statusColor }}; background-color: {{ $statusBg }}; border: 1px solid {{ $statusColor }}; border-radius: 3px;">
                        {{ $statusLabel }}
                    </span>
                </div>
            @endif
        </div>
    @endforeach

    @if($items->isEmpty())
        <div style="padding: 20px; text-align: center; color: #aaaaaa; font-size: 11px; border: 1px solid #eeeeee;">
            No examination results available.
        </div>
    @endif

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
                            <div style="font-size: 9px; color: #888888;">Radiologist / Technician</div>
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
