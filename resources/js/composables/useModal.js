import { computed, nextTick, ref } from "vue";

export function useModal() {
  // Modal states
  const modals = ref(new Map());
  const modalStack = ref([]);
  const isAnyModalOpen = computed(() => modalStack.value.length > 0);

  // Modal configuration
  const defaultConfig = {
    closable: true,
    backdrop: true,
    keyboard: true,
    size: "medium",
    animation: "fade",
    persistent: false,
    autoFocus: true,
  };

  // Register a modal
  const registerModal = (id, config = {}) => {
    modals.value.set(id, {
      id,
      isOpen: false,
      config: { ...defaultConfig, ...config },
      data: null,
      resolve: null,
      reject: null,
    });
  };

  // Open modal
  const openModal = async (id, data = null, config = {}) => {
    if (!modals.value.has(id)) {
      registerModal(id, config);
    }

    const modal = modals.value.get(id);

    // Close other modals if not stackable
    if (!config.stackable) {
      await closeAllModals();
    }

    // Update modal state
    modal.isOpen = true;
    modal.data = data;
    modal.config = { ...modal.config, ...config };

    // Add to stack
    modalStack.value.push(id);

    // Handle body scroll lock
    if (modalStack.value.length === 1) {
      document.body.style.overflow = "hidden";
    }

    // Auto focus
    if (modal.config.autoFocus) {
      await nextTick();
      const modalElement = document.querySelector(`[data-modal="${id}"]`);
      if (modalElement) {
        const focusableElement = modalElement.querySelector(
          "input, button, textarea, select"
        );
        if (focusableElement) {
          focusableElement.focus();
        }
      }
    }

    // Return a promise that resolves when modal closes
    return new Promise((resolve, reject) => {
      modal.resolve = resolve;
      modal.reject = reject;
    });
  };

  // Close modal
  const closeModal = (id, result = null) => {
    if (!modals.value.has(id)) return;

    const modal = modals.value.get(id);
    if (!modal.isOpen) return;

    // Update state
    modal.isOpen = false;
    modal.data = null;

    // Remove from stack
    const index = modalStack.value.indexOf(id);
    if (index > -1) {
      modalStack.value.splice(index, 1);
    }

    // Restore body scroll
    if (modalStack.value.length === 0) {
      document.body.style.overflow = "";
    }

    // Resolve promise
    if (modal.resolve) {
      modal.resolve(result);
      modal.resolve = null;
      modal.reject = null;
    }
  };

  // Close all modals
  const closeAllModals = async () => {
    const openModals = [...modalStack.value];
    for (const modalId of openModals) {
      closeModal(modalId);
    }
    await nextTick();
  };

  // Check if modal is open
  const isModalOpen = (id) => {
    const modal = modals.value.get(id);
    return modal ? modal.isOpen : false;
  };

  // Get modal data
  const getModalData = (id) => {
    const modal = modals.value.get(id);
    return modal ? modal.data : null;
  };

  // Get modal config
  const getModalConfig = (id) => {
    const modal = modals.value.get(id);
    return modal ? modal.config : null;
  };

  // Handle keyboard events
  const handleKeydown = (event) => {
    if (modalStack.value.length === 0) return;

    const currentModalId = modalStack.value[modalStack.value.length - 1];
    const modal = modals.value.get(currentModalId);

    if (!modal || !modal.config.keyboard) return;

    // Escape key
    if (event.key === "Escape") {
      event.preventDefault();
      if (modal.config.closable) {
        closeModal(currentModalId);
      }
    }

    // Tab key - trap focus within modal
    if (event.key === "Tab") {
      const modalElement = document.querySelector(
        `[data-modal="${currentModalId}"]`
      );
      if (modalElement) {
        const focusableElements = modalElement.querySelectorAll(
          'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length > 0) {
          const firstElement = focusableElements[0];
          const lastElement = focusableElements[focusableElements.length - 1];

          if (event.shiftKey) {
            if (document.activeElement === firstElement) {
              event.preventDefault();
              lastElement.focus();
            }
          } else {
            if (document.activeElement === lastElement) {
              event.preventDefault();
              firstElement.focus();
            }
          }
        }
      }
    }
  };

  // Setup keyboard event listeners
  if (typeof window !== "undefined") {
    document.addEventListener("keydown", handleKeydown);
  }

  // Cleanup function
  const cleanup = () => {
    if (typeof window !== "undefined") {
      document.removeEventListener("keydown", handleKeydown);
      document.body.style.overflow = "";
    }
  };

  return {
    // State
    modals: computed(() => modals.value),
    modalStack: computed(() => modalStack.value),
    isAnyModalOpen,

    // Methods
    registerModal,
    openModal,
    closeModal,
    closeAllModals,
    isModalOpen,
    getModalData,
    getModalConfig,
    cleanup,
  };
}

// Global modal instance for app-wide usage
let globalModalInstance = null;

export function useGlobalModal() {
  if (!globalModalInstance) {
    globalModalInstance = useModal();
  }
  return globalModalInstance;
}
