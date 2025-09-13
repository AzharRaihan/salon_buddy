<script setup>
import { computed } from 'vue'

// Define props
const props = defineProps({
  link: String,
  text: String,
  icon: String
})

// Use props in computed
const iconClass = computed(() =>
  props.icon === 'tabler-reload' ? 'reload-icon' : 'arrow-icon'
)
</script>
<template>
<div class="btn-section-more-wrap">
    <RouterLink :to="link" class="btn btn-section-more">
      <span>{{ text }}</span>
      <div class="arrow-icon-wrap">
        <VIcon size="22" :icon="icon" :class="iconClass" />
      </div>
    </RouterLink>
</div>
</template>

<style scoped>

</style>
