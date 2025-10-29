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
    default: 'account-statement-report'
  },
  headerData: {
    type: Object,
    default: () => ({
      reportTitle: 'Account Statement Report',
      outletName: 'All Outlets',
      dateRange: 'All Time',
      phone: null,
      address: null,
      generatedOn: '',
      generatedBy: 'N/A',
      paymentAccountName: 'All Payment Account'
    })
  },
  summaryData: {
    type: Object,
    default: null
  }
})

const isExporting = ref(false)

// Helper function to format data for export
const formatDataForExport = () => {
  const exportHeaders = props.headers.map(header => header.title)
  const exportData = []

  // Add opening balance row first (if summary data exists)
  if (props.summaryData && props.summaryData.openingBalance !== undefined) {
    const openingRow = {}
    props.headers.forEach(header => {
      if (header.key === 'sn') {
        openingRow[header.title] = ''
      } else if (header.key === 'date') {
        openingRow[header.title] = '-'
      } else if (header.key === 'title') {
        openingRow[header.title] = 'Opening Balance'
      } else if (header.key === 'debit') {
        openingRow[header.title] = '-'
      } else if (header.key === 'credit') {
        openingRow[header.title] = '-'
      } else if (header.key === 'balance') {
        openingRow[header.title] = props.summaryData.openingBalance || 0
      } else if (header.key === 'added_by') {
        openingRow[header.title] = '-'
      } else if (header.key === 'added_date_time') {
        openingRow[header.title] = '-'
      } else {
        openingRow[header.title] = ''
      }
    })
    exportData.push(openingRow)
  }

  // Add all transaction rows
  props.data.forEach((item) => {
    const row = {}
    props.headers.forEach(header => {
      let value = item[header.key] || ''
      
      // Clean up multiline titles for export
      if (header.key === 'title' && typeof value === 'string') {
        value = value.replace(/\n/g, ' - ')
      }
      
      row[header.title] = value
    })
    exportData.push(row)
  })

  // Add summary row (matches Account Balance template)
  if (props.summaryData) {
    const summaryRow = {}
    props.headers.forEach(header => {
      if (header.key === 'sn') {
        summaryRow[header.title] = ''
      } else if (header.key === 'date') {
        summaryRow[header.title] = ''
      } else if (header.key === 'title') {
        summaryRow[header.title] = ''
      } else if (header.key === 'debit') {
        summaryRow[header.title] = `Total Debit: ${props.summaryData?.totalDebit || 0}`
      } else if (header.key === 'credit') {
        summaryRow[header.title] = `Total Credit: ${props.summaryData?.totalCredit || 0}`
      } else if (header.key === 'balance') {
        summaryRow[header.title] = `Closing: ${props.summaryData?.closingBalance || 0}`
      } else if (header.key === 'added_by') {
        summaryRow[header.title] = ''
      } else if (header.key === 'added_date_time') {
        summaryRow[header.title] = ''
      } else {
        summaryRow[header.title] = ''
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

    // Create header info rows (matches Account Balance template)
    const headerInfo = [
      props.headerData.reportTitle,
      '',
      `Outlet: ${props.headerData.outletName}`
    ]
    
    // Add phone and address only if they exist (specific branch selected)
    if (props.headerData.phone) {
      headerInfo.push(`Phone: ${props.headerData.phone}`)
    }
    if (props.headerData.address) {
      headerInfo.push(`Address: ${props.headerData.address}`)
    }
    
    headerInfo.push(
      `Date Range: ${props.headerData.dateRange}`,
      `Generated On: ${props.headerData.generatedOn}`,
      `Generated By: ${props.headerData.generatedBy}`,
      `Payment Account: ${props.headerData.paymentAccountName}`,
      '',
      ''
    )

    const csvContent = [
      ...headerInfo,
      headers.join(','),
      ...data.map(row => 
        headers.map(header => {
          const value = row[header] || ''
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
    const XLSX = await import('xlsx')
    const { headers, data } = formatDataForExport()
    
    // Create header info rows (matches Account Balance template)
    const headerInfo = [
      [props.headerData.reportTitle],
      [],
      ['Outlet:', props.headerData.outletName]
    ]
    
    // Add phone and address only if they exist (specific branch selected)
    if (props.headerData.phone) {
      headerInfo.push(['Phone:', props.headerData.phone])
    }
    if (props.headerData.address) {
      headerInfo.push(['Address:', props.headerData.address])
    }
    
    headerInfo.push(
      ['Date Range:', props.headerData.dateRange],
      ['Generated On:', props.headerData.generatedOn],
      ['Generated By:', props.headerData.generatedBy],
      ['Payment Account:', props.headerData.paymentAccountName],
      []
    )
    
    // Create worksheet with header info
    const ws = XLSX.utils.aoa_to_sheet(headerInfo)
    
    // Add table headers and data
    XLSX.utils.sheet_add_json(ws, data, { 
      origin: `A${headerInfo.length + 1}`,
      skipHeader: false 
    })
    
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1')
    XLSX.writeFile(wb, `${props.filename}.xlsx`)
    toast('Excel file exported successfully', { type: 'success' })
  } catch (error) {
    console.error('Error exporting Excel:', error)
    toast('Failed to export Excel file', { type: 'error' })
  } finally {
    isExporting.value = false
  }
}

// Export to PDF
const exportToPDF = () => {
  try {
    isExporting.value = true
    const { headers, data } = formatDataForExport()
    const doc = new jsPDF('l') // Landscape for wider table
    const pageWidth = doc.internal.pageSize.getWidth()
    
    let currentY = 15
    
    // Report Title
    doc.setFontSize(16)
    doc.setFont(undefined, 'bold')
    doc.text(props.headerData.reportTitle, pageWidth / 2, currentY, { align: 'center' })
    currentY += 10
    
    // Outlet Info (Left Side)
    doc.setFontSize(10)
    doc.setFont(undefined, 'normal')
    doc.text(`Outlet: ${props.headerData.outletName}`, 14, currentY)
    
    // Track the right side Y position
    const rightStartY = currentY
    currentY += 6
    
    // Add phone and address only if they exist (specific branch selected)
    if (props.headerData.phone) {
      doc.text(`Phone: ${props.headerData.phone}`, 14, currentY)
      currentY += 6
    }
    if (props.headerData.address) {
      doc.text(`Address: ${props.headerData.address}`, 14, currentY)
      currentY += 6
    }
    
    // Generated Info (Right Side)
    let rightY = rightStartY
    doc.text(`Date Range: ${props.headerData.dateRange}`, pageWidth - 14, rightY, { align: 'right' })
    rightY += 6
    doc.text(`Generated On: ${props.headerData.generatedOn}`, pageWidth - 14, rightY, { align: 'right' })
    rightY += 6
    doc.text(`Generated By: ${props.headerData.generatedBy}`, pageWidth - 14, rightY, { align: 'right' })
    rightY += 6
    doc.text(`Payment Account: ${props.headerData.paymentAccountName}`, pageWidth - 14, rightY, { align: 'right' })
    
    currentY = Math.max(currentY, rightY) + 4
    
    // Add table
    autoTable(doc, {
      head: [headers],
      body: data.map(row => headers.map(header => row[header] || '')),
      startY: currentY,
      styles: {
        fontSize: 7,
        cellPadding: 2
      },
      headStyles: {
        fillColor: [66, 139, 202],
        textColor: 255,
        fontStyle: 'bold'
      }
    })
    
    doc.save(`${props.filename}.pdf`)
    toast('PDF exported successfully', { type: 'success' })
  } catch (error) {
    console.error('Error exporting PDF:', error)
    toast('Failed to export PDF', { type: 'error' })
  } finally {
    isExporting.value = false
  }
}
</script>

