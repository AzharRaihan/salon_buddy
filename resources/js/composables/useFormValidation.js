import { computed, nextTick, reactive, ref, watch } from "vue";

export function useFormValidation(initialData = {}, validationRules = {}) {
  // Form state
  const formData = reactive({ ...initialData });
  const errors = ref({});
  const touched = ref({});
  const isValidating = ref(false);
  const isSubmitting = ref(false);

  // Validation rules
  const rules = ref(validationRules);

  // Built-in validation rules
  const builtInRules = {
    required: (value, message = "This field is required") => {
      if (value === null || value === undefined || value === "") {
        return message;
      }
      if (Array.isArray(value) && value.length === 0) {
        return message;
      }
      return null;
    },

    email: (value, message = "Please enter a valid email address") => {
      if (!value) return null;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(value) ? null : message;
    },

    phone: (value, message = "Please enter a valid phone number") => {
      if (!value) return null;
      const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
      return phoneRegex.test(value.replace(/[\s\-\(\)]/g, "")) ? null : message;
    },

    min: (minValue, message) => (value) => {
      if (!value) return null;
      const num = parseFloat(value);
      if (isNaN(num) || num < minValue) {
        return message || `Value must be at least ${minValue}`;
      }
      return null;
    },

    max: (maxValue, message) => (value) => {
      if (!value) return null;
      const num = parseFloat(value);
      if (isNaN(num) || num > maxValue) {
        return message || `Value must not exceed ${maxValue}`;
      }
      return null;
    },

    minLength: (minLen, message) => (value) => {
      if (!value) return null;
      if (value.length < minLen) {
        return message || `Must be at least ${minLen} characters long`;
      }
      return null;
    },

    maxLength: (maxLen, message) => (value) => {
      if (!value) return null;
      if (value.length > maxLen) {
        return message || `Must not exceed ${maxLen} characters`;
      }
      return null;
    },

    numeric: (value, message = "Please enter a valid number") => {
      if (!value) return null;
      return !isNaN(parseFloat(value)) && isFinite(value) ? null : message;
    },

    positive: (value, message = "Value must be positive") => {
      if (!value) return null;
      const num = parseFloat(value);
      return !isNaN(num) && num > 0 ? null : message;
    },

    percentage: (
      value,
      message = "Please enter a valid percentage (0-100)"
    ) => {
      if (!value) return null;
      const num = parseFloat(value);
      if (isNaN(num) || num < 0 || num > 100) {
        return message;
      }
      return null;
    },

    currency: (value, message = "Please enter a valid amount") => {
      if (!value) return null;
      const num = parseFloat(value);
      if (isNaN(num) || num < 0) {
        return message;
      }
      return null;
    },

    // POS-specific validations
    orderMinimum: (minAmount, message) => (value) => {
      if (!value) return null;
      const num = parseFloat(value);
      if (isNaN(num) || num < minAmount) {
        return message || `Minimum order amount is ${minAmount}`;
      }
      return null;
    },

    inventoryCheck: (availableStock, message) => (value) => {
      if (!value) return null;
      const num = parseInt(value);
      if (isNaN(num) || num > availableStock) {
        return message || `Only ${availableStock} items available in stock`;
      }
      return null;
    },

    tableNumber: (value, message = "Please enter a valid table number") => {
      if (!value) return null;
      const num = parseInt(value);
      if (isNaN(num) || num < 1 || num > 999) {
        return message;
      }
      return null;
    },
  };

  // Computed properties
  const isValid = computed(() => {
    return Object.keys(errors.value).length === 0;
  });

  const hasErrors = computed(() => {
    return Object.keys(errors.value).length > 0;
  });

  const touchedFields = computed(() => {
    return Object.keys(touched.value).filter((key) => touched.value[key]);
  });

  const isDirty = computed(() => {
    return Object.keys(formData).some((key) => {
      return JSON.stringify(formData[key]) !== JSON.stringify(initialData[key]);
    });
  });

  // Validate single field
  const validateField = async (fieldName) => {
    const fieldRules = rules.value[fieldName];
    if (!fieldRules) return;

    const value = formData[fieldName];
    const fieldErrors = [];

    for (const rule of fieldRules) {
      let errorMessage = null;

      if (typeof rule === "string") {
        // Built-in rule
        if (builtInRules[rule]) {
          errorMessage = builtInRules[rule](value);
        }
      } else if (typeof rule === "function") {
        // Custom function
        errorMessage = await rule(value, formData);
      } else if (typeof rule === "object") {
        // Rule with parameters
        const { name, params = [], message } = rule;
        if (builtInRules[name]) {
          const ruleFunction = builtInRules[name](...params, message);
          errorMessage = ruleFunction(value);
        }
      }

      if (errorMessage) {
        fieldErrors.push(errorMessage);
        break; // Stop at first error
      }
    }

    if (fieldErrors.length > 0) {
      errors.value[fieldName] = fieldErrors[0];
    } else {
      delete errors.value[fieldName];
    }
  };

  // Validate all fields
  const validateAll = async () => {
    isValidating.value = true;
    errors.value = {};

    const validationPromises = Object.keys(rules.value).map((fieldName) =>
      validateField(fieldName)
    );

    await Promise.all(validationPromises);
    isValidating.value = false;

    return isValid.value;
  };

  // Mark field as touched
  const touchField = (fieldName) => {
    touched.value[fieldName] = true;
  };

  // Clear errors for field
  const clearFieldError = (fieldName) => {
    delete errors.value[fieldName];
  };

  // Clear all errors
  const clearErrors = () => {
    errors.value = {};
  };

  // Reset form
  const resetForm = () => {
    Object.keys(formData).forEach((key) => {
      formData[key] = initialData[key];
    });
    errors.value = {};
    touched.value = {};
    isSubmitting.value = false;
  };

  // Update form data
  const updateField = (fieldName, value) => {
    formData[fieldName] = value;
    touchField(fieldName);

    // Real-time validation
    if (touched.value[fieldName]) {
      nextTick(() => validateField(fieldName));
    }
  };

  // Batch update
  const updateFields = (data) => {
    Object.keys(data).forEach((key) => {
      if (key in formData) {
        formData[key] = data[key];
      }
    });
  };

  // Submit handler with validation
  const handleSubmit = async (submitFunction) => {
    isSubmitting.value = true;

    try {
      // Mark all fields as touched
      Object.keys(formData).forEach((field) => touchField(field));

      // Validate all fields
      const isFormValid = await validateAll();

      if (isFormValid) {
        try {
          const result = await submitFunction(formData);
          return { success: true, data: result };
        } catch (error) {
          return { success: false, error };
        }
      } else {
        return { success: false, errors: errors.value };
      }
    } finally {
      isSubmitting.value = false;
    }
  };

  // Get field error
  const getFieldError = (fieldName) => {
    return errors.value[fieldName] || null;
  };

  // Check if field has error
  const hasFieldError = (fieldName) => {
    return !!errors.value[fieldName];
  };

  // Check if field is touched
  const isFieldTouched = (fieldName) => {
    return !!touched.value[fieldName];
  };

  // Add custom rule
  const addRule = (fieldName, rule) => {
    if (!rules.value[fieldName]) {
      rules.value[fieldName] = [];
    }
    rules.value[fieldName].push(rule);
  };

  // Remove rule
  const removeRule = (fieldName, ruleIndex) => {
    if (rules.value[fieldName] && rules.value[fieldName][ruleIndex]) {
      rules.value[fieldName].splice(ruleIndex, 1);
    }
  };

  // Set up watchers for real-time validation
  Object.keys(rules.value).forEach((fieldName) => {
    watch(
      () => formData[fieldName],
      () => {
        if (touched.value[fieldName]) {
          validateField(fieldName);
        }
      }
    );
  });

  return {
    // Form data
    formData,

    // State
    errors: computed(() => errors.value),
    touched: computed(() => touched.value),
    isValid,
    hasErrors,
    isDirty,
    isValidating,
    isSubmitting,
    touchedFields,

    // Methods
    validateField,
    validateAll,
    touchField,
    clearFieldError,
    clearErrors,
    resetForm,
    updateField,
    updateFields,
    handleSubmit,
    getFieldError,
    hasFieldError,
    isFieldTouched,
    addRule,
    removeRule,

    // Built-in rules for reference
    builtInRules,
  };
}

// POS-specific form validation presets
export const posValidationPresets = {
  waiterForm: {
    name: [
      "required",
      {
        name: "minLength",
        params: [2],
        message: "Name must be at least 2 characters",
      },
    ],
    email: ["email"],
    phone: ["phone"],
  },

  customerForm: {
    name: ["required", { name: "minLength", params: [2] }],
    phone: ["phone"],
    email: ["email"],
    tableNumber: ["tableNumber"],
  },

  discountForm: {
    amount: ["required", "positive"],
    type: ["required"],
  },

  orderForm: {
    items: ["required"],
    waiter: ["required"],
    orderType: ["required"],
  },

  paymentForm: {
    amount: ["required", "positive", "currency"],
    method: ["required"],
    tip: ["currency"],
  },
};

// Helper function to create form with preset rules
export function createPOSForm(preset, initialData = {}) {
  const rules = posValidationPresets[preset] || {};
  return useFormValidation(initialData, rules);
}
