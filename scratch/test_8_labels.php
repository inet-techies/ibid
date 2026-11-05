<?php
define('BASEPATH', 'true');

function classifyActivity($label)
{
    $lbl = trim(preg_replace('/\s+/', ' ', $label));
    // Normalize OCR variations
    $lbl = preg_replace('/\b0pening\b/i', 'opening', $lbl);
    $lbl = preg_replace('/\bd0cuments?\b/i', 'document', $lbl);

    if (strlen($lbl) < 3) return null;

    // Discard table headers / non-milestone rows
    if (preg_match('/^(?:sl\.?\s*no\.?|s\.no|item\s+no|description|event|activity|particulars|milestones?|schedule|critical\s+dates?|key\s+dates?|stage|information)$/i', $lbl)) {
        return null;
    }

    // 12. Financial Bid Opening
    if (preg_match('/\b(?:financial|price|commercial|cover\s*2)\b/i', $lbl)) {
        if (preg_match('/\b(?:opening|open|opened|evaluation)\b/i', $lbl)) {
            return 'financial_bid_opening';
        }
    }

    // 11. Technical Bid Opening
    if (preg_match('/\b(?:technical|cover\s*1)\b/i', $lbl)) {
        if (preg_match('/\b(?:opening|open|opened|evaluation)\b/i', $lbl)) {
            return 'technical_bid_opening';
        }
    }

    // 10. PQ / Pre-Qualification Bid Opening
    if (preg_match('/\b(?:pq|pre[\s\-]qualification|eligibility|cover\s*0)\b/i', $lbl)) {
        if (preg_match('/\b(?:opening|open|opened)\b/i', $lbl)) {
            return 'pq_bid_opening';
        }
    }

    // Generic Bid Opening (when technical/financial not specified, default is Technical Opening)
    if (preg_match('/\b(?:tender|bid|bids)\s+opening\b/i', $lbl) || preg_match('/\bopening\s+of\s+(?:the\s+)?(?:bids?|tender|rfp)\b/i', $lbl)) {
        if (!preg_match('/\b(?:financial|price|commercial)\b/i', $lbl)) {
            return 'technical_bid_opening';
        }
    }

    // 7. Pre-bid Response / Reply to queries
    if (preg_match('/\b(?:reply|response|replies)\b/i', $lbl)) {
        if (preg_match('/\b(?:pre[\s\-]bid|prebid|queries|query|clarifications?|vendor)\b/i', $lbl) || preg_match('/\bresponse\s+submission\b/i', $lbl)) {
            return 'prebid_response';
        }
    }

    // 4 & 5. Pre-bid Query Start / End
    if (preg_match('/\b(?:queries|query|clarification|clarifications)\b/i', $lbl)) {
        if (preg_match('/\b(?:start|commenc|from)\b/i', $lbl)) {
            return 'prebid_query_start';
        }
        if (preg_match('/\b(?:last|end|clos|deadline|cutoff|receipt|receiving|due|reaching)\b/i', $lbl)) {
            return 'prebid_query_end';
        }
        if (preg_match('/\bseeking\s+clarification\b/i', $lbl)) {
            return 'prebid_query_end';
        }
    }

    // 6. Pre-bid Meeting
    if (preg_match('/\b(?:pre[\s\-]bid|prebid)\b/i', $lbl)) {
        if (preg_match('/\b(?:meeting|conference|discussion|briefing)\b/i', $lbl)) {
            return 'prebid_meeting';
        }
    }

    // 8 & 9. Bid Submission Start / End (Strict: exclude queries / clarification submission)
    if (!preg_match('/\b(?:queries|query|clarification|clarifications)\b/i', $lbl)) {
        if (preg_match('/\b(?:submission|submitting|submit)\b/i', $lbl) || preg_match('/\bclosing\s+of\s+submission\b/i', $lbl) || preg_match('/\bbid\s+(?:end|due|closing)\b/i', $lbl)) {
            if (preg_match('/\b(?:start|commenc|from)\b/i', $lbl)) {
                return 'bid_submission_start';
            }
            if (preg_match('/\b(?:end|clos|closing|last|deadline|due|cutoff|upto|till)\b/i', $lbl) || preg_match('/\bclosing\s+of\s+submission\b/i', $lbl)) {
                return 'bid_submission_end';
            }
            if (preg_match('/\b(?:date|last\s+date)\s+(?:and\s+time\s+)?(?:of|for)\s+submission\b/i', $lbl)) {
                return 'bid_submission_end';
            }
        }
    }

    // 2 & 3. Document Sale / Download Start / End
    if (preg_match('/\b(?:download|downloading|sale|availability|purchase)\b/i', $lbl)) {
        if (preg_match('/\b(?:start|commenc|from)\b/i', $lbl)) {
            return 'document_sale_start';
        }
        if (preg_match('/\b(?:end|clos|closing|last|deadline|to)\b/i', $lbl)) {
            return 'document_sale_end';
        }
        if (preg_match('/\bavailability\s+of\b/i', $lbl)) {
            return 'document_sale_start';
        }
    }

    // 1. Tender Publication
    if (preg_match('/\b(?:publication|publishing|published)\b/i', $lbl) || preg_match('/\bdate\s+of\s+uploading\b/i', $lbl) || preg_match('/\btender\s+date\b/i', $lbl) || preg_match('/\bnit\s+date\b/i', $lbl)) {
        if (!preg_match('/\b(?:submission|opening|pre[\s\-]bid|download|sale)\b/i', $lbl)) {
            return 'tender_publication';
        }
    }

    return null;
}

$labels = [
    '1 Documents Publish/download/Sale Start Date',
    '2 Pre-bid Queries Submission Start Date',
    '3 Pre-bid Queries Submission End Date',
    '4 Pre-bid Meeting Date',
    '5 Pre-bid Queries Response Submission Date',
    '6 Bid Submission Start Date',
    '7 Bid Submission End Date',
    '8 Opening of PQ Bid'
];

foreach ($labels as $lbl) {
    $clean = preg_replace('/^\d+\s+/', '', $lbl);
    echo "$lbl => " . (classifyActivity($clean) ?? 'NULL') . "\n";
}
