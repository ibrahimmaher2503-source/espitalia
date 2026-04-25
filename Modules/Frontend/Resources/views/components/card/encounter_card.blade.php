<div class="col">
<div class="pp-enc-card">

    {{-- Header: date + status --}}
    <div class="pp-enc-header">
        <span class="pp-enc-date">
            <i class="ph ph-calendar-blank" aria-hidden="true"></i>
            {{ DateFormate($encounter->updated_at) }}
        </span>
        <span class="{{ $encounter->status ? 'pp-enc-status-active' : 'pp-enc-status-closed' }}">
            {{ $encounter->status ? __('frontend.active') : __('frontend.close') }}
        </span>
    </div>

    {{-- Body: encounter facts --}}
    <div class="pp-enc-body">
        <div class="pp-enc-grid">
            <span class="pp-enc-label">{{ __('frontend.appt_id') ?? 'Appt ID' }}</span>
            <span class="pp-enc-value pp-enc-value-id">#{{ $encounter->appointment_id }}</span>

            <span class="pp-enc-label">{{ __('frontend.doctor') }}</span>
            <span class="pp-enc-value">
                {{ optional($encounter->doctor)->first_name . ' ' . optional($encounter->doctor)->last_name ?: '-' }}
            </span>

            <span class="pp-enc-label">{{ __('frontend.clinic') }}</span>
            <span class="pp-enc-value">{{ optional($encounter->clinic)->name ?? '-' }}</span>
        </div>

        @if($encounter->description)
            <p class="pp-enc-desc">{{ $encounter->description }}</p>
        @endif
    </div>

    {{-- Footer: view detail link --}}
    <div class="pp-enc-footer">
        <button type="button"
                class="pp-btn pp-btn-ghost"
                data-bs-toggle="modal"
                data-bs-target="#encounter-modal-{{ $encounter->id }}">
            <i class="ph ph-eye" aria-hidden="true"></i>
            {{ __('messages.view_detail') ?? 'View Detail' }}
        </button>
    </div>

</div>
</div>


{{-- ═══════════════════════════════════════════════════
     ENCOUNTER DETAIL MODAL
     ═══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal"
     id="encounter-modal-{{ $encounter->id }}"
     tabindex="-1"
     aria-labelledby="encModalLabel{{ $encounter->id }}"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="encModalLabel{{ $encounter->id }}">
                    <i class="ph ph-stethoscope me-2" aria-hidden="true"></i>
                    {{ __('frontend.encounter_details') ?? 'Encounter Details' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.close') }}"></button>
            </div>

            <div class="modal-body">

                {{-- Basic info block --}}
                <div class="pp-enc-modal-info">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div>
                            <div class="pp-enc-meta-row">
                                <span class="pp-enc-meta-label">{{ __('frontend.appt_id') ?? 'Appt ID' }}</span>
                                <span class="pp-enc-meta-value" style="color:var(--pp-navy);font-weight:700">
                                    #{{ optional($encounter->appointment)->id }}
                                </span>
                            </div>
                            <div class="pp-enc-meta-row">
                                <span class="pp-enc-meta-label">{{ __('frontend.doctor') }}</span>
                                <span class="pp-enc-meta-value">{{ getDisplayName($encounter->doctor) }}</span>
                            </div>
                            <div class="pp-enc-meta-row">
                                <span class="pp-enc-meta-label">{{ __('frontend.clinic') }}</span>
                                <span class="pp-enc-meta-value">{{ optional($encounter->clinic)->name ?? '-' }}</span>
                            </div>
                        </div>
                        <span class="{{ $encounter->status ? 'pp-status pp-status-confirmed' : 'pp-status pp-status-cancelled' }}">
                            {{ $encounter->status ? __('frontend.active') : __('frontend.close') }}
                        </span>
                    </div>
                    @if($encounter->descrtiption)
                        <div style="padding-top:0.625rem;border-top:1px solid var(--pp-border)">
                            <p style="font-size:0.75rem;color:var(--pp-muted);margin:0;font-family:var(--pp-font)">
                                {{ $encounter->descrtiption }}
                            </p>
                        </div>
                    @endif
                </div>

                @php
                    $problems      = $medical_history->get('encounter_problem',      collect());
                    $observations  = $medical_history->get('encounter_observations', collect());
                    $notes         = $medical_history->get('encounter_notes',        collect());
                @endphp

                {{-- Problems --}}
                <div class="pp-encounter-section" id="enc-wrap-prb-{{ $encounter->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-body-prb-{{ $encounter->id }}','enc-wrap-prb-{{ $encounter->id }}')"
                         aria-expanded="false" role="button" tabindex="0">
                        <span class="pp-encounter-toggle-title">
                            <i class="ph ph-warning-circle" aria-hidden="true"></i>
                            {{ __('frontend.problem') ?? 'Problems' }}
                        </span>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" aria-hidden="true"></i>
                    </div>
                    <div class="pp-encounter-body" id="enc-body-prb-{{ $encounter->id }}" style="display:none">
                        @forelse($problems as $problem)
                            <p style="font-size:0.82rem;margin:0 0 6px;font-family:var(--pp-font)">
                                {{ $loop->iteration }}. {{ $problem->title }}
                            </p>
                        @empty
                            <div class="pp-empty">
                                <i class="ph ph-check-circle" aria-hidden="true"></i>
                                <span>{{ __('frontend.no_problems_found') ?? 'No problems found' }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Observations --}}
                <div class="pp-encounter-section" id="enc-wrap-obs-{{ $encounter->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-body-obs-{{ $encounter->id }}','enc-wrap-obs-{{ $encounter->id }}')"
                         aria-expanded="false" role="button" tabindex="0">
                        <span class="pp-encounter-toggle-title">
                            <i class="ph ph-eye" aria-hidden="true"></i>
                            {{ __('frontend.observation') ?? 'Observations' }}
                        </span>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" aria-hidden="true"></i>
                    </div>
                    <div class="pp-encounter-body" id="enc-body-obs-{{ $encounter->id }}" style="display:none">
                        @forelse($observations as $obs)
                            <p style="font-size:0.82rem;margin:0 0 6px;font-family:var(--pp-font)">
                                {{ $loop->iteration }}. {{ $obs->title }}
                            </p>
                        @empty
                            <div class="pp-empty">
                                <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                                <span>{{ __('frontend.no_observations_found') ?? 'No observations found' }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Notes --}}
                <div class="pp-encounter-section" id="enc-wrap-nts-{{ $encounter->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-body-nts-{{ $encounter->id }}','enc-wrap-nts-{{ $encounter->id }}')"
                         aria-expanded="false" role="button" tabindex="0">
                        <span class="pp-encounter-toggle-title">
                            <i class="ph ph-note" aria-hidden="true"></i>
                            {{ __('frontend.notes') ?? 'Notes' }}
                        </span>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" aria-hidden="true"></i>
                    </div>
                    <div class="pp-encounter-body" id="enc-body-nts-{{ $encounter->id }}" style="display:none">
                        @forelse($notes as $note)
                            <p style="font-size:0.82rem;margin:0 0 6px;font-family:var(--pp-font)">
                                {{ $loop->iteration }}. {{ $note->title ?? '-' }}
                            </p>
                        @empty
                            <div class="pp-empty">
                                <i class="ph ph-note-blank" aria-hidden="true"></i>
                                <span>{{ __('frontend.no_notes_found') ?? 'No notes found' }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Body Chart --}}
                <div class="pp-encounter-section" id="enc-wrap-bc-{{ $encounter->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-body-bc-{{ $encounter->id }}','enc-wrap-bc-{{ $encounter->id }}')"
                         aria-expanded="false" role="button" tabindex="0">
                        <span class="pp-encounter-toggle-title">
                            <i class="ph ph-person" aria-hidden="true"></i>
                            {{ __('frontend.body_chart') ?? 'Body Chart' }}
                        </span>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" aria-hidden="true"></i>
                    </div>
                    <div class="pp-encounter-body" id="enc-body-bc-{{ $encounter->id }}" style="display:none">
                        @if(isset($bodychart) && $bodychart->isNotEmpty())
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($bodychart as $chart)
                                    @if($chart->media && $chart->media->isNotEmpty())
                                        @foreach($chart->media as $media)
                                            <div class="text-center">
                                                <img src="{{ asset($media->getUrl()) }}"
                                                     alt="{{ $media->name ?? 'Chart' }}"
                                                     class="img-fluid rounded"
                                                     style="width:100px;height:100px;object-fit:cover">
                                                <div style="margin-top:4px">
                                                    <a href="{{ asset($media->getUrl()) }}"
                                                       download
                                                       class="pp-btn pp-btn-ghost"
                                                       style="font-size:0.7rem">
                                                        <i class="ph ph-download-simple"></i>
                                                        {{ __('messages.download') ?? 'Download' }}
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="pp-empty">
                                <i class="ph ph-image" aria-hidden="true"></i>
                                <span>{{ __('frontend.no_chart_found') ?? 'No chart found' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Medical Report --}}
                <div class="pp-encounter-section" id="enc-wrap-mr-{{ $encounter->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-body-mr-{{ $encounter->id }}','enc-wrap-mr-{{ $encounter->id }}')"
                         aria-expanded="false" role="button" tabindex="0">
                        <span class="pp-encounter-toggle-title">
                            <i class="ph ph-file-text" aria-hidden="true"></i>
                            {{ __('frontend.medical_report') ?? 'Medical Report' }}
                        </span>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" aria-hidden="true"></i>
                    </div>
                    <div class="pp-encounter-body" id="enc-body-mr-{{ $encounter->id }}" style="display:none">
                        @if(isset($medical_report) && $medical_report && $medical_report->file_url)
                            <a href="{{ asset($medical_report->file_url) }}"
                               download
                               class="pp-btn pp-btn-teal">
                                <i class="ph ph-download-simple" aria-hidden="true"></i>
                                {{ __('messages.download') ?? 'Download Report' }}
                            </a>
                        @else
                            <div class="pp-empty">
                                <i class="ph ph-file-dashed" aria-hidden="true"></i>
                                <span>{{ __('frontend.no_report_found') ?? 'No report found' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Prescriptions --}}
                <div class="pp-encounter-section" id="enc-wrap-rx-{{ $encounter->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-body-rx-{{ $encounter->id }}','enc-wrap-rx-{{ $encounter->id }}')"
                         aria-expanded="false" role="button" tabindex="0">
                        <span class="pp-encounter-toggle-title">
                            <i class="ph ph-pill" aria-hidden="true"></i>
                            {{ __('frontend.prescription') ?? 'Prescriptions' }}
                        </span>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" aria-hidden="true"></i>
                    </div>
                    <div class="pp-encounter-body" id="enc-body-rx-{{ $encounter->id }}" style="display:none">
                        @if(isset($prescriptions) && $prescriptions->isNotEmpty())
                            @foreach($prescriptions as $rx)
                                <div class="pp-rx-item">
                                    <p class="pp-rx-name">{{ $rx->name }}</p>
                                    @if($rx->instruction)
                                        <p style="font-size:0.78rem;color:var(--pp-muted);margin:0 0 8px;font-family:var(--pp-font)">
                                            {{ $rx->instruction }}
                                        </p>
                                    @endif
                                    <div class="pp-rx-meta">
                                        @if($rx->frequency)
                                            <span class="pp-rx-chip">
                                                <i class="ph ph-clock" aria-hidden="true"></i>
                                                {{ __('frontend.frequency') ?? 'Freq' }}: {{ $rx->frequency }}x
                                            </span>
                                        @endif
                                        @if($rx->duration)
                                            <span class="pp-rx-chip">
                                                <i class="ph ph-calendar" aria-hidden="true"></i>
                                                {{ $rx->duration }} {{ __('frontend.days') ?? 'Days' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="pp-empty">
                                <i class="ph ph-pill" aria-hidden="true"></i>
                                <span>{{ __('frontend.no_prescription_found') ?? 'No prescriptions found' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- SOAP --}}
                <div class="pp-encounter-section" id="enc-wrap-soap-{{ $encounter->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-body-soap-{{ $encounter->id }}','enc-wrap-soap-{{ $encounter->id }}')"
                         aria-expanded="false" role="button" tabindex="0">
                        <span class="pp-encounter-toggle-title">
                            <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                            {{ __('frontend.soap') ?? 'SOAP' }}
                        </span>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" aria-hidden="true"></i>
                    </div>
                    <div class="pp-encounter-body" id="enc-body-soap-{{ $encounter->id }}" style="display:none">
                        @if(isset($soap) && $soap)
                            @foreach([
                                'subjective'  => __('frontend.subjective')  ?? 'Subjective',
                                'objective'   => __('frontend.objective')   ?? 'Objective',
                                'assessment'  => __('frontend.assessment')  ?? 'Assessment',
                                'plan'        => __('frontend.plan')        ?? 'Plan',
                            ] as $key => $label)
                                @if($soap->$key)
                                    <div style="margin-bottom:0.75rem">
                                        <p style="font-size:0.7rem;font-weight:700;color:var(--pp-muted);text-transform:uppercase;letter-spacing:0.05em;margin:0 0 3px;font-family:var(--pp-font)">
                                            {{ $label }}
                                        </p>
                                        <p style="font-size:0.82rem;color:var(--pp-text-2);margin:0;font-family:var(--pp-font)">
                                            {{ $soap->$key }}
                                        </p>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="pp-empty">
                                <i class="ph ph-clipboard" aria-hidden="true"></i>
                                <span>{{ __('frontend.no_soap_found') ?? 'No SOAP note found' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>{{-- /.modal-body --}}
        </div>
    </div>
</div>
