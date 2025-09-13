<script setup>
const date = ref('')
const label = ref('')
</script>

<template>
  <AppDateTimePicker
    v-model="date"
    :label="label || undefined"
    placeholder=""
    :config="{
      enableTime: true,
      dateFormat: 'F j, Y h:i K',
      time_24hr: false
    }"
  />
</template>
