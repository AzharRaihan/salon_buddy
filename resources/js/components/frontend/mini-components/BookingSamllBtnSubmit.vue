<script setup>
defineProps({
  text: String,
  disabled: Boolean,
  onClick: Function
})
</script>
<template>
<div class="booking-small-btn">
    <button type="submit" class="btn btn-booking" :disabled="disabled" @click="onClick">
      <span>{{ text }}</span>
      <div class="arrow-icon-wrap">
        <VIcon size="22" icon="tabler-arrow-narrow-right" class="arrow-icon"/>
      </div>
    </button>
</div>
</template>
