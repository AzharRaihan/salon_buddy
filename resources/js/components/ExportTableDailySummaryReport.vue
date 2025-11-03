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
  sales: {
    type: Array,
    default: () => []
  },
  purchases: {
    type: Array,
    default: () => []
  },
  supplierDuePayments: {
    type: Array,
    default: () => []
  },
  customerDueReceives: {
    type: Array,
    default: () => []
  },
  filename: {
    type: String,
    default: 'daily-summary-report'
  },
  title: {
    type: String,
    default: 'Daily Summary Report'
  },
  headerData: {
    type: Object,
    default: () => ({
      reportTitle: 'Daily Summary Report',
      outletName: 'All Outlets',
      phone: null,
      address: null,
      date: 'N/A',
      generatedOn: '',
      generatedBy: 'N/A'
    })
  }
})

const isExporting = ref(false)

// Helper function to format data for export
const formatDataForExport = () => {
  const allData = []
  
  // Add Sales section
  if (props.sales && props.sales.length > 0) {
    allData.push({ type: 'heading', label: 'SALES', colspan: 6 })
    props.sales.forEach(item => {
      allData.push({
        invoice_no: item.invoice_no || 'N/A',
        date: item.date || 'N/A',
        customer: item.customer_name || 'N/A',
        total: item.total_payable || 0,
        paid: item.total_paid || 0,
        due: item.total_due || 0
      })
    })
    // Add summary row
    const totalSales = props.sales.reduce((sum, s) => sum + (s.total_payable || 0), 0)
    const totalPaidSales = props.sales.reduce((sum, s) => sum + (s.total_paid || 0), 0)
    const totalDueSales = props.sales.reduce((sum, s) => sum + (s.total_due || 0), 0)
    allData.push({
      type: 'summary',
      invoice_no: 'TOTAL SALES',
      date: '',
      customer: '',
      total: totalSales,
      paid: totalPaidSales,
      due: totalDueSales
    })
  }
  
  // Add Purchases section
  if (props.purchases && props.purchases.length > 0) {
    allData.push({ type: 'heading', label: 'PURCHASES', colspan: 6 })
    props.purchases.forEach(item => {
      allData.push({
        invoice_no: item.invoice_no || 'N/A',
        date: item.date || 'N/A',
        supplier: item.supplier_name || 'N/A',
        total: item.grand_total || 0,
        paid: item.paid_amount || 0,
        due: item.due_amount || 0
      })
    })
    // Add summary row
    const totalPurchases = props.purchases.reduce((sum, p) => sum + (p.grand_total || 0), 0)
    const totalPaidPurchases = props.purchases.reduce((sum, p) => sum + (p.paid_amount || 0), 0)
    const totalDuePurchases = props.purchases.reduce((sum, p) => sum + (p.due_amount || 0), 0)
    allData.push({
      type: 'summary',
      invoice_no: 'TOTAL PURCHASES',
      date: '',
      supplier: '',
      total: totalPurchases,
      paid: totalPaidPurchases,
      due: totalDuePurchases
    })
  }
  
  // Add Supplier Due Payments section
  if (props.supplierDuePayments && props.supplierDuePayments.length > 0) {
    allData.push({ type: 'heading', label: 'SUPPLIER DUE PAYMENTS', colspan: 4 })
    props.supplierDuePayments.forEach(item => {
      allData.push({
        name: item.supplier_name || 'N/A',
        date: item.date || 'N/A',
        amount: item.amount || 0,
        note: item.note || 'N/A'
      })
    })
    const totalSupplierDue = props.supplierDuePayments.reduce((sum, p) => sum + (p.amount || 0), 0)
    allData.push({
      type: 'summary',
      name: 'TOTAL SUPPLIER DUE',
      date: '',
      amount: totalSupplierDue,
      note: ''
    })
  }
  
  // Add Customer Due Receives section
  if (props.customerDueReceives && props.customerDueReceives.length > 0) {
    allData.push({ type: 'heading', label: 'CUSTOMER DUE RECEIVES', colspan: 4 })
    props.customerDueReceives.forEach(item => {
      allData.push({
        name: item.customer_name || 'N/A',
        date: item.date || 'N/A',
        amount: item.amount || 0,
        note: item.note || 'N/A'
      })
    })
    const totalCustomerDue = props.customerDueReceives.reduce((sum, r) => sum + (r.amount || 0), 0)
    allData.push({
      type: 'summary',
      name: 'TOTAL CUSTOMER DUE',
      date: '',
      amount: totalCustomerDue,
      note: ''
    })
  }
  
  return allData
}

// Export to CSV
const exportToCSV = () => {
  try {
    isExporting.value = true
    const data = formatDataForExport()

    // Create header info rows
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
      `Date: ${props.headerData.date}`,
      `Generated On: ${props.headerData.generatedOn}`,
      `Generated By: ${props.headerData.generatedBy}`,
      '',
      ''
    )

    const csvLines = []
    
    // Process data
    data.forEach(item => {
      if (item.type === 'heading') {
        csvLines.push(item.label)
        if (item.label === 'SALES') {
          csvLines.push('Invoice No,Date,Customer,Total,Paid,Due')
        } else if (item.label === 'PURCHASES') {
          csvLines.push('Invoice No,Date,Supplier,Total,Paid,Due')
        } else {
          csvLines.push('Name,Date,Amount,Note')
        }
      } else if (item.type === 'summary') {
        if (item.total !== undefined) {
          csvLines.push(`"${item.invoice_no}","${item.date}","${item.customer || item.supplier || ''}","${item.total}","${item.paid}","${item.due}"`)
        } else {
          csvLines.push(`"${item.name}","${item.date}","${item.amount}","${item.note}"`)
        }
        csvLines.push('')
      } else {
        if (item.total !== undefined) {
          csvLines.push(`"${item.invoice_no}","${item.date}","${item.customer || item.supplier || 'N/A'}","${item.total}","${item.paid}","${item.due}"`)
        } else {
          csvLines.push(`"${item.name}","${item.date}","${item.amount}","${item.note}"`)
        }
      }
    })

    const csvContent = [
      ...headerInfo,
      ...csvLines
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
    const data = formatDataForExport()
    
    // Create header info rows
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
      ['Date:', props.headerData.date],
      ['Generated On:', props.headerData.generatedOn],
      ['Generated By:', props.headerData.generatedBy],
      []
    )
    
    // Create worksheet with header info
    const ws = XLSX.utils.aoa_to_sheet(headerInfo)
    
    // Add table data
    let currentRow = headerInfo.length + 1
    
    data.forEach(item => {
      if (item.type === 'heading') {
        XLSX.utils.sheet_add_aoa(ws, [[item.label]], { origin: `A${currentRow}` })
        currentRow++
        if (item.label === 'SALES') {
          XLSX.utils.sheet_add_aoa(ws, [['Invoice No', 'Date', 'Customer', 'Total', 'Paid', 'Due']], { origin: `A${currentRow}` })
        } else if (item.label === 'PURCHASES') {
          XLSX.utils.sheet_add_aoa(ws, [['Invoice No', 'Date', 'Supplier', 'Total', 'Paid', 'Due']], { origin: `A${currentRow}` })
        } else {
          XLSX.utils.sheet_add_aoa(ws, [['Name', 'Date', 'Amount', 'Note']], { origin: `A${currentRow}` })
        }
        currentRow++
      } else if (item.type === 'summary') {
        if (item.total !== undefined) {
          XLSX.utils.sheet_add_aoa(ws, [[item.invoice_no, item.date, item.customer || item.supplier || '', item.total, item.paid, item.due]], { origin: `A${currentRow}` })
        } else {
          XLSX.utils.sheet_add_aoa(ws, [[item.name, item.date, item.amount, item.note]], { origin: `A${currentRow}` })
        }
        currentRow++
        XLSX.utils.sheet_add_aoa(ws, [[]], { origin: `A${currentRow}` })
        currentRow++
      } else {
        if (item.total !== undefined) {
          XLSX.utils.sheet_add_aoa(ws, [[item.invoice_no, item.date, item.customer || item.supplier || 'N/A', item.total, item.paid, item.due]], { origin: `A${currentRow}` })
        } else {
          XLSX.utils.sheet_add_aoa(ws, [[item.name, item.date, item.amount, item.note]], { origin: `A${currentRow}` })
        }
        currentRow++
      }
    })
    
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
    const data = formatDataForExport()
    const doc = new jsPDF()
    const pageWidth = doc.internal.pageSize.getWidth()
    const pageHeight = doc.internal.pageSize.getHeight()
    
    let currentY = 15
    
    // Report Title
    doc.setFontSize(16)
    doc.setFont(undefined, 'bold')
    doc.text(props.headerData.reportTitle, pageWidth / 2, 15, { align: 'center' })
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
    doc.text(`Date: ${props.headerData.date}`, pageWidth - 14, rightStartY, { align: 'right' })
    doc.text(`Generated On: ${props.headerData.generatedOn}`, pageWidth - 14, rightStartY + 6, { align: 'right' })
    doc.text(`Generated By: ${props.headerData.generatedBy}`, pageWidth - 14, rightStartY + 12, { align: 'right' })
    
    // Calculate the maximum Y position from both left and right sides
    const rightEndY = rightStartY + 6
    const maxY = Math.max(currentY, rightEndY)
    
    // Add spacing before table (ensure there's enough gap)
    currentY = maxY + 6
    
    // Process and add tables section by section
    let currentSectionType = ''
    let currentTableHead = []
    let currentTableBody = []
    let currentSectionLabel = ''
    
    const processTable = () => {
      // Always draw table headers, even if body is empty
      if (currentTableHead.length > 0) {
        // Check if we need a new page
        if (currentY > pageHeight - 60) {
          doc.addPage()
          currentY = 20
        }
        
        // Convert body to array format for autoTable
        const tableBody = currentTableBody.map(row => {
          if (typeof row === 'object' && row.content) {
            return row.content
          }
          return row
        })
        
        // Store reference to original rows for styling and check if we have data
        const originalRows = [...currentTableBody]
        const hasData = tableBody.length > 0
        
        // Create empty data row with correct column count if no data
        const emptyRow = currentTableHead.map(() => 'No data available')
        
        autoTable(doc, {
          head: [currentTableHead],
          body: hasData ? tableBody : [emptyRow],
          startY: currentY,
          styles: {
            fontSize: 8,
            cellPadding: 2,
            overflow: 'linebreak'
          },
          headStyles: {
            fillColor: [66, 139, 202],
            textColor: 255,
            fontStyle: 'bold'
          },
          bodyStyles: {
            textColor: [0, 0, 0]
          },
          didParseCell: function (data) {
            // Style summary rows - find the original row data
            if (hasData && data.row.index < originalRows.length) {
              const originalRow = originalRows[data.row.index]
              if (originalRow && originalRow.raw && originalRow.raw.isSummary) {
                data.cell.styles.fillColor = [230, 242, 255]
                data.cell.styles.fontStyle = 'bold'
              }
            }
            // Style "No data available" rows
            if (!hasData) {
              if (data.column.index === 0) {
                data.cell.colSpan = currentTableHead.length
                data.cell.styles.textColor = [128, 128, 128]
                data.cell.styles.fontStyle = 'italic'
                data.cell.styles.halign = 'center'
              } else {
                // Hide other columns when first column spans all
                data.cell.styles.cellWidth = 0
              }
            }
          },
          didDrawPage: function (data) {
            // Update currentY after table is drawn
            currentY = data.cursor.y + 10
          }
        })
        
        // Update currentY after table is drawn
        currentY = doc.lastAutoTable.finalY + 10
      }
    }
    
    data.forEach(item => {
      if (item.type === 'heading') {
        // Process previous table if exists
        if (currentSectionLabel) {
          processTable()
        }
        
        // Reset for new section
        currentTableBody = []
        currentSectionLabel = item.label
        
        // Section heading
        // Check if we need a new page
        if (currentY > pageHeight - 40) {
          doc.addPage()
          currentY = 20
        }
        
        doc.setFontSize(12)
        doc.setFont(undefined, 'bold')
        doc.text(item.label, 14, currentY)
        currentY += 8
        
        // Set up table headers based on section type
        if (item.label === 'SALES') {
          currentTableHead = ['Invoice No', 'Date', 'Customer', 'Total', 'Paid', 'Due']
          currentSectionType = 'sales_purchases'
        } else if (item.label === 'PURCHASES') {
          currentTableHead = ['Invoice No', 'Date', 'Supplier', 'Total', 'Paid', 'Due']
          currentSectionType = 'sales_purchases'
        } else {
          currentTableHead = ['Name', 'Date', 'Amount', 'Note']
          currentSectionType = 'dues'
        }
      } else {
        // Add row to current table (data row or summary row)
        // Only add if we have a valid section type
        if (!currentSectionType) {
          console.warn('Trying to add row without section type set')
          return
        }
        
        const isSummary = item.type === 'summary'
        
        if (currentSectionType === 'sales_purchases') {
          const rowData = [
            item.invoice_no || '',
            item.date || '',
            item.customer || item.supplier || '',
            parseFloat(item.total || 0).toFixed(2),
            parseFloat(item.paid || 0).toFixed(2),
            parseFloat(item.due || 0).toFixed(2)
          ]
          
          // Create row object with raw data for styling
          const row = {
            content: rowData,
            raw: { ...item, isSummary: isSummary }
          }
          
          currentTableBody.push(row)
        } else if (currentSectionType === 'dues') {
          const rowData = [
            item.name || '',
            item.date || '',
            parseFloat(item.amount || 0).toFixed(2),
            item.note || ''
          ]
          
          // Create row object with raw data for styling
          const row = {
            content: rowData,
            raw: { ...item, isSummary: isSummary }
          }
          
          currentTableBody.push(row)
        }
      }
    })
    
    // Process the last table
    if (currentSectionLabel) {
      processTable()
    }

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

