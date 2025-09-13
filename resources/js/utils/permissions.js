import {
  getUserData,
} from '@/utils/storage';
import { useCookie } from '@/@core/composable/useCookie';
/**
 * Permissions utility for checking user permissions
 *
 * Example usage in a Vue component:
 *
 * <script setup>
 * import { hasPermission, hasAnyPermission } from '@/utils/permissions'
 *
 * // Check if user has a specific permission
 * const canViewDashboard = hasPermission('dashboard')
 *
 * // Check if user has any of multiple permissions
 * const canManageUsers = hasAnyPermission(['user-create', 'user-edit', 'user-delete'])
 * </script>
 *
 * <template>
 *   <div v-if="canViewDashboard">Dashboard content</div>
 *   <button v-if="hasPermission('user-create')">Add User</button>
 * </template>
 */

/**
 * Check if user has a specific permission
 * @param {string} permission - Permission name to check
 * @returns {boolean} - Whether the user has the permission
 */
export const hasPermission = (permission) => {
  // Check if user role is 1 (admin/superuser), bypass permission check
  const userData = useCookie("userData").value;
  if (userData && userData.role == 1) return true;

  // Get user ability rules from cookie
  const userAbilityRules = useCookie("userAbilityRules").value;

  // If userAbilityRules doesn't exist or is not an array, return false
  if (!userAbilityRules || !Array.isArray(userAbilityRules)) return false;

  // Check if the permission exists in the userAbilityRules array
  return userAbilityRules.includes(permission);
};

/**
 * Check if user has any of the given permissions
 * @param {string|string[]} permissions - Single permission or array of permissions to check
 * @returns {boolean} - Whether the user has any of the permissions
 */
export const hasAnyPermission = (permissions) => {
  // If permissions is a string, convert it to an array
  const permissionsArray = Array.isArray(permissions)
    ? permissions
    : [permissions];

  // Return true if user has any of the permissions
  return permissionsArray.some((permission) => hasPermission(permission));
};

/**
 * Check if user has all of the given permissions
 * @param {string|string[]} permissions - Single permission or array of permissions to check
 * @returns {boolean} - Whether the user has all of the permissions
 */
export const hasAllPermissions = (permissions) => {
  // If permissions is a string, convert it to an array
  const permissionsArray = Array.isArray(permissions)
    ? permissions
    : [permissions];

  // Return true if user has all of the permissions
  return permissionsArray.every((permission) => hasPermission(permission));
};

/**
 * Check if a navigation item should be visible based on permissions
 * @param {object} item - Navigation item to check
 * @returns {boolean} - Whether the item should be visible
 */
export const canViewNavItem = (item) => {
  // Check if user role is 1 (admin/superuser), bypass permission check
  const userData = useCookie("userData").value;
  if (userData && userData.role == 1) return true;

  // If item has permission array, check that first
  if (item.permission && Array.isArray(item.permission)) {
    return hasAnyPermission(item.permission);
  }

  // If it's a heading, check if any items in the following section have permissions
  if (item.heading) {
    return true; // Will be filtered out later if no visible items follow
  }

  // Default to visible if no permissions specified
  return true;
};

/**
 * Check if a navigation group should be visible based on children permissions
 * @param {object} item - Navigation group item to check
 * @returns {boolean} - Whether the group should be visible
 */
export const canViewNavGroup = (item) => {
  // Check if user role is 1 (admin/superuser), bypass permission check
  const userData = useCookie("userData").value;
  if (userData && userData.role == 1) return true;

  // If item itself has permission requirements, check those first
  if (item.permission && Array.isArray(item.permission)) {
    const hasGroupPermission = hasAnyPermission(item.permission);
    if (!hasGroupPermission) return false;
  }

  // Check if any children are visible
  if (item.children && Array.isArray(item.children)) {
    return item.children.some(child => {
      // Recursively check child groups
      if (child.children) {
        return canViewNavGroup(child);
      }
      // Check individual child items
      return canViewNavItem(child);
    });
  }

  // If no children, fall back to item permission check
  return canViewNavItem(item);
};

/**
 * Filter navigation items based on permissions
 * @param {array} navItems - Array of navigation items
 * @returns {array} - Filtered navigation items
 */
export const filterNavItems = (navItems) => {
  const userData = useCookie("userData").value;
  if (userData && userData.role == 1) return navItems; // Admin sees everything

  const filteredItems = [];
  let currentHeading = null;
  let currentSectionItems = [];

  for (let i = 0; i < navItems.length; i++) {
    const item = navItems[i];

    // If it's a heading
    if (item.heading) {
      // First, process any accumulated section
      if (currentHeading && currentSectionItems.length > 0) {
        filteredItems.push(currentHeading, ...currentSectionItems);
      }
      
      // Start a new section
      currentHeading = item;
      currentSectionItems = [];
      continue;
    }

    // Check if the item should be visible
    let shouldShow = false;

    if (item.children) {
      // For groups, check if any children are visible
      shouldShow = canViewNavGroup(item);
      
      // If group is visible, filter its children
      if (shouldShow) {
        const filteredChildren = item.children.filter(child => {
          if (child.children) {
            return canViewNavGroup(child);
          }
          return canViewNavItem(child);
        });
        
        // Only show the group if it has visible children
        if (filteredChildren.length > 0) {
          currentSectionItems.push({
            ...item,
            children: filteredChildren
          });
        }
      }
    } else {
      // For individual items
      shouldShow = canViewNavItem(item);
      if (shouldShow) {
        currentSectionItems.push(item);
      }
    }
  }

  // Add the last section if it has visible items
  if (currentHeading && currentSectionItems.length > 0) {
    filteredItems.push(currentHeading, ...currentSectionItems);
  }

  return filteredItems;
};
