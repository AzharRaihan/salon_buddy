<template>
    <div class="category-buttons">
        <button v-for="category in categories" :key="category.id" class="btn btn-primary" :class="{ 'category-active': selectedCategory === category.name }"
            @click="$emit('category-select', category.name)">
            {{ category.name }}
        </button>
    </div>
</template>

<script setup>
// Define props
defineProps({
    categories: {
        type: Array,
        required: true
    },
    selectedCategory: {
        type: String,
        required: true
    }
})

// Define emits
defineEmits(['category-select'])
</script>
<style scoped>
.category-active {
    background-color: var(--primary-color-hover) !important;
    color: #fff !important;
}
</style>