<script setup>
defineProps({
  link: String,
  text: String
})
</script>
<template>
<RouterLink :to="link" class="btn btn-book-now btn-sm">
  {{ text }}
  <span class="booking-arrow">
    <VIcon size="18" icon="tabler-arrow-right icon"/>
  </span>
</RouterLink>
</template>
<style scoped>
</style>
