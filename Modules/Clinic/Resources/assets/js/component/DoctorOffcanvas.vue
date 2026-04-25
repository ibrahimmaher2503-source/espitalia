<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end offcanvas-w-40 offcanvas-booking" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
      <div class="offcanvas-body">
        <div class="row">
          <!-- <div class="col-md-6 create-service-image">
            <label for="" class="form-label w-100">{{ $t('clinic.lbl_image') }}</label>
            <ImageComponent :ImageViewer="image_url" v-model="profile_image" />
            <span class="text-danger">{{ errors.profile_image }}</span>
          </div> -->

          <div class="col-md-6 create-service-image">
            <label for="profile_image" class="form-label w-100">{{ $t('clinic.lbl_image') }}</label>

            <!-- Avatar Style Image Upload Section -->
            <div class="image-upload-container text-center">
              <!-- Circle for the Avatar -->
              <div class="avatar-preview" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; margin: 0 auto;">
                <img :src="profile_image || defaultImage" alt="Profile Image" style="width: 100%; height: 100%; object-fit: cover;">
              </div>

              <!-- Trigger Button to Open File Dialog -->
              <button type="button" class="btn btn-light mt-2" @click="triggerFileInput">{{ $t('clinic.upload') }}</button>

              <!-- Button to remove the image if exists, with margin-top -->
              <button type="button" class="btn btn-danger mt-2 ml-2" name="remove" :disabled="!profile_image" @click="removeImage">{{ $t('messages.remove') }}</button>
            </div>

            <!-- Error Message Display -->
            <span class="text-danger">{{ errors.profile_image }}</span>
          </div>

          <div class="col-md-6">
            <TranslatableInput :is-required="true" :label="$t('clinic.lbl_first_name')" field-id="doctor-first-name" v-model="first_name" :error-message="errors['first_name'] || errors['first_name.en']" :error-messages="errorMessages['first_name']" />
            <TranslatableInput :is-required="true" :label="$t('clinic.lbl_last_name')" field-id="doctor-last-name" v-model="last_name" :error-message="errors['last_name'] || errors['last_name.en']" :error-messages="errorMessages['last_name']" />
            <label for="" class="form-label w-100">{{ $t('clinic.lbl_Email') }} <span class="text-danger">*</span></label>
            <InputField :is-required="true" :label="$t('clinic.lbl_Email')" :placeholder="$t('clinic.lbl_Email')" :icon="'<i class=\'ph ph-envelope\'></i>'" v-model="email" :error-message="errors['email']" :error-messages="errorMessages['email']"></InputField>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('clinic.lbl_gender') }} </label>
              <Multiselect id="gender" v-model="gender" :value="gender" v-bind="gender_data" class="form-group"> </Multiselect>
              <span v-if="errorMessages['gender']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['gender']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.gender }}</span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('clinic.lbl_phone_number') }}<span class="text-danger">*</span> </label>
              <vue-tel-input :value="mobile" @input="handleInput" @keypress="preventInvalidChar" v-bind="{ mode: 'international', maxLen: 15 }" class="form-control"></vue-tel-input>
              <span class="text-danger">{{ errors['mobile'] }}</span>
            </div>
          </div>
          <div class="col-md-6" v-if="currentId === 0">
            <label for="" class="form-label w-100">{{ $t('clinic.lbl_password') }}<span class="text-danger">*</span></label>
            <InputField type="password" :is-required="true" :label="$t('clinic.lbl_password')" :placeholder="$t('clinic.lbl_password')" v-model="password" :error-message="errors['password']" :error-messages="errorMessages['password']"> </InputField>
          </div>
          <div class="col-md-6" v-if="currentId === 0">
            <label for="" class="form-label w-100">{{ $t('clinic.lbl_confirm_password') }} <span class="text-danger">*</span></label>
            <InputField type="password" :is-required="true" :label="$t('clinic.lbl_confirm_password')" :placeholder="$t('clinic.lbl_confirm_password')" v-model="confirm_password" :error-message="errors['confirm_password']" :error-messages="errorMessages['confirm_password']"> </InputField>
          </div>
          <div class="col-md-6">
            <TranslatableInput :label="$t('clinic.lbl_about_self')" field-id="doctor-about-self" v-model="about_self" :error-message="errors['about_self'] || errors['about_self.en']" :error-messages="errorMessages['about_self']" />
          </div>
          <div class="col-md-6">
            <TranslatableInput :label="$t('clinic.lbl_expert')" field-id="doctor-expert" v-model="expert" :error-message="errors['expert'] || errors['expert.en']" :error-messages="errorMessages['expert']" />
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label" for="specialization_ids">{{ $t('specialization.lbl_specializations') }}</label>
              <Multiselect id="specialization_ids" v-model="specialization_ids" :value="specialization_ids" :multiple="true" :placeholder="$t('specialization.lbl_specializations')" v-bind="multiSelectOption" :options="specializations.options" class="form-group"></Multiselect>
            </div>
          </div>
          <div class="col-md-12 mt-4">
            <legend class="px-0 text-capitalize">{{ $t('clinic.other_detail') }}</legend>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label" for="commission_id"> {{ $t('clinic.lbl_select_commission') }} </label><span class="text-danger">*</span>
              <Multiselect id="commission_id" v-model="commission_id" :value="commission_id" :placeholder="$t('clinic.lbl_select_commission')" v-bind="multiselectOption" :options="commissions.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['commission_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['commission_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.commission_id }}</span>
            </div>
          </div>
          <div class="col-md-6" v-if="enable_multi_vendor() == 1 && (role() === 'admin' || role() === 'demo_admin')">
            <div class="form-group">
              <label class="form-label" for="vendor_id">{{ $t('clinic.clinic_admin') }} </label>
              <Multiselect class="form-group" v-model="vendor_id" :value="vendor_id" :options="vendors.options" v-bind="singleSelectOption" :placeholder="$t('clinic.clinic_admin')" @select="getClinic" placeholder="Select Vendor" id="vendor_id"> </Multiselect>
              <span v-if="errorMessages['vendor_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['vendor_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.vendor_id }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label" for="clinic_center">{{ $t('clinic.lbl_select_clinic_center') }}</label>
              <span class="text-danger">*</span>
              <input class="form-check-input m-0 ms-1 align-middle" type="checkbox" id="all-clinics" v-model="selectAllClinics" @change="selectAllClinicsHandler" />
              <Multiselect id="clinic_id" v-model="clinic_id" :value="clinic_id" :multiple="true" :placeholder="$t('clinic.lbl_select_clinic_center')" v-bind="clinicmultiSelectOption" :options="clinicCenter.options" @select="clinicCenterSelect" @deselect="removeAssociatedServices" class="form-group"> </Multiselect>
              <span v-if="errorMessages['clinic_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['clinic_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.clinic_id }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label" for="service">{{ $t('clinic.lbl_select_service') }}</label
              ><span class="text-danger">*</span>
              <input class="form-check-input m-0 ms-1 align-middle" type="checkbox" id="all-services" v-model="selectAllServices" @change="selectAllServicesHandler" />
              <Multiselect id="service_id" v-model="service_id" :multiple="true" :value="service_id" :placeholder="$t('clinic.lbl_select_service')" v-bind="multiSelectOption" :options="services.options" class="form-group"> </Multiselect>
              <span v-if="errorMessages['service_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['service_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.service_id }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <TranslatableInput :label="$t('clinic.experience')" field-id="doctor-experience" v-model="experience" :error-message="errors['experience'] || errors['experience.en']" :error-messages="errorMessages['experience']" />
          </div>
          <div class="col-md-6">
            <TranslatableInput :label="$t('clinic.lbl_description')" field-id="doctor-description" v-model="description" :error-message="errors['description'] || errors['description.en']" :error-messages="errorMessages['description']" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="service">{{ $t('clinic.lbl_postal_code') }} </label>
            <InputField type="text" :label="$t('clinic.lbl_postal_code')" :placeholder="$t('clinic.lbl_postal_code')" v-model="pincode" :error-message="errors['pincode']" :error-messages="errorMessages['pincode']" @keypress="allowOnlyDigits"> </InputField>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('clinic.lbl_country') }} </label>
              <Multiselect id="country-list" v-model="country" :value="country" :placeholder="$t('clinic.lbl_country')" v-bind="singleSelectOption" :options="countries.options" @select="getState" class="form-group"></Multiselect>
              <span v-if="errorMessages['country']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['country']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['country'] }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('clinic.lbl_state') }} </label>
              <Multiselect id="state-list" v-model="state" :value="state" :placeholder="$t('clinic.lbl_state')" v-bind="singleSelectOption" :options="states.options" @select="getCity" class="form-group"></Multiselect>
              <span v-if="errorMessages['state']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['state']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['state'] }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">{{ $t('clinic.lbl_city') }} </label>
              <Multiselect id="city-list" v-model="city" :value="city" :placeholder="$t('clinic.lbl_city')" v-bind="singleSelectOption" :options="cities.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['city']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['city']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['city'] }}</span>
            </div>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-label" for="address">{{ $t('clinic.lbl_address') }}</label>
              <textarea class="form-control" v-model="address" id="address" :placeholder="$t('clinic.lbl_address')"></textarea>
              <span v-if="errorMessages['address']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['address']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <div class="text-danger">{{ errors.address }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="address">{{ $t('clinic.lbl_lat') }}</label>
            <InputField :label="$t('clinic.lbl_lat')" :placeholder="$t('clinic.lbl_lat')" v-model="latitude" :error-message="errors['latitude']" :error-messages="errorMessages['latitude']" @keypress="preventInvalidLatLong"> </InputField>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="address">{{ $t('clinic.lbl_long') }}</label>
            <InputField :label="$t('clinic.lbl_long')" :placeholder="$t('clinic.lbl_long')" v-model="longitude" :error-message="errors['longitude']" :error-messages="errorMessages['longitude']" @keypress="preventInvalidLatLong"></InputField>
          </div>

          <div class="col-md-12 mt-4">
            <legend class="px-0 text-capitalize">{{ $t('clinic.social_media') }}</legend>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="name">{{ $t('clinic.lbl_facebook_link') }}</label>
            <InputField :label="$t('clinic.lbl_facebook_link')" :placeholder="$t('clinic.lbl_facebook_link')" v-model="facebook_link" :error-message="errors['facebook_link']" :error-messages="errorMessages['facebook_link']"> </InputField>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="name">{{ $t('clinic.lbl_instagram_link') }}</label>
            <InputField :label="$t('clinic.lbl_instagram_link')" :placeholder="$t('clinic.lbl_instagram_link')" v-model="instagram_link" :error-message="errors['instagram_link']" :error-messages="errorMessages['instagram_link']"> </InputField>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="name">{{ $t('clinic.lbl_twitter_link') }}</label>
            <InputField :label="$t('clinic.lbl_twitter_link')" :placeholder="$t('clinic.lbl_twitter_link')" v-model="twitter_link" :error-message="errors['twitter_link']" :error-messages="errorMessages['twitter_link']"> </InputField>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="name">{{ $t('clinic.lbl_dribbble_link') }}</label>
            <InputField :label="$t('clinic.lbl_dribbble_link')" :placeholder="$t('clinic.lbl_dribbble_link')" v-model="dribbble_link" :error-message="errors['dribbble_link']" :error-messages="errorMessages['dribbble_link']"> </InputField>
          </div>

          <div class="col-md-12 mt-4">
            <legend class="px-0 text-capitalize">{{ $t('clinic.qualification') }}</legend>
          </div>

          <div class="row">
            <div class="col-md-12" v-for="(input, index) in userInputList" :key="index">
              <div class="row align-items-center">
                <div class="form-group col-md-4">
                  <label class="form-label" for="degree">{{ $t('clinic.degree') }}</label>
                  <input type="text" class="form-control" :placeholder="$t('clinic.degree')" v-model="input.degree" id="degree" />
                  <span class="text-danger degree_err" :id="`degree_err_${index}`"></span>
                </div>

                <div class="form-group col-md-4">
                  <label class="form-label" for="university">{{ $t('clinic.university') }}</label>
                  <input type="text" class="form-control" :placeholder="$t('clinic.university')" v-model="input.university" id="university" />
                  <span class="text-danger university_err" :id="`university_err_${index}`"></span>
                </div>

                <div class="form-group col-md-3">
                  <label class="form-label" for="year">{{ $t('clinic.year') }}</label>
                  <multiselect v-model="input.year" :placeholder="$t('clinic.year')" :options="yearRange()" v-bind="singleSelectOption" id="year"></multiselect>
                  <span class="text-danger year_err" :id="`year_err_${index}`"></span>
                </div>

                <div class="form-group col-md-1 text-end mb-0" v-if="userInputList.length > 1">
                  <button type="button" @click="removeQualification(index)" class="btn btn-danger px-3">
                    <i class="fa-regular fa-trash-can"></i>
                  </button>
                </div>
              </div>
              <div class="col-12">
                <div class="border-top mt-3 pb-5"></div>
              </div>
            </div>

            <div class="form-group col-md-12 text-end">
              <a @click="addQualification" class="btn btn-primary"> <i class="fa-solid fa-plus"></i> {{ $t('clinic.add_qualification') }} </a>
            </div>
          </div>
          <div class="col-md-12 mb-3">
            <label class="form-label" for="name">{{ $t('clinic.lbl_signature') }}</label>
            <Vue3Signature ref="signature" :h="'60px'" :placeholder="$t('clinic.lbl_signature')" style="" class="example form-control signature-pad"> </Vue3Signature>
            <span class="p-0 border-0 text-secondary d-flex justify-content-end w-100 bg-transparent cursor-pointer" @click="clear">Clear</span>
          </div>
          <div class="col-md-12">
            <div class="form-group">
              <div class="d-flex justify-content-between align-items-center form-control">
                <label class="form-label mb-0" for="category-status">{{ $t('clinic.lbl_status') }}</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" :value="1" name="status" id="category-status" type="checkbox" v-model="status" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Vue3Signature from 'vue3-signature'
import { EDIT_URL, STORE_URL, UPDATE_URL, CLINIC_CENTER_LIST, COUNTRY_URL, STATE_URL, CITY_URL, SERVICE_LIST, COMMISSION_LIST, VENDOR_LIST, USER_LIST, SPECIALIZATION_LIST } from '../constant/doctor'
import { useField, useForm } from 'vee-validate'

import { VueTelInput } from 'vue3-tel-input'

import { useModuleId, useRequest, useOnOffcanvasHide } from '@/helpers/hooks/useCrudOpration'
import * as yup from 'yup'

import { readFile, buildMultiSelectObject } from '@/helpers/utilities'
import { useSelect } from '@/helpers/hooks/useSelect'

import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import InputField from '@/vue/components/form-elements/InputField.vue'
import FormElement from '@/helpers/custom-field/FormElement.vue'
import ImageComponent from '@/vue/components/form-elements/imageComponent.vue'
import TranslatableInput from '@/vue/components/form-elements/TranslatableInput.vue'

// Trigger file input click programmatically
const triggerFileInput = () => {
  const fileInput = document.createElement('input');
  fileInput.type = 'file';
  fileInput.accept = '.jpeg, .jpg, .png, .gif';
  fileInput.style.display = 'none'; // Hide file input from UI

  // Handle the file change event
  fileInput.addEventListener('change', handleFileChange);

  // Append to the DOM, trigger the click and then remove from DOM
  document.body.appendChild(fileInput);
  fileInput.click();
  document.body.removeChild(fileInput);
};

const profileFile = ref(null)

// Handle the file input change event
const handleFileChange = (event) => {
  const file = event.target.files[0]; // Get the selected file
  if (file) {
    const reader = new FileReader(); // Create a file reader
    reader.onload = () => {
      profile_image.value = reader.result; // Set the image as base64 string
    };
    reader.readAsDataURL(file); // Read the file as a base64 encoded string
    profileFile.value = file; // store the file for upload
  }
};

// Remove the uploaded image
const removeImage = () => {
  profile_image.value = null; // Clear the image preview
};



const mobile_valid = ref()
// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  type: { type: String, default: () => 'staff' },
  customefield: { type: Array, default: () => [] }
})

const role = () => {
  return window.auth_role[0]
}
const isReceptionist = computed(() => role() === 'receptionist')
const enable_multi_vendor = () => {
  return window.multiVendorEnabled
}

// Select Options
const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true,
  select: 1
})
const multiSelectOption = ref({
  mode: 'tags',
  closeOnSelect: true,
  searchable: true,
  multiple: true
})

const clinicmultiSelectOption = ref({
  mode: 'tags',
  closeOnSelect: true,
  searchable: true,
  multiple: true
})

const multiselectOption = ref({
  mode: 'tags',
  searchable: true,
  multiple: true,
  closeOnSelect: true
})

const gender_data = ref({
  searchable: true,
  options: [
    { label: 'Male', value: 'male' },
    { label: 'Female', value: 'female' },
    { label: 'Intersex', value: 'intersex' }
  ],
  closeOnSelect: true,
  createOption: false
})

const signature = ref(null)
const clear = () => {
  signature.value.clear()
}
const clinicCenter = ref({ options: [], list: [] })
const commissions = ref({ options: [], list: [] })
const services = ref({ options: [], list: [] })
const specializations = ref({ options: [], list: [] })

const getSpecializations = () => {
  useSelect({ url: SPECIALIZATION_LIST }, { value: 'id', label: 'name' }).then((data) => (specializations.value = data))
}

const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

const vendors = ref({ options: [], list: [] })
const getVendorList = () => {
  useSelect({ url: VENDOR_LIST, data: { system_service: 'clinic' } }, { value: 'id', label: 'name' }).then((data) => (vendors.value = data))
}

const getClinic = (value) => {
  clinic_id.value = []
  service_id.value = []
  useSelect({ url: CLINIC_CENTER_LIST, data: { id: value } }, { value: 'id', label: 'clinic_name' }).then((data) => {
    clinicCenter.value = data
    if (isReceptionist.value && clinicCenter.value.list.length > 0) {
      clinic_id.value = [clinicCenter.value.list[0].id] // Automatically select the first clinic for receptionist
    }
  })
}
let selectedService = ref([])
// Edit Form Or Create Form
const currentId = useModuleId(() => {
  if (currentId.value > 0) {
    getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
      if (res.data.signature !== null) {
        signature.value.fromDataURL(res.data.signature)
      }
      signature.value.clear()
      userInputList.value = res.data.doctor_document
      if (res.status && res.data) {
        getState(res.data.country)
        getCity(res.data.state)
        if (enable_multi_vendor() == 1 && res.data.vendor_id) {
          getClinic(res.data.vendor_id)
        } else {
          getClinic()
        }
        clinicCenterSelect(res.data.clinic_id)
        if (res.data.specializations_list) {
          specializations.value = {
            options: res.data.specializations_list.map(s => ({ value: s.id, label: s.name_en || s.name_ar || s.name })),
            list: res.data.specializations_list
          }
        }
        setFormData(res.data)
        selectedService.value = res.data.service_id || []
        service_id.value = selectedService.value
      }
    })
  } else {
    setFormData(defaultData())
  }
})

const yearRange = () => {
  const currentYear = new Date().getFullYear()
  const startYear = 1900
  const years = []
  for (let year = currentYear; year >= startYear; year--) {
    years.push(year)
  }
  return years
}

const userInputList = ref([
  {
    degree: '',
    university: '',
    year: ''
  }
])

const isAddQualificationBtnDisabled = computed(() => {
  return userInputList.value.some((input) => !input.degree || !input.university || !input.year)
})

const addQualification = () => {
  const newIndex = userInputList.value.length
  if (!isAddQualificationBtnDisabled.value) {
    userInputList.value.push({
      index: newIndex,
      degree: '',
      university: '',
      year: ''
    })

    const degreeEl = document.querySelector(`#degree_err_${newIndex - 1}`)
    const universityEl = document.querySelector(`#university_err_${newIndex - 1}`)
    const yearEl = document.querySelector(`#year_err_${newIndex - 1}`)
    if (degreeEl) degreeEl.textContent = ''
    if (universityEl) universityEl.textContent = ''
    if (yearEl) yearEl.textContent = ''
  } else {
    const degreeEl = document.querySelector(`#degree_err_${newIndex - 1}`)
    const universityEl = document.querySelector(`#university_err_${newIndex - 1}`)
    const yearEl = document.querySelector(`#year_err_${newIndex - 1}`)
    if (degreeEl) degreeEl.textContent = 'Degree is required'
    if (universityEl) universityEl.textContent = 'University is required'
    if (yearEl) yearEl.textContent = 'Year is required'
  }
}

const removeQualification = (index) => {
  userInputList.value.splice(index, 1)
}

const countries = ref({ options: [], list: [] })

const getCountry = () => {
  useSelect({ url: COUNTRY_URL }, { value: 'id', label: 'name' }).then((data) => (countries.value = data))
}

const states = ref({ options: [], list: [] })

const getState = (value) => {
  useSelect({ url: STATE_URL, data: value }, { value: 'id', label: 'name' }).then((data) => (states.value = data))
}

const cities = ref({ options: [], list: [] })

const getCity = (value) => {
  useSelect({ url: CITY_URL, data: value }, { value: 'id', label: 'name' }).then((data) => (cities.value = data))
}

const getcommission = () => {
  useSelect({ url: COMMISSION_LIST, data: { type: 'doctor_commission' } }, { value: 'id', label: 'name' }).then((data) => (commissions.value = data))
}
const users = ref({ options: [], list: [] })
const getUser = () => {
  useSelect({ url: USER_LIST }, { value: 'id', label: 'name' }).then((data) => (users.value = data))
}
const emailAlreadyExists = (email) => {
  const userEmails = users.value.list.map((user) => user.email)
  return userEmails.includes(email)
}

onMounted(() => {
  setFormData(defaultData())
  getVendorList()
  getClinic()
  getCountry()
  getcommission()
  getUser()
  getSpecializations()
})

const clinicCenterSelect = (value) => {
  // service_id.value = []
  useSelect({ url: SERVICE_LIST, data: { clinic_id: value } }, { value: 'id', label: 'name' }).then((data) => (services.value = data))
}
const removeAssociatedServices = (deselectedValue) => {
  if (Array.isArray(services.value.list)) {
    const associatedServiceIds = services.value.list.reduce((acc, service) => {
      if (Array.isArray(service.clinic_service_mapping)) {
        const mappings = service.clinic_service_mapping.filter((mapping) => mapping.clinic_id === deselectedValue)
        acc.push(...mappings.map((mapping) => mapping.service_id))
      } else {
        console.error('service.clinic_service_mapping is not an array:', service.clinic_service_mapping)
      }
      return acc
    }, [])
    service_id.value = service_id.value.filter((serviceId) => !associatedServiceIds.includes(serviceId))
    clinicCenterSelect(clinic_id.value)
  } else {
    console.error('services.value.list is not an array:', services.value.list)
  }
}

const selectAllClinics = ref(false)

const selectAllClinicsHandler = () => {
  if (selectAllClinics.value) {
    if (clinicCenter.value && clinicCenter.value.options) {
      clinic_id.value = clinicCenter.value.options.map((option) => option.value)
      clinicCenterSelect(clinic_id.value)
    }
  } else {
    clinic_id.value = []
    clinicCenterSelect()
    selectAllServices.value = false
    removeAssociatedServices(null)
  }
}

const selectAllServices = ref(false)

const selectAllServicesHandler = () => {
  if (selectAllServices.value) {
    if (services.value && services.value.options) {
      service_id.value = services.value.options.map((option) => option.value)
    }
  } else {
    service_id.value = []
  }
}

/*
 * Form Data & Validation & Handeling
 */
// Default FORM DATA
const defaultData = () => {
  userInputList.value = [{ degree: '', university: '', year: '' }]
  errorMessages.value = {}
  return {
    id: '',
    first_name: { en: '', ar: '' },
    last_name: { en: '', ar: '' },
    email: '',
    mobile: '',
    password: '',
    confirm_password: '',
    gender: 'male',
    profile_image: [],
    status: 1,
    vendor_id: '',
    clinic_id: isReceptionist.value && clinicCenter.value.list.length > 0 ? [clinicCenter.value.list[0].id] : [], // Automatically select the first clinic for receptionist
    service_id: [],
    commission_id: [],
    about_self: { en: '', ar: '' },
    expert: { en: '', ar: '' },
    description: { en: '', ar: '' },
    facebook_link: '',
    instagram_link: '',
    twitter_link: '',
    dribbble_link: '',
    address: '',
    city: '',
    country: '',
    pincode: '',
    state: '',
    latitude: '',
    longitude: '',
    experience: { en: '', ar: '' },
    signature: '',
    specialization_ids: [],
    custom_fields_data: {}
  }
}

// const USER_TYPE = ref('')
const image_url = ref()
//  Reset Form
const setFormData = (data) => {
  image_url.value = data.profile_image
  resetForm({
    values: {
      id: data.id,
      first_name: typeof data.first_name === 'object' ? data.first_name : { en: data.first_name || '', ar: '' },
      last_name: typeof data.last_name === 'object' ? data.last_name : { en: data.last_name || '', ar: '' },
      email: data.email,
      mobile: data.mobile,
      password: data.password,
      confirm_password: data.confirm_password,
      gender: data.gender,
      profile_image: data.profile_image,
      vendor_id: data.vendor_id,
      clinic_id: data.clinic_id || [],
      service_id: data.service_id || [],
      commission_id: data.commission_id,
      status: data.status ? true : false,
      custom_fields_data: data.custom_field_data,
      about_self: typeof data.about_self === 'object' ? data.about_self : { en: data.about_self || '', ar: '' },
      expert: typeof data.expert === 'object' ? data.expert : { en: data.expert || '', ar: '' },
      description: typeof data.description === 'object' ? data.description : { en: data.description || '', ar: '' },
      facebook_link: data.facebook_link,
      instagram_link: data.instagram_link,
      twitter_link: data.twitter_link,
      dribbble_link: data.dribbble_link,
      address: data.address,
      city: data.city,
      state: data.state,
      country: data.country,
      pincode: data.pincode,
      latitude: data.latitude,
      longitude: data.longitude,
      experience: typeof data.experience === 'object' ? data.experience : { en: data.experience || '', ar: '' },
      signature: data.signature,
      specialization_ids: data.specialization_ids || []
    }
  })
}

// Reload Datatable, SnackBar Message, Alert, Offcanvas Close
const reset_datatable_close_offcanvas = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
    renderedDataTable.ajax.reload(null, false)
    bootstrap.Offcanvas.getInstance('#form-offcanvas').hide()
    setFormData(defaultData())
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
}
// Validations

const validationSchema = yup.object({
  first_name: yup.object({
    en: yup.string().required('First Name (English) is required'),
    ar: yup.string().nullable()
  }),
  last_name: yup.object({
    en: yup.string().required('Last Name (English) is required'),
    ar: yup.string().nullable()
  }),
  email: yup
    .string()
    .required('Email is a required field')
    .matches(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i, 'Must be a valid email')
    .test('is-unique', 'This email is already taken', function (value) {
      if (currentId.value === 0) {
        return !emailAlreadyExists(value)
      }
      return true
    }),
  mobile: yup
    .string()
    .required('Contact Number is a required field')
    .matches(/^(\+?\d+)?(\s?\d+)*$/, 'Contact Number must contain only digits')
    .test('valid-phone', 'Invalid phone number for the selected country', () => mobile_valid.value),
  password: yup
    .string()
    .test('password', 'Password is required', function (value) {
      if (currentId.value === 0 && !value) {
        return false
      }
      return true
    })
    .min(8, 'Password must be at least 8 characters long'),
  confirm_password: yup
    .string()
    .test('confirm_password', 'Current password is required', function (value) {
      if (currentId.value === 0 && !value) {
        return false
      }
      return true
    })
    .oneOf([yup.ref('password')], 'Passwords must match'),

  commission_id: yup.array().test('commission_id', 'Commission is a required field', function (value) {
    if (value.length == 0) {
      return false
    }
    return true
  }),
  clinic_id: yup.array().test('clinic_id', 'Clinic Center is a required field', function (value) {
    if (value.length == 0) {
      return false
    }
    return true
  }),
  service_id: yup.array().test('service_id', 'Service is a required field', function (value) {
    if (value.length == 0) {
      return false
    }
    return true
  }),
  latitude: yup
    .string()
    .required('Latitude is required')
    .matches(/^-?\d*\.?\d*$/, 'Latitude must be a valid number'),

  longitude: yup
    .string()
    .required('Longitude is required')
    .matches(/^-?\d*\.?\d*$/, 'Longitude must be a valid number'),

  pincode: yup
    .string()
    .nullable()  // allows null or empty string
    .matches(/^\d*$/, 'Postal Code must contain only digits'),
  // country: yup.string().required('Country is required'),
  // state: yup.string().required('State is required'),
  // city: yup.string().required('City is required')
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})
const { value: id } = useField('id')
const { value: first_name } = useField('first_name')
const { value: last_name } = useField('last_name')
const { value: email } = useField('email')
const { value: password } = useField('password')
const { value: confirm_password } = useField('confirm_password')
const { value: gender } = useField('gender')
const { value: mobile } = useField('mobile')
const { value: vendor_id } = useField('vendor_id')
const { value: clinic_id } = useField('clinic_id')
const { value: status } = useField('status')
const { value: service_id } = useField('service_id')
const { value: commission_id } = useField('commission_id')
const { value: profile_image } = useField('profile_image')
const { value: custom_fields_data } = useField('custom_fields_data')
const { value: about_self } = useField('about_self')
const { value: expert } = useField('expert')
const { value: facebook_link } = useField('facebook_link')
const { value: instagram_link } = useField('instagram_link')
const { value: twitter_link } = useField('twitter_link')
const { value: dribbble_link } = useField('dribbble_link')
const { value: address } = useField('address')
const { value: city } = useField('city')
const { value: state } = useField('state')
const { value: country } = useField('country')
const { value: pincode } = useField('pincode')
const { value: latitude } = useField('latitude')
const { value: longitude } = useField('longitude')
const { value: experience } = useField('experience')
const { value: description } = useField('description')
const { value: specialization_ids } = useField('specialization_ids')

watch([clinic_id], ([newClinics], [oldClinics]) => {
  selectAllClinics.value = newClinics.length === clinicCenter.value.options.length
  clinicCenterSelect(newClinics)
})

watch(
  service_id,
  (newServices) => {
    if (newServices.length === 0) {
      selectAllServices.value = false
    }

    selectAllServices.value = newServices.length > 0 && newServices.length === services.value.options.length
  },
  { deep: true }
)

watch(
  () => services.value.options,
  (newOptions) => {
    selectAllServices.value = service_id.value.length > 0 && service_id.value.length === newOptions.length
  }
)

const errorMessages = ref({})

// phone number
const handleInput = (phone, phoneObject) => {
  // Handle the input event
  if (phoneObject?.formatted) {
    console.log(phoneObject)
    mobile.value = phoneObject.formatted
    mobile_valid.value = phoneObject.valid
    console.log(phoneObject.valid)
  }
}

const preventInvalidChar = (event) => {
  const char = String.fromCharCode(event.which);
  const allowed = /[0-9+\s()-]/; // Only allow digits, +, space, (, )
  if (!allowed.test(char)) {
    event.preventDefault();
  }
}
const IS_SUBMITED = ref(false)
// Form Submit
// Flatten translatable {en, ar} objects to _en/_ar fields for FormData
const flattenTranslatableFields = (values) => {
  const translatableFields = ['first_name', 'last_name', 'experience', 'about_self', 'expert', 'description']
  const flattened = { ...values }
  translatableFields.forEach((field) => {
    if (flattened[field] && typeof flattened[field] === 'object' && !Array.isArray(flattened[field]) && !(flattened[field] instanceof File)) {
      flattened[`${field}_en`] = flattened[field].en || ''
      flattened[`${field}_ar`] = flattened[field].ar || ''
      delete flattened[field]
    }
  })
  return flattened
}

const formSubmit = handleSubmit((values) => {
  IS_SUBMITED.value = true
  values.custom_fields_data = JSON.stringify(values.custom_fields_data)
  values.qualifications = JSON.stringify(userInputList.value)
  let signatureData = null
  if (signature.value) {
    signatureData = signature.value.save('image/jpeg')
  }
  values.signature = signatureData
  // Attach the file object for upload
  values.profile_image = profileFile.value
  // Flatten translatable objects to _en/_ar fields
  const body = flattenTranslatableFields(values)
  if (currentId.value > 0) {
    updateRequest({ url: UPDATE_URL, id: currentId.value, body, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  } else {
    storeRequest({ url: STORE_URL, body, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  }
})

const preventInvalidLatLong = (event) => {
  const char = String.fromCharCode(event.which);
  const currentValue = event.target.value;

  // Allow only digits, one dot, and a leading minus sign
  if (!/[0-9.-]/.test(char)) {
    event.preventDefault();
  }

  // Allow only one dot
  if (char === '.' && currentValue.includes('.')) {
    event.preventDefault();
  }

  // Allow only one minus and only at the beginning
  if (char === '-' && (currentValue.includes('-') || event.target.selectionStart !== 0)) {
    event.preventDefault();
  }
};

const allowOnlyDigits = (event) => {
  const char = String.fromCharCode(event.which);
  if (!/[0-9]/.test(char)) {
    event.preventDefault();
  }
};

useOnOffcanvasHide('form-offcanvas', () => setFormData(defaultData()))
</script>
