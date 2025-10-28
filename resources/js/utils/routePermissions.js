/**
 * Route to permission mapping utility
 * Maps route names/paths to their required permissions
 */

// Route name to permission mapping
export const routePermissionMap = {
  // Common pages
  'root': ['home'],
  'dashboard': ['dashboard'],
  'admin-dashboard': ['admin-dashboard'],
  'branch-list': ['branch-list'],
  
  'outlets': ['outlets'],
  
  // Panel routes
  'panel-kitchen': ['panel-kitchen'],
  'panel-waiter': ['panel-waiter'],
  
  // Table routes
  'table-area': ['table-area'],
  'table': ['table'],
  'table-qrcode': ['table-qrcode'],
  
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
  
  // Food settings routes
  'food-meal-time': ['food-meal-time-list'],
  'food-course-type': ['food-course-type-list'],
  'food-speciality': ['food-speciality-list'],
  
  // Customer preference routes
  'customer-preference-cuisine': ['customer-preference-cuisine-list'],
  'customer-preference-dietary': ['customer-preference-dietary-list'],
  'customer-preference-spice-level': ['customer-preference-spice-level-list'],
  'customer-preference-protein-choice': ['customer-preference-protein-choice-list'],
  'customer-preference-flavor-profile': ['customer-preference-flavor-profile-list'],
  'customer-preference-beverage-preferences': ['customer-preference-beverage-preferences-list'],
  'customer-preference-allergy': ['customer-preference-allergy-list'],
  'customer-preference-cooking-oil': ['customer-preference-cooking-oil-list'],
  'customer-preference-health-focused': ['customer-preference-health-focused-list'],
  'customer-preference-disease': ['customer-preference-disease-list'],
  'customer-preference-sauce': ['customer-preference-sauce-list'],
  'customer-preference-texture': ['customer-preference-texture-list'],
  'customer-preference-religion-preference-islam': ['customer-preference-religion-preference-islam-list'],
  'customer-preference-religion-preference-judaism': ['customer-preference-religion-preference-judaism-list'],
  'customer-preference-religion-preference-hinduism': ['customer-preference-religion-preference-hinduism-list'],
  'customer-preference-religion-preference-buddhism': ['customer-preference-religion-preference-buddhism-list'],
  'customer-preference-religion-preference-christianity': ['customer-preference-religion-preference-christianity-list'],
  'customer-preference-religion-preference-sikhism': ['customer-preference-religion-preference-sikhism-list'],
  'customer-preference-religion-preference-jainism': ['customer-preference-religion-preference-jainism-list'],
  
  // Items routes
  'items-unit': ['items-unit-list'],
  'items-ingredient-category': ['items-ingredient-category-list'],
  'items-ingredient': ['items-ingredient-list'],
  'items-food-menu-category': ['items-food-menu-category-list'],
  'items-food-menu': ['items-food-menu-list'],
  'items-modifiers': ['items-modifiers-list'],
  
  // Inventory routes
  'daily-opening-stock': ['daily-opening-stock-list'],
  'ingredient-stock': ['ingredient-stock'],
  'ingredient-stock-low-stock': ['ingredient-low-stock'],
  'stock-adjustment': ['stock-adjustment-list'],
  
  // Sales routes
  'pos': ['pos'],
  'sales-customer': ['sales-customer-list'],
  'promotion': ['promotion-list'],
  'sales-draft': ['sales-draft'],
  'sales-running': ['sales-running'],
  'sales-complete': ['sales-complete'],
  'sales-cancelled': ['sales-cancelled'],
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
  
  // Transfer routes
  'stock-transfer-create': ['stock-transfer-create'],
  'stock-transfer': ['stock-transfer-list'],
  
  // Waste routes
  'waste-create': ['waste-create'],
  'waste': ['waste-list'],
  
  // Reports routes
  'reports-register': ['reports-register'],
  'reports-zreport': ['reports-zreport'],
  'reports-kitchen-performance': ['reports-kitchen-performance'],
  'reports-product-analysis': ['reports-product-analysis'],
  'reports-daily-summary': ['reports-daily-summary'],
  'reports-food-sale': ['reports-food-sale'],
  'reports-daily-sale': ['reports-daily-sale'],
  'reports-detailed-sale': ['reports-detailed-sale'],
  'reports-stock': ['reports-stock'],
  'reports-low-stock': ['reports-low-stock'],
  'reports-profit-loss': ['reports-profit-loss'],
  'reports-attendance': ['reports-attendance'],
  'reports-supplier-ledger': ['reports-supplier-ledger'],
  'reports-customer-ledger': ['reports-customer-ledger'],
  'reports-supplier-due': ['reports-supplier-due'],
  'reports-customer-due': ['reports-customer-due'],
  'reports-purchase': ['reports-purchase'],
  'reports-expense': ['reports-expense'],
  'reports-waste': ['reports-waste'],
  'reports-tax': ['reports-tax'],
  'reports-waiter-tips': ['reports-waiter-tips'],
  'reports-transfer': ['reports-transfer'],
  
  // User & Role routes
  'role-create': ['role-create'],
  'role': ['role-list'],
  'user-create': ['user-create'],
  'user': ['user-list'],
  
  // Attendance routes
  'attendance-create': ['attendance-create'],
  'attendance': ['attendance-list'],
  
  // POS routes
  'pos-registers': ['pos-registers'],

  'damage-create': ['damage-create'],
};

// Path pattern to permission mapping for dynamic routes
export const pathPermissionMap = {
  '/admin-dashboard': ['admin-dashboard'],
  // Settings paths
  '/settings/restaurant': ['settings-restaurant'],
  '/settings/white-label': ['settings-white-label'],
  '/settings/mail': ['email-settings'],
  '/settings/payment': ['payment-settings'],
  '/settings/tax': ['tax-settings'],
  '/settings/sms': ['sms-settings'],
  '/settings/whatsapp': ['whatsapp-settings'],
  '/settings/delivery-partner': ['delivery-partner-list'],
  
  // User and role paths
  '/user': ['user-list'],
  '/role': ['role-list'],
  
  // Outlets
  '/outlets': ['outlets'],
  
  // Items paths
  '/items/unit': ['items-unit-list'],
  '/items/ingredient-category': ['items-ingredient-category-list'],
  '/items/ingredient': ['items-ingredient-list'],
  '/items/food-menu-category': ['items-food-menu-category-list'],
  '/items/food-menu': ['items-food-menu-list'],
  '/items/modifiers': ['items-modifiers-list'],
  
  // Stock/Inventory paths
  '/ingredient-stock': ['ingredient-stock'],
  '/daily-opening-stock': ['daily-opening-stock-list'],
  '/stock-adjustment': ['stock-adjustment-list'],
  
  // Sales paths
  '/pos': ['pos'],
  '/sales/customer': ['sales-customer-list'],
  '/sales/draft': ['sales-draft'],
  '/sales/running': ['sales-running'],
  '/sales/complete': ['sales-complete'],
  '/sales/cancelled': ['sales-cancelled'],
  '/customer-due': ['customer-due-list'],
  '/promotion': ['promotion-list'],
  
  // Purchase paths
  '/purchase': ['purchase-list'],
  '/supplier': ['supplier-list'],
  '/supplier-due': ['supplier-due-list'],
  
  // Expense paths
  '/expense': ['expense-list'],
  '/expense-category': ['expense-category-list'],
  
  // Transfer paths
  '/stock-transfer': ['stock-transfer-list'],
  
  // Waste paths
  '/waste': ['waste-list'],
  
  // Reports paths
  '/reports': ['reports-register', 'reports-zreport', 'reports-kitchen-performance', 'reports-product-analysis', 'reports-daily-summary', 'reports-food-sale', 'reports-daily-sale', 'reports-detailed-sale', 'reports-stock', 'reports-low-stock', 'reports-profit-loss', 'reports-attendance', 'reports-supplier-ledger', 'reports-customer-ledger', 'reports-supplier-due', 'reports-customer-due', 'reports-purchase', 'reports-expense', 'reports-waste', 'reports-tax', 'reports-waiter-tips', 'reports-transfer'],
  
  // Attendance paths
  '/attendance': ['attendance-list'],
  
  // Customer preference paths
  '/customer-preference': ['customer-preference-cuisine-list', 'customer-preference-dietary-list', 'customer-preference-spice-level-list', 'customer-preference-protein-choice-list', 'customer-preference-flavor-profile-list', 'customer-preference-beverage-preferences-list', 'customer-preference-allergy-list', 'customer-preference-cooking-oil-list', 'customer-preference-health-focused-list', 'customer-preference-disease-list', 'customer-preference-sauce-list', 'customer-preference-texture-list'],
  
  // Food settings paths
  '/food-meal-time': ['food-meal-time-list'],
  '/food-course-type': ['food-course-type-list'],
  '/food-speciality': ['food-speciality-list'],
  
  // Table paths
  '/table': ['table-area', 'table', 'table-qrcode'],
  
  // Panel paths
  '/panel': ['panel-kitchen', 'panel-waiter', 'printer-list'],
  
  // Counter paths
  '/counter': ['counter-list'],
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
