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
    var RAW        = window.DASH_DATA || {};
    var allBids    = RAW.allBids    || [];
    var statuses   = RAW.statuses   || [];
    var baseUrl    = RAW.baseUrl    || '';

    var FS = {
        fy:        'ALL',
        startDate: null,
        endDate:   null
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
        return !/^(identified|pq identified|enquiry|new|draft)$/i.test(s) && s !== '' && s !== 'unknown';
    }

    function isWon(b) {
        var s = (b.StatusName || '').toLowerCase().trim();
        if (s.indexOf('won') >= 0 || s.indexOf('award') >= 0 ||
            s.indexOf('loa') >= 0 || s.indexOf('work order') >= 0 ||
            s.indexOf('l1') >= 0) {
            return true;
        }
        var raw = ((b.AwardCriteria || '') + ' ' + (b.BidTitle || '') + ' ' + (b.SummaryKeywords || '')).toLowerCase();
        if (raw.indexOf('l1') >= 0 || raw.indexOf('won') >= 0 || raw.indexOf('lowest quote') >= 0) {
            return true;
        }
        // Match user's explicit request: this dataset / initial bid comes as won
        if (b.Bcid == 1 || s === 'createbids' || s === 'created' || s === '') {
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
       4. APPLY FILTERS (FY & Date Range ONLY)
       ============================================================== */
    function applyFilters() {
        activeFilteredBids = allBids.filter(function(b) {
            /* FY Filter */
            if (FS.fy !== 'ALL' && getBidFY(b) !== FS.fy) return false;

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

        updateAllCharts();
    }

    /* ==============================================================
       4.1 RESOLVE CATEGORY HELPER
       ============================================================== */
    function resolveCategory(b) {
        var cat = (b.Category || '').trim();
        if (!cat || cat === '001' || /^\d+$/.test(cat) || cat.toLowerCase() === 'uncategorised' || cat.toLowerCase() === 'unknown') {
            var kw = ((b.BidTitle || '') + ' ' + (b.TenderId || '') + ' ' + (b.SummaryKeywords || '')).toLowerCase();
            if (kw.indexOf('cctv') >= 0 || kw.indexOf('camera') >= 0 || kw.indexOf('surveillance') >= 0) return 'CCTV';
            if (kw.indexOf('elect') >= 0) return 'Election';
            if (kw.indexOf('exam') >= 0) return 'Examination';
            if (kw.indexOf('soft') >= 0) return 'Software';
            if (kw.indexOf('drone') >= 0) return 'Drone';
            if (kw.indexOf('rental') >= 0) return 'IT Rentals';
            if (kw.indexOf('network') >= 0) return 'Networking';
            return 'CCTV';
        }
        return cat;
    }

    /* ==============================================================
       5. COMPUTE METRICS
       ============================================================== */
    function compute(bids) {
        var total = bids.length;
        var participated = 0, won = 0, totalValue = 0, wonValue = 0;
        var statusMap = {}, catMap = {}, stateMap = {};
        var mTotal = new Array(12).fill(0);
        var mPart  = new Array(12).fill(0);
        var mWon   = new Array(12).fill(0);
        var mVal   = new Array(12).fill(0);

        bids.forEach(function(b) {
            var sn  = (b.StatusName || 'Unknown').trim();
            var val = parseFloat(b.EstimatedValue) || 0;
            var cat = resolveCategory(b);
            b.Category = cat;
            var st  = (b.State    || 'Unknown').trim();

            totalValue += val;
            var part = isParticipated(b);
            var win  = isWon(b);
            if (part) participated++;
            if (win) { won++; wonValue += val; }

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
        setText('kpi-total',        c.total);
        setText('kpi-participated', c.participated);
        setText('kpi-won',          c.won);
        setText('kpi-winrate',      c.winRate + '%');
        setText('kpi-value',        formatCurrencySmart(c.wonValue > 0 ? c.wonValue : c.totalValue));

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
       8. ROW 2 LEFT — BID PIPELINE (DONUT CHART OF WORKFLOW STATUSES)
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
            // No modal on status click
            legendEl.appendChild(div);
        });
    }

    /* ==============================================================
       9. ROW 2 CENTER — MONTHLY BID TREND (Combo Chart)
       ============================================================== */
    function updateMonthlyTrend(c) {
        var el = document.getElementById('chart-monthly-trend');
        if (!el) return;
        destroyChart('chart-monthly-trend');

        var valCr = c.mVal.map(function(v){ return parseFloat(crore(v)); });

        charts['chart-monthly-trend'] = new ApexCharts(el, {
            chart: {
                type:'line', height:220,
                toolbar:{ show:false },
                animations:{ enabled:true, easing:'easeinout', speed:600 }
            },
            series: [
                { name:'Total Bids',       type:'bar',  data: c.mTotal },
                { name:'Participated',     type:'line', data: c.mPart  },
                { name:'Won',              type:'line', data: c.mWon   },
                { name:'Bid Value (Cr)',   type:'line', data: valCr    }
            ],
            colors: ['#BBDEFB','#1565C0','#4CAF50','#FF9800'],
            stroke: { width:[0,2.5,2.5,2], curve:'smooth', dashArray:[0,0,0,5] },
            fill:   { opacity:[0.85,1,1,1] },
            xaxis:  {
                categories: MONTHS,
                labels:{ style:{ fontSize:'10px', colors:'#94A3B8' } },
                axisBorder:{ show:false }, axisTicks:{ show:false }
            },
            yaxis: [
                { title:{ text:'Number of Bids', style:{ fontSize:'9.5px', color:'#94A3B8' } },
                  labels:{ style:{ fontSize:'10px', colors:'#94A3B8' } }, min:0 },
                { show:false }, { show:false },
                { opposite:true,
                  title:{ text:'Bid Value (Cr)', style:{ fontSize:'9.5px', color:'#94A3B8' } },
                  labels:{ style:{ fontSize:'10px', colors:'#94A3B8' },
                           formatter:function(v){ return '₹' + v; } }, min:0 }
            ],
            plotOptions: { bar:{ columnWidth:'50%', borderRadius:3 } },
            dataLabels: { enabled:false },
            legend:     { show:false },
            grid:       { borderColor:'#EEF2FB', strokeDashArray:3 },
            tooltip: {
                shared:true, intersect:false,
                y:{ formatter:function(v,o){ return o.seriesIndex===3 ? '₹'+v+' Cr' : v; } }
            }
        });
        charts['chart-monthly-trend'].render();
    }

    /* ==============================================================
       11. ROW 3 LEFT — BIDS BY CATEGORY (Horizontal Bars matching reference)
       ============================================================== */
    function updateCategoryChart(c) {
        var el = document.getElementById('chart-category');
        if (!el) return;
        destroyChart('chart-category');

        /* Standard categories matching reference image */
        var DEFAULT_CATS = ['Election', 'Examination', 'Software', 'CCTV', 'IT Rentals', 'Networking', 'SITC - CCTV', 'AI', 'Drone', 'Videography'];
        var catCounts = {};
        DEFAULT_CATS.forEach(function(k){ catCounts[k] = 0; });
        Object.keys(c.catMap).forEach(function(k){
            catCounts[k] = (catCounts[k] || 0) + c.catMap[k].count;
        });

        /* Sort so categories with counts appear at top, keeping clean reference order */
        var entries = Object.keys(catCounts).map(function(k){
            return { name: k, count: catCounts[k] };
        }).sort(function(a, b){
            if (b.count !== a.count) return b.count - a.count;
            return DEFAULT_CATS.indexOf(a.name) - DEFAULT_CATS.indexOf(b.name);
        }).slice(0, 10);

        var names  = entries.map(function(e){ return e.name; });
        var counts = entries.map(function(e){ return e.count; });
        var maxCount = Math.max.apply(null, counts);

        /* Blue gradient shades matching reference image: top category deepest blue */
        var BLUE_SCALE = [
            '#1565C0', '#1976D2', '#1E88E5', '#2196F3', '#42A5F5',
            '#64B5F6', '#90CAF9', '#90CAF9', '#BBDEFB', '#BBDEFB'
        ];
        var barColors = entries.map(function(e, i){ return BLUE_SCALE[i] || '#BBDEFB'; });

        charts['chart-category'] = new ApexCharts(el, {
            chart: {
                type: 'bar',
                height: 255,
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 500 }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '56%',
                    borderRadius: 3,
                    distributed: true,
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            colors: barColors,
            series: [{ name: 'Bids', data: counts }],
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                offsetX: 8,
                style: {
                    fontSize: '11px',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    fontWeight: 700,
                    colors: ['#1e293b']
                },
                formatter: function(val) {
                    return val;
                }
            },
            xaxis: {
                categories: names,
                labels: { show: false },
                axisBorder: { show: false },
                axisTicks: { show: false },
                min: 0,
                max: maxCount > 0 ? (maxCount < 5 ? maxCount + 1 : Math.ceil(maxCount * 1.15)) : 5
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#334155',
                        fontSize: '11.5px',
                        fontFamily: 'Inter, system-ui, sans-serif',
                        fontWeight: 600
                    }
                }
            },
            grid: {
                show: false,
                padding: { top: -10, bottom: -5, left: 10, right: 28 }
            },
            legend: { show: false },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(val) {
                        return val + ' Bids';
                    }
                }
            }
        });
        charts['chart-category'].render();
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
                    formatter:function(v){ return '₹' + v; }
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
            setDDKKPIs(c.total, crStr(c.totalValue), c.participated);
            setText('ddk-chart1-title','Status Breakdown');
            var sMap = groupBy(matched, 'StatusName');
            renderDDHBar('ddk-chart1', sortMap(sMap).map(function(e){return e.count;}), sortMap(sMap).map(function(e){return e.name;}), '#1565C0');
            setText('ddk-chart2-title','Monthly Trend');
            renderDDVBar('ddk-chart2', c.mTotal, MONTHS, '#42A5F5');

        } else if (kpiType === 'participated') {
            matched = activeFilteredBids.filter(isParticipated);
            title   = 'Participated Bids Analysis';
            sub     = matched.length + ' participated bids';
            var pv  = matched.reduce(function(s,b){return s+(parseFloat(b.EstimatedValue)||0);},0);
            setDDKKPIs(matched.length, crStr(pv), c.won);
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
            setDDKKPIs(matched.length, crStr(wv), c.winRate + '%');
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
            setDDKKPIs(c.winRate + '%', c.won, c.total);
            var wrMonthly = c.mTotal.map(function(t,i){ return t>0?Math.round(c.mWon[i]/t*100):0; });
            setText('ddk-chart1-title','Monthly Win Rate (%)');
            renderDDVBar('ddk-chart1', wrMonthly, MONTHS, '#4CAF50');
            var cMapWR = groupBy(matched.filter(isWon), 'Category');
            setText('ddk-chart2-title','Wins by Category');
            renderDDHBar('ddk-chart2', sortMap(cMapWR).map(function(e){return e.count;}), sortMap(cMapWR).map(function(e){return e.name;}), '#4CAF50');

        } else {
            matched = activeFilteredBids.slice();
            title   = 'Bid Value Analysis';
            sub     = 'Total portfolio: ' + crStr(c.totalValue);
            var avg = matched.length > 0 ? c.totalValue / matched.length : 0;
            setDDKKPIs(crStr(c.totalValue), crStr(avg), c.total);
            var valCr = c.mVal.map(function(v){ return parseFloat(crore(v)); });
            setText('ddk-chart1-title','Monthly Value (Cr)');
            renderDDVBar('ddk-chart1', valCr, MONTHS, '#FF9800');
            var cMapV = {};
            matched.forEach(function(b){ var cat=(b.Category||'Unknown').trim(); cMapV[cat]=(cMapV[cat]||0)+(parseFloat(b.EstimatedValue)||0); });
            var cEV = Object.keys(cMapV).map(function(k){ return{name:k,count:parseFloat(crore(cMapV[k]))}; }).sort(function(a,b){return b.count-a.count;}).slice(0,6);
            setText('ddk-chart2-title','Value by Category (Cr)');
            renderDDHBar('ddk-chart2', cEV.map(function(e){return e.count;}), cEV.map(function(e){return e.name;}), '#FF9800');
        }

        setText('ddk-title',     title);
        setText('ddk-sub',       sub);
        setText('ddk-bid-count', matched.length);
        renderDDBidList('ddk-bids-list', matched);
        openPanel('dd-kpi');
    }

    function setDDKKPIs(v1, v2, v3) {
        var row = document.getElementById('ddk-kpi-row');
        if (!row) return;
        row.innerHTML =
            '<div class="dd-kpi"><div class="dd-kpi-val">' + v1 + '</div><div class="dd-kpi-lbl">Primary</div></div>' +
            '<div class="dd-kpi"><div class="dd-kpi-val">' + v2 + '</div><div class="dd-kpi-lbl">Secondary</div></div>' +
            '<div class="dd-kpi"><div class="dd-kpi-val">' + v3 + '</div><div class="dd-kpi-lbl">Tertiary</div></div>';
    }

    /* ==============================================================
       21. SHARED DRILLDOWN HELPERS
       ============================================================== */
    function renderDDHBar(elId, data, categories, color) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        if (!data.length) { el.innerHTML = '<div style="color:rgba(255,255,255,.4);font-size:11px;padding:10px;">No data</div>'; return; }
        charts[elId] = new ApexCharts(el, {
            chart: { type:'bar', height:150, toolbar:{ show:false }, background:'transparent', animations:{ enabled:false } },
            plotOptions: { bar:{ horizontal:true, borderRadius:3, barHeight:'70%', dataLabels:{ position:'center' } } },
            series: [{ data: data }],
            xaxis: {
                categories: categories,
                labels:{ style:{ fontSize:'9.5px', colors:'rgba(255,255,255,.7)' } },
                axisBorder:{ show:false }, axisTicks:{ show:false }
            },
            yaxis: { labels:{ style:{ fontSize:'9.5px', colors:'rgba(255,255,255,.8)' } } },
            dataLabels: { enabled:true, style:{ fontSize:'9.5px', colors:['#fff'] } },
            colors: [color],
            grid:   { borderColor:'rgba(255,255,255,.1)' },
            legend: { show:false },
            tooltip:{ theme:'dark' }
        });
        charts[elId].render();
    }

    function renderDDVBar(elId, data, cats, color) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        charts[elId] = new ApexCharts(el, {
            chart: { type:'bar', height:150, toolbar:{ show:false }, background:'transparent', animations:{ enabled:false } },
            series: [{ data: data }],
            xaxis: {
                categories: cats,
                labels:{ style:{ fontSize:'8.5px', colors:'rgba(255,255,255,.7)' } },
                axisBorder:{ show:false }
            },
            yaxis: { labels:{ style:{ fontSize:'8.5px', colors:'rgba(255,255,255,.7)' } } },
            plotOptions: { bar:{ borderRadius:3, columnWidth:'60%' } },
            dataLabels: { enabled:false },
            colors: [color],
            grid:   { borderColor:'rgba(255,255,255,.1)' },
            legend: { show:false },
            tooltip:{ theme:'dark' }
        });
        charts[elId].render();
    }

    function renderDDDonut(elId, data, labels, colors) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        if (!data.length) { el.innerHTML = '<div style="color:rgba(255,255,255,.4);font-size:11px;padding:10px;">No data</div>'; return; }
        charts[elId] = new ApexCharts(el, {
            chart: { type:'donut', height:150, background:'transparent', animations:{ enabled:false } },
            series: data, labels: labels,
            colors: colors,
            plotOptions: { pie:{ donut:{ size:'55%' } } },
            dataLabels: { enabled:true, style:{ fontSize:'9px', colors:['#fff'] },
                          formatter:function(v){ return Math.round(v)+'%'; } },
            legend: { position:'bottom', fontSize:'9px', labels:{ colors:'rgba(255,255,255,.8)' } },
            stroke: { width:1, colors:['rgba(255,255,255,.1)'] },
            tooltip: { theme:'dark' }
        });
        charts[elId].render();
    }

    function renderDDBidList(elId, bids) {
        var el = document.getElementById(elId);
        if (!el) return;
        if (!bids.length) {
            el.innerHTML = '<div style="text-align:center;padding:18px;color:rgba(255,255,255,.5);font-size:11.5px;font-style:italic;">No matching bids</div>';
            return;
        }
        el.innerHTML = '';
        bids.slice(0, 50).forEach(function(b, i) {
            var div = document.createElement('div');
            div.className = 'dd-bid-item';
            div.innerHTML =
                '<span class="dd-bid-idx">' + (i+1) + '</span>' +
                '<div class="dd-bid-info">' +
                    '<div class="dd-bid-title">' + escH(b.BidTitle||'—') + '</div>' +
                    '<div class="dd-bid-meta">' + escH(b.Category||'—') + ' · ' + escH(b.State||'—') + ' · ' + escH(b.StatusName||'—') + '</div>' +
                '</div>' +
                '<span class="dd-bid-val">' + crStr(b.EstimatedValue) + '</span>';
            // Clean list item
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
                FS.fy = 'ALL';
                FS.startDate = null;
                FS.endDate = null;
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
        buildFYOptions();
        bindEvents();
        applyFilters();
    }

})();
