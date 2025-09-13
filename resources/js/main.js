import { createApp, nextTick  } from "vue";
import App from "@/App.vue";
import { registerPlugins } from "@core/utils/plugins";
import Vue3Toastify from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { useWebsiteSettingsStore } from "@/stores/websiteSetting.js";
import { useWebsiteSettings } from "@/composables/useWebsiteSettings.js";
const app = createApp(App);

// Motion animation for Vue 3
import { MotionPlugin } from '@vueuse/motion'
app.use(MotionPlugin)

// AOS (Animate On Scroll) library
import AOS from 'aos'
import 'aos/dist/aos.css'

// Font Awesome
import "@fortawesome/fontawesome-free/css/all.min.css";

// Styles
import "@core-scss/template/index.scss";
import "@styles/styles.scss";


// Register plugins
registerPlugins(app);

app.use(Vue3Toastify, {
autoClose: 3000,
  theme: "colored",
});

// Register Motion plugin for animations

// Initialize website settings globally
const initializeApp = async () => {
  // Mount vue app
  app.mount("#app");
  
  // Initialize website settings after app is mounted
  const websiteStore = useWebsiteSettingsStore();
  await websiteStore.initializeSettings();
  
  // Initialize website settings composable for title and favicon
  useWebsiteSettings();
   // Initialize AOS after DOM updates are complete
   await nextTick();
   AOS.init({
     duration: 800,
     easing: 'ease-in-out',
     once: true,
     offset: 100,
   });
}

// Initialize the app
initializeApp();

