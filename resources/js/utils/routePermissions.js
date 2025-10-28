/**
 * Route to permission mapping utility
 * Maps route names/paths to their required permissions
 */

// Route name to permission mapping
export const routePermissionMap = {
  // Common pages
  'home': ['home'],
  'admin-dashboard': ['admin-dashboard'],

  'branch-create': ['branch-create'],
  'branch-edit': ['branch-edit'],
  'branch-delete': ['branch-delete'],
  'branch': ['branch-list'],

  'booking-create': ['booking-create'],
  'booking-edit': ['booking-edit'],
  'booking-delete': ['booking-delete'],
  'booking': ['booking-list'],
  'booking-calendar': ['booking-calendar'],

  'unit-create': ['unit-create'],
  'unit-edit': ['unit-edit'],
  'unit-delete': ['unit-delete'],
  'unit': ['unit-list'],

  'category-create': ['category-create'],
  'category-edit': ['category-edit'],
  'category-delete': ['category-delete'],
  'category': ['category-list'],

  'item-create': ['item-create'],
  'item-edit': ['item-edit'],
  'item-delete': ['item-delete'],
  'item': ['item-list'],

  'customer-create': ['customer-create'],
  'customer-edit': ['customer-edit'],
  'customer-delete': ['customer-delete'],
  'customer': ['customer-list'],

  'customer-receive-create': ['customer-receive-create'],
  'customer-receive-edit': ['customer-receive-edit'],
  'customer-receive-delete': ['customer-receive-delete'],
  'customer-receive': ['customer-receive-list'],
  
  'promotion-create': ['promotion-create'],
  'promotion-edit': ['promotion-edit'],
  'promotion-delete': ['promotion-delete'],
  'promotion': ['promotion-list'],

  'purchase-create': ['purchase-create'],
  'purchase-edit': ['purchase-edit'],
  'purchase-delete': ['purchase-delete'],
  'purchase': ['purchase-list'],

  'supplier-create': ['supplier-create'],
  'supplier-edit': ['supplier-edit'],
  'supplier-delete': ['supplier-delete'],
  'supplier': ['supplier-list'],

  'supplier-payment-create': ['supplier-payment-create'],
  'supplier-payment-edit': ['supplier-payment-edit'],
  'supplier-payment-delete': ['supplier-payment-delete'],
  'supplier-payment': ['supplier-payment-list'],

  'expense-create': ['expense-create'],
  'expense-edit': ['expense-edit'],
  'expense-delete': ['expense-delete'],
  'expense-list': ['expense-list'],
  'expense': ['expense'],

  'expense-category-create': ['expense-category-create'],
  'expense-category-edit': ['expense-category-edit'],
  'expense-category-delete': ['expense-category-delete'],
  'expense-category': ['expense-category-list'],

  'stock-stock': ['stock-stock'],
  'stock-alert-stock': ['stock-alert-stock'],
  'product-usages-create': ['product-usages-create'],
  'product-usages': ['product-usages'],

  'sale-create': ['sale-create'],
  'sale-edit': ['sale-edit'],
  'sale-delete': ['sale-delete'],
  'sale': ['sale-list'],

  'staff-payment-create': ['staff-payment-create'],
  'staff-payment-edit': ['staff-payment-edit'],
  'staff-payment-delete': ['staff-payment-delete'],
  'staff-payment': ['staff-payment-list'],

  'damage-create': ['damage-create'],
  'damage-edit': ['damage-edit'],
  'damage-delete': ['damage-delete'],
  'damage': ['damage-list'],


  'payment-account-create': ['payment-account-create'],
  'payment-account-edit': ['payment-account-edit'],
  'payment-account-delete': ['payment-account-delete'],
  'payment-account': ['payment-account-list'],

  'deposit-withdraw-create': ['deposit-withdraw-create'],
  'deposit-withdraw-edit': ['deposit-withdraw-edit'],
  'deposit-withdraw-delete': ['deposit-withdraw-delete'],
  'deposit-withdraw': ['deposit-withdraw-list'],

  'accounting-account-balance': ['accounting-account-balance'],
  'accounting-account-statement': ['accounting-account-statement'],
  'accounting-balance-sheet': ['accounting-balance-sheet'],
  'accounting-trial-balance': ['accounting-trial-balance'],
  'accounting-transaction-history': ['accounting-transaction-history'],

  'role-create': ['role-create'],
  'role-edit': ['role-edit'],
  'role-delete': ['role-delete'],
  'role': ['role-list'],

  'employee-create': ['employee-create'],
  'employee-edit': ['employee-edit'],
  'employee-delete': ['employee-delete'],
  'employee': ['employee-list'],

  'settings-settings': ['settings-settings'],
  'settings-tax': ['settings-tax'],
  'settings-email': ['settings-email'],
  'settings-sms': ['settings-sms'],
  'settings-whatsapp': ['settings-whatsapp'],
  'settings-payment': ['settings-payment'],
  'settings-social-auth': ['settings-social-auth'],
  'settings-white-label': ['settings-white-label'],
  'settings-vacation': ['settings-vacation'],
  'settings-holiday': ['settings-holiday'],

  'report-daily-summary': ['report-daily-summary'],
  'report-profit-loss': ['report-profit-loss'],
  'report-sale': ['report-sale'],
  'report-purchase': ['report-purchase'],
  'report-stock': ['report-stock'],
  'report-commission': ['report-commission'],
  'report-earning': ['report-earning'],
  'report-payout': ['report-payout'],
  'report-evaluation': ['report-evaluation'],
  'report-evaluation-details': ['report-evaluation-details'],
  'report-attendance': ['report-attendance'],
  'report-damage': ['report-damage'],

};

// Path pattern to permission mapping for dynamic routes
export const pathPermissionMap = {
  '/home': ['home'],
  '/admin-dashboard': ['admin-dashboard'],

  '/branch-create': ['branch-create'],
  '/branch-edit': ['branch-edit'],
  '/branch-delete': ['branch-delete'],
  '/branch-list': ['branch-list'],

  '/booking-create': ['booking-create'],
  '/booking-edit': ['booking-edit'],
  '/booking-delete': ['booking-delete'],
  '/booking-list': ['booking-list'],
  '/booking-calendar': ['booking-calendar'],

  '/unit-create': ['unit-create'],
  '/unit-edit': ['unit-edit'],
  '/unit-delete': ['unit-delete'],
  '/unit-list': ['unit-list'],

  '/category-create': ['category-create'],
  '/category-edit': ['category-edit'],
  '/category-delete': ['category-delete'],
  '/category-list': ['category-list'],

  '/item-create': ['item-create'],
  '/item-edit': ['item-edit'],
  '/item-delete': ['item-delete'],
  '/item-list': ['item-list'],

  '/customer-create': ['customer-create'],
  '/customer-edit': ['customer-edit'],
  '/customer-delete': ['customer-delete'],
  '/customer-list': ['customer-list'],

  '/customer-receive-create': ['customer-receive-create'],
  '/customer-receive-edit': ['customer-receive-edit'],
  '/customer-receive-delete': ['customer-receive-delete'],
  '/customer-receive-list': ['customer-receive-list'],

  '/promotion-create': ['promotion-create'],
  '/promotion-edit': ['promotion-edit'],
  '/promotion-delete': ['promotion-delete'],
  '/promotion-list': ['promotion-list'],

  '/purchase-create': ['purchase-create'],
  '/purchase-edit': ['purchase-edit'],
  '/purchase-delete': ['purchase-delete'],
  '/purchase-list': ['purchase-list'],

  '/supplier-create': ['supplier-create'],
  '/supplier-edit': ['supplier-edit'],
  '/supplier-delete': ['supplier-delete'],
  '/supplier-list': ['supplier-list'],

  '/supplier-payment-create': ['supplier-payment-create'],
  '/supplier-payment-edit': ['supplier-payment-edit'],
  '/supplier-payment-delete': ['supplier-payment-delete'],
  '/supplier-payment-list': ['supplier-payment-list'],

  '/expense-create': ['expense-create'],
  '/expense-edit': ['expense-edit'],
  '/expense-delete': ['expense-delete'],
  '/expense-list': ['expense-list'],

  '/expense-category-create': ['expense-category-create'],
  '/expense-category-edit': ['expense-category-edit'],
  '/expense-category-delete': ['expense-category-delete'],
  '/expense-category-list': ['expense-category-list'],

  '/stock-stock': ['stock-stock'],
  '/stock-alert-stock': ['stock-alert-stock'],
  '/product-usages-create': ['product-usages-create'],
  '/product-usages': ['product-usages'],

  '/sale-create': ['sale-create'],
  '/sale-edit': ['sale-edit'],
  '/sale-delete': ['sale-delete'],
  '/sale-list': ['sale-list'],

  '/staff-payment-create': ['staff-payment-create'],
  '/staff-payment-edit': ['staff-payment-edit'],
  '/staff-payment-delete': ['staff-payment-delete'],
  '/staff-payment-list': ['staff-payment-list'],

  '/damage-create': ['damage-create'],
  '/damage-edit': ['damage-edit'],
  '/damage-delete': ['damage-delete'],
  '/damage-list': ['damage-list'],





  '/deposit-withdraw-create': ['deposit-withdraw-create'],
  '/deposit-withdraw-edit': ['deposit-withdraw-edit'],
  '/deposit-withdraw-delete': ['deposit-withdraw-delete'],
  '/deposit-withdraw-list': ['deposit-withdraw-list'],

  '/accounting-account-balance': ['accounting-account-balance'],
  '/accounting-account-statement': ['accounting-account-statement'],
  '/accounting-balance-sheet': ['accounting-balance-sheet'],
  '/accounting-trial-balance': ['accounting-trial-balance'],
  '/accounting-transaction-history': ['accounting-transaction-history'],

  '/role-create': ['role-create'],
  '/role-edit': ['role-edit'],
  '/role-delete': ['role-delete'],
  '/role-list': ['role-list'],

  '/employee-create': ['employee-create'],
  '/employee-edit': ['employee-edit'],
  '/employee-delete': ['employee-delete'],
  '/employee-list': ['employee-list'],

  '/settings-settings': ['settings-settings'],
  '/settings-tax': ['settings-tax'],
  '/settings-email': ['settings-email'],
  '/settings-sms': ['settings-sms'],
  '/settings-whatsapp': ['settings-whatsapp'],
  '/settings-payment': ['settings-payment'],
  '/settings-social-auth': ['settings-social-auth'],
  '/settings-white-label': ['settings-white-label'],
  '/settings-vacation': ['settings-vacation'],
  '/settings-holiday': ['settings-holiday'],

  '/report-daily-summary': ['report-daily-summary'],
  '/report-profit-loss': ['report-profit-loss'],
  '/report-sale': ['report-sale'],
  '/report-purchase': ['report-purchase'],
  '/report-stock': ['report-stock'],
  '/report-commission': ['report-commission'],
  '/report-earning': ['report-earning'],
  '/report-payout': ['report-payout'],
  '/report-evaluation': ['report-evaluation'],
  '/report-evaluation-details': ['report-evaluation-details'],
  '/report-attendance': ['report-attendance'],
  '/report-damage': ['report-damage'],
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
