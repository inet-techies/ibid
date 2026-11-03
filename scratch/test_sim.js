// Test simulation of compute and updateKPIs
var fs = require('fs');
var js = fs.readFileSync('themes/bo_theme/assets/js/dashboard.js', 'utf8');

// Sample bid from database
var testBids = [
    {
        Bcid: 1,
        BidTitle: 'WEBEL TECHNOLOGY LIMITED',
        TenderId: 'WTL/WBPNB/CCTV/26-27/046',
        SummaryKeywords: 'ANPR, CCTV Surveillance, Body Worn Camera',
        Category: '001',
        State: 'Andhra Pradesh',
        BidStatus: 1,
        StatusName: 'Created',
        EstimatedValue: '10000.00',
        PublishedDate: '2026-09-01',
        CreationTs: '2026-11-03 13:39:20'
    }
];

// Extract functions to test
eval(js.substring(js.indexOf('function formatCurrencySmart'), js.indexOf('function updateAllCharts()')));

var c = compute(testBids);
console.log("Total Bids:", c.total);
console.log("Participated Bids:", c.participated);
console.log("Won Bids:", c.won);
console.log("Win Rate:", c.winRate + '%');
console.log("Won Value:", formatCurrencySmart(c.wonValue));
console.log("Total Value:", formatCurrencySmart(c.totalValue));

