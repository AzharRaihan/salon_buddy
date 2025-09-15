<script setup>
const props = defineProps({
  text: {
    type: String,
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  }
})
</script>
<template>
<div class="booking-small-btn">
    <button type="submit" class="btn btn-booking" :disabled="disabled">
      <span>{{ text }}</span>
      <div class="arrow-icon-wrap">
        <VIcon size="22" icon="tabler-arrow-narrow-right" class="arrow-icon"/>
      </div>
    </button>
</div>
</template>
