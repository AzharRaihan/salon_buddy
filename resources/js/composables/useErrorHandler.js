import { computed, ref } from "vue";
import { toast } from "vue3-toastify";

export function useErrorHandler() {
  // Error state
  const errors = ref(new Map());
  const isRetrying = ref(false);

  // Computed
  const hasErrors = computed(() => errors.value.size > 0);
  const errorList = computed(() => Array.from(errors.value.values()));

  // Error types
  const ERROR_TYPES = {
    NETWORK: "network",
    API: "api",
    VALIDATION: "validation",
    AUTHENTICATION: "authentication",
    PERMISSION: "permission",
    NOT_FOUND: "not_found",
    SERVER: "server",
    CLIENT: "client",
  };

  // Error severity levels
  const ERROR_SEVERITY = {
    LOW: "low", // Minor issues, user can continue
    MEDIUM: "medium", // Significant issues, some features affected
    HIGH: "high", // Critical issues, major functionality broken
    CRITICAL: "critical", // System-wide issues, app unusable
  };

  // Add error
  const addError = (key, error, options = {}) => {
    const errorObj = {
      id: key,
      message: error.message || error,
      type: options.type || ERROR_TYPES.CLIENT,
      severity: options.severity || ERROR_SEVERITY.MEDIUM,
      timestamp: Date.now(),
      retryCount: 0,
      maxRetries: options.maxRetries || 3,
      retryable: options.retryable !== false,
      context: options.context || {},
      originalError: error,
    };

    errors.value.set(key, errorObj);

    // Auto-show toast for medium/high severity errors
    if (errorObj.severity !== ERROR_SEVERITY.LOW) {
      showErrorToast(errorObj);
    }

    // Log error for debugging
    console.error(`[${errorObj.type.toUpperCase()}] ${errorObj.message}`, {
      error: errorObj.originalError,
      context: errorObj.context,
    });
  };

  // Handle API errors specifically
  const handleApiError = (key, error, context = {}) => {
    let errorType = ERROR_TYPES.API;
    let severity = ERROR_SEVERITY.MEDIUM;
    let message = "An unexpected error occurred";

    // Categorize based on status code
    if (error.response) {
      const status = error.response.status;

      if (status === 401) {
        errorType = ERROR_TYPES.AUTHENTICATION;
        message = "Authentication required. Please login again.";
        severity = ERROR_SEVERITY.HIGH;
      } else if (status === 403) {
        errorType = ERROR_TYPES.PERMISSION;
        message = "Access denied. You don't have permission for this action.";
        severity = ERROR_SEVERITY.MEDIUM;
      } else if (status === 404) {
        errorType = ERROR_TYPES.NOT_FOUND;
        message = "Requested resource not found.";
        severity = ERROR_SEVERITY.LOW;
      } else if (status >= 500) {
        errorType = ERROR_TYPES.SERVER;
        message = "Server error. Please try again later.";
        severity = ERROR_SEVERITY.HIGH;
      } else if (status >= 400) {
        errorType = ERROR_TYPES.CLIENT;
        message = error.response.data?.message || "Request failed";
        severity = ERROR_SEVERITY.MEDIUM;
      }

      // Use server message if available
      if (error.response.data?.message) {
        message = error.response.data.message;
      }
    } else if (error.request) {
      errorType = ERROR_TYPES.NETWORK;
      message = "Network error. Please check your connection.";
      severity = ERROR_SEVERITY.HIGH;
    }

    addError(
      key,
      { message },
      {
        type: errorType,
        severity,
        context: { ...context, status: error.response?.status },
        retryable:
          errorType === ERROR_TYPES.NETWORK || errorType === ERROR_TYPES.SERVER,
      }
    );
  };

  // Show error toast with appropriate styling
  const showErrorToast = (error) => {
    const toastOptions = {
      position: "top-right",
      timeout: 5000,
      hideProgressBar: false,
      pauseOnHover: true,
    };

    switch (error.severity) {
      case ERROR_SEVERITY.CRITICAL:
        toast(error.message, { ...toastOptions, type: "error", timeout: 0 }); // Persistent
        break;
      case ERROR_SEVERITY.HIGH:
        toast(error.message, { ...toastOptions, type: "error", timeout: 8000 });
        break;
      case ERROR_SEVERITY.MEDIUM:
        toast(error.message, {
          ...toastOptions,
          type: "warning",
          timeout: 5000,
        });
        break;
      case ERROR_SEVERITY.LOW:
        toast(error.message, { ...toastOptions, type: "info", timeout: 3000 });
        break;
    }
  };

  // Error boundary wrapper for async operations
  const withErrorHandling = (key, operation, options = {}) => {
    return async (...args) => {
      try {
        removeError(key); // Clear previous errors
        return await operation(...args);
      } catch (error) {
        if (options.handleAs === "api") {
          handleApiError(key, error, options.context);
        } else {
          addError(key, error, options);
        }

        // Re-throw if needed
        if (options.rethrow) {
          throw error;
        }

        return options.fallback || null;
      }
    };
  };

  // Remove error
  const removeError = (key) => {
    errors.value.delete(key);
  };

  // Clear all errors
  const clearErrors = () => {
    errors.value.clear();
  };

  // Get error by key
  const getError = (key) => {
    return errors.value.get(key) || null;
  };

  return {
    // State
    errors: computed(() => errors.value),
    isRetrying,

    // Computed
    hasErrors,
    errorList,
    notifications: errorList, // Alias for compatibility

    // Constants
    ERROR_TYPES,
    ERROR_SEVERITY,

    // Methods
    addError,
    removeError,
    clearErrors,
    getError,
    handleApiError,
    withErrorHandling,
    showErrorToast,
    dismissNotification: removeError, // Alias for compatibility
    handleError: addError, // Alias for compatibility
  };
}
