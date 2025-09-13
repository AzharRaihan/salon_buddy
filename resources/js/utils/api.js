// import { ofetch } from 'ofetch'

// export const $api = ofetch.create({
//   baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
//   async onRequest({ options }) {
//     // Check for customer token first (for customer endpoints)
//     const customerAccessToken = useCookie('customerAccessToken').value
//     const accessToken = useCookie('accessToken').value
    
//     // Use customer token if available, otherwise use regular admin token
//     const token = customerAccessToken || accessToken
    
//     if (token) {
//       options.headers = options.headers || {}
//       options.headers.Authorization = `Bearer ${token}`
//     }
//   },
// })



import { ofetch } from "ofetch";
import { useRouter } from "vue-router";
import { toast } from "vue3-toastify";

// Create a common headers function to reuse
// const getCommonHeaders = () => {
//   const headers = new Headers();
//   const accessToken = useCookie("accessToken").value;
//   if (accessToken) headers.append("Authorization", `Bearer ${accessToken}`);
//   return headers;
// };

// Raw fetch function for internal use (not subject to day closed check)
// const rawApiFetch = ofetch.create({
//   baseURL: import.meta.env.VITE_API_BASE_URL || "/api",
//   headers: getCommonHeaders(),
// });

// Main API fetch function with day closed check
export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "/api",
  async onRequest({ options }) {
    const accessToken = useCookie("accessToken").value;
    if (accessToken)
      options.headers.append("Authorization", `Bearer ${accessToken}`);
  },
});