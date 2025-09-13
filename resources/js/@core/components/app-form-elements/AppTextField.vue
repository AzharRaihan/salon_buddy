<script setup>
defineOptions({
  name: 'AppTextField',
  inheritAttrs: false,
})

const elementId = computed(() => {
  const attrs = useAttrs()
  const _elementIdToken = attrs.id
  const _id = useId()
  
  return _elementIdToken ? `app-text-field-${ _elementIdToken }` : _id
})

const label = computed(() => useAttrs().label)


const labelWithAsterisk = computed(() => {
  const attrs = useAttrs()
  const isRequired = attrs.required !== undefined && attrs.required !== false

  if (!label.value) return ''
  
  return isRequired
    ? `${label.value}<span style='color:red; font-size: 14px; font-weight: 600;'> *</span>`
    : label.value
})

</script>

<template>
  <div
    class="app-text-field flex-grow-1"
    :class="$attrs.class"
  >

    <VLabel
      v-if="label"
      :for="elementId"
      class="mb-1 text-body-2 text-wrap"
      style="line-height: 15px;"
    >
      <span v-html="labelWithAsterisk" />
    </VLabel>

    <VTextField
      v-bind="{
        ...$attrs,
        class: null,
        label: undefined,
        variant: 'outlined',
        id: elementId,
      }"
    >
      <template
        v-for="(_, name) in $slots"
        #[name]="slotProps"
      >
        <slot
          :name="name"
          v-bind="slotProps || {}"
        />
      </template>
    </VTextField>
  </div>
</template>
