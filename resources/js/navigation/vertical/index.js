export default [
  {
    title: "Home",
    to: { name: "home" },
    icon: { icon: "tabler-home" },
  },
  {
    title: "Dashboard",
    to: { name: "dashboard" }, 
    icon: { icon: "tabler-gauge" },
    permission: ["dashboard"],
  },
  {
    title: "Branch",
    icon: { icon: "tabler-layout-grid" },
    children: [
      { title: "Add Branch", to: { name: "branch-create" }, permission: ["branch-create"] },
      { title: "List Branch", to: { name: "branch" }, permission: ["branch-list"] },
    ],
  },
  {
    title: "Booking",
    icon: { icon: "tabler-calendar-event" },
    children: [
      { title: "Add Booking", to: { name: "booking-create" }, permission: ["booking-create"]},
      { title: "List Booking", to: { name: "booking" }, permission: ["booking-list"] },
      { title: "Booking Calendar", to: { name: "booking-calendar" }, permission: ["booking-calendar"]},
    ],
  },
  { heading: 'Item & Stock'},
  {
    title: "Item",
    icon: { icon: "tabler-box" },
    children: [
      { title: "Add Unit", to: { name: "unit-create" }, permission: ["unit-create"] },
      { title: "List Unit", to: { name: "unit" }, permission: ["unit-list"] },
      { title: "Add Category", to: { name: "category-create" }, permission: ["category-create"] },
      { title: "List Category", to: { name: "category" }, permission: ["category-list"] },
      { title: "Add Item", to: { name: "item-create" }, permission: ["item-create"] },
      { title: "List Item", to: { name: "item" }, permission: ["item-list"] },
    ],
  },
  {
    title: "Stock",
    icon: { icon: "tabler-replace" },
    children: [
      { title: "Stock", to: { name: "stock-stock" }, permission: ["stock-stock"] },
      { title: "Alert Stock", to: { name: "stock-alert-stock" }, permission: ["stock-alert"] },
      { title: "Product Usages", to: { name: "stock-product-usages" }, permission: ["stock-product-usages"] },
    ],
  },
  { heading: 'Sales & Customers' },
  {
    title: "Sale",
    icon: { icon: "tabler-shopping-cart-heart" },
    children: [
      { title: "List Sale", to: { name: "sale" }, permission: ["sale-list"] },
      { title: "Add Promotion", to: { name: "promotion-create" }, permission: ["promotion-create"] },
      { title: "List Promotion", to: { name: "promotion" }, permission: ["promotion-list"] },
    ],
  },
  {
    title: "Customer",
    icon: { icon: "tabler-users" },
    children: [
      { title: "Add Customer", to: { name: "customer-create" }, permission: ["customer-create"] },
      { title: "List Customer", to: { name: "customer" }, permission: ["customer-list"] },
      { title: "Add Customer Receive", to: { name: "customer-receive-create" }, permission: ["customer_receive-create"] },
      { title: "List Customer Receive", to: { name: "customer-receive" }, permission: ["customer_receive-list"] },
    ],
  },
  { heading: 'Purchase & Expense' },
  {
    title: "Purchase",
    icon: { icon: "tabler-basket-down" },
    children: [
      { title: "Add Purchase", to: { name: "purchase-create" }, permission: ["purchase-create"] },
      { title: "List Purchase", to: { name: "purchase" }, permission: ["purchase-list"] },
    ],
  },
  {
    title: "Supplier",
    icon: { icon: "tabler-user-dollar" },
    children: [
      { title: "Add Supplier", to: { name: "supplier-create" }, permission: ["supplier-create"] },
      { title: "List Supplier", to: { name: "supplier" }, permission: ["supplier-list"] },
      { title: "Add Supplier Payment", to: { name: "supplier-payment-create" }, permission: ["supplier_payment-create"] },
      { title: "List Supplier Payment", to: { name: "supplier-payment" }, permission: ["supplier_payment-list"] },
    ],
  },
  {
    title: "Expense",
    icon: { icon: "tabler-circle-minus" },
    children: [
      { title: "Add Expense", to: { name: "expense-create" }, permission: ["expense-create"] },
      { title: "List Expense", to: { name: "expense" }, permission: ["expense-list"] },
      { title: "Add Expense Category", to: { name: "expense-category-create" }, permission: ["expense_category-create"] },
      { title: "List Expense Category", to: { name: "expense-category" }, permission: ["expense_category-list"] },
    ],
  },
  {
    title: "Damage",
    icon: { icon: "tabler-trash" },
    children: [
      { title: "Add Damage", to: { name: "damage-create" }, permission: ["damage-create"] },
      { title: "List Damage", to: { name: "damage" }, permission: ["damage-list"] },
    ],
  },
  { heading: 'Accounting' },
  {
    title: "Accounting",
    icon: { icon: "tabler-receipt-dollar" },
    children: [
      { title: "Add Payment Method", to: { name: "payment-method-create" }, permission: ["payment_method-create"] },
      { title: "List Payment Method", to: { name: "payment-method" }, permission: ["payment_method-list"] },
    ],
  },
  
  { heading: 'Employee Management' },
  {
    title: "Roles & Permissions",
    icon: { icon: "tabler-lock" },
    children: [
      { title: "Add Role", to: { name: "role-create" }, permission: ["role-create"] },
      { title: "List Role", to: { name: "role" }, permission: ["role-list"] },
    ],
  },
  {
    title: "Employee",
    icon: { icon: "tabler-user" },
    children: [
      { title: "Add Employee", to: { name: "employee-create" }, permission: ["employee-create"] },
      { title: "List Employee", to: { name: "employee" }, permission: ["employee-list"] },
    ],
  },
  {
    title: "HRM",
    icon: { icon: "tabler-hospital-circle" },
    
    children: [
      { title: "Add Attendance", to: { name: "attendance-create" }, permission: ["attendance-create"] },
      { title: "List Attendance", to: { name: "attendance" }, permission: ["attendance-list"] },
      { title: "Add Salary", to: { name: "salary-create" }, permission: ["salary-create"] },
      { title: "List Salary", to: { name: "salary" }, permission: ["salary-list"] },
    ],
  },
  { heading: 'Settings & Reports' },
  {
    title: "Report",
    icon: { icon: "tabler-book-2" },
    children: [
      { title: "Daily Summary Report", to: { name: "report-daily-summary-report" }, permission: ["report-daily_summary"] },
      { title: "Profit & Loss Report", to: { name: "report-profit-loss-report" }, permission: ["report-profit-loss"] },
      { title: "Sales Report", to: { name: "report-sales-report" }, permission: ["report-sale"] },
      { title: "Purchase Report", to: { name: "report-purchase-report" }, permission: ["report-purchase"] },
      { title: "Stock Report", to: { name: "report-stock-report" }, permission: ["report-stock"] },
      { title: "Employee Commission Report", to: { name: "report-employee-commission-report" }, permission: ["report-commission"] },
      { title: "Expense Report", to: { name: "report-expense-report" }, permission: ["report-expense"] },
      { title: "Damage Report", to: { name: "report-damage-report" }, permission: ["report-damage"] },
      { title: "Salary Report", to: { name: "report-salary-report" }, permission: ["report-salary"] },
      { title: "Attendance Report", to: { name: "report-attendance-report" }, permission: ["report-attendance"] },
    ],
  },
  {
    title: "Settings",
    icon: { icon: "tabler-settings" },
    children: [
      { title: "Setting", to: { name: "settings-company" }, permission: ["settings-settings"] },
      { title: "Tax Setting", to: { name: "settings-tax-setting" }, permission: ["settings-tax"] },
      { title: "White Label", to: { name: "settings-white-label" }, permission: ["settings-white_label"] },
      { title: "Email Settings", to: { name: "settings-email" }, permission: ["settings-email"] },
      { title: "SMS Settings", to: { name: "settings-sms" }, permission: ["settings-sms"] },
      { title: "Whatsapp Settings", to: { name: "settings-whatsapp" }, permission: ["settings-whatsapp"] },
      { title: "Payment Settings", to: { name: "settings-payment" }, permission: ["settings-payment"] },
      { title: "Vacation", to: { name: "vacation" }, permission: ["settings-vacation"] },
      { title: "Holiday", to: { name: "holiday" }, permission: ["settings-holiday"] },
    ],
  },
  {
    title: "Website Settings",
    icon: { icon: "tabler-globe" },
    children: [
      { title: "Website Settings", to: { name: "website-settings" }, permission: ["website-settings"] },
      { title: "About Us", to: { name: "website-about-us" }, permission: ["website-aboutus"] },
      { title: "Add Delivery Area", to: { name: "website-delivery-area-create" }, permission: ["website-delivery_area"] },
      { title: "List Delivery Area", to: { name: "website-delivery-area" }, permission: ["website-delivery_area"] },
      { title: "Add Delivery Partner", to: { name: "website-delivery-partner-create" }, permission: ["website-delivery_partner"] },
      { title: "List Delivery Partner", to: { name: "website-delivery-partner" }, permission: ["website-delivery_partner"] },
      { title: "Add Banner", to: { name: "website-banner-create" }, permission: ["website-banner"] },
      { title: "List Banner", to: { name: "website-banner" }, permission: ["website-banner"] },
      { title: "Add FAQ", to: { name: "website-faq-create" }, permission: ["website-faq"] },
      { title: "List FAQ", to: { name: "website-faq" }, permission: ["website-faq"] },
      { title: "Add Working Process", to: { name: "website-workingprocess-create" }, permission: ["website-workingprocess"] },
      { title: "List Working Process", to: { name: "website-workingprocess" }, permission: ["website-workingprocess"] },
      { title: "Add Portfolio", to: { name: "website-portfolio-create" }, permission: ["website-portfolio"] },
      { title: "List Portfolio", to: { name: "website-portfolio" }, permission: ["website-portfolio"] },
    ],
  },

];
