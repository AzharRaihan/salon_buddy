<script setup>
defineProps({
  link: String,
  text: String
})
</script>
<template>
<div class="btn-booking-wrap">
    <RouterLink :to="link" class="btn btn-booking">
      <span>{{ text }}</span>
      <div class="arrow-icon-wrap">
        <VIcon size="22" icon="tabler-arrow-narrow-right" class="arrow-icon"/>
      </div>
    </RouterLink>
</div>
</template>
<style scoped>
</style>
