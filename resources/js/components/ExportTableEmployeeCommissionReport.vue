<template>
  <VMenu>
    <template #activator="{ props }">
      <VBtn 
        v-bind="props"
        variant="tonal" 
        color="secondary" 
        prepend-icon="tabler-upload"
        :loading="isExporting"
      >
        Export
        <VIcon icon="tabler-chevron-down" class="ml-1" />
      </VBtn>
    </template>

    <VList>
      <VListItem
        prepend-icon="tabler-file-type-pdf"
        title="Export as PDF"
        @click="exportToPDF"
      />
      <VListItem
        prepend-icon="tabler-file-type-csv"
        title="Export as CSV"
        @click="exportToCSV"
      />
      <VListItem
        prepend-icon="tabler-file-spreadsheet"
        title="Export as Excel"
        @click="exportToExcel"
      />
    </VList>
  </VMenu>
</template>

<script setup>
import { ref } from 'vue'
import { toast } from 'vue3-toastify'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  },
  headers: {
    type: Array,
    required: true,
    default: () => []
  },
  filename: {
    type: String,
    default: 'export'
  },
  title: {
    type: String,
    default: 'Export Data'
  },
  summaryData: {
    type: Object,
    default: null
  }
})

const isExporting = ref(false)

// Helper function to get nested property value
const getNestedValue = (obj, path) => {
  return path.split('.').reduce((o, p) => o && o[p], obj)
}

// Helper function to format data for export
const formatDataForExport = () => {
  const exportHeaders = props.headers
    .filter(header => header.key !== 'actions')
    .map(header => header.title)

  const exportData = props.data.map(item => {
    const row = {}
    props.headers.forEach(header => {
      if (header.key !== 'actions') {
        let value = getNestedValue(item, header.key)
        
        // Handle special formatting
        if (header.key === 'date' && value) {
          value = new Date(value).toLocaleDateString()
        } else if (header.key.includes('employee') && typeof value === 'object' && value?.name) {
          value = value.name
        } else if (value === null || value === undefined) {
          value = 'N/A'
        }
        
        row[header.title] = value
      }
    })
    return row
  })

  // Add summary row if summaryData is provided
  if (props.summaryData) {
    const summaryRow = {}
    props.headers.forEach(header => {
      if (header.key != 'actions') {
        if (header.key == 'order_date') {
          summaryRow[header.title] = 'TOTAL SUMMARY'
        } else if (header.key == 'subtotal') {
          summaryRow[header.title] = props.summaryData?.totalSubtotal || 0
        } else if (header.key == 'commission_rate') {
          summaryRow[header.title] = props.summaryData?.totalCommissionRate || 0
        } else if (header.key == 'commission_amount') {
          summaryRow[header.title] = props.summaryData?.totalCommissionAmount || 0
        } else {
          summaryRow[header.title] = ''
        }
      }
    })
    exportData.push(summaryRow)
  }

  return { headers: exportHeaders, data: exportData }
}

// Export to CSV
const exportToCSV = () => {
  try {
    isExporting.value = true
    const { headers, data } = formatDataForExport()

    const csvContent = [
      headers.join(','),
      ...data.map(row => 
        headers.map(header => {
          const value = row[header] || ''
          // Escape quotes and wrap in quotes if contains comma
          return typeof value === 'string' && (value.includes(',') || value.includes('"'))
            ? `"${value.replace(/"/g, '""')}"`
            : value
        }).join(',')
      )
    ].join('\n')
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `${props.filename}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    toast('CSV exported successfully', { type: 'success' })
  } catch (error) {
    console.error('Error exporting CSV:', error)
    toast('Failed to export CSV', { type: 'error' })
  } finally {
    isExporting.value = false
  }
}

// Export to Excel
const exportToExcel = async () => {
  try {
    isExporting.value = true
    
    // Dynamic import to avoid bundling if not used
    const XLSX = await import('xlsx')
    
    const { headers, data } = formatDataForExport()
    
    // Create worksheet
    const ws = XLSX.utils.json_to_sheet(data)
    
    // Create workbook
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1')
    
    // Save file
    XLSX.writeFile(wb, `${props.filename}.xlsx`)
    
    toast('Excel file exported successfully', { type: 'success' })
  } catch (error) {
    console.error('Error exporting Excel:', error)
    toast('Failed to export Excel file. Please install xlsx package.', { type: 'error' })
  } finally {
    isExporting.value = false
  }
}

// Export to PDF
const exportToPDF = () => {
  try {
    isExporting.value = true

    const { headers, data } = formatDataForExport()
    const doc = new jsPDF()

    doc.setFontSize(16)
    doc.text(props.title, 14, 15)

    autoTable(doc, {
      head: [headers],
      body: data.map(row => headers.map(header => row[header] || '')),
      startY: 25,
      styles: {
        fontSize: 8,
        cellPadding: 2
      },
      headStyles: {
        fillColor: [66, 139, 202],
        textColor: 255
      }
    })

    doc.save(`${props.filename}.pdf`)
    toast('PDF exported successfully', { type: 'success' })
  } catch (error) {
    console.error('Error exporting PDF:', error)
    toast('Failed to export PDF. Please install jspdf and jspdf-autotable packages.', { type: 'error' })
  } finally {
    isExporting.value = false
  }
}
</script> 
