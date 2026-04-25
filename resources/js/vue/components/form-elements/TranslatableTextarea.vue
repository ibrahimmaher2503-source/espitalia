<template>
  <div class="form-group" :class="containerClass">
    <label class="form-label" v-if="label">
      {{ label }} <span class="text-danger" v-if="isRequired">*</span>
    </label>
    <ul class="nav nav-tabs mb-1" :id="`${fieldId}-lang-tabs`" style="--bs-nav-link-padding-y: 0.15rem; --bs-nav-link-padding-x: 0.5rem; font-size: 0.75rem;">
      <li class="nav-item" v-for="locale in locales" :key="locale.code">
        <button
          class="nav-link"
          :class="{ active: activeLocale === locale.code }"
          type="button"
          @click.prevent="activeLocale = locale.code"
        >
          {{ locale.label }}
        </button>
      </li>
    </ul>
    <div class="tab-content">
      <div v-for="locale in locales" :key="locale.code" v-show="activeLocale === locale.code">
        <textarea
          class="form-control"
          :dir="locale.code === 'ar' ? 'rtl' : 'ltr'"
          :placeholder="placeholder || label"
          :value="modelValue?.[locale.code] || ''"
          @input="updateValue(locale.code, $event.target.value)"
          :rows="rows"
        ></textarea>
      </div>
    </div>
    <span v-if="errorMessage" class="text-danger">{{ errorMessage }}</span>
    <span v-if="errorMessages && errorMessages.length">
      <ul class="text-danger">
        <li v-for="err in errorMessages" :key="err">{{ err }}</li>
      </ul>
    </span>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: { type: Object, default: () => ({ en: '' }) },
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  isRequired: { type: Boolean, default: false },
  fieldId: { type: String, default: 'field' },
  containerClass: { type: String, default: '' },
  rows: { type: Number, default: 3 },
  errorMessage: { type: String, default: '' },
  errorMessages: { type: Array, default: () => [] },
  locales: {
    type: Array,
    default: () => (window.__app_locales || [
      { code: 'en', label: 'EN' },
      { code: 'ar', label: 'AR' }
    ])
  }
})

const emit = defineEmits(['update:modelValue'])

const activeLocale = ref('en')

const updateValue = (locale, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [locale]: value
  })
}
</script>
