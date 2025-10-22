<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import * as XLSX from 'xlsx'
import { jsPDF } from 'jspdf'
import 'jspdf-autotable'
import { useCompanyFormatters } from '@/composables/useCompanyFormatters'

const { t } = useI18n()
const { formatAmount } = useCompanyFormatters()

// Props
const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  headers: {
    type: Array,
    required: true,
  },
  filename: {
    type: String,
    default: 'transaction-history-report',
  },
  title: {
    type: String,
    default: 'Transaction History Report',
  },
  summaryData: {
    type: Object,
    default: () => ({}),
  },
})

// State
const isExportMenuOpen = ref(false)

// Get company name from cookie
const getCompanyName = () => {
  const companySettings = useCookie('company_settings').value
  return companySettings?.company_name || 'Company Name'
}

// Export to Excel
const exportToExcel = () => {
  if (!props.data || props.data.length === 0) {
    return
  }

  // Prepare data for Excel
  const excelData = props.data.map(item => {
    const row = {}
    props.headers.forEach(header => {
      const value = item[header.key]
      // Format amount if it's the amount field
      if (header.key === 'amount') {
        row[header.title] = typeof value === 'number' ? value : value
      } else {
        row[header.title] = value
      }
    })
    return row
  })

  // Add summary rows
  if (props.summaryData) {
    excelData.push({}) // Empty row
    excelData.push({
      [props.headers[0].title]: 'Summary',
    })
    excelData.push({
      [props.headers[0].title]: 'Total Transactions',
      [props.headers[1].title]: props.summaryData.totalTransactions || 0,
    })
    excelData.push({
      [props.headers[0].title]: 'Total Amount',
      [props.headers[1].title]: props.summaryData.totalAmount || 0,
    })
  }

  // Create workbook and worksheet
  const ws = XLSX.utils.json_to_sheet(excelData)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Transaction History')

  // Save file
  XLSX.writeFile(wb, `${props.filename}.xlsx`)
  isExportMenuOpen.value = false
}

// Export to PDF
const exportToPDF = () => {
  if (!props.data || props.data.length === 0) {
    return
  }

  const doc = new jsPDF()
  const companyName = getCompanyName()

  // Add title
  doc.setFontSize(18)
  doc.text(companyName, 14, 15)
  doc.setFontSize(14)
  doc.text(props.title, 14, 25)

  // Prepare table data
  const tableData = props.data.map(item => {
    return props.headers.map(header => {
      const value = item[header.key]
      if (header.key === 'amount') {
        return typeof value === 'number' ? formatAmount(value) : value
      }
      return value || ''
    })
  })

  // Add table
  doc.autoTable({
    head: [props.headers.map(h => h.title)],
    body: tableData,
    startY: 30,
    theme: 'grid',
    styles: {
      fontSize: 8,
      cellPadding: 2,
    },
    headStyles: {
      fillColor: [66, 139, 202],
      textColor: 255,
      fontStyle: 'bold',
    },
  })

  // Add summary
  if (props.summaryData) {
    const finalY = doc.lastAutoTable.finalY + 10
    doc.setFontSize(10)
    doc.setFont(undefined, 'bold')
    doc.text('Summary:', 14, finalY)
    doc.setFont(undefined, 'normal')
    doc.text(`Total Transactions: ${props.summaryData.totalTransactions || 0}`, 14, finalY + 7)
    doc.text(`Total Amount: ${formatAmount(props.summaryData.totalAmount || 0)}`, 14, finalY + 14)
  }

  // Save PDF
  doc.save(`${props.filename}.pdf`)
  isExportMenuOpen.value = false
}

// Print
const printReport = () => {
  window.print()
  isExportMenuOpen.value = false
}
</script>

<template>
  <VMenu
    v-model="isExportMenuOpen"
    :close-on-content-click="false"
  >
    <template #activator="{ props: menuProps }">
      <VBtn
        v-bind="menuProps"
        prepend-icon="tabler-download"
        variant="outlined"
        color="primary"
      >
        {{ t('Export') }}
      </VBtn>
    </template>

    <VList>
      <VListItem
        @click="exportToExcel"
      >
        <template #prepend>
          <VIcon icon="tabler-file-spreadsheet" />
        </template>
        <VListItemTitle>{{ t('Export to Excel') }}</VListItemTitle>
      </VListItem>

      <VListItem
        @click="exportToPDF"
      >
        <template #prepend>
          <VIcon icon="tabler-file-type-pdf" />
        </template>
        <VListItemTitle>{{ t('Export to PDF') }}</VListItemTitle>
      </VListItem>

      <VListItem
        @click="printReport"
      >
        <template #prepend>
          <VIcon icon="tabler-printer" />
        </template>
        <VListItemTitle>{{ t('Print') }}</VListItemTitle>
      </VListItem>
    </VList>
  </VMenu>
</template>

