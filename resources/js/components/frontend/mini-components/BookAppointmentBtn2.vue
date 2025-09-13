<script setup>
defineProps({
  text: String
})
</script>
<template>
<div class="btn-booking-wrap">
    <button  type="submit" class="btn btn-booking">
      <span>{{ text }}</span>
      <div class="arrow-icon-wrap">
        <VIcon size="22" icon="tabler-arrow-narrow-right" class="arrow-icon"/>
      </div>
    </button>
</div>
</template>
<style scoped>
</style>
