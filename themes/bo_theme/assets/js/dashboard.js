/**
 * Bid Intelligence Dashboard — dashboard.js
 * iinet · Track. Analyze. Win.
 * Exact match to reference dashboard image.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (typeof jQuery !== 'undefined' && typeof ApexCharts !== 'undefined') {
            $(document).ready(fn);
        } else {
            window.addEventListener('load', fn);
        }
    }
    ready(init);

    /* ==============================================================
       1. DATA & STATE
       ============================================================== */
    var RAW         = window.DASH_DATA || {};
    var allBids     = RAW.allBids      || [];
    var statuses    = RAW.statuses     || [];
    var states      = RAW.states       || [];
    var departments = RAW.departments  || [];
    var baseUrl     = RAW.baseUrl      || '';

    var FS = {
        state:      'ALL',
        fy:         'ALL',
        startDate:  null,
        endDate:    null
    };

    var activeFilteredBids = [];
    var charts = {};

    /* ==============================================================
       2. UTILITIES
       ============================================================== */
    function crore(v) {
        return ((parseFloat(v) || 0) / 1e7).toFixed(1);
    }
        function formatCurrencySmart(v) {
        var n = parseFloat(v) || 0;
        if (n === 0) return '₹ 0 Cr';
        if (n >= 10000000) {
            var cr = n / 10000000;
            return '₹ ' + (cr >= 100 ? cr.toFixed(0) : cr.toFixed(1)) + ' Cr';
        }
        if (n >= 100000) {
            return '₹ ' + (n / 100000).toFixed(1) + ' L';
        }
        if (n >= 1000) {
            return '₹ ' + n.toLocaleString('en-IN');
        }
        return '₹ ' + n;
    }

    function crStr(v) {
        return formatCurrencySmart(v);
    }

    function escH(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function setText(id, v) {
        var el = document.getElementById(id);
        if (el) el.textContent = (v == null ? '—' : v);
    }

    function fmtDate(s) {
        if (!s) return '—';
        var d = new Date(s);
        if (isNaN(d)) return String(s);
        return d.toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' });
    }

    function getBidFY(b) {
        var s = b.PublishedDate || b.CreationTs || '';
        if (!s) return null;
        var d = new Date(s);
        if (isNaN(d)) return null;
        var m = d.getMonth(), y = d.getFullYear();
        return m >= 3 ? y + '-' + String(y+1).slice(2)
                      : (y-1) + '-' + String(y).slice(2);
    }

    function isParticipated(b) {
        var s = (b.StatusName || '').toLowerCase().trim();
        var d = (b.DisplayName || '').toLowerCase().trim();
        var sid = parseInt(b.BidStatus, 10);
        if ([9, 21, 23, 25, 27, 29, 31, 17, 33, 35, 37, 13, 39].indexOf(sid) >= 0) return true;
        if (/^(createbids|created|identified|pq identified|enquiry|new|draft|bidinactive|inactive|bidcancelled|cancelled|nlbids|not eligible bids)$/i.test(s) ||
            /^(created|identified|draft|inactive|cancelled)$/i.test(d)) {
            return false;
        }
        var all = s + ' ' + d;
        return all.indexOf('part') >= 0 || all.indexOf('submit') >= 0 ||
               all.indexOf('tech') >= 0 || all.indexOf('evaluat') >= 0 ||
               all.indexOf('financ') >= 0 || all.indexOf('l1') >= 0 ||
               all.indexOf('award') >= 0 || all.indexOf('won') >= 0 ||
               all.indexOf('loa') >= 0 || all.indexOf('wo') >= 0;
    }

    function isWon(b) {
        var s = (b.StatusName || '').toLowerCase().trim();
        var d = (b.DisplayName || '').toLowerCase().trim();
        var sid = parseInt(b.BidStatus, 10);
        if ([13, 33, 37, 39].indexOf(sid) >= 0) return true;
        if (parseInt(b.LoaReceived, 10) === 1 || parseInt(b.WoReceived, 10) === 1 || parseInt(b.CompletionCertificateReceived, 10) === 1) return true;
        var all = s + ' ' + d;
        if (all.indexOf('won') >= 0 || all.indexOf('award') >= 0 ||
            all.indexOf('loa') >= 0 || all.indexOf('work order') >= 0 ||
            all.indexOf('wo received') >= 0 ||
            all.indexOf('completion') >= 0) {
            return true;
        }
        var raw = ((b.AwardCriteria || '') + ' ' + (b.BidTitle || '') + ' ' + (b.SummaryKeywords || '')).toLowerCase();
        if (raw.indexOf('won') >= 0 || raw.indexOf('awarded') >= 0) {
            return true;
        }
        return false;
    }

    function isTechQualified(b) {
        var s = (b.StatusName || '').toLowerCase().trim();
        var d = (b.DisplayName || '').toLowerCase().trim();
        var sid = parseInt(b.BidStatus, 10);
        if ([29, 31, 17, 33, 35, 37, 13, 39].indexOf(sid) >= 0) return true;
        var all = s + ' ' + d;
        if (all.indexOf('technic') >= 0 || all.indexOf('financial') >= 0 ||
            all.indexOf('l1') >= 0 || all.indexOf('award') >= 0 ||
            all.indexOf('won') >= 0 || all.indexOf('loa') >= 0 ||
            all.indexOf('wo') >= 0 || all.indexOf('completion') >= 0) {
            return true;
        }
        return false;
    }

    function isFinEval(b) {
        var s = (b.StatusName || '').toLowerCase().trim();
        var d = (b.DisplayName || '').toLowerCase().trim();
        var sid = parseInt(b.BidStatus, 10);
        if ([31, 17, 33, 37, 13, 39].indexOf(sid) >= 0) return true;
        var all = s + ' ' + d;
        if (all.indexOf('financial') >= 0 || all.indexOf('opening') >= 0 ||
            all.indexOf('l1') >= 0 || all.indexOf('award') >= 0 ||
            all.indexOf('won') >= 0 || all.indexOf('loa') >= 0 ||
            all.indexOf('wo') >= 0 || all.indexOf('completion') >= 0) {
            return true;
        }
        return false;
    }

    function statusBadgeClass(sn) {
        if (!sn) return 'badge-default';
        var s = sn.toLowerCase();
        if (s.indexOf('won') >= 0 || s.indexOf('award') >= 0 || s.indexOf('loa') >= 0) return 'badge-won';
        if (s.indexOf('participat') >= 0 || s.indexOf('bid opened') >= 0 || s.indexOf('commercial') >= 0) return 'badge-participated';
        if (s.indexOf('cancel') >= 0 || s.indexOf('drop') >= 0) return 'badge-cancelled';
        if (s.indexOf('technical') >= 0 || s.indexOf('financial') >= 0 || s.indexOf('evaluat') >= 0) return 'badge-evaluation';
        if (s.indexOf('discussion') >= 0 || s.indexOf('pq') >= 0) return 'badge-discussion';
        if (s.indexOf('identif') >= 0 || s.indexOf('enquir') >= 0) return 'badge-identified';
        return 'badge-default';
    }

    var STATUS_PALETTE = [
        '#1565C0','#42A5F5','#009688','#7E57C2','#FF9800',
        '#4CAF50','#F44336','#00BCD4','#AB47BC','#26A69A'
    ];
    function sPalette(i) { return STATUS_PALETTE[i % STATUS_PALETTE.length]; }

    var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    /* ==============================================================
       3.0 BUILD STATE & DEPARTMENT DROPDOWNS
       ============================================================== */
    function buildStateOptions() {
        var stateSet = {};
        if (Array.isArray(states)) {
            states.forEach(function(s) {
                if (s && String(s).trim()) stateSet[String(s).trim()] = true;
            });
        }
        allBids.forEach(function(b) {
            var s = (b.State || '').trim();
            if (s && s !== 'Unknown') stateSet[s] = true;
        });
        var sortedStates = Object.keys(stateSet).sort(function(a, b) {
            return a.localeCompare(b);
        });
        var sel = document.getElementById('filter-state');
        if (!sel) return;
        sel.innerHTML = '<option value="ALL">All States</option>';
        sortedStates.forEach(function(st) {
            var o = document.createElement('option');
            o.value = st;
            o.textContent = st;
            sel.appendChild(o);
        });
        sel.value = 'ALL';
        FS.state = 'ALL';
    }

    function buildDepartmentOptions() {
        var deptSet = {};
        if (Array.isArray(departments)) {
            departments.forEach(function(d) {
                if (d && String(d).trim()) deptSet[String(d).trim()] = true;
            });
        }
        allBids.forEach(function(b) {
            var d = (b.BidDepartment || '').trim();
            if (d && d !== 'Unknown') deptSet[d] = true;
        });
        var sortedDepts = Object.keys(deptSet).sort(function(a, b) {
            return a.localeCompare(b);
        });
        var sel = document.getElementById('filter-department');
        if (!sel) return;
        sel.innerHTML = '<option value="ALL">All Departments</option>';
        sortedDepts.forEach(function(dept) {
            var o = document.createElement('option');
            o.value = dept;
            var label = dept.charAt(0).toUpperCase() + dept.slice(1);
            o.textContent = label;
            sel.appendChild(o);
        });
        sel.value = 'ALL';
        FS.department = 'ALL';
    }

    function buildStatusOptions() {
        var sel = document.getElementById('filter-status');
        if (!sel) return;
        sel.innerHTML = '<option value="ALL">All Statuses</option>';
        var statusSet = {};
        if (Array.isArray(statuses)) {
            statuses.forEach(function(s) {
                var name = (s.DisplayName || s.StatusName || '').trim();
                if (name) statusSet[name] = true;
            });
        }
        allBids.forEach(function(b) {
            var name = (b.DisplayName || b.StatusName || '').trim();
            if (name) statusSet[name] = true;
        });
        Object.keys(statusSet).sort().forEach(function(st) {
            var o = document.createElement('option');
            o.value = st;
            o.textContent = st;
            sel.appendChild(o);
        });
        sel.value = 'ALL';
        FS.status = 'ALL';
    }

    /* ==============================================================
       3. BUILD FINANCIAL YEAR DROPDOWN
       ============================================================== */
    function buildFYOptions() {
        var fySet = {};
        allBids.forEach(function(b) {
            var f = getBidFY(b);
            if (f) fySet[f] = true;
        });
        var fys = Object.keys(fySet).sort().reverse();
        var sel = document.getElementById('filter-fy');
        if (!sel) return;
        sel.innerHTML = '<option value="ALL">All Years</option>';
        fys.forEach(function(f) {
            var o = document.createElement('option');
            o.value = f;
            o.textContent = 'FY ' + f;
            sel.appendChild(o);
        });
        /* Default to All Years so user immediately sees all records */
        sel.value = 'ALL';
        FS.fy = 'ALL';
    }

    /* ==============================================================
       4. APPLY FILTERS (State, Date Range, Department, FY)
       ============================================================== */
    function applyFilters() {
        activeFilteredBids = allBids.filter(function(b) {
            /* State Filter */
            if (FS.state && FS.state !== 'ALL') {
                var st = (b.State || '').trim().toLowerCase();
                if (st !== FS.state.toLowerCase()) return false;
            }

            /* FY Filter */
            if (FS.fy && FS.fy !== 'ALL' && getBidFY(b) !== FS.fy) return false;

            /* Date Range Filter */
            if (FS.startDate || FS.endDate) {
                var ds = b.PublishedDate || b.CreationTs;
                if (!ds) return false;
                var d = new Date(ds);
                if (isNaN(d)) return false;
                if (FS.startDate && d < FS.startDate) return false;
                if (FS.endDate   && d > FS.endDate)   return false;
            }
            return true;
        });

        // Update active filter badge on filter button
        var activeCount = 0;
        if (FS.state && FS.state !== 'ALL') activeCount++;
        if (FS.fy && FS.fy !== 'ALL') activeCount++;
        if (FS.startDate || FS.endDate) activeCount++;
        var $badge = $('#dashboard-filter-count');
        if (activeCount > 0) {
            $badge.text(activeCount).removeClass('d-none');
        } else {
            $badge.addClass('d-none');
        }

        updateAllCharts();
    }

    /* ==============================================================
       4.1 RESOLVE CATEGORY HELPER
       ============================================================== */
    function resolveCategory(b) {
        var cat = (b.CategoryName || b.Category || '').trim();
        if (cat === '001' || cat === '1') {
            return 'CCTV';
        }
        if (!cat || /^\d+$/.test(cat) || cat.toLowerCase() === 'uncategorised' || cat.toLowerCase() === 'unknown') {
            return 'General';
        }
        return cat.charAt(0).toUpperCase() + cat.slice(1);
    }

    /* ==============================================================
       5. COMPUTE METRICS
       ============================================================== */
    function compute(bids) {
        var total = bids.length;
        var participated = 0, won = 0, totalValue = 0, participatedValue = 0, wonValue = 0;
        var statusMap = {}, catMap = {}, stateMap = {};
        var mTotal = new Array(12).fill(0);
        var mPart  = new Array(12).fill(0);
        var mWon   = new Array(12).fill(0);
        var mVal   = new Array(12).fill(0);

        bids.forEach(function(b) {
            var sn  = (b.DisplayName || b.StatusName || 'Created').trim();
            var val = parseFloat(b.EstimatedValue) || 0;
            var cat = resolveCategory(b);
            b.Category = cat;
            var st  = (b.State    || 'Unknown').trim();

            totalValue += val;
            var part = isParticipated(b);
            var win  = isWon(b);
            if (part) { participated++; participatedValue += val; }
            if (win)  { won++; wonValue += val; }

            /* Status */
            if (!statusMap[sn]) statusMap[sn] = { count:0, value:0, bids:[] };
            statusMap[sn].count++; statusMap[sn].value += val; statusMap[sn].bids.push(b);

            /* Category */
            if (!catMap[cat]) catMap[cat] = { count:0, value:0, won:0, bids:[] };
            catMap[cat].count++; catMap[cat].value += val;
            if (win) catMap[cat].won++;
            catMap[cat].bids.push(b);

            /* State */
            if (!stateMap[st]) stateMap[st] = { count:0, value:0, won:0, bids:[] };
            stateMap[st].count++; stateMap[st].value += val;
            if (win) stateMap[st].won++;
            stateMap[st].bids.push(b);

            /* Monthly */
            var ds = b.PublishedDate || b.CreationTs;
            if (ds) {
                var d = new Date(ds);
                if (!isNaN(d)) {
                    var m = d.getMonth();
                    mTotal[m]++; mVal[m] += val;
                    if (part) mPart[m]++;
                    if (win)  mWon[m]++;
                }
            }
        });

        var wr = participated > 0 ? Math.round((won / participated) * 100) : (total > 0 ? Math.round((won / total) * 100) : 0);
        return {
            total: total, participated: participated, won: won,
            totalValue: totalValue,
            participatedValue: participatedValue,
            wonValue: wonValue,
            winRate: wr,
            statusMap: statusMap, catMap: catMap, stateMap: stateMap,
            mTotal: mTotal, mPart: mPart, mWon: mWon, mVal: mVal
        };
    }

    /* ==============================================================
       6. UPDATE ALL COMPONENTS
       ============================================================== */
    function updateAllCharts() {
        var c = compute(activeFilteredBids);
        updateKPIs(c);
        updatePipelineDonut(c);
        updateMonthlyTrend(c);
        updateBidValueChart(c);
        updateCategoryChart(c);
        updateStateMap(c);
        updateRecentBids(c);
    }

    /* ==============================================================
       7. ROW 1 — 5 CLEAN KPI CARDS (No percentages, No sparklines)
       ============================================================== */
    function updateKPIs(c) {
        setText('kpi-total',              c.total);
        setText('kpi-participated',       c.participated);
        setText('kpi-participated-value', formatCurrencySmart(c.participatedValue));
        setText('kpi-won',                c.won);
        setText('kpi-winrate',            c.winRate + '%');
        setText('kpi-value',              formatCurrencySmart(c.wonValue));

        renderWinRateGauge(c.winRate);
    }

    function renderWinRateGauge(pct) {
        var el = document.getElementById('chart-winrate');
        if (!el) return;
        destroyChart('chart-winrate');
        charts['chart-winrate'] = new ApexCharts(el, {
            chart:  { type:'radialBar', width:56, height:56, sparkline:{ enabled:true } },
            series: [pct],
            plotOptions: {
                radialBar: {
                    hollow:      { size:'52%' },
                    dataLabels:  { name:{ show:false }, value:{ show:false } },
                    track:       { background:'#E3EAFC' }
                }
            },
            fill:   { colors:['#009688'] },
            stroke: { lineCap:'round' }
        });
        charts['chart-winrate'].render();
    }

    /* ==============================================================
       8. ROW 2 LEFT — BID PIPELINE (STATUS DONUT CHART)
       ============================================================== */
    function updatePipelineDonut(c) {
        var chartEl  = document.getElementById('chart-pipeline-donut');
        var legendEl = document.getElementById('pipeline-donut-legend');
        if (!chartEl || !legendEl) return;
        destroyChart('chart-pipeline-donut');

        var entries = Object.keys(c.statusMap)
            .map(function(k){ return { name:k, count:c.statusMap[k].count, value:c.statusMap[k].value, bids:c.statusMap[k].bids }; })
            .sort(function(a,b){ return b.count - a.count; });

        if (entries.length === 0) {
            chartEl.innerHTML  = '<div class="bcc-empty">No status data available</div>';
            legendEl.innerHTML = '';
            return;
        }

        var colors = entries.map(function(e, i){ return sPalette(i); });
        var total  = c.total || 1;

        charts['chart-pipeline-donut'] = new ApexCharts(chartEl, {
            chart: {
                type:'donut', height:185
            },
            series: entries.map(function(e){ return e.count; }),
            labels: entries.map(function(e){ return e.name; }),
            colors: colors,
            plotOptions: {
                pie: {
                    donut: {
                        size:'68%',
                        labels: {
                            show:true,
                            total: {
                                show:true, label:'Total Bids',
                                fontSize:'10.5px', color:'#475569',
                                formatter:function(){ return c.total; }
                            },
                            value:{ fontSize:'19px', fontWeight:'800', color:'#1E293B' }
                        }
                    }
                }
            },
            dataLabels: { enabled:false },
            legend:     { show:false },
            stroke:     { width:2, colors:['#fff'] },
            tooltip:    { y:{ formatter:function(v){ return v + ' bids (' + Math.round(v/total*100) + '%)'; } } }
        });
        charts['chart-pipeline-donut'].render();

        /* Custom itemized legend */
        legendEl.innerHTML = '';
        entries.forEach(function(e, i) {
            var pct = Math.round(e.count / total * 100);
            var div = document.createElement('div');
            div.className = 'pdl-item';
            div.innerHTML =
                '<span class="pdl-dot" style="background:' + colors[i] + '"></span>' +
                '<span class="pdl-name" title="' + escH(e.name) + '">' + escH(e.name) + '</span>' +
                '<span class="pdl-count">' + e.count + '</span>' +
                '<span class="pdl-pct">(' + pct + '%)</span>';
            legendEl.appendChild(div);
        });
    }

    /* ==============================================================
       9. ROW 2 CENTER — MONTHLY BID TREND (FUNNEL FORMAT)
       ============================================================== */
    function updateMonthlyTrend(c) {
        var el = document.getElementById('chart-monthly-trend');
        if (!el) return;

        var total = activeFilteredBids.length;
        var cIdentified = total;
        var cParticipated = activeFilteredBids.filter(isParticipated).length;
        var cTech = activeFilteredBids.filter(isTechQualified).length;
        var cFin = activeFilteredBids.filter(isFinEval).length;
        var cWon = activeFilteredBids.filter(isWon).length;

        var p1 = total > 0 ? '100%' : '0%';
        var p2 = total > 0 ? Math.round(cParticipated / total * 100) + '%' : '0%';
        var p3 = total > 0 ? Math.round(cTech / total * 100) + '%' : '0%';
        var p4 = total > 0 ? Math.round(cFin / total * 100) + '%' : '0%';
        var p5 = total > 0 ? Math.round(cWon / total * 100) + '%' : '0%';

        var stages = [
            { key: 'identified',    cls: 'pf-stage-1', name: 'Identified',                  count: cIdentified,    pct: p1, bids: activeFilteredBids },
            { key: 'participated',  cls: 'pf-stage-2', name: 'Participated',                count: cParticipated,  pct: p2, bids: activeFilteredBids.filter(isParticipated) },
            { key: 'tech',          cls: 'pf-stage-3', name: 'Technically Qualified',        count: cTech,          pct: p3, bids: activeFilteredBids.filter(isTechQualified) },
            { key: 'fin',           cls: 'pf-stage-4', name: 'Under Financial Evaluation',   count: cFin,           pct: p4, bids: activeFilteredBids.filter(isFinEval) },
            { key: 'won',           cls: 'pf-stage-5', name: 'Won',                         count: cWon,           pct: p5, bids: activeFilteredBids.filter(isWon) }
        ];

        var html = '';
        stages.forEach(function(st) {
            html +=
                '<div class="pf-stage" data-funnel-key="' + st.key + '" title="' + escH(st.name) + ': ' + st.count + ' (' + st.pct + ')">' +
                    '<span class="pf-label">' + escH(st.name) + '</span>' +
                    '<div class="pf-bar-cell">' +
                        '<div class="pf-bar ' + st.cls + '">' + st.count + '</div>' +
                    '</div>' +
                    '<span class="pf-pct">' + st.pct + '</span>' +
                '</div>';
        });
        el.innerHTML = html;

        el.querySelectorAll('.pf-stage').forEach(function(row) {
            row.addEventListener('click', function() {
                var key = this.getAttribute('data-funnel-key');
                var found = stages.find(function(s){ return s.key === key; });
                if (found) {
                    openFunnelDrillDown(found);
                }
            });
        });
    }

    function openFunnelDrillDown(stage) {
        var matched = stage.bids || [];
        var totVal = matched.reduce(function(s,b){ return s + (parseFloat(b.EstimatedValue)||0); }, 0);
        setText('ddk-title', stage.name + ' Stage');
        setText('ddk-sub', matched.length + ' bids in ' + stage.name + ' stage');
        setDDKKPIs(matched.length, 'Stage Bids', crStr(totVal), 'Total Value', stage.pct, 'Conversion');

        var cMap = groupBy(matched, 'Category');
        setText('ddk-chart1-title', 'Bids by Category');
        renderDDHBar('ddk-chart1', sortMap(cMap).slice(0,8).map(function(e){return e.count;}), sortMap(cMap).slice(0,8).map(function(e){return e.name;}), '#2563EB');

        var sMap = groupBy(matched, 'State');
        setText('ddk-chart2-title', 'Bids by State');
        renderDDHBar('ddk-chart2', sortMap(sMap).slice(0,8).map(function(e){return e.count;}), sortMap(sMap).slice(0,8).map(function(e){return e.name;}), '#10B981');

        setText('ddk-bid-count', matched.length);
        renderDDBidList('ddk-bids-list', matched);
        openPanel('dd-kpi');
    }

    /* ==============================================================
       11. ROW 3 LEFT — BIDS BY CATEGORY (Dynamic with Side Category Names)
       ============================================================== */
    function updateCategoryChart(c) {
        var el = document.getElementById('chart-category');
        if (!el) return;
        destroyChart('chart-category');

        var catCounts = {};
        activeFilteredBids.forEach(function(b) {
            var cat = resolveCategory(b);
            catCounts[cat] = (catCounts[cat] || 0) + 1;
        });

        var entries = Object.keys(catCounts).map(function(k) {
            return { name: k, count: catCounts[k] };
        }).sort(function(a, b) {
            return b.count - a.count;
        });

        if (entries.length === 0) {
            el.innerHTML = '<div class="bcc-empty">No category data available</div>';
            return;
        }

        var maxCount = entries[0].count || 1;
        var BLUE_SCALE = ['#1565C0', '#1976D2', '#1E88E5', '#2196F3', '#42A5F5', '#64B5F6', '#90CAF9'];

        el.innerHTML = '';
        var list = document.createElement('div');
        list.className = 'cat-ranked-list';

        entries.forEach(function(e, i) {
            var pct = Math.max(12, Math.round((e.count / maxCount) * 100));
            var color = BLUE_SCALE[i % BLUE_SCALE.length];
            var div = document.createElement('div');
            div.className = 'crl-item';
            div.innerHTML =
                '<span class="crl-name" title="' + escH(e.name) + '">' + escH(e.name) + '</span>' +
                '<div class="crl-bar-wrap"><div class="crl-bar-fill" style="width:' + pct + '%; background:' + color + '"></div></div>' +
                '<span class="crl-count">' + e.count + '</span>';
            list.appendChild(div);
        });
        el.appendChild(list);
    }

    /* ==============================================================
       12. ROW 3 RIGHT — BIDS BY STATE / UT (INDIA SVG MAP + RANKED LIST)
       ============================================================== */
    function updateStateMap(c) {
        var svgPaths = document.querySelectorAll('.india-state-path');
        var tooltip  = document.getElementById('state-map-tooltip');

        /* Color SVG paths based on bid density */
        svgPaths.forEach(function(path) {
            var stateName = (path.getAttribute('data-state') || '').trim();

            /* Find match in stateMap (exact or normalized) */
            var count = 0;
            var matchedBids = [];
            var snNorm = stateName.toLowerCase().replace(/[^a-z]/g, '');

            Object.keys(c.stateMap).forEach(function(k) {
                var kl = k.toLowerCase().replace(/[^a-z]/g, '');
                if (kl === snNorm || kl.indexOf(snNorm) >= 0 || snNorm.indexOf(kl) >= 0 ||
                    (stateName === 'Andhra Pradesh' && (kl === 'andhrapradesh' || kl === 'telangana')) ||
                    (stateName === 'Jammu & Kashmir' && (kl === 'jammukashmir' || kl === 'ladakh'))) {
                    count += c.stateMap[k].count;
                    matchedBids = matchedBids.concat(c.stateMap[k].bids);
                }
            });

            /* Color shade matching reference: High (>=10), Medium (5-9), Low (1-4), No Bids */
            var fill = '#CFD8DC';
            if (count >= 10) fill = '#0D47A1';
            else if (count >= 5) fill = '#1976D2';
            else if (count >= 1) fill = '#90CAF9';

            path.style.fill = fill;

            /* Tooltip on mouseover */
            path.onmouseenter = function(ev) {
                if (tooltip) {
                    tooltip.innerHTML = escH(stateName) + ': <b>' + count + '</b> bid' + (count !== 1 ? 's' : '');
                    tooltip.style.display = 'block';
                }
            };
            path.onmousemove = function(ev) {
                if (tooltip) {
                    tooltip.style.left = (ev.clientX + 14) + 'px';
                    tooltip.style.top  = (ev.clientY - 12) + 'px';
                }
            };
            path.onmouseleave = function() {
                if (tooltip) tooltip.style.display = 'none';
            };
// No modal on state click
        });

        /* Ranked State List */
        var listEl = document.getElementById('state-ranked-list');
        if (!listEl) return;

        var entries = Object.keys(c.stateMap)
            .map(function(k){ return { name:k, count:c.stateMap[k].count }; })
            .sort(function(a,b){ return b.count - a.count; })
            .slice(0, 10);

        if (entries.length === 0) {
            listEl.innerHTML = '<div class="bcc-empty">No state data</div>';
            return;
        }

        var maxC = entries[0].count || 1;
        listEl.innerHTML = '';
        entries.forEach(function(e) {
            var pct = Math.max(8, Math.round((e.count / maxC) * 100));
            var div = document.createElement('div');
            div.className = 'srl-item';
            div.innerHTML =
                '<span class="srl-name" title="' + escH(e.name) + '">' + escH(e.name) + '</span>' +
                '<div class="srl-bar-wrap"><div class="srl-bar-fill" style="width:' + pct + '%"></div></div>' +
                '<span class="srl-count">' + e.count + '</span>';
            // No modal on state item click
            listEl.appendChild(div);
        });
    }

    /* ==============================================================
       13. ROW 2 RIGHT — BID VALUE ANALYSIS (Area Chart)
       ============================================================== */
    function updateBidValueChart(c) {
        var el = document.getElementById('chart-bid-value');
        if (!el) return;
        destroyChart('chart-bid-value');

        var valCr = c.mVal.map(function(v){ return parseFloat(crore(v)); });

        charts['chart-bid-value'] = new ApexCharts(el, {
            chart: {
                type:'area', height:210, toolbar:{ show:false },
                animations:{ enabled:true, easing:'easeinout', speed:600 }
            },
            series: [{ name:'Bid Value (Cr)', data:valCr }],
            xaxis: {
                categories: MONTHS,
                labels:{ style:{ fontSize:'9.5px', colors:'#94A3B8' } },
                axisBorder:{ show:false }, axisTicks:{ show:false }
            },
            yaxis: {
                labels:{
                    style:{ fontSize:'9.5px', colors:'#94A3B8' },
                    formatter:function(v){ var n = parseFloat(v); if(isNaN(n)) return '₹'+v; return '₹' + (Number.isInteger(n) ? n : n.toFixed(1)); }
                }
            },
            stroke:      { curve:'smooth', width:2, colors:['#1565C0'] },
            fill: {
                type:'gradient',
                gradient:{
                    shadeIntensity:1, opacityFrom:0.4, opacityTo:0.02,
                    colorStops:[
                        { offset:0,   color:'#1565C0', opacity:0.4 },
                        { offset:100, color:'#1565C0', opacity:0.02 }
                    ]
                }
            },
            colors:      ['#1565C0'],
            markers:     { size:4, colors:['#1565C0'], strokeColors:'#fff', strokeWidth:2 },
            dataLabels: {
                enabled:true,
                formatter:function(v){ return v > 0 ? '₹'+v : ''; },
                style:{ fontSize:'9px', colors:['#1565C0'] },
                background:{ enabled:false }
            },
            grid:        { borderColor:'#EEF2FB', strokeDashArray:3 },
            tooltip:     { y:{ formatter:function(v){ return '₹ '+v+' Cr'; } } }
        });
        charts['chart-bid-value'].render();
    }

    /* ==============================================================
       14. ROW 4 — TOP OPPORTUNITIES (FULL-WIDTH DATA TABLE)
       Winning Categories removed as requested
       ============================================================== */
    function updateRecentBids(c) {
        var tbody = document.getElementById('top-bids-tbody');
        if (!tbody) return;

        var sorted = activeFilteredBids.slice()
            .sort(function(a,b){ return (parseFloat(b.EstimatedValue)||0) - (parseFloat(a.EstimatedValue)||0); })
            .slice(0, 8);

        if (sorted.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#94A3B8;padding:24px;font-size:12px;font-style:italic;">No opportunities in current selection</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        sorted.forEach(function(b, i) {
            var sn    = b.StatusName || '—';
            var bc    = statusBadgeClass(sn);
            var val   = formatCurrencySmart(b.EstimatedValue);
            var tr    = document.createElement('tr');
            tr.innerHTML =
                '<td class="opp-rank">' + (i+1) + '</td>' +
                '<td><div class="opp-title" title="' + escH(b.BidTitle) + '">' + escH(b.BidTitle||'—') + '</div></td>' +
                '<td>' + escH(b.Category||'—') + '</td>' +
                '<td>' + escH(b.State||'—') + '</td>' +
                '<td class="opp-value">₹ ' + val + '</td>' +
                '<td><span class="bid-status-badge ' + bc + '">' + escH(sn) + '</span></td>' +
                '<td style="text-align:right"><a href="' + (window.DASH_DATA && window.DASH_DATA.baseUrl ? window.DASH_DATA.baseUrl + 'bid/BidDetails' : '#') + '" class="tbl-action-btn">View &rsaquo;</a></td>';;
            tbody.appendChild(tr);
        });
    }

    /* ==============================================================
       19. KPI DRILL-DOWN PANEL
       ============================================================== */
    function openKpiDD(kpiType) {
        var c = compute(activeFilteredBids);
        var matched, title, sub;

        if (kpiType === 'total') {
            matched = activeFilteredBids.slice();
            title   = 'Total Bids Analysis';
            sub     = matched.length + ' bids in current selection';
            setDDKKPIs(c.total, 'Total Bids', crStr(c.totalValue), 'Total Value', c.participated, 'Participated');
            setText('ddk-chart1-title','Status Breakdown');
            var sMap = groupBy(matched, 'StatusName');
            renderDDHBar('ddk-chart1', sortMap(sMap).map(function(e){return e.count;}), sortMap(sMap).map(function(e){return e.name;}), '#1565C0');
            setText('ddk-chart2-title','Monthly Trend');
            renderDDVBar('ddk-chart2', c.mTotal, MONTHS, '#42A5F5');

        } else if (kpiType === 'participated' || kpiType === 'participated-value') {
            matched = activeFilteredBids.filter(isParticipated);
            var pv  = matched.reduce(function(s,b){return s+(parseFloat(b.EstimatedValue)||0);},0);
            title   = kpiType === 'participated-value' ? 'Participated Bids Total Value Analysis' : 'Participated Bids Analysis';
            sub     = matched.length + ' participated bids (' + crStr(pv) + ')';
            setDDKKPIs(crStr(pv), 'Participated Value', matched.length, 'Participated Bids', c.won, 'Won Bids');
            var cMap = groupBy(matched, 'Category');
            setText('ddk-chart1-title','By Category');
            renderDDHBar('ddk-chart1', sortMap(cMap).slice(0,8).map(function(e){return e.count;}), sortMap(cMap).slice(0,8).map(function(e){return e.name;}), '#7E57C2');
            var stMap = groupBy(matched, 'State');
            setText('ddk-chart2-title','By State');
            renderDDHBar('ddk-chart2', sortMap(stMap).slice(0,8).map(function(e){return e.count;}), sortMap(stMap).slice(0,8).map(function(e){return e.name;}), '#009688');

        } else if (kpiType === 'won') {
            matched = activeFilteredBids.filter(isWon);
            title   = 'Won Bids Analysis';
            sub     = matched.length + ' won bids';
            var wv  = matched.reduce(function(s,b){return s+(parseFloat(b.EstimatedValue)||0);},0);
            setDDKKPIs(matched.length, 'Won Bids', crStr(wv), 'Won Value', c.winRate + '%', 'Win Rate');
            var cMapW = groupBy(matched, 'Category');
            setText('ddk-chart1-title','Winning Categories');
            renderDDHBar('ddk-chart1', sortMap(cMapW).map(function(e){return e.count;}), sortMap(cMapW).map(function(e){return e.name;}), '#4CAF50');
            var stMapW = groupBy(matched, 'State');
            setText('ddk-chart2-title','Winning States');
            renderDDHBar('ddk-chart2', sortMap(stMapW).slice(0,8).map(function(e){return e.count;}), sortMap(stMapW).slice(0,8).map(function(e){return e.name;}), '#26A69A');

        } else if (kpiType === 'winrate') {
            matched = activeFilteredBids.slice();
            title   = 'Win Rate Analysis';
            sub     = 'Overall win rate: ' + c.winRate + '%';
            setDDKKPIs(c.winRate + '%', 'Win Rate', c.won, 'Won Bids', c.total, 'Total Bids');
            var wrMonthly = c.mTotal.map(function(t,i){ return t>0?Math.round(c.mWon[i]/t*100):0; });
            setText('ddk-chart1-title','Monthly Win Rate (%)');
            renderDDVBar('ddk-chart1', wrMonthly, MONTHS, '#4CAF50');
            var cMapWR = groupBy(matched.filter(isWon), 'Category');
            setText('ddk-chart2-title','Wins by Category');
            renderDDHBar('ddk-chart2', sortMap(cMapWR).map(function(e){return e.count;}), sortMap(cMapWR).map(function(e){return e.name;}), '#4CAF50');

        } else {
            matched = activeFilteredBids.filter(isWon);
            title   = 'Total Won Value Analysis';
            sub     = 'Total won bids: ' + c.won + ' (' + crStr(c.wonValue) + ')';
            var avg = c.won > 0 ? c.wonValue / c.won : 0;
            setDDKKPIs(crStr(c.wonValue), 'Total Won Value', c.won, 'Won Bids', crStr(avg), 'Avg Won Value');
            var valCr = c.mWon.map(function(w, i){
                return parseFloat(crore(activeFilteredBids.filter(function(b){
                    if (!isWon(b)) return false;
                    var ds = b.PublishedDate || b.CreationTs;
                    if (!ds) return false;
                    var d = new Date(ds);
                    return !isNaN(d) && d.getMonth() === i;
                }).reduce(function(s, b){ return s + (parseFloat(b.EstimatedValue) || 0); }, 0)));
            });
            setText('ddk-chart1-title','Monthly Won Value (Cr)');
            renderDDVBar('ddk-chart1', valCr, MONTHS, '#FF9800');
            var cMapV = {};
            matched.forEach(function(b){ var cat=(b.Category||'Unknown').trim(); cMapV[cat]=(cMapV[cat]||0)+(parseFloat(b.EstimatedValue)||0); });
            var cEV = Object.keys(cMapV).map(function(k){ return{name:k,count:parseFloat(crore(cMapV[k]))}; }).sort(function(a,b){return b.count-a.count;}).slice(0,6);
            setText('ddk-chart2-title','Won Value by Category (Cr)');
            renderDDHBar('ddk-chart2', cEV.map(function(e){return e.count;}), cEV.map(function(e){return e.name;}), '#FF9800');
        }

        setText('ddk-title',     title);
        setText('ddk-sub',       sub);
        setText('ddk-bid-count', matched.length);
        renderDDBidList('ddk-bids-list', matched);
        openPanel('dd-kpi');
    }

    function setDDKKPIs(v1, l1, v2, l2, v3, l3) {
        var row = document.getElementById('ddk-kpi-row');
        if (!row) return;
        row.innerHTML =
            '<div class="dd-kpi"><div class="dd-kpi-val">' + v1 + '</div><div class="dd-kpi-lbl">' + (l1 || 'Primary') + '</div></div>' +
            '<div class="dd-kpi"><div class="dd-kpi-val">' + v2 + '</div><div class="dd-kpi-lbl">' + (l2 || 'Secondary') + '</div></div>' +
            '<div class="dd-kpi"><div class="dd-kpi-val">' + v3 + '</div><div class="dd-kpi-lbl">' + (l3 || 'Tertiary') + '</div></div>';
    }

    /* ==============================================================
       21. SHARED DRILLDOWN HELPERS (Clean Light Theme)
       ============================================================== */
    function renderDDHBar(elId, data, categories, color) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        if (!data.length) { el.innerHTML = '<div style="color:#94A3B8;font-size:11px;padding:12px;text-align:center;">No data available</div>'; return; }
        charts[elId] = new ApexCharts(el, {
            chart: { type:'bar', height:160, toolbar:{ show:false }, background:'transparent', animations:{ enabled:false } },
            plotOptions: { bar:{ horizontal:true, borderRadius:4, barHeight:'65%', dataLabels:{ position:'center' } } },
            series: [{ name:'Bids', data: data }],
            xaxis: {
                categories: categories,
                labels:{ style:{ fontSize:'9.5px', colors:'#64748B' } },
                axisBorder:{ show:false }, axisTicks:{ show:false }
            },
            yaxis: { labels:{ style:{ fontSize:'9.5px', colors:'#64748B' } } },
            dataLabels: { enabled:true, style:{ fontSize:'9.5px', colors:['#fff'], fontWeight:700 } },
            colors: [color],
            grid:   { borderColor:'#F1F5F9' },
            legend: { show:false },
            tooltip:{ theme:'light' }
        });
        charts[elId].render();
    }

    function renderDDVBar(elId, data, cats, color) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        charts[elId] = new ApexCharts(el, {
            chart: { type:'bar', height:160, toolbar:{ show:false }, background:'transparent', animations:{ enabled:false } },
            series: [{ name:'Count', data: data }],
            xaxis: {
                categories: cats,
                labels:{ style:{ fontSize:'8.5px', colors:'#64748B' } },
                axisBorder:{ show:false }
            },
            yaxis: { labels:{ style:{ fontSize:'8.5px', colors:'#64748B' } } },
            plotOptions: { bar:{ borderRadius:4, columnWidth:'55%' } },
            dataLabels: { enabled:false },
            colors: [color],
            grid:   { borderColor:'#F1F5F9' },
            legend: { show:false },
            tooltip:{ theme:'light' }
        });
        charts[elId].render();
    }

    function renderDDDonut(elId, data, labels, colors) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        if (!data.length) { el.innerHTML = '<div style="color:#94A3B8;font-size:11px;padding:12px;text-align:center;">No data available</div>'; return; }
        charts[elId] = new ApexCharts(el, {
            chart: { type:'donut', height:160, background:'transparent', animations:{ enabled:false } },
            series: data, labels: labels,
            colors: colors,
            plotOptions: { pie:{ donut:{ size:'58%' } } },
            dataLabels: { enabled:true, style:{ fontSize:'9px', colors:['#fff'] },
                          formatter:function(v){ return Math.round(v)+'%'; } },
            legend: { position:'bottom', fontSize:'9.5px', labels:{ colors:'#64748B' } },
            stroke: { width:1, colors:['#fff'] },
            tooltip: { theme:'light' }
        });
        charts[elId].render();
    }

    function renderDDBidList(elId, bids) {
        var el = document.getElementById(elId);
        if (!el) return;
        if (!bids.length) {
            el.innerHTML = '<div style="text-align:center;padding:24px;color:#94A3B8;font-size:12px;font-style:italic;">No matching bids found</div>';
            return;
        }
        el.innerHTML = '';
        bids.slice(0, 50).forEach(function(b, i) {
            var div = document.createElement('div');
            div.className = 'dd-bid-item';
            var cat = escH(b.Category || 'General');
            var st  = escH(b.State || 'Unknown');
            var sn  = escH(b.DisplayName || b.StatusName || 'Created');
            div.innerHTML =
                '<span class="dd-bid-idx">' + (i+1) + '</span>' +
                '<div class="dd-bid-info">' +
                    '<div class="dd-bid-title" title="' + escH(b.BidTitle||'—') + '">' + escH(b.BidTitle||'—') + '</div>' +
                    '<div class="dd-bid-meta">' + cat + ' &bull; ' + st + ' &bull; <span class="dd-bid-status-pill">' + sn + '</span></div>' +
                '</div>' +
                '<span class="dd-bid-val">' + crStr(b.EstimatedValue) + '</span>';
            el.appendChild(div);
        });
    }

    function groupBy(arr, key) {
        var map = {};
        arr.forEach(function(b) {
            var v = (b[key] || 'Unknown').trim();
            if (!map[v]) map[v] = { name:v, count:0, bids:[] };
            map[v].count++;
            map[v].bids.push(b);
        });
        return map;
    }

    function sortMap(map) {
        return Object.keys(map)
            .map(function(k){ return map[k]; })
            .sort(function(a,b){ return b.count - a.count; });
    }

    /* ==============================================================
       22. MODAL PANELS
       ============================================================== */
    var ALL_PANELS = ['dd-kpi'];

    function openPanel(id) {
        closeAllPanels();
        var p = document.getElementById(id);
        var o = document.getElementById('dd-overlay');
        if (p) p.style.display = 'block';
        if (o) o.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeAllPanels() {
        ALL_PANELS.forEach(function(id) {
            var p = document.getElementById(id);
            if (p) p.style.display = 'none';
        });
        var o = document.getElementById('dd-overlay');
        if (o) o.style.display = 'none';
        document.body.style.overflow = '';
    }

    function destroyChart(id) {
        if (charts[id]) {
            try { charts[id].destroy(); } catch(e) {}
            delete charts[id];
        }
    }

    /* ==============================================================
       23. EVENTS
       ============================================================== */
    function bindEvents() {

        /* State Filter */
        var stateEl = document.getElementById('filter-state');
        if (stateEl) {
            stateEl.addEventListener('change', function() {
                FS.state = this.value;
                applyFilters();
            });
        }



        /* Financial Year Filter */
        var fyEl = document.getElementById('filter-fy');
        if (fyEl) {
            fyEl.addEventListener('change', function() {
                FS.fy = this.value;
                applyFilters();
            });
        }

        /* Date Range Picker (Bootstrap / daterangepicker) */
        var drEl = $('#filter-daterange');
        if (drEl.length && $.fn.daterangepicker) {
            drEl.daterangepicker({
                autoUpdateInput: false,
                locale: { cancelLabel:'Clear', format:'DD MMM YYYY' },
                opens:'left'
            });
            drEl.on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
                FS.startDate = picker.startDate.toDate();
                FS.endDate   = picker.endDate.toDate();
                applyFilters();
            });
            drEl.on('cancel.daterangepicker', function() {
                $(this).val('');
                FS.startDate = null;
                FS.endDate   = null;
                applyFilters();
            });
        }

        /* Reset Button in Header */
        var hReset = document.getElementById('btn-reset-filters');
        if (hReset) {
            hReset.addEventListener('click', function() {
                FS.state = 'ALL';
                FS.fy = 'ALL';
                FS.startDate = null;
                FS.endDate = null;

                var stateEl = document.getElementById('filter-state');
                if (stateEl) stateEl.value = 'ALL';

                var fyEl = document.getElementById('filter-fy');
                if (fyEl) fyEl.value = 'ALL';

                var drInput = document.getElementById('filter-daterange');
                if (drInput) drInput.value = '';

                applyFilters();
            });
        }

        /* View All States */
        var vasBtn = document.getElementById('btn-view-all-states');
        if (vasBtn) {
            vasBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openKpiDD('total');
            });
        }

        /* View All Bids */
        var vabBtn = document.getElementById('btn-view-all-bids');
        if (vabBtn) {
            vabBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openKpiDD('total');
            });
        }

        /* KPI Card Clicks */
        document.querySelectorAll('.bid-kpi-card[data-kpi]').forEach(function(card) {
            card.addEventListener('click', function() {
                openKpiDD(this.dataset.kpi);
            });
        });

        /* Close Panel Buttons */
        document.querySelectorAll('.dd-close-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.panel;
                if (id) {
                    var p = document.getElementById(id);
                    if (p) p.style.display = 'none';
                }
                var anyOpen = ['dd-status','dd-state','dd-category','dd-kpi'].some(function(pid) {
                    var pp = document.getElementById(pid);
                    return pp && pp.style.display !== 'none';
                });
                if (!anyOpen) closeAllPanels();
            });
        });

        /* Overlay */
        var overlay = document.getElementById('dd-overlay');
        if (overlay) overlay.addEventListener('click', closeAllPanels);

        /* Escape key */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAllPanels();
        });

        /* Fact Sheet & Timeline */
        document.addEventListener('click', function(e) {
            var fs = e.target.closest('.view-factsheet');
            var tl = e.target.closest('.view-timeline');
            if (!fs && !tl) return;
            e.preventDefault();
            var el   = fs || tl;
            var bcid = el.dataset.bcid;
            var act  = fs ? 'factsheet' : 'timeline';
            if (bcid && baseUrl) {
                var url = baseUrl + 'bid/' + act + '?bcid=' + encodeURIComponent(bcid) + '&ajax=1';
                $.get(url, function(data) {
                    var c = document.getElementById('ddd-content');
                    if (c) {
                        var wrap = document.createElement('div');
                        wrap.style.cssText = 'margin-top:14px;padding:14px;background:#F3F6FE;border-radius:8px;';
                        wrap.innerHTML = data;
                        c.appendChild(wrap);
                    }
                });
            }
        });
    }

    /* ==============================================================
       24. INITIALIZATION
       ============================================================== */
    function init() {
        if (!document.getElementById('kpi-total') && !document.querySelector('.bid-kpi-row')) {
            return;
        }
        buildStateOptions();
        buildFYOptions();
        bindEvents();
        applyFilters();
    }

})();
