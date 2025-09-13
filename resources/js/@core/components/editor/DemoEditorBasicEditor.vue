<script setup>
const basicEditorContent = ref(`
<h2>
          Item Details
        </h2>
        <p>
          Enter Item Details
</p>
`)
</script>

<template>
  <div>
    <TiptapEditor
      v-model="basicEditorContent"
      class="border rounded basic-editor"
    />
  </div>
</template>

<style lang="scss">
.basic-editor {
  .ProseMirror {
    block-size: 200px;
    outline: none;
    overflow-y: auto;
    padding-inline: 0.5rem;
  }
}
</style>
