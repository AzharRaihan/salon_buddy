<script setup>
import { ref } from 'vue'

const testValue = ref('')
</script>

<template>
  <div class="pa-4">
    <h3>AppTextField Test</h3>
    
    <!-- Test with placeholder -->
    <AppTextField
      v-model="testValue"
      label="Test Field"
      placeholder="This is a test placeholder"
      class="mb-4"
    />
    
    <!-- Test with i18n placeholder -->
    <AppTextField
      v-model="testValue"
      label="Total Time"
      :placeholder="$t('Total time')"
      class="mb-4"
    />
    
    <!-- Test readonly field -->
    <AppTextField
      v-model="testValue"
      label="Readonly Field"
      placeholder="This is readonly"
      readonly
      class="mb-4"
    />
    
    <p>Current value: {{ testValue }}</p>
  </div>
</template> 