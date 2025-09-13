import { $api } from "@/utils/api";
import { setupLayouts } from "virtual:generated-layouts";
import { createRouter, createWebHistory } from "vue-router/auto";
import { toast } from "vue3-toastify";
import {
  getUserData,
  getAccessToken,
} from '@/utils/storage';
import { useCookie } from '@/@core/composable/useCookie';
import { hasAnyPermission } from '@/utils/permissions';
import { getRoutePermissions, isPermissionExcludedRoute } from '@/utils/routePermissions';

function recursiveLayouts(route) {
  if (route.children) {
    for (let i = 0; i < route.children.length; i++)
      route.children[i] = recursiveLayouts(route.children[i]);

    return route;
  }

  return setupLayouts([route])[0];
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to) {
    if (to.hash) return { el: to.hash, behavior: "smooth", top: 60 };

    return { top: 0 };
  },
  extendRoutes: (pages) => [
    ...[...pages].map((route) => recursiveLayouts(route)),
  ],
});

// Updated navigation guard
router.beforeEach(async (to, from, next) => {
  const accessToken = getAccessToken();
  const userData = getUserData();
  const outletId = useCookie("sd_ai_outletId").value;

  const publicPages = ["/login", "/register", "/forgot-password"];
  const isPublicPage =
    publicPages.includes(to.path) || to.path.startsWith("/forgot-password/");

  // 1️⃣ Must be logged in
  if (!accessToken) {
    return isPublicPage ? next() : next({ path: "/login" });
  }

  // 2️⃣ If logged in and visiting public page -> redirect to home
  if (isPublicPage) {
    return next({ path: "/" });
  }

  // 3️⃣ Must finish first login security question
  if (
    userData?.is_first_login === 0 &&
    to.path !== "/profile/security-question"
  ) {
    return next({ path: "/profile/security-question" });
  }

  const $excludedRoutes = [
    "/",
    "/profile/*",
    "/select-outlet",
    "/outlets/*",
    "/outlets",
    "/items/*",
    "/settings/*",
    "/register/*",
    "/role/*",
    "/role",
    "/user/*",
    "/user",
  ];

  // helper function to check if path matches wildcard pattern
  function isExcludedRoute(path) {
    return $excludedRoutes.some(pattern => {
      if (pattern.includes("*")) {
        const base = pattern.replace("*", "");
        return path.startsWith(base);
      }
      return path == pattern;
    });
  }

  // 4️⃣ Must select outlet
  if (!outletId && !isExcludedRoute(to.path)) {
    toast(`Please enter an outlet first.`, {
      type: "error",
    });
    return next({ path: "/select-outlet" });
  }

  // 5️⃣ POS role: only redirect after login
  if (
    userData?.role === 2 &&
    from.path === "/login" &&
    to.path !== "/pos"
  ) {
    return next({ path: "/pos" });
  }

  // 6️⃣ Check if day is closed
  if (
    to.path !== "/dashboard" &&
    to.path !== "/login" &&
    to.path !== "/select-outlet" &&
    to.path !== "/profile/security-question" &&
    !to.path.startsWith("/profile/") &&
    !to.path.startsWith("/auth/")
  ) {
    try {
      const dayStatusResponse = await $api("/stock/day-status");
      if (dayStatusResponse.data.is_closed) {
        toast(dayStatusResponse.data.message, {
          type: "error",
          position: "top-right",
          autoClose: 5000,
        });
        return next({ path: "/dashboard" });
      }
    } catch (error) {
      console.error("Error checking day status:", error);
      // optional: handle API errors gracefully
    }
  }

  // 7️⃣ Check daily opening stock (skip on daily-opening-stock page)
  if (
    !to.path.startsWith("/daily-opening-stock") &&
    !to.path.startsWith("/register") &&
    to.path !== "/login" &&
    !isExcludedRoute(to.path)
  ) {
    try {
      const response = await $api("/daily-opening-stock/check-today");
      if (!response.data.exists) {
        return next({ path: "/daily-opening-stock" });
      }
    } catch (error) {
      console.error("Error checking daily opening stock:", error);
    }
  }

  // 8️⃣ Check register status before accessing POS
  if (
    to.path.startsWith("/pos") &&
    to.path !== "/login" &&
    to.path !== "/dashboard" &&
    to.path !== "/select-outlet" &&
    to.path !== "/profile/security-question" &&
    to.path !== "/pos-registers" &&
    !to.path.startsWith("/profile/") 
  ) {
    try {
      const response = await $api("/register/status");
      if (!response.data.is_open) {
        toast("Please open register first to access POS.", {
          type: "error",
          position: "top-right",
          autoClose: 5000,
        });
        return next({ path: "/pos-registers" });
      }
    } catch (error) {
      console.error("Error checking register status:", error);
    }
  }

  // 9️⃣ Check route permissions
  if (!isPermissionExcludedRoute(to.path)) {
    const requiredPermissions = getRoutePermissions(to);
    
    if (requiredPermissions && requiredPermissions.length > 0) {
      const hasPermission = hasAnyPermission(requiredPermissions);      
      if (!hasPermission) {
        toast("You don't have permission to access this page.", {
          type: "error",
          position: "top-right",
          autoClose: 5000,
        });
        return next({ path: "/dashboard" });
      }
    }
  }

  // ✅ Finally, allow navigation
  next();
});

export { router };
export default function (app) {
  app.use(router);
}
