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
  assets: {
    type: Array,
    required: true,
    default: () => []
  },
  liabilities: {
    type: Array,
    required: true,
    default: () => []
  },
  filename: {
    type: String,
    default: 'balance-sheet-report'
  },
  title: {
    type: String,
    default: 'Balance Sheet Report'
  },
  summaryData: {
    type: Object,
    default: null
  }
})

const isExporting = ref(false)

// Export to CSV
const exportToCSV = () => {
  try {
    isExporting.value = true
    
    let csvContent = 'ASSETS\n'
    csvContent += 'SN,Title,Amount\n'
    props.assets.forEach(item => {
      csvContent += `${item.sn},${item.title},${item.amount}\n`
    })
    csvContent += `,,Total Assets: ${props.summaryData?.totalAssets || 0}\n\n`
    
    csvContent += 'LIABILITIES\n'
    csvContent += 'SN,Title,Amount\n'
    props.liabilities.forEach(item => {
      csvContent += `${item.sn},${item.title},${item.amount}\n`
    })
    csvContent += `,,Total Liabilities: ${props.summaryData?.totalLiabilities || 0}\n\n`
    csvContent += `,,Net Worth: ${props.summaryData?.netWorth || 0}\n`
    
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
    
    // Create assets sheet
    const assetsData = props.assets.map(item => ({
      SN: item.sn,
      Title: item.title,
      Amount: item.amount
    }))
    assetsData.push({ SN: '', Title: 'Total Assets', Amount: props.summaryData?.totalAssets || 0 })
    
    // Create liabilities sheet
    const liabilitiesData = props.liabilities.map(item => ({
      SN: item.sn,
      Title: item.title,
      Amount: item.amount
    }))
    liabilitiesData.push({ SN: '', Title: 'Total Liabilities', Amount: props.summaryData?.totalLiabilities || 0 })
    liabilitiesData.push({ SN: '', Title: 'Net Worth', Amount: props.summaryData?.netWorth || 0 })
    
    // Combine data
    const combinedData = [
      { SN: 'ASSETS', Title: '', Amount: '' },
      ...assetsData,
      { SN: '', Title: '', Amount: '' },
      { SN: 'LIABILITIES', Title: '', Amount: '' },
      ...liabilitiesData
    ]
    
    const ws = XLSX.utils.json_to_sheet(combinedData)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, 'Balance Sheet')
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
    const doc = new jsPDF()
    
    doc.setFontSize(16)
    doc.text(props.title, 14, 15)
    
    // Assets table
    doc.setFontSize(14)
    doc.text('ASSETS', 14, 30)
    
    autoTable(doc, {
      head: [['SN', 'Title', 'Amount']],
      body: props.assets.map(item => [item.sn, item.title, item.amount]),
      startY: 35,
      styles: { fontSize: 8, cellPadding: 2 },
      headStyles: { fillColor: [40, 167, 69], textColor: 255 }
    })
    
    const assetsEndY = doc.lastAutoTable.finalY
    doc.text(`Total Assets: ${props.summaryData?.totalAssets || 0}`, 14, assetsEndY + 10)
    
    // Liabilities table
    doc.setFontSize(14)
    doc.text('LIABILITIES', 14, assetsEndY + 20)
    
    autoTable(doc, {
      head: [['SN', 'Title', 'Amount']],
      body: props.liabilities.map(item => [item.sn, item.title, item.amount]),
      startY: assetsEndY + 25,
      styles: { fontSize: 8, cellPadding: 2 },
      headStyles: { fillColor: [220, 53, 69], textColor: 255 }
    })
    
    const liabilitiesEndY = doc.lastAutoTable.finalY
    doc.text(`Total Liabilities: ${props.summaryData?.totalLiabilities || 0}`, 14, liabilitiesEndY + 10)
    doc.text(`Net Worth: ${props.summaryData?.netWorth || 0}`, 14, liabilitiesEndY + 20)
    
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

