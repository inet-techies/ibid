/**
 * Bid Tracking Dashboard JavaScript
 * Exact Layout, Colors, Charts and Behavior matching Reference Screenshot
 * Single Unified Dashboard (No Toggles, Zero Percentages)
 */
(function ($) {
    "use strict";

    var payload = null;
    var overviewCharts = {};

    function init() {
        var payloadEl = document.getElementById("bid-dashboard-payload");
        if (!payloadEl || !payloadEl.textContent) return;

        try {
            payload = JSON.parse(payloadEl.textContent);
        } catch (e) {
            console.error("Error parsing bid dashboard payload:", e);
            return;
        }

        renderOverviewCharts();
        setupRegistrySearch();
        setupDateRangeFilter();
    }

    // -------------------------------------------------------------
    // Overview Dashboard Charts (Exact Reference Spec & Colors)
    // -------------------------------------------------------------
    function renderOverviewCharts() {
        if (!payload || !payload.overview) return;
        var ov = payload.overview;

        // 1. Bid Status Donut Chart (Blue, Orange, Grey matching screenshot)
        var statusEl = document.getElementById("chart-overview-status");
        if (statusEl) {
            var sSeries = (ov.statusDonut && ov.statusDonut.series.length) ? ov.statusDonut.series : [1];
            var sLabels = (ov.statusDonut && ov.statusDonut.labels.length) ? ov.statusDonut.labels : ["No Data"];
            var sColors = (ov.statusDonut && ov.statusDonut.colors.length) ? ov.statusDonut.colors : ["#3b82f6", "#f97316", "#94a3b8", "#10b981", "#ef4444", "#6b7280"];

            overviewCharts.status = new ApexCharts(statusEl, {
                chart: {
                    type: "donut",
                    height: 160,
                    sparkline: { enabled: true }
                },
                series: sSeries,
                labels: sLabels,
                colors: sColors,
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: "65%",
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: "9px",
                                    fontWeight: 500,
                                    color: "#64748b",
                                    offsetY: 12
                                },
                                value: {
                                    show: true,
                                    fontSize: "18px",
                                    fontWeight: 700,
                                    color: "#0f172a",
                                    offsetY: -10,
                                    formatter: function () {
                                        return ov.kpis ? ov.kpis.totalBids : 0;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: "Total Bids",
                                    fontSize: "9px",
                                    fontWeight: 500,
                                    color: "#64748b",
                                    formatter: function () {
                                        return ov.kpis ? ov.kpis.totalBids : 0;
                                    }
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " Bids";
                        }
                    }
                }
            });
            overviewCharts.status.render();
        }

        // 2. Participated Bids by Value (Cyan columns + Gold line matching screenshot)
        var valueEl = document.getElementById("chart-overview-value");
        if (valueEl) {
            var mCats = (ov.monthlyTrend && ov.monthlyTrend.categories) ? ov.monthlyTrend.categories : ['Jan-26', 'Feb-26', 'Mar-26'];
            var mValues = (ov.monthlyTrend && ov.monthlyTrend.values) ? ov.monthlyTrend.values : [0, 0, 0];
            var mEmds = (ov.monthlyTrend && ov.monthlyTrend.emdValues) ? ov.monthlyTrend.emdValues : [0, 0, 0];
            var mCounts = (ov.monthlyTrend && ov.monthlyTrend.counts) ? ov.monthlyTrend.counts : [0, 0, 0];

            overviewCharts.value = new ApexCharts(valueEl, {
                chart: {
                    height: 135,
                    toolbar: { show: false }
                },
                stroke: {
                    width: [0, 0, 2],
                    curve: 'smooth'
                },
                series: [
                    {
                        name: "Total Value (₹)",
                        type: "column",
                        data: mValues
                    },
                    {
                        name: "EMD Value (₹)",
                        type: "column",
                        data: mEmds
                    },
                    {
                        name: "No. of Bids",
                        type: "line",
                        data: mCounts
                    }
                ],
                xaxis: {
                    categories: mCats,
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
                                if (v >= 100000) return (v / 100000).toFixed(0) + "L";
                                if (v >= 1000) return (v / 1000).toFixed(0) + "K";
                                return Math.round(v);
                            }
                        }
                    },
                    {
                        show: false
                    },
                    {
                        opposite: true,
                        title: { text: "No. of Bids", style: { fontSize: "9px", color: "#64748b" } },
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            style: { fontSize: "9px", colors: "#64748b" },
                            formatter: function (v) {
                                return Math.round(v);
                            }
                        }
                    }
                ],
                // Cyan, Blue, Gold matching screenshot
                colors: ["#00a8e8", "#2563eb", "#f59e0b"],
                plotOptions: {
                    bar: {
                        columnWidth: "35%",
                        borderRadius: 2
                    }
                },
                markers: {
                    size: [0, 0, 3],
                    colors: ["#f59e0b"]
                },
                dataLabels: { enabled: false },
                legend: {
                    position: "top",
                    horizontalAlign: "right",
                    fontSize: "10px",
                    itemMargin: { horizontal: 6 }
                },
                tooltip: {
                    shared: true,
                    intersect: false
                }
            });
            overviewCharts.value.render();
        }

        // 3. Bids by Category (Light Green + Deep Navy Blue matching screenshot)
        var typeEl = document.getElementById("chart-overview-type");
        if (typeEl) {
            var tCats = (ov.typeBreakdown && ov.typeBreakdown.categories) ? ov.typeBreakdown.categories : ['OEM', 'Service'];
            var tBids = (ov.typeBreakdown && ov.typeBreakdown.series) ? ov.typeBreakdown.series : [0, 0];
            var tVals = (ov.typeBreakdown && ov.typeBreakdown.values) ? ov.typeBreakdown.values : [0, 0];

            overviewCharts.type = new ApexCharts(typeEl, {
                chart: {
                    type: "bar",
                    height: 160,
                    toolbar: { show: false }
                },
                series: [
                    {
                        name: "Total Bids",
                        data: tBids
                    },
                    {
                        name: "Total Value (₹)",
                        data: tVals.map(function(v) { return Math.round(v / 100000); })
                    }
                ],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: "55%",
                        borderRadius: 2,
                        dataLabels: {
                            position: "top"
                        }
                    }
                },
                xaxis: {
                    categories: tCats,
                    labels: { style: { fontSize: "10px", colors: "#64748b" } }
                },
                yaxis: {
                    labels: { style: { fontSize: "11px", fontWeight: 600, colors: "#0f172a" } }
                },
                // Light Green (#22c55e) and Dark Navy Blue (#0f2942) matching screenshot
                colors: ["#22c55e", "#0f2942"],
                dataLabels: {
                    enabled: true,
                    offsetX: 20,
                    style: { fontSize: "10px", colors: ["#0f172a"] },
                    formatter: function (val, opts) {
                        if (opts.seriesIndex === 1) {
                            return "₹" + val + "L";
                        }
                        return val;
                    }
                },
                legend: {
                    position: "top",
                    horizontalAlign: "right",
                    fontSize: "10px"
                }
            });
            overviewCharts.type.render();
        }

        // 4. Bids by Participated (Rich chocolate brown #8B3A14 matching screenshot)
        var funnelEl = document.getElementById("chart-overview-funnel");
        if (funnelEl) {
            var pData = ov.participationResults || [
                { label: 'Won', count: 1, color: '#8B3A14' },
                { label: 'Not L1', count: 0, color: '#8B3A14' },
                { label: 'Technically Qualified', count: 0, color: '#8B3A14' },
                { label: 'Technical Qualified', count: 0, color: '#8B3A14' },
                { label: 'Under Technical Eval.', count: 1, color: '#8B3A14' },
                { label: 'Technical Rejected', count: 2, color: '#8B3A14' }
            ];

            overviewCharts.funnel = new ApexCharts(funnelEl, {
                chart: {
                    type: "bar",
                    height: 200,
                    toolbar: { show: false }
                },
                series: [{
                    name: "Count",
                    data: pData.map(function (d) { return d.count; })
                }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        barHeight: "55%",
                        borderRadius: 2
                    }
                },
                xaxis: {
                    categories: pData.map(function (d) { return d.label; }),
                    labels: {
                        style: { fontSize: "10px", colors: "#64748b" },
                        formatter: function (v) { return Math.round(v); }
                    }
                },
                yaxis: {
                    labels: { style: { fontSize: "10px", colors: "#0f172a" } }
                },
                colors: pData.map(function () { return '#8B3A14'; }),
                dataLabels: {
                    enabled: true,
                    style: { fontSize: "10px", fontWeight: 700 }
                },
                legend: { show: false }
            });
            overviewCharts.funnel.render();
        }

        // 5. Department Activity (Blue & Orange matching reference layout)
        var deptEl = document.getElementById("chart-overview-department");
        if (deptEl) {
            var dCats = (ov.deptBreakdown && ov.deptBreakdown.categories) ? ov.deptBreakdown.categories : ['ClickUp', 'New Business', 'Projects', 'Tech'];
            var dSeries = (ov.deptBreakdown && ov.deptBreakdown.series) ? ov.deptBreakdown.series : [0, 0, 0, 0];
            var dColors = ['#2563eb', '#f97316', '#10b981', '#8b5cf6', '#06b6d4'];

            overviewCharts.department = new ApexCharts(deptEl, {
                chart: {
                    type: "bar",
                    height: 200,
                    toolbar: { show: false }
                },
                series: [{
                    name: "Bids",
                    data: dSeries
                }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        barHeight: "50%",
                        borderRadius: 2
                    }
                },
                xaxis: {
                    categories: dCats,
                    labels: {
                        style: { fontSize: "10px", colors: "#64748b" },
                        formatter: function (v) { return Math.round(v); }
                    }
                },
                yaxis: {
                    labels: { style: { fontSize: "11px", fontWeight: 600, colors: "#0f172a" } }
                },
                colors: dColors,
                dataLabels: {
                    enabled: true,
                    style: { fontSize: "10px", fontWeight: 700 }
                },
                legend: { show: false }
            });
            overviewCharts.department.render();
        }

        // 6. Bids by Month (Bright Red Line #dc2626 matching screenshot)
        var monthlyEl = document.getElementById("chart-overview-monthly");
        if (monthlyEl) {
            var trCats = (ov.monthlyTrend && ov.monthlyTrend.categories) ? ov.monthlyTrend.categories : ['Jan-26', 'Feb-26', 'Mar-26'];
            var trCounts = (ov.monthlyTrend && ov.monthlyTrend.counts) ? ov.monthlyTrend.counts : [0, 0, 0];
            var trVals = (ov.monthlyTrend && ov.monthlyTrend.values) ? ov.monthlyTrend.values : [0, 0, 0];

            overviewCharts.monthly = new ApexCharts(monthlyEl, {
                chart: {
                    height: 200,
                    toolbar: { show: false }
                },
                series: [
                    {
                        name: "No. of Bids",
                        type: "line",
                        data: trCounts
                    },
                    {
                        name: "Total Value (₹)",
                        type: "area",
                        data: trVals
                    }
                ],
                xaxis: {
                    categories: trCats,
                    labels: { style: { fontSize: "10px", colors: "#64748b" } }
                },
                yaxis: [
                    {
                        title: { text: "No. of Bids", style: { fontSize: "9px", color: "#64748b" } },
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            style: { fontSize: "9px", colors: "#64748b" },
                            formatter: function (v) { return Math.round(v); }
                        }
                    },
                    {
                        opposite: true,
                        title: { text: "Value (₹)", style: { fontSize: "9px", color: "#64748b" } },
                        min: 0,
                        labels: {
                            style: { fontSize: "9px", colors: "#64748b" },
                            formatter: function (v) {
                                if (v >= 100000) return (v / 100000).toFixed(0) + "L";
                                if (v >= 1000) return (v / 1000).toFixed(0) + "K";
                                return Math.round(v);
                            }
                        }
                    }
                ],
                // Bright Red line matching screenshot
                colors: ["#dc2626", "#00a8e8"],
                stroke: { curve: "smooth", width: [3, 2] },
                fill: {
                    type: ["solid", "gradient"],
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.05
                    }
                },
                markers: {
                    size: [4, 0],
                    colors: ["#dc2626"]
                },
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [0],
                    style: { fontSize: "10px", fontWeight: 700, colors: ["#0f172a"] }
                },
                legend: {
                    position: "top",
                    horizontalAlign: "right",
                    fontSize: "10px"
                }
            });
            overviewCharts.monthly.render();
        }
    }

    // -------------------------------------------------------------
    // Bid Registry Live Search Filter
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

    // -------------------------------------------------------------
    // Unified Date Range Filter Integration
    // -------------------------------------------------------------
    function setupDateRangeFilter() {
        var reportRangeEl = $("#reportrange");
        if (!reportRangeEl.length) return;

        reportRangeEl.on("apply.daterangepicker", function (ev, picker) {
            var startDate = picker.startDate.format("YYYY-MM-DD");
            var endDate = picker.endDate.format("YYYY-MM-DD");
            applyFilterToDashboard(startDate, endDate);
        });

        if (payload && payload.startDate && payload.endDate) {
            applyFilterToDashboard(payload.startDate, payload.endDate);
        }
    }

    function applyFilterToDashboard(startDate, endDate) {
        if (!payload || !payload.allBids) return;

        var filteredBids = payload.allBids.filter(function (bid) {
            var bidDate = bid.PublishedDate;
            if (!bidDate && bid.CreationTs) {
                bidDate = bid.CreationTs.substring(0, 10);
            }
            if (!bidDate) return false;
            return bidDate >= startDate && bidDate <= endDate;
        });

        updateDashboard(filteredBids);
    }

    function updateDashboard(bids) {
        var totalBids = bids.length;
        var participated = 0;
        var underParticipation = 0;
        var underDiscussion = 0;
        var awarded = 0;
        var cancelled = 0;
        var inactive = 0;

        var deptMap = {};
        var typeMap = {};
        var monthMap = {};

        bids.forEach(function (b) {
            var sid = parseInt(b.BidStatus, 10);
            var est = parseFloat(b.EstimatedValue) || 0;
            var emd = parseFloat(b.EmdValue) || 0;

            if (sid === 5 || sid === 13) participated++;
            else if (sid === 2 || sid === 4 || sid === 12) underParticipation++;
            else if (sid === 3 || sid === 11) underDiscussion++;
            else if (sid === 7) awarded++;
            else if (sid === 8) cancelled++;
            else if (sid === 6) inactive++;

            var dept = b.BidDepartment || "Other";
            if (!deptMap[dept]) deptMap[dept] = 0;
            deptMap[dept]++;

            var type = b.BidType || "Other";
            if (!typeMap[type]) typeMap[type] = { count: 0, val: 0 };
            typeMap[type].count++;
            typeMap[type].val += est;

            var dStr = b.PublishedDate || (b.CreationTs ? b.CreationTs.substring(0, 10) : null);
            if (dStr) {
                var ym = dStr.substring(0, 7);
                if (!monthMap[ym]) monthMap[ym] = { count: 0, val: 0, emd: 0 };
                monthMap[ym].count++;
                monthMap[ym].val += est;
                monthMap[ym].emd += emd;
            }
        });

        // Update Top 7 KPI cards (Zero percentages)
        $("#kpi-ov-total").text(totalBids);
        $("#kpi-ov-part").text(participated);
        $("#kpi-ov-under-part").text(underParticipation);
        $("#kpi-ov-under-disc").text(underDiscussion);
        $("#kpi-ov-awarded").text(awarded);
        $("#kpi-ov-types").text(Object.keys(typeMap).length);
        $("#kpi-ov-depts").text(Object.keys(deptMap).length);

        // Update Status Table (Status + Count only, No % column)
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
                var rowHtml = '<tr>' +
                    '<td class="ps-1 py-1 text-nowrap" style="line-height: 1.2;">' +
                    '<span class="status-dot-bullet" style="background-color: ' + st.color + '; width: 7px; height: 7px;"></span>' +
                    '<span class="text-dark fw-medium" style="font-size: 11px;">' + st.name + '</span>' +
                    '</td>' +
                    '<td class="text-end pe-2 py-1 fw-bold" style="font-size: 11px;">' + st.count + '</td>' +
                    '</tr>';
                stTbody.append(rowHtml);
            });
        }

        // Update Donut
        if (overviewCharts.status) {
            var activeSlices = workflowCounts.filter(function (s) { return s.count > 0; });
            if (activeSlices.length === 0) {
                overviewCharts.status.updateOptions({ labels: ["No Records"] });
                overviewCharts.status.updateSeries([1]);
            } else {
                overviewCharts.status.updateOptions({
                    labels: activeSlices.map(function (s) { return s.name; }),
                    colors: activeSlices.map(function (s) { return s.color; })
                });
                overviewCharts.status.updateSeries(activeSlices.map(function (s) { return s.count; }));
            }
        }

        // Update Registry Table
        var regTbody = $("#overview-registry-tbody");
        if (regTbody.length) {
            regTbody.empty();
            if (bids.length === 0) {
                regTbody.append('<tr><td colspan="9" class="text-center text-muted py-4">No bids found for selected period</td></tr>');
            } else {
                var rowIdx = 1;
                bids.forEach(function (b) {
                    var typeBadge = "";
                    var bType = (b.BidType || "").toLowerCase();
                    if (bType === "oem") {
                        typeBadge = '<span class="badge bg-soft-info text-info">OEM</span>';
                    } else if (bType === "service") {
                        typeBadge = '<span class="badge bg-soft-primary text-primary">Service</span>';
                    } else {
                        typeBadge = '<span class="badge bg-soft-secondary text-secondary">' + (b.BidType || "—") + '</span>';
                    }

                    var estFmt = b.EstimatedValue ? formatNumberCommas(b.EstimatedValue) : "—";
                    var emdFmt = b.EmdValue ? formatNumberCommas(b.EmdValue) : "—";
                    var subDate = b.SubmissionDate ? formatDateShort(b.SubmissionDate) : "—";
                    var viewUrl = (payload.baseUrl || "") + "bid/BidDetails";

                    var rowHtml = '<tr>' +
                        '<td class="ps-3 text-muted">' + (rowIdx++) + '</td>' +
                        '<td class="fw-semibold text-dark">' + escapeHtml(b.BidTitle) + '</td>' +
                        '<td>' + typeBadge + '</td>' +
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

    function escapeHtml(str) {
        if (!str) return "";
        return $("<div>").text(str).html();
    }

    $(document).ready(function () {
        init();
    });

})(jQuery);
