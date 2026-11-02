/**
 * Bid Tracking Dashboard JavaScript
 * 100% Data-Driven Architecture
 * 
 * Strict Compliance:
 * - ZERO hard-coded State names
 * - ZERO hard-coded Category names
 * - Single source of truth: allBids from BidCreationDetails
 * - Dynamic cascading filter bar with active filter pills
 * - Dynamic State chart & Category chart generated from active dataset
 * - Every KPI, chart, and registry row recalculated from filteredBids
 */
(function ($) {
    "use strict";

    var payload = null;
    var charts = {};

    // Single Unified Filter State
    var filterState = {
        category: 'ALL',
        state: 'ALL',
        startDate: null,
        endDate: null
    };

    function init() {
        var payloadEl = document.getElementById("bid-dashboard-payload");
        if (!payloadEl || !payloadEl.textContent) return;

        try {
            payload = JSON.parse(payloadEl.textContent);
        } catch (e) {
            console.error("Error parsing bid dashboard payload:", e);
            return;
        }

        if (!payload.allBids) {
            payload.allBids = [];
        }

        // Initialize URL dates if present
        if (payload.startDate && payload.endDate) {
            filterState.startDate = payload.startDate;
            filterState.endDate = payload.endDate;
        }

        initializeCharts();
        setupFilterListeners();
        setupRegistrySearch();

        // Initial application of filters & initial render
        applyDashboardFilters(true);
    }

    // -------------------------------------------------------------
    // Generic Helper & Normalization Functions
    // -------------------------------------------------------------
    function normalizeStr(val) {
        if (val === null || val === undefined) return '';
        return String(val).trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function isAll(val) {
        return !val || val === 'ALL' || val === '' || val === null || val === undefined;
    }

    function getUniqueValues(bids, field) {
        var seen = {};
        var list = [];
        bids.forEach(function (b) {
            var raw = b[field];
            if (!raw) return;
            var str = String(raw).trim();
            if (!str) return;
            var key = normalizeStr(str);
            if (!seen[key]) {
                seen[key] = true;
                list.push(str);
            }
        });
        list.sort(function (a, b) {
            return a.localeCompare(b, undefined, { sensitivity: 'base' });
        });
        return list;
    }

    function matchesDateRange(bid, start, end) {
        if (!start || !end) return true;
        var d = bid.PublishedDate || bid.CreationTs || bid.SubmissionDate;
        if (!d) return false;
        var dStr = String(d).substring(0, 10);
        var sStr = String(start).substring(0, 10);
        var eStr = String(end).substring(0, 10);
        return dStr >= sStr && dStr <= eStr;
    }

    function formatNumberCommas(val) {
        var n = parseFloat(val);
        if (isNaN(n)) return val;
        return n.toLocaleString('en-IN');
    }

    function formatDateShort(dStr) {
        if (!dStr) return "—";
        var dt = new Date(dStr);
        if (isNaN(dt.getTime())) return dStr;
        return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatMonthLabel(ym) {
        if (!ym) return "—";
        var parts = ym.split("-");
        if (parts.length < 2) return ym;
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        var mIdx = parseInt(parts[1], 10) - 1;
        var yr = parts[0].substring(2);
        return (monthNames[mIdx] || parts[1]) + "-" + yr;
    }

    function escapeHtml(str) {
        if (!str) return "";
        return $("<div>").text(str).html();
    }

    // -------------------------------------------------------------
    // Initialize ApexCharts Structures (Data dynamically updated)
    // -------------------------------------------------------------
    function initializeCharts() {
        // 1. Bid Status Donut Chart
        var statusEl = document.getElementById("chart-overview-status");
        if (statusEl) {
            charts.status = new ApexCharts(statusEl, {
                chart: { type: "donut", height: 160, sparkline: { enabled: true } },
                series: [1],
                labels: ["Loading"],
                colors: ["#3b82f6", "#f97316", "#94a3b8", "#10b981", "#ef4444", "#6b7280"],
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: "65%",
                            labels: {
                                show: true,
                                name: { show: true, fontSize: "9px", fontWeight: 500, color: "#64748b", offsetY: 12 },
                                value: { show: true, fontSize: "18px", fontWeight: 700, color: "#0f172a", offsetY: -10, formatter: function(v) { return v; } },
                                total: {
                                    show: true,
                                    label: "Total Bids",
                                    fontSize: "9px",
                                    fontWeight: 500,
                                    color: "#64748b",
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: function (val) { return val + " Bids"; } } }
            });
            charts.status.render();
        }

        // 2. Participated Bids by Value (Combo Bar + Line)
        var valueEl = document.getElementById("chart-overview-value");
        if (valueEl) {
            charts.value = new ApexCharts(valueEl, {
                chart: { height: 135, toolbar: { show: false } },
                stroke: { width: [0, 0, 2], curve: 'smooth' },
                series: [
                    { name: "Total Value (₹)", type: "column", data: [] },
                    { name: "EMD Value (₹)", type: "column", data: [] },
                    { name: "No. of Bids", type: "line", data: [] }
                ],
                xaxis: {
                    categories: [],
                    labels: { style: { fontSize: "10px", colors: "#64748b" } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: [
                    {
                        title: { text: "Value (₹)", style: { fontSize: "9px", color: "#64748b" } },
                        min: 0,
                        labels: {
                            style: { fontSize: "9px", colors: "#64748b" },
                            formatter: function (v) {
                                if (v >= 10000000) return (v / 10000000).toFixed(1) + "Cr";
                                if (v >= 100000) return (v / 100000).toFixed(0) + "L";
                                if (v >= 1000) return (v / 1000).toFixed(0) + "K";
                                return Math.round(v);
                            }
                        }
                    },
                    { show: false },
                    {
                        opposite: true,
                        title: { text: "No. of Bids", style: { fontSize: "9px", color: "#64748b" } },
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            style: { fontSize: "9px", colors: "#64748b" },
                            formatter: function (v) { return Math.round(v); }
                        }
                    }
                ],
                colors: ["#00a8e8", "#2563eb", "#f59e0b"],
                plotOptions: { bar: { columnWidth: "35%", borderRadius: 2 } },
                markers: { size: [0, 0, 3], colors: ["#f59e0b"] },
                dataLabels: { enabled: false },
                legend: { position: "top", horizontalAlign: "right", fontSize: "10px", itemMargin: { horizontal: 6 } },
                tooltip: { shared: true, intersect: false }
            });
            charts.value.render();
        }

        // 3. Dynamic Category Chart (100% driven from filteredBids)
        var categoryEl = document.getElementById("chart-overview-category");
        if (categoryEl) {
            charts.category = new ApexCharts(categoryEl, {
                chart: {
                    type: "bar",
                    height: 230,
                    toolbar: { show: false },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            var clickedIndex = config.dataPointIndex;
                            var clickedCategory = (config.w.config.xaxis && config.w.config.xaxis.categories)
                                ? config.w.config.xaxis.categories[clickedIndex]
                                : config.w.globals.labels[clickedIndex];
                            if (clickedCategory) {
                                toggleCategoryFilter(clickedCategory);
                            }
                        }
                    }
                },
                series: [{ name: "Bids", data: [] }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        barHeight: "60%",
                        borderRadius: 2
                    }
                },
                xaxis: {
                    categories: [],
                    labels: {
                        style: { fontSize: "10px", colors: "#64748b" },
                        formatter: function (v) { return Math.round(v); }
                    }
                },
                yaxis: {
                    labels: { style: { fontSize: "11px", fontWeight: 600, colors: ["#0f172a"] } }
                },
                colors: ["#10b981", "#0f172a"],
                dataLabels: {
                    enabled: true,
                    style: { fontSize: "10px", fontWeight: 700, colors: ["#ffffff"] },
                    formatter: function (v) { return v > 0 ? v : ""; }
                },
                legend: { show: false },
                grid: {
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                    borderColor: "#f1f5f9"
                },
                tooltip: { y: { formatter: function (val) { return val + " Bids"; } } }
            });
            charts.category.render();
        }

        // 4. Dynamic State Chart (Horizontal Stacked Bar: OEM vs Service)
        var stateEl = document.getElementById("chart-overview-state");
        if (stateEl) {
            charts.state = new ApexCharts(stateEl, {
                chart: {
                    type: "bar",
                    stacked: true,
                    height: 380,
                    toolbar: { show: false },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            var clickedIndex = config.dataPointIndex;
                            var clickedState = (config.w.config.xaxis && config.w.config.xaxis.categories)
                                ? config.w.config.xaxis.categories[clickedIndex]
                                : config.w.globals.labels[clickedIndex];
                            if (clickedState) {
                                toggleStateFilter(clickedState);
                            }
                        }
                    }
                },
                series: [
                    { name: "OEM", data: [] },
                    { name: "Service", data: [] }
                ],
                colors: ["#2563eb", "#ea580c"],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: "65%",
                        borderRadius: 1
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: "10px", fontWeight: 700, colors: ["#ffffff"] },
                    formatter: function (val) { return val > 0 ? val : ""; }
                },
                xaxis: {
                    categories: [],
                    labels: {
                        style: { fontSize: "10px", colors: "#64748b" },
                        formatter: function (val) { return Math.round(val); }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { fontSize: "11px", fontWeight: 600, colors: ["#0f172a"] } }
                },
                grid: {
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                    borderColor: "#f1f5f9"
                },
                legend: { position: "bottom", horizontalAlign: "center", fontSize: "11px", itemMargin: { horizontal: 10 } },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: { formatter: function (val) { return val + " Bids"; } }
                }
            });
            charts.state.render();
        }

        // 5. Bids by Participated (Funnel Bar Chart)
        var funnelEl = document.getElementById("chart-overview-funnel");
        if (funnelEl) {
            charts.funnel = new ApexCharts(funnelEl, {
                chart: { type: "bar", height: 200, toolbar: { show: false } },
                series: [{ name: "Count", data: [0, 0, 0, 0, 0] }],
                plotOptions: {
                    bar: { horizontal: true, distributed: true, barHeight: "55%", borderRadius: 2 }
                },
                xaxis: {
                    categories: ['Won', 'Not L1', 'Technically Qualified', 'Under Technical Eval.', 'Technical Rejected'],
                    labels: { style: { fontSize: "10px", colors: "#64748b" }, formatter: function (v) { return Math.round(v); } }
                },
                yaxis: {
                    labels: { style: { fontSize: "11px", fontWeight: 600, colors: ["#0f172a"] } }
                },
                colors: ['#10b981', '#64748b', '#3b82f6', '#f59e0b', '#ef4444'],
                dataLabels: {
                    enabled: true,
                    style: { fontSize: "11px", fontWeight: 700, colors: ["#ffffff"] },
                    formatter: function (val) { return val; }
                },
                legend: { show: false },
                grid: {
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                    borderColor: "#f1f5f9"
                }
            });
            charts.funnel.render();
        }

        // 6. Department Activity Chart
        var deptEl = document.getElementById("chart-overview-department");
        if (deptEl) {
            charts.department = new ApexCharts(deptEl, {
                chart: { type: "bar", height: 200, toolbar: { show: false } },
                series: [{ name: "Bids", data: [] }],
                plotOptions: {
                    bar: { horizontal: true, distributed: true, barHeight: "55%", borderRadius: 2 }
                },
                xaxis: {
                    categories: [],
                    labels: { style: { fontSize: "10px", colors: "#64748b" }, formatter: function (v) { return Math.round(v); } }
                },
                yaxis: {
                    labels: { style: { fontSize: "11px", fontWeight: 600, colors: ["#0f172a"] } }
                },
                colors: ["#00a8e8", "#2563eb", "#10b981", "#f59e0b", "#8b5cf6", "#ec4899", "#6366f1", "#14b8a6"],
                dataLabels: { enabled: true, style: { fontSize: "10px", fontWeight: 700 } },
                legend: { show: false }
            });
            charts.department.render();
        }
    }

    // -------------------------------------------------------------
    // Setup Filter Listeners
    // -------------------------------------------------------------
    function setupFilterListeners() {
        // Category Dropdown change
        $("#filter-category").on("change", function () {
            filterState.category = $(this).val();
            applyDashboardFilters();
        });

        // State Dropdown change
        $("#filter-state").on("change", function () {
            filterState.state = $(this).val();
            applyDashboardFilters();
        });

        // Clear Filters button
        $("#btn-clear-filters").on("click", function () {
            clearAllFilters();
        });

        // Date Range Picker
        var reportRangeEl = $("#filter-date-range");
        if (reportRangeEl.length && typeof $.fn.daterangepicker === "function") {
            reportRangeEl.daterangepicker({
                autoUpdateInput: false,
                opens: "left",
                alwaysShowCalendars: true,
                ranges: {
                    "Today": [moment(), moment()],
                    "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                    "Last 7 Days": [moment().subtract(6, "days"), moment()],
                    "Last 30 Days": [moment().subtract(29, "days"), moment()],
                    "This Month": [moment().startOf("month"), moment().endOf("month")],
                    "Last Month": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")],
                    "All Dates": [moment("2020-01-01"), moment("2030-12-31")]
                },
                locale: {
                    cancelLabel: "Clear"
                }
            }, function (start, end, label) {
                if (label === "All Dates") {
                    filterState.startDate = null;
                    filterState.endDate = null;
                    reportRangeEl.find(".reportrange-picker-field").text("All Dates");
                    reportRangeEl.removeClass("filter-active");
                } else {
                    filterState.startDate = start.format("YYYY-MM-DD");
                    filterState.endDate = end.format("YYYY-MM-DD");
                    reportRangeEl.find(".reportrange-picker-field").text(start.format("MMM D, YY") + " - " + end.format("MMM D, YY"));
                    reportRangeEl.addClass("filter-active");
                }
                applyDashboardFilters();
            });

            reportRangeEl.on("apply.daterangepicker", function (ev, picker) {
                if (picker.chosenLabel === "All Dates") {
                    filterState.startDate = null;
                    filterState.endDate = null;
                    reportRangeEl.find(".reportrange-picker-field").text("All Dates");
                    reportRangeEl.removeClass("filter-active");
                } else {
                    filterState.startDate = picker.startDate.format("YYYY-MM-DD");
                    filterState.endDate = picker.endDate.format("YYYY-MM-DD");
                    reportRangeEl.find(".reportrange-picker-field").text(picker.startDate.format("MMM D, YY") + " - " + picker.endDate.format("MMM D, YY"));
                    reportRangeEl.addClass("filter-active");
                }
                applyDashboardFilters();
            });

            reportRangeEl.on("cancel.daterangepicker", function () {
                filterState.startDate = null;
                filterState.endDate = null;
                reportRangeEl.find(".reportrange-picker-field").text("All Dates");
                reportRangeEl.removeClass("filter-active");
                applyDashboardFilters();
            });
        }
    }

    function toggleCategoryFilter(categoryName) {
        if (!categoryName) return;
        if (!isAll(filterState.category) && normalizeStr(filterState.category) === normalizeStr(categoryName)) {
            filterState.category = 'ALL';
        } else {
            filterState.category = categoryName;
        }
        applyDashboardFilters();
    }

    function toggleStateFilter(stateName) {
        if (!stateName) return;
        if (!isAll(filterState.state) && normalizeStr(filterState.state) === normalizeStr(stateName)) {
            filterState.state = 'ALL';
        } else {
            filterState.state = stateName;
        }
        applyDashboardFilters();
    }

    function clearAllFilters() {
        filterState.category = 'ALL';
        filterState.state = 'ALL';
        filterState.startDate = null;
        filterState.endDate = null;

        $("#filter-category").val('ALL');
        $("#filter-state").val('ALL');
        $("#filter-date-range .reportrange-picker-field").text("All Dates");
        $("#filter-date-range").removeClass("filter-active");

        applyDashboardFilters();
    }

    // -------------------------------------------------------------
    // Single Unified Filtering Function (One Source of Truth)
    // -------------------------------------------------------------
    function applyDashboardFilters(isInitial) {
        if (!payload || !payload.allBids) return;

        // 1. Calculate filteredBids based on current filterState
        var filteredBids = payload.allBids.filter(function (bid) {
            // Category condition
            var categoryMatches = isAll(filterState.category) ||
                normalizeStr(bid.Category) === normalizeStr(filterState.category);

            // State condition
            var stateMatches = isAll(filterState.state) ||
                normalizeStr(bid.State) === normalizeStr(filterState.state);

            // Date condition
            var dateMatches = matchesDateRange(bid, filterState.startDate, filterState.endDate);

            return categoryMatches && stateMatches && dateMatches;
        });

        // 2. Cascading dropdown options calculation
        updateCascadingDropdowns();

        // 3. Update Visual active states & Active Filter Pills
        updateFilterVisuals();

        // 4. Update ALL dashboard components from filteredBids
        updateDashboard(filteredBids);
    }

    // -------------------------------------------------------------
    // Cascading Dropdown Options (Derived Dynamically from Data)
    // -------------------------------------------------------------
    function updateCascadingDropdowns() {
        // Categories available given current state & date filters
        var bidsForCategories = payload.allBids.filter(function (b) {
            var stateMatches = isAll(filterState.state) || normalizeStr(b.State) === normalizeStr(filterState.state);
            var dateMatches = matchesDateRange(b, filterState.startDate, filterState.endDate);
            return stateMatches && dateMatches;
        });
        var availableCategories = getUniqueValues(bidsForCategories, 'Category');

        // States available given current category & date filters
        var bidsForStates = payload.allBids.filter(function (b) {
            var categoryMatches = isAll(filterState.category) || normalizeStr(b.Category) === normalizeStr(filterState.category);
            var dateMatches = matchesDateRange(b, filterState.startDate, filterState.endDate);
            return categoryMatches && dateMatches;
        });
        var availableStates = getUniqueValues(bidsForStates, 'State');

        // Re-populate Category Dropdown
        var catSelect = $("#filter-category");
        catSelect.empty();
        catSelect.append('<option value="ALL">All Categories (' + bidsForCategories.length + ' bids)</option>');
        availableCategories.forEach(function (cat) {
            var count = bidsForCategories.filter(function(b) { return normalizeStr(b.Category) === normalizeStr(cat); }).length;
            var isSelected = (!isAll(filterState.category) && normalizeStr(filterState.category) === normalizeStr(cat));
            catSelect.append('<option value="' + escapeHtml(cat) + '"' + (isSelected ? ' selected' : '') + '>' + escapeHtml(cat) + ' (' + count + ')</option>');
        });

        // Re-populate State Dropdown
        var stSelect = $("#filter-state");
        stSelect.empty();
        stSelect.append('<option value="ALL">All States (' + bidsForStates.length + ' bids)</option>');
        availableStates.forEach(function (st) {
            var count = bidsForStates.filter(function(b) { return normalizeStr(b.State) === normalizeStr(st); }).length;
            var isSelected = (!isAll(filterState.state) && normalizeStr(filterState.state) === normalizeStr(st));
            stSelect.append('<option value="' + escapeHtml(st) + '"' + (isSelected ? ' selected' : '') + '>' + escapeHtml(st) + ' (' + count + ')</option>');
        });
    }

    // -------------------------------------------------------------
    // Active Filter Display & Visual Highlights
    // -------------------------------------------------------------
    function updateFilterVisuals() {
        // Dropdown styling
        if (!isAll(filterState.category)) {
            $("#filter-category").addClass("filter-active");
        } else {
            $("#filter-category").removeClass("filter-active");
        }

        if (!isAll(filterState.state)) {
            $("#filter-state").addClass("filter-active");
        } else {
            $("#filter-state").removeClass("filter-active");
        }

        // Active Badges strip
        var badgesContainer = $("#active-filter-badges");
        badgesContainer.empty();
        var hasActive = false;

        // Category Badge
        if (!isAll(filterState.category)) {
            hasActive = true;
            var catBadge = $('<span class="badge bg-primary active-filter-badge">' +
                'Category: ' + escapeHtml(filterState.category) +
                '<span class="badge-clear-btn ms-1 fs-12">&times;</span>' +
                '</span>');
            catBadge.find(".badge-clear-btn").on("click", function () {
                filterState.category = 'ALL';
                applyDashboardFilters();
            });
            badgesContainer.append(catBadge);
        }

        // State Badge
        if (!isAll(filterState.state)) {
            hasActive = true;
            var stBadge = $('<span class="badge bg-info active-filter-badge">' +
                'State: ' + escapeHtml(filterState.state) +
                '<span class="badge-clear-btn ms-1 fs-12">&times;</span>' +
                '</span>');
            stBadge.find(".badge-clear-btn").on("click", function () {
                filterState.state = 'ALL';
                applyDashboardFilters();
            });
            badgesContainer.append(stBadge);
        }

        // Date Range Badge
        if (filterState.startDate && filterState.endDate) {
            hasActive = true;
            var dtBadge = $('<span class="badge bg-success active-filter-badge">' +
                'Date: ' + escapeHtml(filterState.startDate) + ' to ' + escapeHtml(filterState.endDate) +
                '<span class="badge-clear-btn ms-1 fs-12">&times;</span>' +
                '</span>');
            dtBadge.find(".badge-clear-btn").on("click", function () {
                filterState.startDate = null;
                filterState.endDate = null;
                $("#filter-date-range .reportrange-picker-field").text("All Dates");
                $("#filter-date-range").removeClass("filter-active");
                applyDashboardFilters();
            });
            badgesContainer.append(dtBadge);
        }

        if (hasActive) {
            $("#active-filter-strip").removeClass("d-none").addClass("d-flex");
        } else {
            $("#active-filter-strip").removeClass("d-flex").addClass("d-none");
        }
    }

    // -------------------------------------------------------------
    // Full Dashboard Recalculation from filteredBids
    // -------------------------------------------------------------
    function updateDashboard(bids) {
        var totalBids = bids.length;
        var participated = 0;
        var underParticipation = 0;
        var underDiscussion = 0;
        var awarded = 0;
        var cancelled = 0;
        var inactive = 0;

        var wonCount = 0;
        var notL1Count = 0;
        var techQualCount = 0;
        var underTechEvalCount = 0;
        var techRejectedCount = 0;

        var deptMap = {};
        var typeMap = {};
        var categoryMap = {};
        var stateMap = {};
        var monthMap = {};

        bids.forEach(function (b) {
            var sid = parseInt(b.BidStatus, 10);
            var est = parseFloat(b.EstimatedValue) || 0;
            var emd = parseFloat(b.EmdValue) || 0;

            // Workflow status classification
            if (sid === 5 || sid === 13) participated++;
            else if (sid === 2 || sid === 4 || sid === 12) underParticipation++;
            else if (sid === 3 || sid === 11) underDiscussion++;
            else if (sid === 7) awarded++;
            else if (sid === 8) cancelled++;
            else if (sid === 6) inactive++;

            // Funnel classification
            if (sid === 7) wonCount++;
            else if (sid === 10) notL1Count++;
            else if (sid === 3 || sid === 11 || sid === 12) techQualCount++;
            else if (sid === 2 || sid === 4) underTechEvalCount++;
            else if (sid === 8 || sid === 6) techRejectedCount++;

            // Dynamic Category aggregation
            var rawCat = b.Category;
            if (rawCat && String(rawCat).trim() !== '') {
                var normCat = normalizeStr(rawCat);
                if (!categoryMap[normCat]) {
                    categoryMap[normCat] = { name: String(rawCat).trim(), count: 0 };
                }
                categoryMap[normCat].count++;
            }

            // Dynamic State aggregation (OEM vs Service)
            var rawSt = b.State;
            if (rawSt && String(rawSt).trim() !== '') {
                var normSt = normalizeStr(rawSt);
                if (!stateMap[normSt]) {
                    stateMap[normSt] = { name: String(rawSt).trim(), OEM: 0, Service: 0, total: 0 };
                }
                var bType = normalizeStr(b.BidType);
                if (bType === 'oem') {
                    stateMap[normSt].OEM++;
                } else {
                    stateMap[normSt].Service++;
                }
                stateMap[normSt].total++;
            }

            // Dynamic Department aggregation
            var rawDept = b.BidDepartment;
            if (rawDept && String(rawDept).trim() !== '') {
                var normDept = normalizeStr(rawDept);
                if (!deptMap[normDept]) {
                    deptMap[normDept] = { name: String(rawDept).trim(), count: 0 };
                }
                deptMap[normDept].count++;
            }

            // Dynamic Type aggregation
            var rawType = b.BidType;
            if (rawType && String(rawType).trim() !== '') {
                var normType = normalizeStr(rawType);
                typeMap[normType] = true;
            }

            // Monthly breakdown
            var dStr = b.PublishedDate || (b.CreationTs ? b.CreationTs.substring(0, 10) : null);
            if (dStr) {
                var ym = dStr.substring(0, 7);
                if (!monthMap[ym]) monthMap[ym] = { count: 0, val: 0, emd: 0 };
                monthMap[ym].count++;
                monthMap[ym].val += est;
                monthMap[ym].emd += emd;
            }
        });

        // 1. Update KPI Card numbers
        $("#kpi-ov-total").text(totalBids);
        $("#kpi-ov-part").text(participated);
        $("#kpi-ov-under-part").text(underParticipation);
        $("#kpi-ov-under-disc").text(underDiscussion);
        $("#kpi-ov-awarded").text(awarded);
        $("#kpi-ov-types").text(Object.keys(typeMap).length);
        $("#dash-total-badge").text(totalBids + " Total Bids");

        // 2. Update Status Workflow Table & Donut
        var workflowCounts = [
            { name: 'Participated', count: participated, color: '#3b82f6' },
            { name: 'Under Participation', count: underParticipation, color: '#f97316' },
            { name: 'Under Discussion', count: underDiscussion, color: '#94a3b8' },
            { name: 'Awarded', count: awarded, color: '#10b981' },
            { name: 'Cancelled', count: cancelled, color: '#ef4444' },
            { name: 'Inactive', count: inactive, color: '#6b7280' }
        ];

        var stTbody = $("#overview-status-tbody");
        if (stTbody.length) {
            stTbody.empty();
            workflowCounts.forEach(function (st) {
                stTbody.append(
                    '<tr>' +
                    '<td class="ps-1 py-1 text-nowrap">' +
                    '<span class="status-dot-bullet" style="background-color: ' + st.color + ';"></span>' +
                    '<span class="text-dark fw-medium fs-11">' + st.name + '</span>' +
                    '</td>' +
                    '<td class="text-end pe-2 py-1 fw-bold fs-11">' + st.count + '</td>' +
                    '</tr>'
                );
            });
        }

        if (charts.status) {
            var activeSlices = workflowCounts.filter(function (s) { return s.count > 0; });
            if (activeSlices.length === 0) {
                charts.status.updateOptions({ labels: ["No Bids"] });
                charts.status.updateSeries([1]);
            } else {
                charts.status.updateOptions({
                    labels: activeSlices.map(function (s) { return s.name; }),
                    colors: activeSlices.map(function (s) { return s.color; })
                });
                charts.status.updateSeries(activeSlices.map(function (s) { return s.count; }));
            }
        }

        // 3. Update Bids by Category Chart
        var catKeys = Object.keys(categoryMap).sort(function (a, b) {
            return categoryMap[b].count - categoryMap[a].count;
        });
        $("#badge-category-count").text(catKeys.length + " Categories");

        if (charts.category) {
            if (catKeys.length === 0) {
                charts.category.updateOptions({ xaxis: { categories: ["No Bids"] } });
                charts.category.updateSeries([{ name: "Bids", data: [0] }]);
            } else {
                var catCategories = catKeys.map(function (k) { return categoryMap[k].name; });
                var catSeriesData = catKeys.map(function (k) { return categoryMap[k].count; });
                var catHeight = Math.max(200, Math.min(450, catCategories.length * 32));
                charts.category.updateOptions({
                    chart: { height: catHeight },
                    xaxis: { categories: catCategories },
                    colors: ["#10b981", "#0f172a"]
                });
                charts.category.updateSeries([{ name: "Bids", data: catSeriesData }]);
            }
        }

        // 4. Update Bids by States/ UT's Horizontal Stacked Chart
        var stKeys = Object.keys(stateMap).sort(function (a, b) {
            return stateMap[b].total - stateMap[a].total;
        });
        $("#badge-state-count").text(stKeys.length + " States");

        if (charts.state) {
            if (stKeys.length === 0) {
                charts.state.updateOptions({ xaxis: { categories: ["No Bids"] } });
                charts.state.updateSeries([
                    { name: "OEM", data: [0] },
                    { name: "Service", data: [0] }
                ]);
            } else {
                var stCategories = stKeys.map(function (k) { return stateMap[k].name; });
                var oemSeries = stKeys.map(function (k) { return stateMap[k].OEM; });
                var serviceSeries = stKeys.map(function (k) { return stateMap[k].Service; });
                var stHeight = Math.max(280, Math.min(650, stCategories.length * 30));
                charts.state.updateOptions({
                    chart: { height: stHeight },
                    xaxis: { categories: stCategories }
                });
                charts.state.updateSeries([
                    { name: "OEM", data: oemSeries },
                    { name: "Service", data: serviceSeries }
                ]);
            }
        }

        // 5. Update Department Activity Chart
        var deptKeys = Object.keys(deptMap).sort(function (a, b) {
            return deptMap[b].count - deptMap[a].count;
        });

        if (charts.department) {
            if (deptKeys.length === 0) {
                charts.department.updateOptions({ xaxis: { categories: ["No Bids"] } });
                charts.department.updateSeries([{ name: "Bids", data: [0] }]);
            } else {
                var deptCategories = deptKeys.slice(0, 10).map(function (k) { return deptMap[k].name; });
                var deptSeriesData = deptKeys.slice(0, 10).map(function (k) { return deptMap[k].count; });
                charts.department.updateOptions({ xaxis: { categories: deptCategories } });
                charts.department.updateSeries([{ name: "Bids", data: deptSeriesData }]);
            }
        }

        // 6. Update Funnel / Participated Breakdown Chart
        if (charts.funnel) {
            var funnelData = [wonCount, notL1Count, techQualCount, underTechEvalCount, techRejectedCount];
            charts.funnel.updateSeries([{ name: "Count", data: funnelData }]);
        }

        // 7. Update Participated Bids by Value Chart & Table
        var valTbody = $("#overview-value-tbody");
        var sortedMonths = Object.keys(monthMap).sort();

        if (valTbody.length) {
            valTbody.empty();
            if (sortedMonths.length === 0) {
                valTbody.append('<tr><td colspan="4" class="text-muted">No monthly records</td></tr>');
            } else {
                sortedMonths.slice(-3).forEach(function (ym) {
                    var mData = monthMap[ym];
                    var mLabel = formatMonthLabel(ym);
                    valTbody.append(
                        '<tr>' +
                        '<td class="fw-semibold text-dark">' + mLabel + '</td>' +
                        '<td>' + mData.count + '</td>' +
                        '<td class="text-dark">₹' + formatNumberCommas(mData.val) + '</td>' +
                        '<td class="text-muted">₹' + formatNumberCommas(mData.emd) + '</td>' +
                        '</tr>'
                    );
                });
            }
        }

        if (charts.value) {
            if (sortedMonths.length === 0) {
                charts.value.updateOptions({ xaxis: { categories: ["No Data"] } });
                charts.value.updateSeries([
                    { name: "Total Value (₹)", data: [0] },
                    { name: "EMD Value (₹)", data: [0] },
                    { name: "No. of Bids", data: [0] }
                ]);
            } else {
                var mCats = sortedMonths.map(function (ym) { return formatMonthLabel(ym); });
                var mTotalVals = sortedMonths.map(function (ym) { return monthMap[ym].val; });
                var mEmdVals = sortedMonths.map(function (ym) { return monthMap[ym].emd; });
                var mBidsCounts = sortedMonths.map(function (ym) { return monthMap[ym].count; });

                charts.value.updateOptions({ xaxis: { categories: mCats } });
                charts.value.updateSeries([
                    { name: "Total Value (₹)", data: mTotalVals },
                    { name: "EMD Value (₹)", data: mEmdVals },
                    { name: "No. of Bids", data: mBidsCounts }
                ]);
            }
        }

        // 8. Update Bid Registry Table & Counter Badge
        $("#registry-count-badge").text(totalBids + " Bids");

        var regTbody = $("#overview-registry-tbody");
        if (regTbody.length) {
            regTbody.empty();
            if (bids.length === 0) {
                regTbody.append('<tr><td colspan="11" class="text-center text-muted py-4">No bids match the selected filters.</td></tr>');
            } else {
                var rowIdx = 1;
                bids.forEach(function (b) {
                    var typeBadge = "";
                    var bType = normalizeStr(b.BidType);
                    if (bType === "oem") {
                        typeBadge = '<span class="badge bg-soft-info text-info">OEM</span>';
                    } else if (bType === "service") {
                        typeBadge = '<span class="badge bg-soft-primary text-primary">Service</span>';
                    } else {
                        typeBadge = '<span class="badge bg-soft-secondary text-secondary">' + escapeHtml(b.BidType || "—") + '</span>';
                    }

                    var estFmt = b.EstimatedValue ? formatNumberCommas(b.EstimatedValue) : "—";
                    var emdFmt = b.EmdValue ? formatNumberCommas(b.EmdValue) : "—";
                    var subDate = b.SubmissionDate ? formatDateShort(b.SubmissionDate) : "—";
                    var viewUrl = (payload.baseUrl || "") + "bid/BidDetails";

                    var rowHtml = '<tr>' +
                        '<td class="ps-3 text-muted">' + (rowIdx++) + '</td>' +
                        '<td class="fw-semibold text-dark">' + escapeHtml(b.BidTitle) + '</td>' +
                        '<td>' + typeBadge + '</td>' +
                        '<td><span class="badge bg-light text-dark border">' + escapeHtml(b.Category || "—") + '</span></td>' +
                        '<td><span class="fw-medium text-dark">' + escapeHtml(b.State || "—") + '</span></td>' +
                        '<td>' + escapeHtml(b.BidDepartment || "—") + '</td>' +
                        '<td class="fw-medium text-dark">' + estFmt + '</td>' +
                        '<td class="text-muted">' + emdFmt + '</td>' +
                        '<td class="text-muted">' + subDate + '</td>' +
                        '<td><span class="badge ' + (b.BadgeClass || "bg-secondary") + '">' + escapeHtml(b.StatusName || "—") + '</span></td>' +
                        '<td class="text-center"><a href="' + viewUrl + '" class="btn btn-xs btn-outline-primary py-0 px-2" title="View"><i class="feather-eye me-1 fs-10"></i>View</a></td>' +
                        '</tr>';
                    regTbody.append(rowHtml);
                });
            }
        }
    }

    // -------------------------------------------------------------
    // Live Search Filter for Bid Registry Table
    // -------------------------------------------------------------
    function setupRegistrySearch() {
        $("#overview-registry-search").on("keyup input", function () {
            var q = $(this).val().toLowerCase().trim();
            $("#overview-registry-tbody tr").each(function () {
                var rowText = $(this).text().toLowerCase();
                if (q === "" || rowText.indexOf(q) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

    $(document).ready(function () {
        init();
    });

})(jQuery);
