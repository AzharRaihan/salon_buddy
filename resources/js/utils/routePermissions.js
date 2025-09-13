/**
 * Route to permission mapping utility
 * Maps route names/paths to their required permissions
 */

// Route name to permission mapping
export const routePermissionMap = {
    // Common pages
    'root': ['home'],
    'dashboard': ['dashboard'],
    'branch': ['branch-list'],
    

    // Settings routes
    'settings-restaurant': ['settings-restaurant'],
    'settings-white-label': ['settings-white-label'],
    'settings-mail': ['email-settings'],
    'settings-payment': ['payment-settings'],
    'settings-tax': ['tax-settings'],
    'settings-sms': ['sms-settings'],
    'settings-whatsapp': ['whatsapp-settings'],
    'settings-delivery-partner': ['delivery-partner-list'],
    'panel-printer': ['printer-list'],
    'counter': ['counter-list'],
    


    // Items routes
    'items-unit': ['items-unit-list'],

    
    
    // Sales routes
    'pos': ['pos'],
    'sales-customer': ['sales-customer-list'],
    'promotion': ['promotion-list'],
    'customer-due': ['customer-due-list'],
    
    // Purchase routes
    'supplier': ['supplier-list'],
    'purchase': ['purchase-list'],
    'supplier-due': ['supplier-due-list'],
    
    // Expense routes
    'expense-create': ['expense-create'],
    'expense': ['expense-list'],
    'expense-category-create': ['expense-category-create'],
    'expense-category': ['expense-category-list'],

  
    // Reports routes
    'reports-daily-summary': ['reports-daily-summary'],
    'reports-daily-sale': ['reports-daily-sale'],
    'reports-detailed-sale': ['reports-detailed-sale'],
    'reports-stock': ['reports-stock'],
    'reports-low-stock': ['reports-low-stock'],
    'reports-profit-loss': ['reports-profit-loss'],
    'reports-attendance': ['reports-attendance'],
    'reports-supplier-due': ['reports-supplier-due'],
    'reports-customer-due': ['reports-customer-due'],
    'reports-purchase': ['reports-purchase'],
    'reports-expense': ['reports-expense'],
    'reports-tax': ['reports-tax'],
    
    // User & Role routes
    'role-create': ['role-create'],
    'role': ['role-list'],
    'employee-create': ['employee-create'],
    'employee': ['employee-list'],
    
    // Attendance routes
    'attendance-create': ['attendance-create'],
    'attendance': ['attendance-list'],
    
    // POS routes
    'pos-registers': ['pos-registers'],
  };
  
  // Path pattern to permission mapping for dynamic routes
  export const pathPermissionMap = {
    // Settings paths
    '/settings/white-label': ['settings-white-label'],
    '/settings/mail': ['email-settings'],
    '/settings/payment': ['payment-settings'],
    '/settings/tax': ['tax-settings'],
    '/settings/sms': ['sms-settings'],
    
    // User and role paths
    '/user': ['user-list'],
    '/role': ['role-list'],
    
    // Outlets
    '/outlets': ['outlets'],
    
    // Items paths
    '/items/unit': ['items-unit-list'],
    
    
    // Sales paths
    '/pos': ['pos'],
    '/sales/customer': ['sales-customer-list'],
    '/customer-due': ['customer-due-list'],
    '/promotion': ['promotion-list'],
    
    // Purchase paths
    '/purchase': ['purchase-list'],
    '/supplier': ['supplier-list'],
    '/supplier-due': ['supplier-due-list'],
    
    // Expense paths
    '/expense': ['expense-list'],
    '/expense-category': ['expense-category-list'],
    
    
    // Reports paths
    '/reports': ['reports-register', 'reports-zreport', 'reports-kitchen-performance', 'reports-product-analysis', 'reports-daily-summary', 'reports-food-sale', 'reports-daily-sale', 'reports-detailed-sale', 'reports-stock', 'reports-low-stock', 'reports-profit-loss', 'reports-attendance', 'reports-supplier-ledger', 'reports-customer-ledger', 'reports-supplier-due', 'reports-customer-due', 'reports-purchase', 'reports-expense', 'reports-waste', 'reports-tax', 'reports-waiter-tips', 'reports-transfer'],
    
    // Attendance paths
    '/attendance': ['attendance-list'],

  };
  
  /**
   * Get required permissions for a route
   * @param {object} route - Vue router route object
   * @returns {string[]|null} - Array of required permissions or null if no permissions required
   */
  export const getRoutePermissions = (route) => {
    // First check by route name
    if (route.name && routePermissionMap[route.name]) {
      return routePermissionMap[route.name];
    }
    
    // Then check by exact path
    if (pathPermissionMap[route.path]) {
      return pathPermissionMap[route.path];
    }
    
    // Check by path patterns
    for (const [pathPattern, permissions] of Object.entries(pathPermissionMap)) {
      if (route.path.startsWith(pathPattern)) {
        return permissions;
      }
    }
    
    // No permissions required for this route
    return null;
  };
  
  /**
   * Check if route should be excluded from permission checks
   * @param {string} path - Route path
   * @returns {boolean} - Whether the route should be excluded
   */
  export const isPermissionExcludedRoute = (path) => {
    const excludedRoutes = [
      '/',
      '/login',
      '/register',
      '/forgot-password',
      '/profile/security-question',
      '/select-outlet',
      '/dashboard',
      '/pos-registers'
    ];
    
    const excludedPatterns = [
      '/profile/',
      '/auth/',
      '/forgot-password/',
      '/register/'
    ];
    
    // Check exact matches
    if (excludedRoutes.includes(path)) {
      return true;
    }
    
    // Check pattern matches
    return excludedPatterns.some(pattern => path.startsWith(pattern));
  };
  