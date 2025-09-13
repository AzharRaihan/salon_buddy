<script setup>
import {
  useDropZone,
  useFileDialog,
  useObjectUrl,
} from '@vueuse/core'

const props = defineProps({
  modelValue: {
    type: [File, String],
    default: null
  },
  image_url: {
    type: String,
    default: null
  },
  title: {
    type: String,
    default: 'Drag and drop your image here.'
  },
  subtitle: {
    type: String,
    default: 'or'
  },
  buttonText: {
    type: String,
    default: 'Browse Images'
  }
})

const emit = defineEmits(['update:modelValue'])

const dropZoneRef = ref()
const fileData = ref([])
const { open, onChange } = useFileDialog({ accept: 'image/*' })

// Initialize fileData with image_url if provided
onMounted(() => {
  if (props.image_url) {
    fileData.value = [{
      file: null,
      url: props.image_url,
    }]
  }
})

// Watch for image_url changes
watch(() => props.image_url, (newValue) => {
  if (newValue) {
    fileData.value = [{
      file: null,
      url: newValue,
    }]
  }
})

function onDrop(DroppedFiles) {
  DroppedFiles?.forEach(file => {
    if (file.type.slice(0, 6) !== 'image/') {
      alert('Only image files are allowed')
      return
    }
    fileData.value = [{
      file,
      url: useObjectUrl(file).value ?? '',
    }]
    emit('update:modelValue', file)
  })
}

onChange(selectedFiles => {
  if (!selectedFiles)
    return
  for (const file of selectedFiles) {
    fileData.value = [{
      file,
      url: useObjectUrl(file).value ?? '',
    }]
    emit('update:modelValue', file)
  }
})

useDropZone(dropZoneRef, onDrop)

// Watch for external modelValue changes
watch(() => props.modelValue, (newValue) => {
  if (!newValue) {
    fileData.value = []
  }
})
</script>

<template>
  <div class="flex">
    <div class="w-full h-auto relative">
      <div ref="dropZoneRef" class="cursor-pointer" @click="() => open()">
        <div v-if="fileData.length === 0"
          class="d-flex flex-column justify-center align-center gap-y-2 pa-12 drop-zone rounded">
          <IconBtn variant="tonal" class="rounded-sm">
            <VIcon icon="tabler-upload" />
          </IconBtn>
          <h4 class="text-h4">
            {{ title }}
          </h4>
          <span class="text-disabled">{{ subtitle }}</span>

          <VBtn variant="tonal" size="small">
            {{ buttonText }}
          </VBtn>
        </div>

        <div v-else class="d-flex justify-center align-center gap-3 pa-8 drop-zone flex-wrap">
          <VRow class="match-height w-100">
            <template v-for="(item, index) in fileData" :key="index">
              <VCol cols="12" sm="4">
                <VCard :ripple="false">
                  <VCardText class="d-flex flex-column" @click.stop>
                    <VImg :src="item.url" width="100%" height="100%" class="w-100 mx-auto" />
                    <div class="mt-2">
                      <span v-if="item.file" class="clamp-text text-wrap">
                        {{ item.file.name }}
                      </span>
                      <span v-if="item.file">
                        {{ item.file.size / 1000 }} KB
                      </span>
                    </div>
                  </VCardText>
                  <VCardActions>
                    <VBtn variant="text" block @click.stop="() => {
                      fileData.splice(index, 1)
                      emit('update:modelValue', null)
                    }">
                      Remove File
                    </VBtn>
                  </VCardActions>
                </VCard>
              </VCol>
            </template>
          </VRow>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.drop-zone {
  border: 1px dashed rgba(var(--v-theme-on-surface), var(--v-border-opacity));
}
</style>
