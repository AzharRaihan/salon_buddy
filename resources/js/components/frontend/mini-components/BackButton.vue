<script setup>
defineProps({
  text: String,
  link: String
})
</script>
<template>
<div class="back-button">
    <RouterLink :to="link" class="btn btn-back">
      <div class="arrow-icon-wrap">
        <VIcon size="22" icon="tabler-arrow-narrow-left" class="arrow-icon"/>
      </div>
      <span>{{ text }}</span>
    </RouterLink>
</div>
</template>


<style scoped>

/* Booking Small Button */
.back-button {
  display: inline-flex;
  justify-content: center;
  align-items: center;
}

.back-button .arrow-icon-wrap {
  width: 30px;
  height: 30px;
  background: var(--color-white);
  border-radius: 50%;
  color: var(--primary-bg-color);
  display: inline-flex;
  justify-content: center;
  align-items: center;
}
.back-button .btn-back {
  display: inline-flex;
  align-items: center;
  padding: 10px 15px 10px 10px;
  color: var(--color-white);
  text-transform: capitalize;
  font-size: 1rem;
  font-weight: 400;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
  z-index: 1;
  height: 46px;
  border-radius: 50px;
}
.back-button .btn-back span {
  margin-left: 10px;
}
.back-button .btn-back::before {
  content: '';
  position: absolute;
  bottom: 0;
  right: 0;
  width: 0%;
  height: 100%;
  background-color: var(--primary-bg-hover-color);
  transition: all 0.3s;
  border-radius: 10rem;
  z-index: -1;
}
.back-button .btn-back::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: var(--primary-bg-color);
  border-radius: 10rem;
  z-index: -2;
}

.back-button .btn-back:hover {
  color: var(--color-white);
}

.back-button .btn-back:hover::before {
  width: 100%;
}
</style>