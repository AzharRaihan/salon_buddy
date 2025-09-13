import { defineStore } from "pinia";

export const useRegisterStore = defineStore("register", {
  state: () => ({
    counterName: "Counter One",
    timeRange: {
      start: "10 May 2025 04:05:37 PM",
      end: "10 May 2025 10:30:00 PM",
    },
    paymentMethods: [
      {
        name: "Paypal",
        transactions: [
          { name: "Opening Balance (+)", amount: 0.0 },
          { name: "Purchase (-)", amount: 0.0 },
          { name: "Sale (+)", amount: 0.0 },
          { name: "Due Receive (+)", amount: 0.0 },
          { name: "Due Payment (-)", amount: 0.0 },
          { name: "Expense (-)", amount: 0.0 },
          { name: "Refund Balance (-)", amount: 0.0 },
          { name: "Closing Balance", amount: 0.0 },
        ],
      },
      {
        name: "Cash",
        transactions: [
          { name: "Opening Balance (+)", amount: 0.0 },
          { name: "Purchase (-)", amount: 0.0 },
          { name: "Sale (+)", amount: 0.0 },
          { name: "Due Receive (+)", amount: 0.0 },
          { name: "Due Payment (-)", amount: 0.0 },
          { name: "Expense (-)", amount: 0.0 },
          { name: "Refund Balance (-)", amount: 0.0 },
          { name: "Closing Balance", amount: 0.0 },
        ],
      },
    ],
  }),

  getters: {
    getFormattedTimeRange: (state) => {
      return `${state.timeRange.start} to ${state.timeRange.end}`;
    },
  },

  actions: {
    closeRegister() {
      // Logic to close register
      console.log("Closing register");
    },

    updatePaymentMethod(methodName, transactionName, amount) {
      const methodIndex = this.paymentMethods.findIndex(
        (method) => method.name === methodName
      );
      if (methodIndex !== -1) {
        const transactionIndex = this.paymentMethods[
          methodIndex
        ].transactions.findIndex(
          (transaction) => transaction.name === transactionName
        );
        if (transactionIndex !== -1) {
          this.paymentMethods[methodIndex].transactions[
            transactionIndex
          ].amount = amount;
        }
      }
    },
  },
});
