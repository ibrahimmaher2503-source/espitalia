<template>
  <div class="offcanvas offcanvas-end" tabindex="-1" id="addOtherPatientOffcanvas">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">{{ $t('customer.add_other_patient') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <form @submit.prevent="submitForm" class="row g-3">
        <!-- First Name -->

          <!-- Profile Image -->
          <div class="col-12">
          <label class="form-label">{{ $t('customer.lbl_profile_image') }}</label>
          <imageComponent :ImageViewer="image_url" v-model="profile_image" />
        </div>
        <div class="col-md-12">
          <label class="form-label">{{ $t('customer.lbl_first_name') }} <span class="text-danger">*</span></label>
          <input 
            type="text" 
            class="form-control" 
            v-model="first_name"
            :class="{ 'is-invalid': errors.first_name }"
            :placeholder="$t('customer.lbl_first_name')"
          >
          <div class="invalid-feedback">{{ errors.first_name }}</div>
        </div>

        <!-- Last Name -->
        <div class="col-md-12">
          <label class="form-label">{{ $t('customer.lbl_last_name') }} <span class="text-danger">*</span></label>
          <input 
            type="text" 
            class="form-control"
            v-model="last_name"
            :class="{ 'is-invalid': errors.last_name }"
            :placeholder="$t('clinic.lbl_last_name')"
          >
          <div class="invalid-feedback">{{ errors.last_name }}</div>
        </div>

        <!-- Date of Birth -->
        <div class="col-md-12">
          <label class="form-label">{{ $t('customer.lbl_date_of_birth') }} <span class="text-danger">*</span></label>
          <flat-pickr 
            v-model="dob"
            :config="dobConfig"
            class="form-control"
            :class="{ 'is-invalid': errors.dob }"
          />
          <div class="invalid-feedback">{{ errors.dob }}</div>
        </div>

        <!-- Phone Number -->
        <div class="col-md-12">
          <label class="form-label">{{ $t('customer.lbl_phone_number') }}</label>
          <vue-tel-input 
            v-model="contactNumber"
            :class="{ 'is-invalid': errors.contactNumber }"
            @input="handleInput"
            :placeholder="$t('customer.enter_phone_number')"
          />
          <div class="invalid-feedback">{{ errors.contactNumber }}</div>
        </div>

        <!-- Gender -->
        <div class="col-md-12">
          <label class="form-label">{{ $t('customer.lbl_gender') }} <span class="text-danger">*</span></label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input type="radio" class="form-check-input" v-model="gender" value="Male">
              <label class="form-check-label">{{ $t('customer.male') }}</label>
            </div>
            <div class="form-check">
              <input type="radio" class="form-check-input" v-model="gender" value="Female">
              <label class="form-check-label">{{ $t('customer.female') }}</label>
            </div>
            <div class="form-check">
              <input type="radio" class="form-check-input" v-model="gender" value="Other">
              <label class="form-check-label">{{ $t('customer.other') }}</label>
            </div>
          </div>
          <div class="text-danger small" v-if="errors.gender">{{ errors.gender }}</div>
        </div>

        <!-- Relation -->
        <div class="col-md-12">
          <label class="form-label">{{ $t('customer.relation') }} <span class="text-danger">*</span></label>
          <select 
            class="form-select"
            v-model="relation"
            :class="{ 'is-invalid': errors.relation }"
          >
            <option value="">{{ $t('messages.select_relation') }}</option>
            <option value="Parents">{{ $t('customer.parents') }}</option>
            <option value="Siblings">{{ $t('customer.siblings') }}</option>
            <option value="Spouse">{{ $t('customer.spouse') }}</option>
            <option value="Others">{{ $t('customer.other') }}</option>
          </select>
          <div class="invalid-feedback">{{ errors.relation }}</div>
        </div>

      

        <div class="col-12 text-end">
          <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="offcanvas">
            {{ $t('messages.close') }}
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
            {{ $t('messages.save') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { PATIENT_OTHER_STORE_URL} from '../constant/constant'

import { VueTelInput } from 'vue3-tel-input'
import 'vue3-tel-input/dist/vue3-tel-input.css'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import { useModuleId,useRequest } from '@/helpers/hooks/useCrudOpration'
import imageComponent from '@/vue/components/form-elements/imageComponent.vue'
import * as yup from 'yup'
import { useField, useForm } from 'vee-validate'
const { t } = useI18n()
const { storeRequest } = useRequest()

const isSubmitting = ref(false)

const dobConfig = {
  dateFormat: 'Y-m-d',
  maxDate: 'today'
}
const currentId = useModuleId(() => {

}, 'employee_assign')

const validationSchema = yup.object({
  first_name: yup.string().required(t('First Name is Required')),
  last_name: yup.string().required(t('Last Name is Required')),
  dob: yup.string().required(t('Date Of Birth is Required')),
  gender: yup.string().required(t('Gender Required')),
  relation: yup.string().required(t('Relation is Required'))
})

// Default form data
const defaultData = () => {
  errors.value = {}
  return {
    first_name: '',
    last_name: '',
    dob: '',
    contactNumber: '',
    gender: '',
    relation: '',
    profile_image: null,
    user_id: ''
  }
}

const handleInput = (Number, phoneObject) => {
  // Handle the input event
  if (phoneObject?.formatted) {
    contactNumber.value = phoneObject.formatted
  }
}
const image_url = ref()
// Set form data function
const setFormData = (data) => {
  image_url.value = data.profile_image
  resetForm({
    values: {
      first_name: data.first_name || '',
      last_name: data.last_name || '',
      dob: data.dob || '',
      contactNumber: data.contactNumber || '',
      gender: data.gender || '',
      relation: data.relation || '',
      profile_image: data.profile_image || null,
      user_id: data.user_id || ''
    }
  })
}
onMounted(() => {
  setFormData(defaultData())
})

// Update form validation
const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})

const { value: first_name } = useField('first_name')
const { value: last_name } = useField('last_name')
const { value: dob } = useField('dob')
const { value: contactNumber } = useField('contactNumber')
const { value: gender } = useField('gender')
const { value: relation } = useField('relation')
const { value: profile_image } = useField('profile_image')
const { value: user_id } = useField('user_id')
// Reset and close handler
const reset_datatable_close_offcanvas = (res) => {
  isSubmitting.value = false
  if (res.status) {
    window.successSnackbar(res.message)
    renderedDataTable.ajax.reload(null, false)
    bootstrap.Offcanvas.getInstance('#addOtherPatientOffcanvas').hide()
    setFormData(defaultData())
  } else {
    window.errorSnackbar(res.message)
    errors.value = res.all_message
  }
}

// Update submit function
const submitForm = handleSubmit((values) => {
  isSubmitting.value = true
values.user_id=currentId.value
  storeRequest({ 
    url: PATIENT_OTHER_STORE_URL, 
    body: values, 
    type: 'file' 
  })
  .then((res) => {
    reset_datatable_close_offcanvas(res)
  })
  .catch(error => {
    isSubmitting.value = false
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      window.errorSnackbar('Something went wrong')
    }
  })
})

// Add onMounted hook

defineExpose({
  setFormData,
  defaultData
})
</script>