// vite.config.js
import VueI18nPlugin from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/@intlify/unplugin-vue-i18n/lib/vite.mjs";
import vue from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import vueJsx from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/@vitejs/plugin-vue-jsx/dist/index.mjs";
import laravel from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/laravel-vite-plugin/dist/index.js";
import { fileURLToPath } from "node:url";
import AutoImport from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/unplugin-auto-import/dist/vite.js";
import Components from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/unplugin-vue-components/dist/vite.js";
import {
  VueRouterAutoImports,
  getPascalCaseRouteName
} from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/unplugin-vue-router/dist/index.mjs";
import VueRouter from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/unplugin-vue-router/dist/vite.mjs";
import { defineConfig } from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/vite/dist/node/index.js";
import Layouts from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/vite-plugin-vue-layouts/dist/index.mjs";
import vuetify from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/vite-plugin-vuetify/dist/index.mjs";
import svgLoader from "file:///D:/xampp-8.2.12/htdocs/salon_buddy/node_modules/vite-svg-loader/index.js";
var __vite_injected_original_import_meta_url = "file:///D:/xampp-8.2.12/htdocs/salon_buddy/vite.config.js";
var vite_config_default = defineConfig({
  base: "/salon_buddy/",
  plugins: [
    // Docs: https://github.com/posva/unplugin-vue-router
    // ℹ️ This plugin should be placed before vue plugin
    VueRouter({
      getRouteName: (routeNode) => {
        return getPascalCaseRouteName(routeNode).replace(/([a-z\d])([A-Z])/g, "$1-$2").toLowerCase();
      },
      routesFolder: "resources/js/pages"
    }),
    vue({
      template: {
        compilerOptions: {
          isCustomElement: (tag) => tag === "swiper-container" || tag === "swiper-slide"
        },
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    laravel({
      input: ["resources/js/main.js"],
      refresh: true,
      hotFile: "public/hot"
    }),
    vueJsx(),
    // Docs: https://github.com/vuetifyjs/vuetify-loader/tree/master/packages/vite-plugin
    vuetify({
      styles: {
        configFile: "resources/styles/variables/_vuetify.scss"
      }
    }),
    // Docs: https://github.com/johncampionjr/vite-plugin-vue-layouts#vite-plugin-vue-layouts
    Layouts({
      layoutsDirs: "./resources/js/layouts/"
    }),
    // Docs: https://github.com/antfu/unplugin-vue-components#unplugin-vue-components
    Components({
      dirs: [
        "resources/js/@core/components",
        "resources/js/views/demos",
        "resources/js/components"
      ],
      dts: true,
      resolvers: [
        (componentName) => {
          if (componentName === "VueApexCharts")
            return {
              name: "default",
              from: "vue3-apexcharts",
              as: "VueApexCharts"
            };
        }
      ]
    }),
    // Docs: https://github.com/antfu/unplugin-auto-import#unplugin-auto-import
    AutoImport({
      imports: [
        "vue",
        VueRouterAutoImports,
        "@vueuse/core",
        "@vueuse/math",
        "vue-i18n",
        "pinia"
      ],
      dirs: [
        "./resources/js/@core/utils",
        "./resources/js/@core/composable/",
        "./resources/js/composables/",
        "./resources/js/utils/",
        "./resources/js/plugins/*/composables/*"
      ],
      vueTemplate: true,
      // ℹ️ Disabled to avoid confusion & accidental usage
      ignore: ["useCookies", "useStorage"],
      eslintrc: {
        enabled: true,
        filepath: "./.eslintrc-auto-import.json"
      }
    }),
    VueI18nPlugin({
      runtimeOnly: true,
      compositionOnly: true,
      include: [
        fileURLToPath(
          new URL("./resources/js/plugins/i18n/locales/**", __vite_injected_original_import_meta_url)
        )
      ]
    }),
    svgLoader()
  ],
  define: { "process.env": {} },
  resolve: {
    alias: {
      "@core-scss": fileURLToPath(
        new URL("./resources/styles/@core", __vite_injected_original_import_meta_url)
      ),
      "@": fileURLToPath(new URL("./resources/js", __vite_injected_original_import_meta_url)),
      "@themeConfig": fileURLToPath(
        new URL("./themeConfig.js", __vite_injected_original_import_meta_url)
      ),
      "@core": fileURLToPath(new URL("./resources/js/@core", __vite_injected_original_import_meta_url)),
      "@layouts": fileURLToPath(
        new URL("./resources/js/@layouts", __vite_injected_original_import_meta_url)
      ),
      "@images": fileURLToPath(new URL("./resources/images/", __vite_injected_original_import_meta_url)),
      "@styles": fileURLToPath(new URL("./resources/styles/", __vite_injected_original_import_meta_url)),
      "@configured-variables": fileURLToPath(
        new URL("./resources/styles/variables/_template.scss", __vite_injected_original_import_meta_url)
      ),
      "@db": fileURLToPath(
        new URL("./resources/js/plugins/fake-api/handlers/", __vite_injected_original_import_meta_url)
      ),
      "@api-utils": fileURLToPath(
        new URL("./resources/js/plugins/fake-api/utils/", __vite_injected_original_import_meta_url)
      ),
      "@public": fileURLToPath(new URL("./public/", __vite_injected_original_import_meta_url))
    }
  },
  build: {
    chunkSizeWarningLimit: 5e3
  },
  optimizeDeps: {
    exclude: ["vuetify"],
    entries: ["./resources/js/**/*.vue"]
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJEOlxcXFx4YW1wcC04LjIuMTJcXFxcaHRkb2NzXFxcXHNhbG9uX2J1ZGR5XCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJEOlxcXFx4YW1wcC04LjIuMTJcXFxcaHRkb2NzXFxcXHNhbG9uX2J1ZGR5XFxcXHZpdGUuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9EOi94YW1wcC04LjIuMTIvaHRkb2NzL3NhbG9uX2J1ZGR5L3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IFZ1ZUkxOG5QbHVnaW4gZnJvbSBcIkBpbnRsaWZ5L3VucGx1Z2luLXZ1ZS1pMThuL3ZpdGVcIjtcclxuaW1wb3J0IHZ1ZSBmcm9tIFwiQHZpdGVqcy9wbHVnaW4tdnVlXCI7XHJcbmltcG9ydCB2dWVKc3ggZnJvbSBcIkB2aXRlanMvcGx1Z2luLXZ1ZS1qc3hcIjtcclxuaW1wb3J0IGxhcmF2ZWwgZnJvbSBcImxhcmF2ZWwtdml0ZS1wbHVnaW5cIjtcclxuaW1wb3J0IHsgZmlsZVVSTFRvUGF0aCB9IGZyb20gXCJub2RlOnVybFwiO1xyXG5pbXBvcnQgQXV0b0ltcG9ydCBmcm9tIFwidW5wbHVnaW4tYXV0by1pbXBvcnQvdml0ZVwiO1xyXG5pbXBvcnQgQ29tcG9uZW50cyBmcm9tIFwidW5wbHVnaW4tdnVlLWNvbXBvbmVudHMvdml0ZVwiO1xyXG5pbXBvcnQge1xyXG4gIFZ1ZVJvdXRlckF1dG9JbXBvcnRzLFxyXG4gIGdldFBhc2NhbENhc2VSb3V0ZU5hbWUsXHJcbn0gZnJvbSBcInVucGx1Z2luLXZ1ZS1yb3V0ZXJcIjtcclxuaW1wb3J0IFZ1ZVJvdXRlciBmcm9tIFwidW5wbHVnaW4tdnVlLXJvdXRlci92aXRlXCI7XHJcbmltcG9ydCB7IGRlZmluZUNvbmZpZyB9IGZyb20gXCJ2aXRlXCI7XHJcbmltcG9ydCBMYXlvdXRzIGZyb20gXCJ2aXRlLXBsdWdpbi12dWUtbGF5b3V0c1wiO1xyXG5pbXBvcnQgdnVldGlmeSBmcm9tIFwidml0ZS1wbHVnaW4tdnVldGlmeVwiO1xyXG5pbXBvcnQgc3ZnTG9hZGVyIGZyb20gXCJ2aXRlLXN2Zy1sb2FkZXJcIjtcclxuXHJcbi8vIGh0dHBzOi8vdml0ZWpzLmRldi9jb25maWcvXHJcbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XHJcbiAgYmFzZTogXCIvc2VsZmRpbmVfYWkvXCIsXHJcbiAgcGx1Z2luczogW1xyXG4gICAgLy8gRG9jczogaHR0cHM6Ly9naXRodWIuY29tL3Bvc3ZhL3VucGx1Z2luLXZ1ZS1yb3V0ZXJcclxuICAgIC8vIFx1MjEzOVx1RkUwRiBUaGlzIHBsdWdpbiBzaG91bGQgYmUgcGxhY2VkIGJlZm9yZSB2dWUgcGx1Z2luXHJcbiAgICBWdWVSb3V0ZXIoe1xyXG4gICAgICBnZXRSb3V0ZU5hbWU6IChyb3V0ZU5vZGUpID0+IHtcclxuICAgICAgICAvLyBDb252ZXJ0IHBhc2NhbCBjYXNlIHRvIGtlYmFiIGNhc2VcclxuICAgICAgICByZXR1cm4gZ2V0UGFzY2FsQ2FzZVJvdXRlTmFtZShyb3V0ZU5vZGUpXHJcbiAgICAgICAgICAucmVwbGFjZSgvKFthLXpcXGRdKShbQS1aXSkvZywgXCIkMS0kMlwiKVxyXG4gICAgICAgICAgLnRvTG93ZXJDYXNlKCk7XHJcbiAgICAgIH0sXHJcblxyXG4gICAgICByb3V0ZXNGb2xkZXI6IFwicmVzb3VyY2VzL2pzL3BhZ2VzXCIsXHJcbiAgICB9KSxcclxuICAgIHZ1ZSh7XHJcbiAgICAgIHRlbXBsYXRlOiB7XHJcbiAgICAgICAgY29tcGlsZXJPcHRpb25zOiB7XHJcbiAgICAgICAgICBpc0N1c3RvbUVsZW1lbnQ6ICh0YWcpID0+XHJcbiAgICAgICAgICAgIHRhZyA9PT0gXCJzd2lwZXItY29udGFpbmVyXCIgfHwgdGFnID09PSBcInN3aXBlci1zbGlkZVwiLFxyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIHRyYW5zZm9ybUFzc2V0VXJsczoge1xyXG4gICAgICAgICAgYmFzZTogbnVsbCxcclxuICAgICAgICAgIGluY2x1ZGVBYnNvbHV0ZTogZmFsc2UsXHJcbiAgICAgICAgfSxcclxuICAgICAgfSxcclxuICAgIH0pLFxyXG4gICAgbGFyYXZlbCh7XHJcbiAgICAgIGlucHV0OiBbXCJyZXNvdXJjZXMvanMvbWFpbi5qc1wiXSxcclxuICAgICAgcmVmcmVzaDogdHJ1ZSxcclxuICAgICAgaG90RmlsZTogXCJwdWJsaWMvaG90XCIsXHJcbiAgICB9KSxcclxuICAgIHZ1ZUpzeCgpLCAvLyBEb2NzOiBodHRwczovL2dpdGh1Yi5jb20vdnVldGlmeWpzL3Z1ZXRpZnktbG9hZGVyL3RyZWUvbWFzdGVyL3BhY2thZ2VzL3ZpdGUtcGx1Z2luXHJcbiAgICB2dWV0aWZ5KHtcclxuICAgICAgc3R5bGVzOiB7XHJcbiAgICAgICAgY29uZmlnRmlsZTogXCJyZXNvdXJjZXMvc3R5bGVzL3ZhcmlhYmxlcy9fdnVldGlmeS5zY3NzXCIsXHJcbiAgICAgIH0sXHJcbiAgICB9KSwgLy8gRG9jczogaHR0cHM6Ly9naXRodWIuY29tL2pvaG5jYW1waW9uanIvdml0ZS1wbHVnaW4tdnVlLWxheW91dHMjdml0ZS1wbHVnaW4tdnVlLWxheW91dHNcclxuICAgIExheW91dHMoe1xyXG4gICAgICBsYXlvdXRzRGlyczogXCIuL3Jlc291cmNlcy9qcy9sYXlvdXRzL1wiLFxyXG4gICAgfSksIC8vIERvY3M6IGh0dHBzOi8vZ2l0aHViLmNvbS9hbnRmdS91bnBsdWdpbi12dWUtY29tcG9uZW50cyN1bnBsdWdpbi12dWUtY29tcG9uZW50c1xyXG4gICAgQ29tcG9uZW50cyh7XHJcbiAgICAgIGRpcnM6IFtcclxuICAgICAgICBcInJlc291cmNlcy9qcy9AY29yZS9jb21wb25lbnRzXCIsXHJcbiAgICAgICAgXCJyZXNvdXJjZXMvanMvdmlld3MvZGVtb3NcIixcclxuICAgICAgICBcInJlc291cmNlcy9qcy9jb21wb25lbnRzXCIsXHJcbiAgICAgIF0sXHJcbiAgICAgIGR0czogdHJ1ZSxcclxuICAgICAgcmVzb2x2ZXJzOiBbXHJcbiAgICAgICAgKGNvbXBvbmVudE5hbWUpID0+IHtcclxuICAgICAgICAgIC8vIEF1dG8gaW1wb3J0IGBWdWVBcGV4Q2hhcnRzYFxyXG4gICAgICAgICAgaWYgKGNvbXBvbmVudE5hbWUgPT09IFwiVnVlQXBleENoYXJ0c1wiKVxyXG4gICAgICAgICAgICByZXR1cm4ge1xyXG4gICAgICAgICAgICAgIG5hbWU6IFwiZGVmYXVsdFwiLFxyXG4gICAgICAgICAgICAgIGZyb206IFwidnVlMy1hcGV4Y2hhcnRzXCIsXHJcbiAgICAgICAgICAgICAgYXM6IFwiVnVlQXBleENoYXJ0c1wiLFxyXG4gICAgICAgICAgICB9O1xyXG4gICAgICAgIH0sXHJcbiAgICAgIF0sXHJcbiAgICB9KSwgLy8gRG9jczogaHR0cHM6Ly9naXRodWIuY29tL2FudGZ1L3VucGx1Z2luLWF1dG8taW1wb3J0I3VucGx1Z2luLWF1dG8taW1wb3J0XHJcbiAgICBBdXRvSW1wb3J0KHtcclxuICAgICAgaW1wb3J0czogW1xyXG4gICAgICAgIFwidnVlXCIsXHJcbiAgICAgICAgVnVlUm91dGVyQXV0b0ltcG9ydHMsXHJcbiAgICAgICAgXCJAdnVldXNlL2NvcmVcIixcclxuICAgICAgICBcIkB2dWV1c2UvbWF0aFwiLFxyXG4gICAgICAgIFwidnVlLWkxOG5cIixcclxuICAgICAgICBcInBpbmlhXCIsXHJcbiAgICAgIF0sXHJcbiAgICAgIGRpcnM6IFtcclxuICAgICAgICBcIi4vcmVzb3VyY2VzL2pzL0Bjb3JlL3V0aWxzXCIsXHJcbiAgICAgICAgXCIuL3Jlc291cmNlcy9qcy9AY29yZS9jb21wb3NhYmxlL1wiLFxyXG4gICAgICAgIFwiLi9yZXNvdXJjZXMvanMvY29tcG9zYWJsZXMvXCIsXHJcbiAgICAgICAgXCIuL3Jlc291cmNlcy9qcy91dGlscy9cIixcclxuICAgICAgICBcIi4vcmVzb3VyY2VzL2pzL3BsdWdpbnMvKi9jb21wb3NhYmxlcy8qXCIsXHJcbiAgICAgIF0sXHJcbiAgICAgIHZ1ZVRlbXBsYXRlOiB0cnVlLFxyXG5cclxuICAgICAgLy8gXHUyMTM5XHVGRTBGIERpc2FibGVkIHRvIGF2b2lkIGNvbmZ1c2lvbiAmIGFjY2lkZW50YWwgdXNhZ2VcclxuICAgICAgaWdub3JlOiBbXCJ1c2VDb29raWVzXCIsIFwidXNlU3RvcmFnZVwiXSxcclxuICAgICAgZXNsaW50cmM6IHtcclxuICAgICAgICBlbmFibGVkOiB0cnVlLFxyXG4gICAgICAgIGZpbGVwYXRoOiBcIi4vLmVzbGludHJjLWF1dG8taW1wb3J0Lmpzb25cIixcclxuICAgICAgfSxcclxuICAgIH0pLFxyXG4gICAgVnVlSTE4blBsdWdpbih7XHJcbiAgICAgIHJ1bnRpbWVPbmx5OiB0cnVlLFxyXG4gICAgICBjb21wb3NpdGlvbk9ubHk6IHRydWUsXHJcbiAgICAgIGluY2x1ZGU6IFtcclxuICAgICAgICBmaWxlVVJMVG9QYXRoKFxyXG4gICAgICAgICAgbmV3IFVSTChcIi4vcmVzb3VyY2VzL2pzL3BsdWdpbnMvaTE4bi9sb2NhbGVzLyoqXCIsIGltcG9ydC5tZXRhLnVybClcclxuICAgICAgICApLFxyXG4gICAgICBdLFxyXG4gICAgfSksXHJcbiAgICBzdmdMb2FkZXIoKSxcclxuICBdLFxyXG4gIGRlZmluZTogeyBcInByb2Nlc3MuZW52XCI6IHt9IH0sXHJcbiAgcmVzb2x2ZToge1xyXG4gICAgYWxpYXM6IHtcclxuICAgICAgXCJAY29yZS1zY3NzXCI6IGZpbGVVUkxUb1BhdGgoXHJcbiAgICAgICAgbmV3IFVSTChcIi4vcmVzb3VyY2VzL3N0eWxlcy9AY29yZVwiLCBpbXBvcnQubWV0YS51cmwpXHJcbiAgICAgICksXHJcbiAgICAgIFwiQFwiOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoXCIuL3Jlc291cmNlcy9qc1wiLCBpbXBvcnQubWV0YS51cmwpKSxcclxuICAgICAgXCJAdGhlbWVDb25maWdcIjogZmlsZVVSTFRvUGF0aChcclxuICAgICAgICBuZXcgVVJMKFwiLi90aGVtZUNvbmZpZy5qc1wiLCBpbXBvcnQubWV0YS51cmwpXHJcbiAgICAgICksXHJcbiAgICAgIFwiQGNvcmVcIjogZmlsZVVSTFRvUGF0aChuZXcgVVJMKFwiLi9yZXNvdXJjZXMvanMvQGNvcmVcIiwgaW1wb3J0Lm1ldGEudXJsKSksXHJcbiAgICAgIFwiQGxheW91dHNcIjogZmlsZVVSTFRvUGF0aChcclxuICAgICAgICBuZXcgVVJMKFwiLi9yZXNvdXJjZXMvanMvQGxheW91dHNcIiwgaW1wb3J0Lm1ldGEudXJsKVxyXG4gICAgICApLFxyXG4gICAgICBcIkBpbWFnZXNcIjogZmlsZVVSTFRvUGF0aChuZXcgVVJMKFwiLi9yZXNvdXJjZXMvaW1hZ2VzL1wiLCBpbXBvcnQubWV0YS51cmwpKSxcclxuICAgICAgXCJAc3R5bGVzXCI6IGZpbGVVUkxUb1BhdGgobmV3IFVSTChcIi4vcmVzb3VyY2VzL3N0eWxlcy9cIiwgaW1wb3J0Lm1ldGEudXJsKSksXHJcbiAgICAgIFwiQGNvbmZpZ3VyZWQtdmFyaWFibGVzXCI6IGZpbGVVUkxUb1BhdGgoXHJcbiAgICAgICAgbmV3IFVSTChcIi4vcmVzb3VyY2VzL3N0eWxlcy92YXJpYWJsZXMvX3RlbXBsYXRlLnNjc3NcIiwgaW1wb3J0Lm1ldGEudXJsKVxyXG4gICAgICApLFxyXG4gICAgICBcIkBkYlwiOiBmaWxlVVJMVG9QYXRoKFxyXG4gICAgICAgIG5ldyBVUkwoXCIuL3Jlc291cmNlcy9qcy9wbHVnaW5zL2Zha2UtYXBpL2hhbmRsZXJzL1wiLCBpbXBvcnQubWV0YS51cmwpXHJcbiAgICAgICksXHJcbiAgICAgIFwiQGFwaS11dGlsc1wiOiBmaWxlVVJMVG9QYXRoKFxyXG4gICAgICAgIG5ldyBVUkwoXCIuL3Jlc291cmNlcy9qcy9wbHVnaW5zL2Zha2UtYXBpL3V0aWxzL1wiLCBpbXBvcnQubWV0YS51cmwpXHJcbiAgICAgICksXHJcbiAgICAgIFwiQHB1YmxpY1wiOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoXCIuL3B1YmxpYy9cIiwgaW1wb3J0Lm1ldGEudXJsKSksXHJcbiAgICB9LFxyXG4gIH0sXHJcbiAgYnVpbGQ6IHtcclxuICAgIGNodW5rU2l6ZVdhcm5pbmdMaW1pdDogNTAwMCxcclxuICB9LFxyXG4gIG9wdGltaXplRGVwczoge1xyXG4gICAgZXhjbHVkZTogW1widnVldGlmeVwiXSxcclxuICAgIGVudHJpZXM6IFtcIi4vcmVzb3VyY2VzL2pzLyoqLyoudnVlXCJdLFxyXG4gIH0sXHJcbn0pO1xyXG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQWdTLE9BQU8sbUJBQW1CO0FBQzFULE9BQU8sU0FBUztBQUNoQixPQUFPLFlBQVk7QUFDbkIsT0FBTyxhQUFhO0FBQ3BCLFNBQVMscUJBQXFCO0FBQzlCLE9BQU8sZ0JBQWdCO0FBQ3ZCLE9BQU8sZ0JBQWdCO0FBQ3ZCO0FBQUEsRUFDRTtBQUFBLEVBQ0E7QUFBQSxPQUNLO0FBQ1AsT0FBTyxlQUFlO0FBQ3RCLFNBQVMsb0JBQW9CO0FBQzdCLE9BQU8sYUFBYTtBQUNwQixPQUFPLGFBQWE7QUFDcEIsT0FBTyxlQUFlO0FBZjZKLElBQU0sMkNBQTJDO0FBa0JwTyxJQUFPLHNCQUFRLGFBQWE7QUFBQSxFQUMxQixNQUFNO0FBQUEsRUFDTixTQUFTO0FBQUE7QUFBQTtBQUFBLElBR1AsVUFBVTtBQUFBLE1BQ1IsY0FBYyxDQUFDLGNBQWM7QUFFM0IsZUFBTyx1QkFBdUIsU0FBUyxFQUNwQyxRQUFRLHFCQUFxQixPQUFPLEVBQ3BDLFlBQVk7QUFBQSxNQUNqQjtBQUFBLE1BRUEsY0FBYztBQUFBLElBQ2hCLENBQUM7QUFBQSxJQUNELElBQUk7QUFBQSxNQUNGLFVBQVU7QUFBQSxRQUNSLGlCQUFpQjtBQUFBLFVBQ2YsaUJBQWlCLENBQUMsUUFDaEIsUUFBUSxzQkFBc0IsUUFBUTtBQUFBLFFBQzFDO0FBQUEsUUFFQSxvQkFBb0I7QUFBQSxVQUNsQixNQUFNO0FBQUEsVUFD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
