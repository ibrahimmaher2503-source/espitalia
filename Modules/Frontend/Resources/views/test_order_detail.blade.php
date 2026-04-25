@extends('frontend::layouts.master')
@section('title', $order->order_number)

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">
            <div class="row g-4">

                {{-- ── Sidebar (col-lg-4) ──────────────────────────────────────── --}}
                <div class="col-lg-4">
                    <div class="pp-detail-section" style="position:sticky;top:5rem">

                        {{-- Icon header --}}
                        <div class="pp-detail-section-header" style="justify-content:center;padding:1.75rem;flex-direction:column;gap:0.75rem">
                            <div style="width:64px;height:64px;border-radius:var(--pp-r);background:var(--pp-teal-100);display:flex;align-items:center;justify-content:center;font-size:1.75rem;color:var(--pp-teal)" aria-hidden="true">
                                <i class="ph ph-test-tube"></i>
                            </div>
                        </div>

                        <div class="pp-detail-section-body">
                            {{-- Order number + status --}}
                            <p class="pp-section-title mb-1" style="text-align:center">{{ $order->order_number }}</p>
                            <div class="mb-3" style="text-align:center">{!! $order->status_badge !!}</div>

                            {{-- Key detail rows --}}
                            <div class="mb-3">
                                <div class="pp-summary-row">
                                    <span class="pp-summary-label">
                                        <i class="ph ph-calendar me-1" style="color:var(--pp-teal)" aria-hidden="true"></i>{{ __('radiology_lab.lbl_order_date') }}
                                    </span>
                                    <span class="pp-summary-value">{{ $order->order_date ? $order->order_date->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="pp-summary-row">
                                    <span class="pp-summary-label">
                                        <i class="ph ph-flag me-1" style="color:var(--pp-teal)" aria-hidden="true"></i>{{ __('radiology_lab.lbl_priority') }}
                                    </span>
                                    <span>{!! $order->priority_badge !!}</span>
                                </div>
                                <div class="pp-summary-row">
                                    <span class="pp-summary-label">
                                        <i class="ph ph-credit-card me-1" style="color:var(--pp-teal)" aria-hidden="true"></i>{{ __('radiology_lab.lbl_payment_status') }}
                                    </span>
                                    <span>{!! $order->payment_badge !!}</span>
                                </div>
                            </div>

                            {{-- Total amount --}}
                            <div class="pp-price-pill mb-4">
                                <i class="ph ph-currency-dollar" aria-hidden="true"></i>
                                <span class="pp-price-pill-amount">{{ number_format($order->total_amount, 2) }}</span>
                            </div>

                            {{-- Actions --}}
                            <a href="{{ route('my-test-orders') }}"
                               class="pp-btn pp-btn-outline w-100 mb-2">
                                <i class="ph ph-arrow-left" aria-hidden="true"></i>{{ __('radiology_lab.lbl_back_to_orders') }}
                            </a>

                            @php
                                $itemsWithResults = $order->items->filter(fn($item) => !empty($item->result_value));
                            @endphp

                            @if($itemsWithResults->count() > 0 || $order->reports->count() > 0)
                                <a href="{{ route('my-test-orders.download-report', $order->id) }}"
                                   class="pp-btn pp-btn-outline w-100 mb-2"
                                   style="color:var(--pp-confirmed);border-color:var(--pp-confirmed)">
                                    <i class="ph ph-download-simple" aria-hidden="true"></i>{{ __('radiology_lab.lbl_download_report') }}
                                </a>
                            @endif

                            @if(in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_PENDING, \Modules\RadiologyLab\Models\TestOrder::STATUS_CONFIRMED]))
                                <button type="button"
                                        class="pp-btn pp-btn-danger w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancelModal">
                                    <i class="ph ph-x-circle" aria-hidden="true"></i>{{ __('radiology_lab.lbl_cancel_order') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── Main content (col-lg-8) ─────────────────────────────────── --}}
                <div class="col-lg-8">

                    {{-- ── Section 1: Test Items ── --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-test-tube" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('radiology_lab.lbl_test_items') }}</span>
                        </div>
                        <div class="pp-detail-section-body p-0">
                            <div class="table-responsive">
                                <table class="pp-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('radiology_lab.lbl_test_name') }}</th>
                                            <th>{{ __('radiology_lab.lbl_test_code') }}</th>
                                            <th>{{ __('radiology_lab.lbl_price') }}</th>
                                            <th>{{ __('radiology_lab.lbl_status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>{{ $item->labTest ? $item->labTest->name : '-' }}</td>
                                                <td>
                                                    @if($item->labTest)
                                                        <span class="pp-badge">{{ $item->labTest->code }}</span>
                                                    @else
                                                        <span style="color:var(--pp-xmuted)">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($item->price, 2) }}</td>
                                                <td>
                                                    @php
                                                        $itemStatusClass = match($item->status) {
                                                            'completed'        => 'pp-status pp-status-completed',
                                                            'processing'       => 'pp-status pp-status-confirmed',
                                                            'sample_collected' => 'pp-status pp-status-checkin',
                                                            default            => 'pp-status pp-status-pending',
                                                        };
                                                    @endphp
                                                    <span class="{{ $itemStatusClass }}">
                                                        {{ __('radiology_lab.status_' . $item->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-end">{{ __('radiology_lab.lbl_total_amount') }}:</td>
                                            <td colspan="2">{{ number_format($order->total_amount, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ── Section 2: Results ── --}}
                    @if($itemsWithResults->count() > 0)
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-chart-bar" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('radiology_lab.test_results') }}</span>
                            </div>
                            <div class="pp-detail-section-body p-0">
                                <div class="table-responsive">
                                    <table class="pp-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('radiology_lab.lbl_test_name') }}</th>
                                                <th>{{ __('radiology_lab.lbl_result_value') }}</th>
                                                <th>{{ __('radiology_lab.lbl_reference_range') }}</th>
                                                <th>{{ __('radiology_lab.lbl_result_status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($itemsWithResults as $item)
                                                <tr>
                                                    <td>{{ $item->labTest ? $item->labTest->name : '-' }}</td>
                                                    <td>
                                                        <strong>{{ $item->result_value }}</strong>
                                                        @if($item->result_unit)
                                                            <small style="color:var(--pp-muted);margin-left:4px">{{ $item->result_unit }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->reference_range ?? '-' }}</td>
                                                    <td>{!! $item->result_status_badge !!}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ── Section 3: Reports ── --}}
                    @if($order->reports->count() > 0)
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-file-text" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('radiology_lab.lbl_reports') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                @foreach($order->reports as $report)
                                    <div class="pp-report-row">
                                        <div>
                                            <p class="pp-report-title">{{ $report->title }}</p>
                                            <div class="pp-report-meta">
                                                @if($report->report_date)
                                                    <span>{{ $report->report_date->format('d M Y') }}</span>
                                                @endif
                                                {!! $report->status_badge !!}
                                            </div>
                                        </div>
                                        @if($report->file_url)
                                            <a href="{{ $report->file_url }}"
                                               class="pp-btn pp-btn-outline flex-shrink-0"
                                               style="color:var(--pp-confirmed);border-color:var(--pp-confirmed);padding:0.35rem 0.75rem;font-size:0.78rem"
                                               target="_blank"
                                               rel="noopener">
                                                <i class="ph ph-download-simple"></i>{{ __('radiology_lab.lbl_download_report') }}
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── Section 4: Clinical Notes ── --}}
                    @if($order->clinical_notes)
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-note" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('radiology_lab.lbl_clinical_notes') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <p style="color:var(--pp-muted);font-size:0.875rem;margin:0">{{ $order->clinical_notes }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- ── Section 5: Cancellation reason ── --}}
                    @if($order->status === \Modules\RadiologyLab\Models\TestOrder::STATUS_CANCELLED)
                        <div class="pp-cancel-alert mb-3">
                            <p class="pp-cancel-alert-title">
                                <i class="ph ph-x-circle" aria-hidden="true"></i>
                                {{ __('radiology_lab.lbl_cancellation_reason') }}
                            </p>
                            <p style="color:var(--pp-text-2);font-size:0.875rem;margin:0">
                                {{ $order->cancellation_reason ?? '-' }}
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- ── Cancel Modal ── --}}
    @if(in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_PENDING, \Modules\RadiologyLab\Models\TestOrder::STATUS_CONFIRMED]))
        <div class="modal fade pp-modal pp-modal-danger" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cancel-test-order', $order->id) }}">
                        @csrf
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ph ph-x-circle" style="color:var(--pp-cancelled);font-size:1.25rem" aria-hidden="true"></i>
                                <h5 class="modal-title m-0" id="cancelModalLabel">{{ __('radiology_lab.lbl_cancel_order') }}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('frontend.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <p style="color:var(--pp-muted);font-size:0.875rem;margin-bottom:1rem">{{ __('messages.are_you_sure') }}</p>
                            <div class="pp-form-group">
                                <label class="pp-label">{{ __('radiology_lab.lbl_cancellation_reason') }}</label>
                                <textarea class="pp-input"
                                          name="cancellation_reason"
                                          rows="3"
                                          style="height:auto"
                                          placeholder="{{ __('radiology_lab.lbl_cancellation_reason') }}..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                            <button type="submit" class="pp-btn pp-btn-danger">
                                <i class="ph ph-x-circle" aria-hidden="true"></i>{{ __('radiology_lab.lbl_cancel_order') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
