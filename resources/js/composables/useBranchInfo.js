// useBranchInfo.js
import { ref, watch } from 'vue'

const branchInfo = ref(useCookie('branch_info').value || 0)

watch(branchInfo, (newVal) => {
  useCookie('branch_info').value = newVal
}, { deep: true })

export function useBranchInfo() {
  return { branchInfo }
}