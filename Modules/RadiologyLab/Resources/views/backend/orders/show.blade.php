@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@php
    $statusOrder = ['pending', 'confirmed', 'sample_collected', 'processing', 'completed', 'delivered'];
    $currentIndex = array_search($order->status, $statusOrder);
    $isCancelled = $order->status === \Modules\RadiologyLab\Models\TestOrder::STATUS_CANCELLED;
    $order->loadMissing('cancelledBy');
@endphp

@section('content')
    {{-- Header Row with Status Timeline --}}
    <div class="card rl-detail-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h5 class="mb-1">{{ __('radiology_lab.order_detail') }} <span class="text-muted">{{ $order->order_number }}</span></h5>
                    <p class="text-muted mb-0">
                        {{ __('radiology_lab.lbl_created') }}: {{ $order->created_at ? $order->created_at->format('d M Y, H:i') : '-' }}
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    {!! $order->status_badge !!}
                    {!! $order->priority_badge !!}
                    <a href="{{ route('backend.test-orders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </div>

            {{-- Status Timeline --}}
            @if(!$isCancelled)
            <div class="rl-timeline">
                @foreach($statusOrder as $i => $step)
                    @php
                        $isActive = $currentIndex !== false && $i <= $currentIndex;
                        $isCurrent = $currentIndex !== false && $i === $currentIndex;
                        $stepColors = [
                            'pending' => '#f59e0b',
                            'confirmed' => '#06b6d4',
                            'sample_collected' => '#8b5cf6',
                            'processing' => '#3b82f6',
                            'completed' => '#10b981',
                            'delivered' => '#059669',
                        ];
                        $color = $isActive ? $stepColors[$step] : '#d1d5db';
                    @endphp
                    <div class="rl-timeline-step {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}">
                        <div class="rl-timeline-dot" style="background: {{ $color }}; {{ $isCurrent ? 'box-shadow: 0 0 0 4px ' . $color . '33;' : '' }}">
                            @if($isActive && !$isCurrent)
                                <i class="ph ph-check" style="font-size:0.7rem;color:#fff;"></i>
                            @endif
                        </div>
                        <span class="rl-timeline-label" style="{{ $isActive ? 'color:' . $color . '; font-weight:600;' : '' }}">
                            {{ __('radiology_lab.status_' . $step) }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div class="rl-timeline-line" style="background: {{ $isActive && $i < $currentIndex ? $stepColors[$statusOrder[$i+1]] : '#e5e7eb' }};"></div>
                    @endif
                @endforeach
            </div>
            @else
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" style="border-radius:10px;">
                    <i class="ph ph-x-circle fs-4"></i>
                    <div>
                        <strong>{{ __('radiology_lab.lbl_order_cancelled') }}</strong>
                        @if($order->cancelledBy ?? null)
                            <span class="ms-1 text-muted">{{ __('radiology_lab.lbl_by') }} {{ $order->cancelledBy->first_name }} {{ $order->cancelledBy->last_name }}</span>
                        @endif
                        @if($order->cancellation_reason)
                            <p class="mb-0 mt-1">{!! nl2br(e($order->cancellation_reason)) !!}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-4">
            {{-- Patient Card --}}
            <div class="card rl-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-user me-1"></i> {{ __('radiology_lab.lbl_patient_name') }}</h6>
                </div>
                <div class="card-body text-center py-4">
                    @if($order->patient)
                        <div class="mb-3">
                            <div class="rl-avatar mx-auto">
                                @if($order->patient->profile_image)
                                    <img src="{{ $order->patient->profile_image }}" alt="{{ $order->patient->first_name }}">
                                @else
                                    <i class="ph ph-user"></i>
                                @endif
                            </div>
                        </div>
                        <h6 class="mb-2">{{ $order->patient->first_name }} {{ $order->patient->last_name }}</h6>
                        <div class="d-flex flex-column align-items-center gap-1">
                            <span class="text-muted small"><i class="ph ph-envelope-simple me-1"></i> {{ $order->patient->email }}</span>
                            @if($order->patient->mobile)
                                <span class="text-muted small"><i class="ph ph-phone me-1"></i> {{ $order->patient->mobile }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order Summary Card --}}
            <div class="card rl-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-clipboard-text me-1"></i> {{ __('radiology_lab.lbl_order_summary') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('radiology_lab.lbl_order_number') }}</span>
                        <span class="fw-semibold">{{ $order->order_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('radiology_lab.lbl_order_date') }}</span>
                        <span class="fw-medium">{{ $order->order_date ? $order->order_date->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('radiology_lab.lbl_priority') }}</span>
                        <span>{!! $order->priority_badge !!}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('radiology_lab.lbl_status') }}</span>
                        <span>{!! $order->status_badge !!}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('radiology_lab.lbl_payment') }}</span>
                        <span>{!! $order->payment_badge !!}</span>
                    </div>
                    @if($order->doctor)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ __('radiology_lab.lbl_doctor') }}</span>
                            <span class="fw-medium">{{ $order->doctor->first_name }} {{ $order->doctor->last_name }}</span>
                        </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">{{ __('radiology_lab.lbl_subtotal') }}</span>
                        <span class="fw-medium">{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">{{ __('radiology_lab.lbl_discount') }}</span>
                        <span class="fw-medium text-danger">-{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">{{ __('radiology_lab.lbl_final_amount') }}</span>
                        <span class="fw-bold fs-5" style="color:#6366f1;">{{ number_format($order->final_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Assigned Technician Card --}}
            <div class="card rl-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-user-gear me-1"></i> {{ __('radiology_lab.lbl_lab_technician') }}</h6>
                </div>
                <div class="card-body">
                    @if($order->labTechnician)
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rl-avatar rl-avatar-sm">
                                <i class="ph ph-user-gear"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $order->labTechnician->first_name }} {{ $order->labTechnician->last_name }}</h6>
                                <span class="text-muted small">{{ $order->labTechnician->email }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <div class="rl-avatar rl-avatar-placeholder mx-auto mb-2">
                                <i class="ph ph-user-plus"></i>
                            </div>
                            <p class="text-muted mb-3">{{ __('radiology_lab.lbl_unassigned') }}</p>
                            @if(!in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_COMPLETED, \Modules\RadiologyLab\Models\TestOrder::STATUS_DELIVERED, \Modules\RadiologyLab\Models\TestOrder::STATUS_CANCELLED]))
                                <div class="d-flex gap-2">
                                    <select class="form-control select2" id="assign-technician-select" style="width:70%">
                                        <option value="">{{ __('radiology_lab.lbl_select_technician') }}</option>
                                        @foreach($availableTechnicians as $tech)
                                            <option value="{{ $tech->id }}">{{ $tech->first_name }} {{ $tech->last_name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-primary btn-sm" id="assign-technician-btn">
                                        <i class="ph ph-user-plus"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-8">
            {{-- Test Items & Results --}}
            <div class="card rl-detail-card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ph ph-flask me-1"></i> {{ __('radiology_lab.lbl_test_items_results') }}</h6>
                    @if(in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_PROCESSING, \Modules\RadiologyLab\Models\TestOrder::STATUS_SAMPLE_COLLECTED]))
                        <button type="button" class="btn btn-outline-primary btn-sm" id="toggle-enter-results">
                            <i class="ph ph-pencil-simple me-1"></i> {{ __('radiology_lab.lbl_enter_results') }}
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('radiology_lab.lbl_test_name') }}</th>
                                    <th>{{ __('radiology_lab.lbl_code') }}</th>
                                    <th>{{ __('radiology_lab.lbl_sample_type') }}</th>
                                    <th>{{ __('radiology_lab.lbl_result') }}</th>
                                    <th>{{ __('radiology_lab.lbl_unit') }}</th>
                                    <th>{{ __('radiology_lab.lbl_reference_range') }}</th>
                                    <th>{{ __('radiology_lab.lbl_result_status') }}</th>
                                    <th>{{ __('radiology_lab.lbl_notes') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="fw-medium">{{ $item->labTest->name ?? '-' }}</td>
                                        <td><code>{{ $item->labTest->code ?? '-' }}</code></td>
                                        <td>{{ $item->labTest->sample_type_label ?? '-' }}</td>
                                        <td>
                                            @if($item->has_result)
                                                <span class="fw-semibold">{{ $item->result_value }}</span>
                                            @else
                                                <span class="text-muted fst-italic">{{ __('radiology_lab.lbl_pending') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->result_unit ?? '-' }}</td>
                                        <td>{{ $item->reference_range ?? '-' }}</td>
                                        <td>{!! $item->result_status_badge !!}</td>
                                        <td>{{ $item->result_notes ?? '-' }}</td>
                                        <td>
                                            @if($item->histories && $item->histories->count() > 0)
                                                <button type="button" class="btn btn-outline-secondary btn-sm p-1" data-bs-toggle="modal" data-bs-target="#historyModal{{ $item->id }}" title="{{ __('radiology_lab.lbl_result_history') }}">
                                                    <i class="ph ph-clock-counter-clockwise"></i> {{ $item->histories->count() }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Enter Results Form (hidden by default) --}}
            @if(in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_PROCESSING, \Modules\RadiologyLab\Models\TestOrder::STATUS_SAMPLE_COLLECTED]))
                <div class="card rl-detail-card mb-4" id="enter-results-section" style="display:none;">
                    <div class="card-header bg-transparent" style="background: linear-gradient(135deg, rgba(99,102,241,0.05), rgba(6,182,212,0.05)) !important;">
                        <h6 class="mb-0"><i class="ph ph-pencil-simple me-1"></i> {{ __('radiology_lab.lbl_enter_results') }}</h6>
                    </div>
                    <div class="card-body">
                        @foreach($order->items as $index => $item)
                            @if(!$item->has_result)
                                <div class="result-entry-block mb-4 p-3" style="background:#f9fafb; border-radius:10px; border:1px solid #e5e7eb;">
                                    <h6 class="mb-3">
                                        <i class="ph ph-flask me-1 text-primary"></i>
                                        {{ $item->labTest->name ?? '-' }}
                                        <code class="ms-2">{{ $item->labTest->code ?? '' }}</code>
                                    </h6>
                                    <input type="hidden" class="result-item-id" value="{{ $item->id }}">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small">{{ __('radiology_lab.lbl_result_value') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm result-value" placeholder="{{ __('radiology_lab.lbl_result_value') }}">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label small">{{ __('radiology_lab.lbl_unit') }}</label>
                                            <input type="text" class="form-control form-control-sm result-unit" placeholder="e.g. mg/dL">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small">{{ __('radiology_lab.lbl_reference_range') }}</label>
                                            <input type="text" class="form-control form-control-sm result-reference" placeholder="e.g. 70-100">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label small">{{ __('radiology_lab.lbl_result_status') }}</label>
                                            <select class="form-control form-control-sm result-status">
                                                <option value="">--</option>
                                                <option value="normal">{{ __('radiology_lab.result_normal') }}</option>
                                                <option value="abnormal">{{ __('radiology_lab.result_abnormal') }}</option>
                                                <option value="critical">{{ __('radiology_lab.result_critical') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label small">{{ __('radiology_lab.lbl_notes') }}</label>
                                            <input type="text" class="form-control form-control-sm result-notes" placeholder="{{ __('radiology_lab.lbl_notes') }}">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary btn-sm" id="save-results-btn">
                                <i class="ph ph-floppy-disk me-1"></i> {{ __('radiology_lab.lbl_save_results') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Reports Section --}}
            <div class="card rl-detail-card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ph ph-file-pdf me-1"></i> {{ __('radiology_lab.lbl_reports') }}</h6>
                    @if(in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_COMPLETED, \Modules\RadiologyLab\Models\TestOrder::STATUS_DELIVERED]))
                        <form action="{{ route('backend.test-orders.generate_report', $order->id) }}" method="POST" target="_blank" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm">
                                <i class="ph ph-file-pdf me-1"></i> {{ __('radiology_lab.lbl_generate_report') }}
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if($order->reports && $order->reports->count() > 0)
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('radiology_lab.lbl_title') }}</th>
                                        <th>{{ __('radiology_lab.lbl_type') }}</th>
                                        <th>{{ __('radiology_lab.lbl_date') }}</th>
                                        <th>{{ __('radiology_lab.lbl_status') }}</th>
                                        <th class="text-end">{{ __('radiology_lab.lbl_action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->reports as $report)
                                        <tr>
                                            <td class="fw-medium">{{ $report->title }}</td>
                                            <td>
                                                @if($report->report_type === 'lab_report')
                                                    <span class="badge bg-info">{{ __('radiology_lab.type_lab_report') }}</span>
                                                @elseif($report->report_type === 'radiology_report')
                                                    <span class="badge bg-warning text-dark">{{ __('radiology_lab.type_radiology_report') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('radiology_lab.type_custom') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $report->report_date ? $report->report_date->format('d M Y') : '-' }}</td>
                                            <td>{!! $report->status_badge !!}</td>
                                            <td class="text-end">
                                                <a href="{{ route('backend.test-orders.download_report', $report->id) }}" class="btn btn-outline-primary btn-sm" title="{{ __('radiology_lab.lbl_download') }}">
                                                    <i class="ph ph-download-simple"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="ph ph-file-dashed fs-1 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">{{ __('radiology_lab.lbl_no_reports') }}</p>
                        </div>
                    @endif

                    {{-- Upload Report Form (UI-19: hide for terminal states) --}}
                    @if(!in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_CANCELLED, \Modules\RadiologyLab\Models\TestOrder::STATUS_DELIVERED]))
                        <div class="border-top pt-3 mt-3">
                            <h6 class="mb-3"><i class="ph ph-upload-simple me-1"></i> {{ __('radiology_lab.lbl_upload_report') }}</h6>
                            <form action="{{ route('backend.test-orders.upload_report', $order->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label small">{{ __('radiology_lab.lbl_title') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="title" required placeholder="{{ __('radiology_lab.lbl_report_title_placeholder') }}">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small">{{ __('radiology_lab.lbl_report_type') }}</label>
                                        <select class="form-control form-control-sm" name="report_type">
                                            <option value="lab_report">{{ __('radiology_lab.type_lab_report') }}</option>
                                            <option value="radiology_report">{{ __('radiology_lab.type_radiology_report') }}</option>
                                            <option value="custom">{{ __('radiology_lab.type_custom') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small">{{ __('radiology_lab.lbl_file') }} (PDF) <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control form-control-sm" name="report_file" accept=".pdf" required>
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="ph ph-upload-simple me-1"></i> {{ __('radiology_lab.lbl_upload') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Clinical Notes --}}
            @if($order->clinical_notes)
            <div class="card rl-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-note me-1"></i> {{ __('radiology_lab.lbl_clinical_notes') }}</h6>
                </div>
                <div class="card-body">
                    <div class="rl-note-block">
                        {{ $order->clinical_notes }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Admin Notes --}}
            <div class="card rl-detail-card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ph ph-note-pencil me-1"></i> {{ __('radiology_lab.lbl_admin_notes') }}</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="save-admin-notes-btn">
                        <i class="ph ph-floppy-disk me-1"></i> {{ __('messages.save') }}
                    </button>
                </div>
                <div class="card-body">
                    <textarea class="form-control" id="admin-notes" rows="3" maxlength="2000" placeholder="{{ __('radiology_lab.lbl_admin_notes_placeholder') }}" style="border-radius:10px;">{{ $order->admin_notes }}</textarea>
                    <small class="text-muted d-block mt-1" id="admin-notes-feedback"></small>
                </div>
            </div>

            {{-- Actions --}}
            @if(!in_array($order->status, [\Modules\RadiologyLab\Models\TestOrder::STATUS_DELIVERED, \Modules\RadiologyLab\Models\TestOrder::STATUS_CANCELLED]))
                <div class="card rl-detail-card mb-4">
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <span class="text-muted small me-2">{{ __('radiology_lab.lbl_actions') }}:</span>

                            @if(in_array(\Modules\RadiologyLab\Models\TestOrder::STATUS_CONFIRMED, $allowedTransitions))
                                <button class="btn btn-info btn-sm status-change-btn" data-status="confirmed">
                                    <i class="ph ph-check-circle me-1"></i> {{ __('radiology_lab.lbl_confirm_order') }}
                                </button>
                            @endif

                            @if(in_array(\Modules\RadiologyLab\Models\TestOrder::STATUS_SAMPLE_COLLECTED, $allowedTransitions))
                                <button class="btn btn-purple btn-sm status-change-btn" data-status="sample_collected" style="background:#8b5cf6; border-color:#8b5cf6; color:#fff;">
                                    <i class="ph ph-eyedropper me-1"></i> {{ __('radiology_lab.lbl_collect_sample') }}
                                </button>
                            @endif

                            @if(in_array(\Modules\RadiologyLab\Models\TestOrder::STATUS_PROCESSING, $allowedTransitions))
                                <button class="btn btn-primary btn-sm status-change-btn" data-status="processing">
                                    <i class="ph ph-flask me-1"></i> {{ __('radiology_lab.lbl_start_processing') }}
                                </button>
                            @endif

                            @if(in_array(\Modules\RadiologyLab\Models\TestOrder::STATUS_COMPLETED, $allowedTransitions))
                                <button class="btn btn-success btn-sm status-change-btn" data-status="completed">
                                    <i class="ph ph-check me-1"></i> {{ __('radiology_lab.lbl_mark_complete') }}
                                </button>
                            @endif

                            @if(in_array(\Modules\RadiologyLab\Models\TestOrder::STATUS_DELIVERED, $allowedTransitions))
                                <button class="btn btn-success btn-sm status-change-btn" data-status="delivered">
                                    <i class="ph ph-package me-1"></i> {{ __('radiology_lab.lbl_mark_delivered') }}
                                </button>
                            @endif

                            @if(in_array(\Modules\RadiologyLab\Models\TestOrder::STATUS_CANCELLED, $allowedTransitions))
                                <button class="btn btn-outline-danger btn-sm ms-auto" id="cancel-order-btn">
                                    <i class="ph ph-x-circle me-1"></i> {{ __('radiology_lab.lbl_cancel_order') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-bottom:none;">
                    <h6 class="modal-title"><i class="ph ph-warning-circle text-danger me-1"></i> {{ __('radiology_lab.lbl_cancel_order') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ __('radiology_lab.lbl_cancellation_reason') }}</label>
                    <textarea class="form-control" id="cancellation-reason" rows="3" style="border-radius:10px;" placeholder="{{ __('radiology_lab.lbl_cancellation_reason_placeholder') }}"></textarea>
                </div>
                <div class="modal-footer border-top-0">
                    {{-- UI-12: destructive action rightmost; non-destructive first --}}
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="button" class="btn btn-danger btn-sm ms-auto" id="confirm-cancel-btn">
                        <i class="ph ph-x-circle me-1"></i> {{ __('radiology_lab.lbl_confirm_cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Result History Modals --}}
    @foreach($order->items as $item)
        @if($item->histories && $item->histories->count() > 0)
        <div class="modal fade" id="historyModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="border-radius:12px;">
                    <div class="modal-header">
                        <h6 class="modal-title"><i class="ph ph-clock-counter-clockwise me-1"></i> {{ __('radiology_lab.lbl_result_history') }} — {{ $item->labTest->name ?? '' }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('radiology_lab.lbl_date') }}</th>
                                        <th>{{ __('radiology_lab.lbl_changed_by') }}</th>
                                        <th>{{ __('radiology_lab.lbl_old_value') }}</th>
                                        <th>{{ __('radiology_lab.lbl_new_value') }}</th>
                                        <th>{{ __('radiology_lab.lbl_result_status') }}</th>
                                        <th>{{ __('radiology_lab.lbl_change_reason') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->histories as $hIndex => $history)
                                        <tr>
                                            <td>{{ $hIndex + 1 }}</td>
                                            <td class="small">{{ $history->created_at->format('d M Y H:i') }}</td>
                                            <td>{{ $history->changedBy ? $history->changedBy->first_name . ' ' . $history->changedBy->last_name : '-' }}</td>
                                            <td>
                                                <span class="text-danger">{{ $history->old_result_value ?? '-' }}</span>
                                                @if($history->old_result_unit) <small class="text-muted">{{ $history->old_result_unit }}</small> @endif
                                            </td>
                                            <td>
                                                <span class="text-success fw-medium">{{ $history->new_result_value ?? '-' }}</span>
                                                @if($history->new_result_unit) <small class="text-muted">{{ $history->new_result_unit }}</small> @endif
                                            </td>
                                            <td>
                                                @if($history->old_result_status !== $history->new_result_status)
                                                    <span class="text-muted">{{ $history->old_result_status ?? '-' }}</span>
                                                    <i class="ph ph-arrow-right" style="font-size:0.7rem;"></i>
                                                    <span class="fw-medium">{{ $history->new_result_status ?? '-' }}</span>
                                                @else
                                                    {{ $history->new_result_status ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="small">{{ $history->change_reason ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
@endsection

@push('after-styles')
<style>
    .rl-detail-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .rl-detail-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
    }
    .rl-detail-card .card-header h6 {
        color: #374151;
        font-weight: 600;
    }

    /* Status Timeline */
    .rl-timeline {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        padding: 0.5rem 0;
    }
    .rl-timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        z-index: 1;
    }
    .rl-timeline-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    .rl-timeline-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        white-space: nowrap;
    }
    .rl-timeline-line {
        flex: 1;
        height: 3px;
        border-radius: 2px;
        margin: 0 -4px;
        margin-bottom: 24px;
        min-width: 30px;
    }

    /* Avatar */
    .rl-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(6,182,212,0.1));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: #6366f1;
        overflow: hidden;
    }
    .rl-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .rl-avatar.rl-avatar-sm {
        width: 44px;
        height: 44px;
        font-size: 1.2rem;
    }
    .rl-avatar.rl-avatar-placeholder {
        border: 2px dashed rgba(99,102,241,0.3);
        background: rgba(99,102,241,0.05);
    }

    /* Note Block */
    .rl-note-block {
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 10px;
        border-left: 3px solid #e5e7eb;
        line-height: 1.7;
        color: #4b5563;
    }

    @media (max-width: 768px) {
        .rl-timeline {
            flex-wrap: wrap;
            gap: 4px;
        }
        .rl-timeline-line {
            min-width: 15px;
        }
    }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // Assign technician
        $('#assign-technician-btn').on('click', function() {
            var techId = $('#assign-technician-select').val();
            if (!techId) return;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="ph ph-spinner ph-spin"></i>');

            $.ajax({
                url: '{{ route("backend.test-orders.assign_technician", $order->id) }}',
                method: 'POST',
                data: {
                    lab_technician_id: techId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="ph ph-user-plus"></i>');
                }
            });
        });

        // Toggle enter results form
        $('#toggle-enter-results').on('click', function() {
            $('#enter-results-section').slideToggle();
        });

        // Save results
        $('#save-results-btn').on('click', function() {
            var results = [];
            var valid = true;

            $('.result-entry-block').each(function() {
                var itemId = $(this).find('.result-item-id').val();
                var resultValue = $(this).find('.result-value').val();

                if (!resultValue) {
                    $(this).find('.result-value').addClass('is-invalid');
                    valid = false;
                    return;
                } else {
                    $(this).find('.result-value').removeClass('is-invalid');
                }

                results.push({
                    item_id: itemId,
                    result_value: resultValue,
                    result_unit: $(this).find('.result-unit').val(),
                    reference_range: $(this).find('.result-reference').val(),
                    result_status: $(this).find('.result-status').val(),
                    result_notes: $(this).find('.result-notes').val(),
                });
            });

            if (!valid || results.length === 0) return;

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("backend.test-orders.enter_results", $order->id) }}',
                method: 'POST',
                data: {
                    results: results,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Status change buttons
        $('.status-change-btn').on('click', function() {
            var newStatus = $(this).data('status');
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("backend.test-orders.update_status", $order->id) }}',
                method: 'POST',
                data: {
                    status: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Save admin notes (FN-03)
        $('#save-admin-notes-btn').on('click', function() {
            var $btn = $(this);
            var notes = $('#admin-notes').val();
            $btn.prop('disabled', true);
            $('#admin-notes-feedback').text('').removeClass('text-success text-danger');

            $.ajax({
                url: '{{ route("backend.test-orders.update_admin_notes", $order->id) }}',
                method: 'POST',
                data: {
                    admin_notes: notes,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#admin-notes-feedback').text(response.message).addClass('text-success');
                },
                error: function() {
                    $('#admin-notes-feedback').text('{{ __("messages.something_wrong") }}').addClass('text-danger');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Cancel order
        $('#cancel-order-btn').on('click', function() {
            $('#cancelModal').modal('show');
        });

        $('#confirm-cancel-btn').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("backend.test-orders.update_status", $order->id) }}',
                method: 'POST',
                data: {
                    status: 'cancelled',
                    cancellation_reason: $('#cancellation-reason').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
