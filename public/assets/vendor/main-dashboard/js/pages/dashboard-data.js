/* Frontend-only sample data. Replace this object with Laravel API responses later. */
window.HimsDashboardData = {
  user: { name: "HIMS User", role: "System Administrator", hospital: "Tala Hospital" },
  overview: [
    { label: "Modules Ready for Integration", value: "1", detail: "1 external frontend module", trend: "Formal integration pending", icon: "ph-squares-four", tone: "primary", sparkline: [2, 2, 3, 3, 4, 4, 5] },
    { label: "Active Hospital Personnel", value: "248", detail: "Frontend sample data", trend: "Sample operational count", icon: "ph-users-three", tone: "info", sparkline: [210, 224, 218, 236, 232, 241, 248] },
    { label: "Pending Requests", value: "18", detail: "Across hospital services", trend: "Awaiting workflow review", icon: "ph-clock", tone: "warning", sparkline: [12, 16, 14, 21, 19, 22, 18] },
    { label: "Critical Alerts", value: "4", detail: "Needs operational review", trend: "Require immediate attention", icon: "ph-warning-circle", tone: "danger", sparkline: [2, 3, 2, 5, 4, 3, 4] },
    { label: "Sample Fleet Availability", value: "28", detail: "Frontend sample vehicles", trend: "Not sourced from Fleet integration", icon: "ph-ambulance", tone: "warning", sparkline: [22, 24, 23, 26, 25, 27, 28] },
    { label: "Appointments Today", value: "42", detail: "Frontend scheduling sample", trend: "Live scheduling not connected", icon: "ph-calendar-check", tone: "primary", sparkline: [26, 30, 34, 31, 38, 40, 42] },
  ],
  operations: {
    updatedLabel: "Frontend sample · updated just now",
    liveStatus: [
      { label: "Hospital Operational", state: "Operational", tone: "success" },
      { label: "Fleet Integration", state: "Pending", tone: "warning" },
      { label: "Laboratory Online", state: "Online", tone: "success" },
      { label: "Pharmacy Ready", state: "Ready", tone: "success" },
      { label: "HR Pending Sync", state: "Pending", tone: "warning" },
      { label: "Security Active", state: "Active", tone: "success" },
    ],
    liveMetrics: [
      { label: "Available Beds", value: "42", icon: "ph-bed", tone: "success", details: [{ label: "Emergency Beds", value: "12" }, { label: "Occupied", value: "30" }] },
      { label: "Ambulances Ready", value: "18", icon: "ph-ambulance", tone: "primary", details: [{ label: "On dispatch", value: "4" }, { label: "Maintenance", value: "2" }] },
      { label: "Staff On Duty", value: "156", icon: "ph-users-three", tone: "info", details: [{ label: "Doctors", value: "38" }, { label: "Nurses", value: "74" }, { label: "Support", value: "44" }] },
      { label: "Pending Laboratory Tests", value: "23", icon: "ph-flask", tone: "warning", details: [{ label: "Urgent", value: "5" }, { label: "Routine", value: "18" }] },
      { label: "Low Medicine Stocks", value: "8", icon: "ph-pill", tone: "warning", details: [{ label: "Replenishment", value: "Required" }] },
      { label: "Emergency Cases", value: "2", icon: "ph-siren", tone: "danger", details: [{ label: "Response", value: "Active" }] },
    ],
    snapshot: [
      { label: "Admissions Today", value: "31", icon: "ph-sign-in" },
      { label: "Discharges", value: "24", icon: "ph-sign-out" },
      { label: "Appointments", value: "42", icon: "ph-calendar-check" },
      { label: "Emergency Visits", value: "9", icon: "ph-first-aid" },
      { label: "Completed Trips", value: "17", icon: "ph-truck" },
      { label: "Scheduled Surgeries", value: "6", icon: "ph-activity" },
      { label: "Pending Approvals", value: "18", icon: "ph-clock" },
      { label: "Completed Requests", value: "37", icon: "ph-check-circle" },
    ],
    departments: [
      { name: "Emergency", status: "High activity", workload: "High", capacity: 86, availability: "4 beds", tone: "danger", icon: "ph-first-aid" },
      { name: "Outpatient", status: "Operational", workload: "Moderate", capacity: 64, availability: "12 slots", tone: "success", icon: "ph-stethoscope" },
