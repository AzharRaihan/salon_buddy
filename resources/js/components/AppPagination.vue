<template>
  <div class="pagination-wrapper" v-if="paginationInfo.totalPages > 1">
    <div class="d-flex align-center justify-space-between flex-wrap gap-3 mt-3">
      <!-- Pagination Info -->
      <div class="pagination-info">
        <p class="text-disabled mb-0">
          Showing {{ paginationInfo.start }} to {{ paginationInfo.end }} of {{ paginationInfo.total }} entries
        </p>
      </div>

      <!-- Pagination Controls -->
      <div class="pagination-controls">
        <VPagination
          :model-value="paginationInfo.currentPage"
          active-color="primary"
          :length="paginationInfo.totalPages"
          :total-visible="$vuetify.display.xs ? 1 : Math.min(paginationInfo.totalPages, 5)"
          @update:model-value="handlePageChange"
        />
      </div>

      <!-- Page Size Selector (Optional) -->
      <div class="page-size-selector" v-if="showPageSizeSelector">
        <VSelect
          :model-value="perPage"
          :items="pageSizeOptions"
          label="Per page"
          density="compact"
          variant="outlined"
          hide-details
          class="page-size-select"
          @update:model-value="handlePageSizeChange"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  paginationInfo: {
    type: Object,
    required: true,
    default: () => ({
      start: 0,
      end: 0,
      total: 0,
      currentPage: 1,
      totalPages: 0,
      perPage: 10
    })
  },
  perPage: {
    type: Number,
    default: 10
  },
  showPageSizeSelector: {
    type: Boolean,
    default: false
  },
  pageSizeOptions: {
    type: Array,
    default: () => [5, 10, 25, 50]
  }
})

const emit = defineEmits(['update:page', 'update:perPage'])

const handlePageChange = (page) => {
  emit('update:page', page)
}

const handlePageSizeChange = (size) => {
  emit('update:perPage', size)
}
</script>

<style scoped>


.pagination-info {
  flex: 1;
}

.pagination-controls {
  flex: 0 0 auto;
}

.page-size-selector {
  flex: 0 0 auto;
}

.page-size-select {
  min-width: 100px;
}

@media (max-width: 600px) {
  .pagination-wrapper .d-flex {
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }
  
  .pagination-info {
    text-align: center;
  }
}
.pagination-wrapper .pagination-controls ul {
    margin-bottom: 0 !important;
}
</style>
