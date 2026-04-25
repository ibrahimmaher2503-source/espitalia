<template>
    <div class="card-list-data">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3" v-if="!IS_LOADER">
            <template v-for="(item, index) in specializationList" :key="`items-${index}`">
                <div class="col">
                    <div class="iq-widget">
                        <input type="radio" :id="'spec-' + item.id" v-model="specialization_id" :value="item.id" name="spec-radio" class="btn-check" @change="onChange"/>
                        <label :for="'spec-' + item.id" class="d-block w-100">
                            <div class="card iq-card iq-staff-box">
                                <div class="card-body text-center p-0">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <div class="avatar-90 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center">
                                            <i class="ph ph-stethoscope" style="font-size: 2rem; color: var(--bs-primary)"></i>
                                        </div>
                                    </div>
                                    <h5 class="m-0">{{ item.name }}</h5>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </template>
        </div>
        <div v-else class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
            <div class="col" v-for="index in 6" :key="index">
                <div class="iq-widget card iq-card card-skeleton">
                    <div class="card-body text-center p-0">
                        <div class="skeleton skeleton-image avatar-90 rounded-circle m-auto"></div>
                        <h5 class="skeleton skeleton-title w-10 mt-3 mb-0"></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-100 w-75 d-flex align-items-center justify-content-center mx-auto" v-if="specializationList.length == 0 && !IS_LOADER">
            No specializations available.
        </div>
    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-secondary iq-text-uppercase" v-if="wizardPrev" @click="prevTabChange(wizardPrev)">Back</button>
        <button type="button" class="btn btn-outline-primary iq-text-uppercase me-2" v-if="wizardNext" @click="skipStep">Skip</button>
        <button type="button" v-if="specializationList.length > 0 && wizardNext" class="btn btn-primary iq-text-uppercase" :disabled="specialization_id === null" @click="nextTabChange(wizardNext)">Next</button>
    </div>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRequest } from '@/helpers/hooks/useCrudOpration'
import { useQuickBooking } from '../../store/quick-booking'
import { SPECIALIZATION_LIST } from '@/vue/constants/quick_booking'

const props = defineProps({
    wizardNext: {
        default: '',
        type: [String, Number]
    },
    wizardPrev: {
        default: '',
        type: [String, Number]
    },
    user_id: [String, Number]
})

const emit = defineEmits(['tab-change', 'onReset'])
const { listingRequest } = useRequest()
const store = useQuickBooking()

const specializationList = ref([])
const specialization_id = ref(null)
const IS_LOADER = ref(true)

const getSpecializations = () => {
    IS_LOADER.value = true
    listingRequest({ url: SPECIALIZATION_LIST }).then((res) => {
        IS_LOADER.value = false
        specializationList.value = res.data || []
    })
}

onMounted(() => {
    getSpecializations()
})

const onChange = () => {
    emit('tab-change', props.wizardNext)
}

const skipStep = () => {
    specialization_id.value = null
    store.updateBookingValues({ key: 'specialization_id', value: null })
    emit('tab-change', props.wizardNext)
}

const nextTabChange = (val) => (emit('tab-change', val))
const prevTabChange = (val) => {
    resetData()
    emit('tab-change', val)
}

const resetData = () => {
    specialization_id.value = null
}

watch(() => specialization_id.value, (value) => {
    store.updateBookingValues({ key: 'specialization_id', value: value })
}, { deep: true })

watch(() => store.bookingResponse, () => {
    resetData()
}, { deep: true })
</script>

<style scoped>
.card-list-data {
    position: relative;
    padding-top: 10px;
    padding-right: 10px;
}
.iq-staff-box {
    padding: 30px 15px;
    border: 1px solid #CCCDCD;
    background: var(--bs-white);
    box-shadow: none;
}
.iq-staff-box::after {
    position: absolute;
    content: "";
    background: var(--bs-primary) url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
    height: 23px;
    width: 23px;
    border: 2px solid var(--bs-white);
    top: -7px;
    left: auto;
    right: -7px;
    border-radius: 100%;
    opacity: 0;
}
.iq-widget .btn-check:checked + label .iq-staff-box {
    border-color: var(--bs-primary);
    background: rgba(var(--bs-primary-rgb), 0.1);
}
.iq-widget .btn-check:checked + label .iq-staff-box::after {
    opacity: 1;
}
</style>
