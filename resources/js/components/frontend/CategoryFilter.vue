 <template>
  <div class="filter-list">
    <h4>{{ title }}</h4>
    <ul>
      <li v-for="category in categories" :key="category.id">
        <div class="custom-checkbox">
          <input 
            type="checkbox" 
            :id="`category-${category.id}`"
            :value="category.id"
            @change="handleCategoryChange(category.id)"
            :checked="selectedCategories.includes(category.id)"
          >
          <label :for="`category-${category.id}`">
            <span class="checkmark"></span>
            {{ category.name }}
          </label>
        </div>
      </li>
    </ul>
    
    <!-- Clear Filters Button -->
    <div v-if="selectedCategories.length > 0" class="mt-3">
      <button 
        class="btn btn-sm btn-filter-count" 
        @click="clearFilters"
        type="button"
      >
        Clear Filters ({{ selectedCategories.length }})
      </button>
    </div>
  </div>
</template>

<script setup>
// Props
defineProps({
  categories: {
    type: Array,
    required: true,
    default: () => []
  },
  selectedCategories: {
    type: Array,
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'All Categories'
  }
})

// Emits
const emit = defineEmits(['category-change', 'clear-filters'])

// Methods
const handleCategoryChange = (categoryId) => {
  emit('category-change', categoryId)
}

const clearFilters = () => {
  emit('clear-filters')
}
</script>

<style scoped>
.btn-filter-count {
  border-color: var(--primary-bg-color);
  background-color: var(--primary-bg-color);
  color: var(--color-white);
}

.btn-filter-count:hover {
  background-color: var(--primary-bg-hover-color);
  color: var(--color-white);
}
</style> 