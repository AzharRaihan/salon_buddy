import { defineStore } from "pinia";

export const useForgotPasswordStore = defineStore("forgotPassword", {
  state: () => ({
    email: "",
  }),
  actions: {
    setEmail(email) {
      this.email = email;
    },
    clearEmail() {
      this.email = "";
    },
    hasEmail() {
      return !!this.email;
    },
  },
});
