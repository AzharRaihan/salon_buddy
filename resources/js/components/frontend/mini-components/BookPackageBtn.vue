<script setup>
const props = defineProps({
  link: String,
  text: String
})

const emit = defineEmits(['click'])

const handleClick = (event) => {
  if (props.link === '#') {
    event.preventDefault()
    emit('click', event)
  }
}
</script>
<template>
<div class="btn-booking-wrap-lg">
    <RouterLink :to="link" class="btn btn-booking" @click="handleClick">
      <span>{{ text }}</span>
    </RouterLink>
</div>
</template>

<style scoped>
</style>
