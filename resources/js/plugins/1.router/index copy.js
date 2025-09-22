import { setupLayouts } from "virtual:generated-layouts";
import { createRouter, createWebHistory } from "vue-router/auto";
import { toast } from 'vue3-toastify';
// import { useCookie } from '@vueuse/core'; // Add missing import
import Hashids from "hashids";

// Initialize hashids for encryption/decryption
const salt = import.meta.env.VITE_HASHIDS_SALT;
const MIN_LEN = 8;
const hashids = new Hashids(salt, MIN_LEN);

// encrypt ID
const encryptID = (id) => {
  if (!id) return "";
  return hashids.encode(id);
};

// decrypt ID
const decryptID = (id) => {
  if (!id) return "";
  const decoded = hashids.decode(id);
  return decoded.length ? decoded[0] : null;
};


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

// helper to encrypt query
function encryptQuery(query) {
  const newQuery = { ...query };
  if (newQuery.id) {
    newQuery.id = encryptID(newQuery.id);
  }
  return newQuery;
}

const originalPush = router.push;
router.push = function (location, ...args) {
  if (location.query) {
    location.query = encryptQuery(location.query);
  }
  return originalPush.call(this, location, ...args);
};

const originalReplace = router.replace;
router.replace = function (location, ...args) {
  if (location.query) {
    location.query = encryptQuery(location.query);
  }
  return originalReplace.call(this, location, ...args);
};

// Updated navigation guard
router.beforeEach((to, from, next) => {
  // Admin/Staff Authentication cookies
  const accessToken = useCookie("accessToken").value ?? null;
  const userData = useCookie("userData").value;
  const branch_info = useCookie("branch_info").value || 0;
  
  // Customer Authentication cookies (separate from admin)
  const customerAccessToken = useCookie("customerAccessToken").value;
  const customerData = useCookie("customerData").value;

  

  // 🔑 Step A: Decrypt query params if they have `id`
  if (to.query?.id) {
    const realId = decryptID(to.query.id);

    if (!realId) {
      // If decryption fails → prevent navigation
      toast("Invalid route parameter", { type: "error" });
      return next({ path: "/dashboard" });
    }

    // Replace encrypted with plain (so your components always get real ID)
    to.query.id = realId;
  }

  // Define public frontend pages that don't require authentication
  const publicFrontendPages = [
    "/", 
    "/login", 
    "/register", 
    "/forgot-password",
    "/aboutus",
    "/contact-us",
    "/service",
    "/gallery",
    "/product",
    "/package",
    "/team-members",
    "/team-member-details/:id",
    "/faq",
    "/shopping-cart",
    "/appointment-service",
    "/appointment-service/:id",
    "/booking/success",
    "/not-found"
  ];

  // Define customer-only pages that require customer authentication
  const customerOnlyPages = [
    "/customer/dashboard",
    "/customer/service-order",
    "/customer/product-order", 
    "/customer/transaction-history",
    "/customer/package-order",
    "/customer/package-details",
    "/customer/profile-setting"
  ];

  // Define backend public pages
  const publicBackendPages = ["/login", "/register", "/forgot-password"];
  
  // Check if current route is a public page 
  const isPublicFrontendPage = publicFrontendPages.includes(to.path);

  // Check if current route is a customer-only page
  const isCustomerOnlyPage = customerOnlyPages.includes(to.path);

  // Check if current route is a public backend page
  const isPublicBackendPage = publicBackendPages.includes(to.path) || 
                              to.path.startsWith("/forgot-password/");

  // Handle customer authentication for customer-only pages
  if (isCustomerOnlyPage) {
    if (!customerAccessToken || !customerData) {
      toast("Please login as a customer to access this page", {
        type: "warning",
        position: "top-right",
        autoClose: 3000
      });
      return next({ path: "/login" });
    }
    return next();
  }

  // If it's a public frontend page, allow access without authentication
  if (isPublicFrontendPage) {
    console.log('Public Frontend Page', isPublicFrontendPage)
    return next();
  }

  // Handle backend authentication logic (Admin/Staff)
  if (accessToken != null && accessToken != undefined && accessToken != '' && accessToken != 'null' && !accessToken) {
    return isPublicBackendPage ? next() : next({ path: "/login" });
  }

  if (isPublicBackendPage) {  
    return next({ path: "/dashboard" });
  }

  // Admin/Staff specific checks
  if (
    userData?.is_first_login === 0 &&
    to.path !== "/profile/security-question"
  ) {
    toast("This is your first login, Please setup your security question first!, Until you setup your security question you can't access any page, You should remember your security question and answer for future password reset.", {
      type: "warning",
      position: "top-right",
      autoClose: 3000
    });
    return next({ path: "/profile/security-question" });
  }

  // URL Protection if Branch is not set at cookie (Admin/Staff only)
  const branchPaths = ["/purchase/create", "/purchase", "/stock/stock", "/stock/alert-stock", "/sale", "/customer-receive/create", "/customer-receive", "/supplier-payment/create", "/supplier-payment", "/expense/create", "/expense", "/salary/create", "/salary", "/booking/calendar", "/booking/create", "/booking", "/promotion/create", "/promotion", "/dashboard", "/pos"];
  
  if (branch_info === 0 && branchPaths.includes(to.path)) {
    toast("Please select your branch first", {
      type: "warning",
      position: "top-right",
      autoClose: 3000
    });
    return next({ path: "/branch" });
  }

  next();
});

export { router };
export default function (app) {
  app.use(router);
}
