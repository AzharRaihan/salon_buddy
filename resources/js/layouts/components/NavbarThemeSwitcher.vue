<script setup>
const themes = [
  {
    name: 'light',
    icon: 'tabler-sun-high',
  },
  {
    name: 'dark',
    icon: 'tabler-moon-stars',
  }
]
</script>

<template>
  <ThemeSwitcher :themes="themes" />
</template>
