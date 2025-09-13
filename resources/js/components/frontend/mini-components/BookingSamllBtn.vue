<script setup>
defineProps({
  text: String,
  link: String
})
</script>
<template>
<div class="booking-small-btn">
    <RouterLink :to="link" class="btn btn-booking">
      <span>{{ text }}</span>
      <div class="arrow-icon-wrap">
        <VIcon size="22" icon="tabler-arrow-narrow-right" class="arrow-icon"/>
      </div>
    </RouterLink>
</div>
</template>
