<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
      <div class="offcanvas-body">
        <div class="form-group">
          <div class="text-center">
            <img :src="ImageViewer || defaultImage" alt="feature-image" class="img-fluid mb-2 avatar-140 avatar-rounded" />
            <div class="d-flex align-items-center justify-content-center gap-2">
              <input type="file" ref="profileInputRef" class="form-control d-none" id="file_url" name="file_url" @change="fileUpload" accept=".jpeg, .jpg, .png, .gif" />
              <label class="btn btn-info" for="file_url">{{ $t('messages.upload') }}</label>
              <input type="button" class="btn btn-danger" name="remove" :value="$t('messages.remove')" @click="removeLogo()" v-if="ImageViewer" />
            </div>
          </div>
        </div>
        <InputField class="col-md-12" type="text" :is-required="true" :label="`${$t('service_package.lbl_name')} (EN)`" placeholder="" v-model="name_en" :error-message="errors['name_en']" :error-messages="errorMessages['name_en']"></InputField>
        <InputField class="col-md-12" type="text" :label="`${$t('service_package.lbl_name')} (AR)`" placeholder="" v-model="name_ar" :error-message="errors['name_ar']" :error-messages="errorMessages['name_ar']"></InputField>
        <InputField class="col-md-12" type="text" :is-required="true" :label="$t('service.lbl_duration_min')" placeholder="" v-model="duration_min" :error-message="errors['duration_min']" :error-messages="errorMessages['duration_min']"></InputField>
        <InputField class="col-md-12" type="text" :is-required="true" :label="`${$t('service.lbl_default_price')} (${CURRENCY_SYMBOL})`" placeholder="" v-model="default_price" :error-message="errors['default_price']" :error-messages="errorMessages['default_price']"></InputField>

        <div class="form-group">
          <label class="form-label" for="category_id">{{ $t('service.lbl_category') }} <span class="text-danger">*</span> </label>
          <Multiselect v-model="category_id" :value="category_id" v-bind="categories" id="category_id" @select="changeCategory"></Multiselect>
          <span v-if="errorMessages['category_id']">
            <ul class="text-danger">
              <li v-for="err in errorMessages['category_id']" :key="err">{{ err }}</li>
            </ul>
          </span>
          <span class="text-danger">{{ errors.category_id }}</span>
        </div>

        <div class="form-group" v-if="subCategories.options.length > 0">
          <label class="form-label" for="sub_category_id">{{ $t('service.lbl_sub_category') }} </label>
          <Multiselect v-model="sub_category_id" :value="sub_category_id" v-bind="subCategories" id="sub_category_id"></Multiselect>
          <span v-if="errorMessages['sub_category_id']">
            <ul class="text-danger">
              <li v-for="err in errorMessages['sub_category_id']" :key="err">{{ err }}</li>
            </ul>
          </span>
          <span class="text-danger">{{ errors.sub_category_id }}</span>
        </div>

        <div v-for="field in customefield" :key="field.id">
          <FormElement v-model="custom_fields_data" :name="field.name" :label="field.label" :type="field.type" :required="field.required" :options="field.value" :field_id="field.id"></FormElement>
        </div>

        <div class="form-group col-md-12">
          <label class="form-label">{{ $t('service.lbl_description') }} (EN)</label>
          <textarea class="form-control" v-model="description_en" rows="3"></textarea>
          <span class="text-danger" v-if="errors['description_en']">{{ errors['description_en'] }}</span>
        </div>
        <div class="form-group col-md-12">
          <label class="form-label">{{ $t('service.lbl_description') }} (AR)</label>
          <textarea class="form-control" v-model="description_ar" rows="3" dir="rtl"></textarea>
          <span class="text-danger" v-if="errors['description_ar']">{{ errors['description_ar'] }}</span>
        </div>

        <div class="form-group">
          <div class="d-flex justify-content-between align-items-center">
            <label class="form-label" for="category-status">{{ $t('service.lbl_status') }}</label>
            <div class="form-check form-switch">
              <input class="form-check-input" :value="status" :checked="status" name="status" id="category-status" type="checkbox" v-model="status" />
            </div>
          </div>
        </div>
      </div>
      <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { EDIT_URL, STORE_URL, UPDATE_URL, CATEGORY_LIST } from '../constant/service'
import { useField, useForm } from 'vee-validate'
import InputField from '@/vue/components/form-elements/InputField.vue'

import { useModuleId, useRequest, useOnOffcanvasHide } from '@/helpers/hooks/useCrudOpration'
import * as yup from 'yup'
import { buildMultiSelectObject } from '@/helpers/utilities'
import { readFile } from '@/helpers/utilities'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import FormElement from '@/helpers/custom-field/FormElement.vue'

// props
defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] },
  defaultImage: { type: String, default: 'https://dummyimage.com/600x300/cfcfcf/000000.png' }
})
const CURRENCY_SYMBOL = ref(window.defaultCurrencySymbol)
const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

// Edit Form Or Create Form
const currentId = useModuleId(() => {
  subCategories.value.options = []
  if (currentId.value > 0) {
    getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
      if (res.status) {
        setFormData(res.data)
        changeCategory(category_id.value)
        if (res.data.sub_category_id.value != 0) {
          sub_category_id.value = res.data.sub_category_id
        }
      }
    })
  } else {
    setFormData(defaultData())
  }
})

// File Upload Function
const ImageViewer = ref(null)
const profileInputRef = ref(null)
const fileUpload = async (e) => {
  let file = e.target.files[0]
  await readFile(file, (fileB64) => {
    ImageViewer.value = fileB64
    profileInputRef.value.value = ''
  })
  file_url.value = file
}

// Function to delete Images
const removeImage = ({ imageViewerBS64, changeFile }) => {
  imageViewerBS64.value = null
  changeFile.value = null
}

const removeLogo = () => removeImage({ imageViewerBS64: ImageViewer, changeFile: file_url })

/*
 * Form Data & Validation & Handeling
 */
// Default FORM DATA
const defaultData = () => {
  ImageViewer.value = props.defaultImage
  errorMessages.value = {}
  return {
    name_en: '',
    name_ar: '',
    description_en: '',
    description_ar: '',
    duration_min: '',
    default_price: '',
    status: 1,
    category_id: '',
    sub_category_id: '',
    file_url: null,
    custom_fields_data: {}
  }
}

//  Reset Form
const setFormData = (data) => {
  ImageViewer.value = data.file_url || props.defaultImage
  resetForm({
    values: {
      name_en: data.name_en || '',
      name_ar: data.name_ar || '',
      description_en: data.description_en || '',
      description_ar: data.description_ar || '',
      duration_min: data.duration_min,
      default_price: data.default_price,
      status: data.status,
      category_id: data.category_id,
      sub_category_id: data.sub_category_id,
      file_url: data.file_url,
      custom_fields_data: data.custom_field_data
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
    removeImage({ imageViewerBS64: ImageViewer, changeFile: file_url })
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
}

const numberRegex = /^\d+$/
// Validations
const validationSchema = yup.object({
  name_en: yup.string().nullable().when('name_ar', {
    is: (val) => !val || val.trim() === '',
    then: (schema) => schema.required('Name (English) is required when Arabic name is not provided'),
    otherwise: (schema) => schema.nullable()
  }),
  name_ar: yup.string().nullable(),
  description_en: yup.string().nullable(),
  description_ar: yup.string().nullable(),
  duration_min: yup.string().required('Service Duration ( Mins ) is a required field').matches(/^\d+$/, 'Only numbers are allowed'),
  default_price: yup.string().required('Default Price is a required field').matches(/^\d+$/, 'Only numbers are allowed'),
  category_id: yup.string().required('Category is a required field').matches(/^\d+$/, 'Only numbers are allowed'),
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})
const { value: name_en } = useField('name_en')
const { value: name_ar } = useField('name_ar')
const { value: description_en } = useField('description_en')
const { value: description_ar } = useField('description_ar')
const { value: duration_min } = useField('duration_min')
const { value: default_price } = useField('default_price')
const { value: status } = useField('status')
const { value: category_id } = useField('category_id')
const { value: sub_category_id } = useField('sub_category_id')
const { value: file_url } = useField('file_url')
const { value: custom_fields_data } = useField('custom_fields_data')

const errorMessages = ref({})

const categories = ref({
  searchable: true,
  createOption: true,
  options: []
})

const subCategories = ref({
  searchable: true,
  createOption: true,
  options: []
})

const getCategoryList = () => {
  listingRequest({ url: CATEGORY_LIST, data: { id: 0 } }).then((res) => (categories.value.options = buildMultiSelectObject(res, { value: 'id', label: 'name' })))
}

const changeCategory = (value) => {
  sub_category_id.value = null
  listingRequest({ url: CATEGORY_LIST, data: { id: value } }).then((res) => (subCategories.value.options = buildMultiSelectObject(res, { value: 'id', label: 'name' })))
}

onMounted(() => {
  getCategoryList()
  setFormData(defaultData())
})

// Form Submit
const IS_SUBMITED = ref(false)
const formSubmit = handleSubmit((values) => {
  if (IS_SUBMITED.value) return false

  values.custom_fields_data = JSON.stringify(values.custom_fields_data)
  if (currentId.value > 0) {
    updateRequest({ url: UPDATE_URL, id: currentId.value, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  } else {
    storeRequest({ url: STORE_URL, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  }
})
</script>
