<script setup>
import { computed } from 'vue'
import { useAttrs, useId } from 'vue'

defineOptions({
  name: 'AppAutocomplete',
  inheritAttrs: false,
})


// const { class: _class, label, variant: _, ...restAttrs } = useAttrs()
const elementId = computed(() => {
  const attrs = useAttrs()
  const _elementIdToken = attrs.id
  const _id = useId()
  
  return _elementIdToken ? `app-autocomplete-${ _elementIdToken }` : _id
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
const toolTipShow = computed(() => {
  const attrs = useAttrs()
  return attrs.tooltipShow
})

const toolTipTitle = computed(() => {
  const attrs = useAttrs()
  return attrs.tooltipTitle
})

</script>

<template>
  <div
    class="app-autocomplete flex-grow-1"
    :class="$attrs.class"
  >

    <div class="d-flex align-items-center justify-content-between">
      <VLabel
        v-if="label"
        :for="elementId"
        class="mb-1 text-body-2 text-wrap"
        style="line-height: 15px;"
      >
        <span v-html="labelWithAsterisk" />
      </VLabel>

      <span v-if="toolTipShow">
        <VIcon
          icon="tabler-info-square-rounded"
        />
        <VTooltip
            activator="parent"
            location="top"
            v-if="toolTipShow"
          >
          {{ toolTipTitle }}
        </VTooltip>
      </span>
    </div>

    <VAutocomplete
      v-bind="{ 
        ...$attrs,
        class: null,
        label: undefined,
        id: elementId,
        variant: 'outlined',
        menuProps: {
          contentClass: [
            'app-inner-list',
            'app-autocomplete__content',
            'v-autocomplete__content',
          ],
        },
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
    </VAutocomplete>
  </div>
</template>
