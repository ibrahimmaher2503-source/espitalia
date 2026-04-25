<template>
  <div class="custom-form" id="customform">
    <form>
      <div>
        <div>
          <h6 class="m-0 h5">
            <span :class="[title_alignment, title_color]">{{ title }}</span>
          </h6>

        </div>
        <div>

          <div v-for="(element, index) in field_data" :key="element.id" class="form-field">
            <!-- Text Field -->
            <div v-if="element.type === 'text'" :ref="'field_' + element.id">
              <label>{{ element.label }} <span v-if="element.validation_status == 1"
                  class="text-danger">*</span></label>
              <input type="text" :id="'text_' + element.id" v-model="element.value" class="form-control"
                :placeholder="element.placeholder" />
            </div>

            <!-- Number Field -->
            <div v-else-if="element.type === 'number'" :ref="'field_' + element.id">
              <label>{{ element.label }} <span v-if="element.validation_status == 1"
                  class="text-danger">*</span></label>
              <input type="number" :id="'number_' + element.id" v-model="element.value" class="form-control"
                :placeholder="element.placeholder" />
            </div>

            <!-- Checkbox -->
            <div v-else-if="element.type === 'checkbox'" :ref="'field_' + element.id">
              <label>
                {{ element.label }}
                <span v-if="element.validation_status == 1" class="text-danger">*</span>
              </label>
              <div v-for="(option, index) in element.option" :key="index" class="form-check">
                <input type="checkbox" :id="'checkbox_' + element.id + '_' + option" :value="option"
                  class="form-check-input" />
                <label :for="'checkbox_' + element.id + '_' + option" class="form-check-label">
                  {{ option }}
                </label>
              </div>
            </div>


            <!-- TextArea -->
            <div v-else-if="element.type === 'textarea'" :ref="'field_' + element.id">
              <label>{{ element.label }} <span v-if="element.validation_status == 1"
                  class="text-danger">*</span></label>
              <textarea :id="'textarea_' + element.id" v-model="element.value" class="form-control"
                :placeholder="element.placeholder"></textarea>
            </div>

            <!-- File Upload -->
            <div v-else-if="element.type === 'file'" :ref="'field_' + element.id">
              <label>{{ element.label }} <span v-if="element.validation_status == 1"
                  class="text-danger">*</span></label>
              <input type="file" :id="'file_' + element.id" class="form-control"
                @change="handleFileUpload($event, element)" />

              <div v-if="element.value" class="mt-2">
                <img :src="element.value" alt="Uploaded Image" class="img-fluid" height="150px" width="200px" />
              </div>
            </div>

            <!-- Multi Select -->
            <div v-else-if="element.type === 'multi_select'" :ref="'field_' + element.id" class="form-group">
              <label>{{ element.label }}
                <span v-if="element.validation_status == 1" class="text-danger">*</span>
              </label>
              <Multiselect :id="'multiselect_' + element.id" :options="element.option" v-model="element.value"
                :searchable="true" multiple="true" v-bind="multiSelectOption">
              </Multiselect>
            </div>

            <!-- Select -->
            <div v-else-if="element.type === 'select'" :ref="'field_' + element.id">
              <label>{{ element.label }} <span v-if="element.validation_status === 1"
                  class="text-danger">*</span></label>
              <select :id="'select_' + element.id" class="form-control" v-model="element.value">
                <option value="" disabled>Select option</option>
                <option v-if="!element.option.length">No options available</option>
                <option v-else v-for="(option, index) in element.option" :key="index" :value="option">
                  {{ option }}
                </option>
              </select>
            </div>

            <!-- Radio -->
            <div v-else-if="element.type === 'radio'" :ref="'field_' + element.id">
              <label>{{ element.label }} <span v-if="element.validation_status == 1"
                  class="text-danger">*</span></label>
              <div v-for="(option, optIndex) in element.option" :key="optIndex" class="form-check">
                <input type="radio" :id="'radio_' + element.id + '_' + option" :value="option" v-model="element.value"
                  class="form-check-input" :checked="optIndex === 0" />
                <label :for="'radio_' + element.id + '_' + option" class="form-check-label">
                  {{ option }}
                </label>
              </div>
            </div>

            <!-- Calendar -->
            <div v-else-if="element.type === 'calender'" :ref="'field_' + element.id">
              <label>{{ element.label }} <span v-if="element.validation_status == 1"
                  class="text-danger">*</span></label>
              <input type="date" :id="'calendar_' + element.id" v-model="element.value" class="form-control" />
            </div>

            <!-- Heading -->

            <div v-else-if="element.type === 'heading'" :ref="'field_' + element.id">
              <component :is="element.placeholder">
                {{ element.label }}
              </component>
            </div>

            <!-- Hr Tag -->
            <div v-else-if="element.type === 'hr_tag'" :ref="'field_' + element.id">
              <hr />
            </div>
          </div>

        </div>

        <div class="offcanvas-footer border-top pt-4">
          <div class="d-grid d-sm-flex justify-content-sm-end gap-3">
            <button class="btn btn-white d-block" type="button" data-bs-dismiss="offcanvas">
              {{ $t('messages.cancel') }}
            </button>
            <button :disabled="!isFormValid" class="btn btn-secondary" name="submit">

              {{ $t('messages.save') }}
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useFormModuleId, useRequest } from "@/helpers/hooks/useCrudOpration";
import { GET_CUSTOM_FORM, STORE_DATA_URL } from '../constant/custom-form'

// Props for receiving dynamic data
const props = defineProps({
  form_id: { type: Number, default: 0 },
  appointment_id: { type: Number, default: 0 },
  type: { type: String, default: "" },
  title: { type: String, default: "" },
});

// Reactive variables
const title_alignment = ref(null);
const title_color = ref(null);
const field_data = ref([]);
const isFormValid = ref(false);




// Watcher for validating the form



// Fetch form data when the component is mounted
const { listingRequest } = useRequest();

listingRequest({ url: GET_CUSTOM_FORM, data: { form_id: props.form_id } }).then((res) => {

  if (res.data) {
    title_alignment.value = res.data.title_alignment;
    title_color.value = res.data.title_color;
    field_data.value = res.data.field_data;
  }
});



// Submit form handler
const { storeRequest } = useRequest();
function submitForm() {
  const payload = {
    form_data: JSON.stringify(field_data.value),
    form_id: props.form_id,
    module_id: props.appointment_id,
    module: props.type,
  };
  storeRequest({ url: STORE_DATA_URL, body: payload }).then((res) => {
    if (res.status) {
      alert("Form submitted successfully");
    } else {
      alert("Error submitting form");
    }
  });
}

// Cancel form handler
function cancelForm() {
  field_data.value = []; // Reset the form
}
</script>

<style scoped>
.custom-form {
  padding: 1.5rem;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-title {
  margin-bottom: 1rem;
}

.form-footer {
  margin-top: 1rem;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}
</style>
