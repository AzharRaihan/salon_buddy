<script setup>
defineProps({
  text: String,
  link: String
})
</script>
<template>
<div class="blur-btn-wrapper">
    <RouterLink :to="link" class="blur-btn">
      <span>{{ text }}</span>
      <div class="arrow-icon-wrap">
        <VIcon icon="tabler-arrow-narrow-right" class="arrow-icon"/>
      </div>
    </RouterLink>
</div>
</template>
<style scoped>
 .blur-btn {
    display: inline-flex;
    align-items: center;
    height: 40px;
    padding: 00px 6px 0px 20px;
    border-radius: 50px;
    /* background: linear-gradient(90deg, #06CFE5, #098D9C); */
    background: #9f9f9f6b;
    color: white;
    font-weight: 500;
    font-size: 16px;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: none;
    position: relative;
    overflow: hidden;
    text-decoration: none;
  }
  [dir="rtl"] .blur-btn {
    padding: 0px 20px 0px 6px;
  }

 .blur-btn span{
    font-size: 16px;
    font-weight: 500;
  }
  .arrow-icon-wrap {
    width: 30px;
    height: 30px;
    margin-left: 12px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }

  [dir="rtl"] .arrow-icon-wrap {
    margin-left: 0px;
    margin-right: 12px;
  }

  .arrow-icon-wrap i {
    font-size: 18px;
    color: #2D2C2B;
    transform: rotate(340deg);
  }

  [dir="rtl"] .arrow-icon-wrap i {
    transform: rotate(210deg);
  }

</style>
