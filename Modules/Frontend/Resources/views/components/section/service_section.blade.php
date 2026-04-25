@if ($sectionData && isset($sectionData['section_3']) && $sectionData['section_3']['section_3'] == 1)
    <div class="section-spacing section-background">
        <div class="container">
            <div
                class="section-title d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                <div class="title-info">
                    <span class="sub-title">{{ $sectionData['section_3']['title'] }}</span>
                    <h5 class="m-0 title">{{ $sectionData['section_3']['subtitle'] }}</h5>
                </div>
                <div><a href="{{ route('services') }}" class="btn btn-secondary">{{ __('clinic.view_all') }}</a></div>
            </div>
            <div class="row gy-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">

                @for ($i = 0; $i < 4; $i++)
                    @include('frontend::components.card.shimmer_service_card')
                @endfor


                @foreach ($sectionData['section_3']['service_id'] as $service_id)
                    @php
                        $service = \Modules\Clinic\Models\ClinicsService::where('id', $service_id)->first();



                        if (!empty($service)) {
                            $discount_amount=0;
                            if($service->discount){
                                $discount_amount = ($service->discount_type == 'percentage')
                                ? $service->charges * $service->discount_value / 100
                                : $service->discount_value;

                            }

                            $service->payable_amount = $service->charges - $discount_amount + $service->inclusive_tax_price ;
                        }
                    @endphp
                    <div class="col d-none servicecards">
                        @if($service)
                            <x-frontend::card.service_card :service="$service" />
                       @endif
                    </div>
                @endforeach

            </div>
        </div>
    </div>
@endif
@push('after-scripts')
    <script>
        // Variables
        document.addEventListener('DOMContentLoaded', () => {
            const services = document.querySelectorAll('.service-card');
            const shimmerCards = document.querySelectorAll('.shimmer-services-card');

            function showNextServiceCards() {
                const servicecards = document.querySelectorAll('.servicecards');
                if (servicecards) {
                    servicecards.forEach(card => card.classList.remove(
                        'd-none'));
                }
                shimmerCards.forEach(card => card.classList.add('d-none'));
            }
            const serviceSection = document.querySelector('.service-section');
            if (serviceSection) {
                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            showNextServiceCards();
                            observer.disconnect();
                        }
                    });
                }, {
                    rootMargin: '0px',
                    threshold: 0.1
                });
                observer.observe(serviceSection);
            } else {
                console.error('Service section not found!');
            }
        });
    </script>
@endpush
