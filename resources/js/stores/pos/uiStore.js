import { defineStore } from "pinia";
export const useUIStore = defineStore("ui", {
  state: () => ({
    modals: {
      itemEdit: false,
      note: false,
      discount: false,
      waiter: false,
      customer: false,
      orderType: false,
      draftSales: false,
      runningOrders: false,
      tips: false,
      charge: false,
      date: false,
      details: false,
    },
    activeItem: null,
  }),

  actions: {
    showModal(modalName) {
      if (this.modals.hasOwnProperty(modalName)) {
        // Hide all other modals first
        Object.keys(this.modals).forEach((key) => {
          if (key !== modalName) {
            this.modals[key] = false;
          }
        });

        // Toggle the requested modal (hide if already showing, show if hidden)
        this.modals[modalName] = !this.modals[modalName];
      }
    },

    hideModal(modalName) {
      if (this.modals.hasOwnProperty(modalName)) {
        this.modals[modalName] = false;
      }
    },

    hideAllModals() {
      Object.keys(this.modals).forEach((key) => {
        this.modals[key] = false;
      });
    },

    setActiveItem(item) {
      this.activeItem = item;
    },
  },
});
