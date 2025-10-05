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
.btn-book-now {
  height: 42px;
  font-size: 14px;
  padding: 5px 7px 5px 15px;
}
</style>
