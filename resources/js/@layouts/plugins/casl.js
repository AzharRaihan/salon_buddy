import { hasPermission } from "@/utils/permissions";
import { useAbility } from "@casl/vue";
import { getCurrentInstance } from "vue";

/**
 * Returns ability result if ACL is configured or else just return true
 * We should allow passing string | undefined to can because for admin ability we omit defining action & subject
 *
 * Useful if you don't know if ACL is configured or not
 * Used in @core files to handle absence of ACL without errors
 *
 * @param {string} action CASL Actions // https://casl.js.org/v4/en/guide/intro#basics
 * @param {string} subject CASL Subject // https://casl.js.org/v4/en/guide/intro#basics
 */
export const can = (action, subject) => {
  // Check if user role is 1 (admin/superuser), bypass permission check
  const userData = useCookie("userData").value;
  if (userData && userData.role === 1) return true;

  // If subject is provided but action is not, treat subject as permission
  if (subject && !action) {
    return hasPermission(subject);
  }

  // Check if the action is a permission string (for compatibility with our navigation setup)
  if (action && !subject && typeof action === "string") {
    return hasPermission(action);
  }

  // Original CASL implementation
  const vm = getCurrentInstance();
  if (!vm) return false;
  const localCan = vm.proxy && "$can" in vm.proxy;

  return localCan ? vm.proxy?.$can(action, subject) : true;
};

/**
 * Check if user can view item based on it's ability
 * Based on item's action and subject & Hide group if all of it's children are hidden
 * @param {object} item navigation object item
 */
export const canViewNavMenuGroup = (item) => {
  // Check if user role is 1 (admin/superuser), bypass permission check
  const userData = useCookie("userData").value;
  if (userData && userData.role === 1) return true;

  // If permission array is present on the item, check that first
  if (item.permission && Array.isArray(item.permission)) {
    // Check if user has any of the permissions required
    const hasPermissions = item.permission.some((perm) => hasPermission(perm));
    if (!hasPermissions) return false;
  }

  const hasAnyVisibleChild = item.children.some((i) => {
    // Check child's permission array if present
    if (i.permission && Array.isArray(i.permission)) {
      return i.permission.some((perm) => hasPermission(perm));
    }
    return can(i.action, i.subject);
  });

  // If subject and action is defined in item => Return based on children visibility (Hide group if no child is visible)
  // Else check for ability using provided subject and action along with checking if has any visible child
  if (!(item.action && item.subject)) return hasAnyVisibleChild;

  return can(item.action, item.subject) && hasAnyVisibleChild;
};

export const canNavigate = (to) => {
  // Check if user role is 1 (admin/superuser), bypass permission check
  const userData = useCookie("userData").value;
  if (userData && userData.role === 1) return true;

  const ability = useAbility();

  // Get the most specific route (last one in the matched array)
  const targetRoute = to.matched[to.matched.length - 1];

  // If the target route has specific permissions, check those first
  if (targetRoute?.meta?.action && targetRoute?.meta?.subject)
    return ability.can(targetRoute.meta.action, targetRoute.meta.subject);

  // If no specific permissions, fall back to checking if any parent route allows access

  return to.matched.some((route) =>
    ability.can(route.meta.action, route.meta.subject)
  );
};
