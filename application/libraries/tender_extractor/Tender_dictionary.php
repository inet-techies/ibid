<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Universal Tender Field Dictionary
 *
 * Configurable repository of field names, synonyms, abbreviations, regex patterns,
 * and context exclusion rules for Indian Central Govt, State Govt, GeM, PSU,
 * Municipal, and Private-sector tenders.
 *
 * Maintains strictly segregated date fields so dates are never merged or confused.
 */
class Tender_dictionary
{
    /**
     * Dictionary registry of supported fields
     */
    protected static $dictionary = [
        'tender_reference' => [
            'name'     => 'Tender Reference Number',
            'synonyms' => [
                'Tender Reference Number', 'Tender Reference No', 'Tender Ref No', 'Tender Ref. No.',
                'Tender ID', 'Tender Notice No', 'Tender Notice Number', 'NIT No', 'NIT Number',
                'Notice Inviting Tender No', 'RFP No', 'RFP Number', 'Request for Proposal No',
                'Bid Reference', 'Bid Reference No', 'Bid/RA Number', 'GEM Bid Number', 'GeM Tender ID',
                'Enquiry No', 'Enquiry Number', 'E-Tender No', 'E-Tender ID', 'E-Procurement Ref',
                'Tender Notification No', 'Document No', 'IFB No', 'Invitation for Bids No',
                'Bid Document No', 'Tender No', 'Tender Sl No', 'RFP Notice No'
            ],
            'label_patterns' => [
                '/(?:Tender\s+Reference\s+No\.?|Tender\s+Ref\s*(?:\.?|No\.?)|Tender\s+ID|NIT\s+No\.?|Notice\s+Inviting\s+Tender\s+No\.?|RFP\s+(?:Notice\s+)?No\.?|Bid\s+Reference\s*(?:No\.?)?|Bid\/RA\s+Number|GeM\s+Bid\s+Number|E-Tender\s+No\.?|Enquiry\s+No\.?|Tender\s+No\.?)\s*[:\t\-# ]+([A-Za-z0-9][A-Za-z0-9\/_\-:\.]{3,70})/iu',
                '/\b(GEM\/\d{4}\/[B|R]\/\d+)\b/i'
            ],
            'exclude_patterns' => [
                '/^(?:GSTIN|PAN|CIN|DIN|ISO|IEC|MSME|UDYAM|Phone|Mobile|Fax|PIN|Pincode)/i',
                '/^\d{6}$/',
                '/^\d{10}$/'
            ]
        ],

        'issuing_authority' => [
            'name'     => 'Issuing Authority',
            'synonyms' => [
                'Issuing Authority', 'Organization Name', 'Department Name', 'Name of Department',
                'Purchaser', 'Employer', 'Client', 'Procuring Entity', 'Tender Inviting Authority',
                'TIA', 'Authority', 'Issued By', 'Corporation', 'Directorate', 'Commissionerate',
                'Board', 'Municipal Corporation', 'Nigam', 'Parishad', 'Public Sector Undertaking'
            ],
            'label_patterns' => [
                '/(?:Tender\s+Inviting\s+Authority|Tendering\s+Authority|Issuing\s+Authority|Procuring\s+Entity|Issued\s+By|Name\s+of\s+(?:the\s+)?(?:Employer|Purchaser|Client))\s*[:\t\- ]+(?:the\s+|m\/s\.?\s+)?([A-Za-z0-9\s.,&\-\'\(\)]{4,100})/iu',
                '/\b([A-Z][A-Za-z0-9\s.,&\-]{3,60}\s+(?:Police|Corporation|Directorate|Commissionerate|Department|Board|Nigam|Limited|Ltd\.?))\b/u'
            ],
            'exclude_patterns' => [
                '/^(?:Notice|Tender|Corrigendum|Contents|Index|Page\s*\d+|Table|the|a|an)\b/i'
            ]
        ],

        'tender_title' => [
            'name'     => 'Tender Title',
            'synonyms' => [
                'Name of Work', 'Name of the Work', 'Name of Item', 'Name of the Item',
                'Brief description of material', 'Description of Work', 'Tender Title',
                'Title of Work', 'Scope of Procurement', 'Subject'
            ],
            'label_patterns' => [],
            'exclude_patterns' => []
        ],

        // ── 1. Tender Publication Date ──
        'published_date' => [
            'name'     => 'Tender Publication Date',
            'synonyms' => [
                'Publishing Date', 'Published Date', 'Tender Published Date', 'Date of Publishing',
                'Publication Date', 'Published On', 'Date of uploading of N.I.T.', 'tender publication in the websites', 'tender publication',
                'Date and time of tender publication', 'e-Published Date'
            ],
            'label_patterns' => [
                '/(?:Date\s+(?:&|and)\s+time\s+of\s+tender\s+publication(?:\s+in\s+(?:the\s+)?websites?)?|tender\s+publication(?:\s+in\s+(?:the\s+)?websites?)?|Publishing\s+Date|Published\s+(?:On|Date)|Date\s+of\s+Publishing|Publication\s+Date|Date\s+of\s+uploading\s+of\s+N\.?I\.?T\.?|Date\s+of\s+publication|e[\s\-]Published\s+Date)\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Submission|Opening|Pre-Bid|Download|Sale|Queries)/i'
            ]
        ],

        // ── 2. NIT Issue Date ──
        'nit_issue_date' => [
            'name'     => 'NIT Issue Date',
            'synonyms' => [
                'NIT Date', 'Date of NIT', 'NIT Issue Date', 'Notice Date', 'Date of Notice Inviting Tender', 'Date of Issue', 'Dated'
            ],
            'label_patterns' => [
                '/(?:NIT\s+(?:Issue\s+)?Date|Date\s+of\s+(?:the\s+)?NIT|Notice\s+Date|Date\s+of\s+Issue|Dated)\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Submission|Opening|Pre-Bid|Download)/i'
            ]
        ],

        // ── 3. Document Download Start Date ──
        'document_download_start_date' => [
            'name'     => 'Document Download Start Date',
            'synonyms' => [
                'Document Download Start Date', 'Documents download start date', 'Tender Sale Start Date',
                'Date of Downloading', 'Availability of RFP Document', 'Bid Document Sale Start Date'
            ],
            'label_patterns' => [
                '/(?:(?:Document\s+)?(?:Download|Downloading|Sale)\s+(?:Start\s+Date|date)|Availability\s+of\s+(?:RFP|Tender)\s+Document|Date\s+of\s+Downloading)\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ]
        ],

        // ── 4. Document Download End Date ──
        'document_download_end_date' => [
            'name'     => 'Document Download End Date',
            'synonyms' => [
                'Document Download End Date', 'Document Download Closing Date', 'Tender Sale End Date',
                'Document Sale Close Date', 'Tender Download Closing Date'
            ],
            'label_patterns' => [
                '/(?:(?:Document\s+)?(?:Download|Sale)\s+(?:End|Closing|Close)\s+Date|Tender\s+Download\s+(?:End|Closing)\s+Date)\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ]
        ],

        // ── 5. Pre-Bid Meeting Date ──
        'pre_bid_date' => [
            'name'     => 'Pre-Bid Meeting Date',
            'synonyms' => [
                'Pre-Bid Meeting Date', 'Pre-Bid Conference Date', 'Date of Pre-Bid Meeting',
                'Pre-Bid Meeting', 'Pre-Bid Conference', 'Pre-Bid Meeting (On Line)', 'Clarification Meeting',
                'Pre-Bid Discussion', 'Pre Bid Meeting / Conference', 'Data and time of Pre-Bid Meeting',
                'Date and time of Pre-Bid Meeting'
            ],
            'label_patterns' => [
                '/(?:Pre[\s\-]Bid\s+(?:Meeting|Conference|Discussion|Clarification)(?:\s+(?:Date|date\s+(?:&|and)\s+time))?|Dat[ae]\s+(?:&|and)\s+time\s+of\s+Pre[\s\-]Bid\s+(?:Meeting|Conference))\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Pre[\s\-]Bid\s+Quer(?:y|ies)\s+Submission|Clarification\s+Deadline|Bid\s+Submission|Bid\s+Opening)/i'
            ]
        ],

        // ── 6. Query Submission Deadline ──
        'prebid_query_start_date' => [
            'name'     => 'Pre-Bid Query Start Date',
            'synonyms' => [
                'Pre-Bid Query Start Date', 'Clarification Start Date', 'Start Date for Seeking Clarification',
                'Pre-bid Query Submission Start Date', 'Query Submission Start Date', 'Start Date for receiving queries'
            ],
            'label_patterns' => [
                '/(?:Pre[\s\-]Bid\s+Quer(?:y|ies)\s+Start(?:\s+Date)?|Clarification\s+Start\s+Date|Start\s+Date\s+(?:for|of)\s+(?:Seeking\s+|Receiving\s+)?Clarifications?|Start\s+Date\s+for\s+receiving\s+queries)\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:End|Deadline|Last\s+Date|Cutoff)/i'
            ]
        ],

        'query_deadline' => [
            'name'     => 'Query Submission Deadline',
            'synonyms' => [
                'Query Submission Deadline', 'Last Date for Pre-Bid Queries', 'Last Date for Receiving Queries',
                'Pre-bid Query Cutoff', 'Clarification Due Date', 'Date for Receipt of Queries',
                'Vendor Query Deadline', 'Last Date of Clarification', 'queries reaching', 'End Date for Seeking Clarification'
            ],
            'label_patterns' => [
                '/(?:Last\s+Date\s+(?:and\s+Time\s+)?(?:for|of)\s+(?:Pre[\s\-]Bid\s+)?(?:Queries|Seeking\s+Clarifications?|Receiving\s+Queries)|Pre[\s\-]bid\s+Query\s+(?:Submission\s+)?(?:Last\s+Date|Cutoff|Deadline)|Clarification\s+(?:End|Due)\s+Date|queries\s+reaching[^\n\r]{0,30}\s+by)\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Bid\s+Submission|Technical\s+Opening|Start\s+Date)/i'
            ]
        ],

        'prebid_response_date' => [
            'name'     => 'Pre-Bid Response Deadline',
            'synonyms' => [
                'Pre-Bid Response Deadline', 'Response to Pre-bid Queries', 'Issue of Clarification Date',
                'Date of Pre-bid Reply', 'Pre-bid Query Reply Date', 'Clarification Response Date',
                'Uploading of reply to Pre-bid queries', 'Response to Clarifications'
            ],
            'label_patterns' => [
                '/(?:Response\s+(?:to|of)\s+Pre[\s\-]Bid\s+Queries|Issue\s+of\s+Clarifications?(?:\s+Date)?|Pre[\s\-]Bid\s+(?:Query\s+)?(?:Response|Reply)(?:\s+Date)?|Uploading\s+of\s+reply\s+to\s+pre[\s\-]bid)\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ]
        ],

        // ── 7. Bid Submission Start Date ──
        'bid_submission_start_date' => [
            'name'     => 'Bid Submission Start Date',
            'synonyms' => [
                'Bid Submission Start Date', 'Bid Submission start date & time', 'Submission Start Date',
                'Online Bid Submission Start Date', 'Start Date of Submission of Bids', 'Bid Submission start date & time (On line)'
            ],
            'label_patterns' => [
                '/(?:Bid\s+Submission\s+Start\s+(?:Date|date\s+(?:&|and)\s+time)|Submission\s+Start\s+Date|Start\s+Date\s+(?:and\s+Time\s+)?of\s+Bid\s+Submission)\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:End|Closing|Deadline|Last\s+Date)/i'
            ]
        ],

        // ── 8. Bid Submission Deadline / Closing Date ──
        'submission_date' => [
            'name'     => 'Bid Submission Deadline',
            'synonyms' => [
                'Last Date of Submission', 'Submission Deadline', 'Bid Submission End Date',
                'Bid Submission Closing Date', 'Bid Closing Date', 'Due Date', 'Submission Due Date',
                'Last date & time of Bid Submission', 'Bid End Date/Time', 'Online Submission Closing Date',
                'Last Date for Submission of RFP', 'Date of Submission of Bids', 'Bid Due Date',
                'closing of submission', 'Date and time for closing of submission', 'closing of bid submission'
            ],
            'label_patterns' => [
                '/(?:Last\s+Date\s+(?:&|and)\s+time\s+of\s+Bid\s+Submission|Bid\s+Submission\s+(?:Closing|End|Last)\s+(?:Date|date\s+(?:&|and)\s+time)|Submission\s+(?:End|Due|Closing|Deadline)\s+Date|Last\s+Date\s+(?:and\s+Time\s+)?(?:of|for)\s+Submission\s+of\s+(?:Bids?|RFP|Proposals?)|Bid\s+Closing\s+Date|Bid\s+Due\s+Date|Online\s+Bid\s+Submission\s+End\s+Date|Date\s+(?:and\s+time\s+)?for\s+closing\s+of\s+submission|closing\s+of\s+submission)\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu',
                '/\bBid\s+End\s+Date\/Time\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Start|Commencement|Downloading|Quer(?:y|ies)|Clarifications?|Response\s+Submission)/i'
            ]
        ],

        // ── 9. Submission Deadline Time ──
        'submission_time' => [
            'name'     => 'Submission Deadline Time',
            'synonyms' => [
                'Submission Time', 'Closing Time', 'Due Time', 'Last Time of Submission',
                'End Time', 'Time of Bid Submission', 'Submission Cutoff Time'
            ],
            'label_patterns' => [
                '/(?:Submission|Closing|Due)\s+Time\s*[:\t\- ]*(\d{1,2}(?::\d{2})?\s*(?:AM|PM|Hrs|Hours)?)/iu',
                '/(?:up\s*to|upto|till|at|by)\s*(\d{1,2}(?::\d{2})?\s*(?:AM|PM|Hrs|Hours|a\.m\.|p\.m\.))/iu'
            ]
        ],

        // ── 10. PQ Bid Opening Date ──
        'pq_bid_opening_date' => [
            'name'     => 'PQ Bid Opening Date',
            'synonyms' => [
                'PQ Bid Opening Date', 'PQ Opening', 'Pre-Qualification Bid Opening Date',
                'Eligibility Bid Opening Date', 'Opening of PQ Bids', 'Pre-Qualification Opening',
                'Cover 0 Opening', 'Opening of Eligibility Criteria', 'PQ Opening Date', 'Opening of Pre-Qualification Bids'
            ],
            'label_patterns' => [
                '/(?:PQ\s+(?:Bid\s+)?Opening(?:\s+Date)?|Pre[\s\-]Qualification\s+(?:Bid\s+)?Opening(?:\s+Date)?|Eligibility\s+(?:Bid\s+)?Opening(?:\s+Date)?|Cover\s*0\s+Opening)\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Financial|Price|Commercial|Technical)/i'
            ]
        ],

        // ── 11. Technical Bid Opening Date ──
        'technical_opening_date' => [
            'name'     => 'Technical Bid Opening Date',
            'synonyms' => [
                'Technical Bid Opening Date', 'Date & time of Technical Bid Opening', 'Opening of Technical Bids',
                'Opening of Pre-Qualification and Technical Bids', 'Bid opening date & time for Technical Proposals',
                'Technical Proposal Opening Date', 'Cover 1 Opening Date', 'Date and Time of Opening of General & Technical Bid',
                'Date and time for opening of technical bid document', 'opening of technical bid document', 'opening of technical bid',
                'Date and time for opening of technical bid'
            ],
            'label_patterns' => [
                '/(?:Date\s+(?:&|and)\s+time\s+(?:for|of)\s+opening\s+of\s+technical\s+bid(?:\s+document)?|Date\s+(?:&|and)\s+time\s+of\s+Technical\s+Bid\s+Opening|Technical\s+(?:Bid\s+)?Opening(?:\s+Date)?|Opening\s+of\s+(?:Pre[\s\-]Qualification\s+and\s+)?Technical\s+Bids?|Bid\s+opening\s+date\s+(?:&|and)\s+time\s+for\s+Technical\s+Proposals?|Cover\s*1\s+Opening)\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Financial|Price|Commercial|Cover\s*2)/i'
            ]
        ],

        // ── 11. Financial Bid Opening Date ──
        'financial_opening_date' => [
            'name'     => 'Financial Bid Opening Date',
            'synonyms' => [
                'Financial Bid Opening Date', 'Price Bid Opening Date', 'Commercial Bid Opening Date',
                'Date and Time of Opening of Commercial Bid', 'Financial Proposal Opening', 'Cover 2 Opening Date',
                'Date and time for opening of financial bid', 'opening of financial bid', 'Date and time of opening of financial bid'
            ],
            'label_patterns' => [
                '/(?:Date\s+(?:&|and)\s+time\s+(?:for|of)\s+opening\s+of\s+(?:Financial|Price|Commercial)\s+Bid|Date\s+(?:&|and)\s+time\s+of\s+(?:Financial|Price|Commercial)\s+Bid\s+Opening|(?:Financial|Price|Commercial)\s+(?:Bid\s+)?Opening(?:\s+Date)?|Cover\s*2\s+Opening)\s*(?:\([^\)]*\))?\s*[:\t\- ]+([^\r\n]{6,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Technical|Cover\s*1|General)/i'
            ]
        ],

        'emd_value' => [
            'name'     => 'EMD Amount',
            'synonyms' => [
                'EMD', 'EMD Amount', 'Earnest Money Deposit', 'Earnest Money', 'Bid Security',
                'Bid Security Amount', 'Earnest Money Deposit (EMD)', 'EMD / Bid Security',
                'Value of EMD', 'Amount of Bid Security', 'EMD in INR', 'EMD (Rs.)'
            ],
            'label_patterns' => [
                '/(?:Earnest\s+Money\s+Deposit(?:\s*\([^\)]*\))?|EMD\s+(?:Amount|Value|Required)?|Bid\s+Security(?:\s*Amount)?)\s*[:\t\- ]*(?:Rs\.?|INR|₹)?\s*([0-9,]+(?:\.\d{1,2})?(?:\s*(?:Lakhs?|Lacs?|Crores?|Cr|K))?|\bNil\b|\bExempted\b)/iu',
                '/(?:EMD|Bid\s+Security)\s*[:\t\- ]*([^\n\r,;]{1,60})/iu'
            ],
            'exclude_patterns' => [
                '/(?:Performance|Security\s+Deposit|Turnover|Tender\s+Fee|Estimated\s+Cost)/i'
            ]
        ],

        'tender_fee' => [
            'name'     => 'Tender Fee',
            'synonyms' => [
                'Tender Fee', 'Tender Document Fee', 'Cost of Tender Document', 'Tender Cost',
                'Tender Document Cost', 'Document Fee', 'Bid Document Cost', 'Bid Document Price',
                'Processing Fee', 'Tender Processing Fee', 'Tender Document Price', 'Tender Form Fee'
            ],
            'label_patterns' => [
                '/(?:Tender\s+(?:Document\s+)?Fee|Cost\s+of\s+Tender(?:\s+Document)?|Tender\s+(?:Document\s+)?Cost|Bid\s+(?:Document\s+)?(?:Fee|Price)|Tender\s+Processing\s+Fee|Processing\s+Fee)\s*[:\t\- ]*(?:Rs\.?|INR|₹)?\s*([0-9,]+(?:\.\d{1,2})?(?:\s*(?:Lakhs?|Lacs?|Crores?|Cr|K))?|\bNil\b|\bExempted\b|\bFree\b)/iu'
            ]
        ],

        'tender_portal' => [
            'name'     => 'Tender Portal or Website',
            'synonyms' => [
                'Tender Portal', 'Submission Website', 'e-Procurement Portal', 'Portal URL',
                'Website for Submission', 'e-Tender Website', 'Procurement Portal', 'GeM Portal'
            ],
            'label_patterns' => [
                '/(?:e[\s\-]Procurement\s+Portal|Tender\s+Portal|Portal\s+URL|Submission\s+Website|Website\s+for\s+Bidding|Online\s+Submission\s+at|download(?:ed)?\s+from(?:\s+the)?\s+website|Place\s+of\s+Submission\s+of\s+RFP\s+Document)\s*[:\t\- ]*([^\s,;\"\']+)/iu',
                '/\b(https?:\/\/[a-zA-Z0-9.\-_]+\.(?:gov\.in|nic\.in|gem\.gov\.in|in|com|org)[^\s,;\"\'\)]*)/i',
                '/\b(eprocure\.gov\.in|gem\.gov\.in|etenders\.gov\.in|wbtenders\.gov\.in|mahatenders\.gov\.in|mptenders\.gov\.in|hptenders\.gov\.in|jharkhandtenders\.gov\.in|kppp\.karnataka\.gov\.in|assamtenders\.gov\.in|tender\.apeprocurement\.gov\.in|etender\.up\.nic\.in|enivida\.odisha\.gov\.in)\b/i'
            ]
        ],

        'scope_of_work' => [
            'name'     => 'Scope of Work',
            'synonyms' => [
                'Scope of Work', 'Brief Description of Work', 'Schedule of Requirements',
                'Work Description', 'Nature of Work', 'Scope of the Project', 'Objective of Tender',
                'Purpose of RFP', 'Broad Scope of Work'
            ],
            'label_patterns' => [
                '/(?:Scope\s+of\s+(?:the\s+)?Work|Brief\s+Description\s+of\s+Work|Schedule\s+of\s+Requirements?|Broad\s+Scope\s+of\s+Work|Objective\s+of\s+(?:the\s+)?(?:Tender|RFP))\s*[:\t\- ]*([^\r\n]{10,250})/iu'
            ]
        ],

        'work_location' => [
            'name'     => 'Work Location / Consignee Location',
            'synonyms' => [
                'Work Location', 'Project Location', 'Place of Delivery', 'Consignee Location',
                'Site Location', 'Delivery Site', 'Installation Site', 'Jurisdiction'
            ],
            'label_patterns' => [
                '/(?:Work\s+Location|Project\s+Location|Place\s+of\s+(?:Work|Delivery)|Consignee\s+Location|Site\s+Location)\s*[:\t\- ]+([^\r\n]{3,100})/iu'
            ]
        ],

        'contract_period' => [
            'name'     => 'Contract Period / Duration',
            'synonyms' => [
                'Contract Period', 'Period of Work', 'Completion Period', 'Duration of Contract',
                'Project Tenure', 'Term of Agreement', 'Validity of Contract', 'Execution Period'
            ],
            'label_patterns' => [
                '/(?:Period\s+of\s+(?:Work|Contract|Completion)|Contract\s+Period|Duration\s+of\s+Contract|Project\s+Tenure|Completion\s+Period)\s*[:\t\- ]+([^\r\n]{2,80})/iu',
                '/(\d{1,2}\s*(?:Years?|Months?|Weeks?|Days?)\s+(?:from\s+(?:the\s+)?date\s+of\s+(?:LOA|Work\s+Order|Agreement)|contract\s+period))/iu'
            ]
        ],

        'eligibility_criteria' => [
            'name'     => 'Eligibility Criteria',
            'synonyms' => [
                'Eligibility Criteria', 'Minimum Eligibility Criteria', 'Bidder Eligibility',
                'Pre-Qualification Criteria', 'PQC Criteria', 'Qualifying Requirements', 'Technical Eligibility'
            ],
            'label_patterns' => [
                '/(?:Eligibility\s+Criteria|Minimum\s+Eligibility(?:\s+Criteria)?|Pre[\s\-]Qualification\s+Criteria|Qualifying\s+Requirements?)\s*[:\t\- ]*([^\r\n]{10,250})/iu'
            ]
        ],

        'required_documents' => [
            'name'     => 'Required Documents / Checklist',
            'synonyms' => [
                'Required Documents', 'Documents to be Submitted', 'Checklist of Documents',
                'Mandatory Submissions', 'Enclosures Required', 'List of Documents'
            ],
            'label_patterns' => [
                '/(?:Documents?\s+(?:to\s+be\s+Submitted|Required)|Checklist\s+of\s+Documents?|Mandatory\s+Documents?|List\s+of\s+Documents?)\s*[:\t\- ]*([^\r\n]{10,250})/iu'
            ]
        ],

        'technical_requirements' => [
            'name'     => 'Technical Requirements / Specifications',
            'synonyms' => [
                'Technical Requirements', 'Technical Specifications', 'Technical Compliance',
                'Functional Requirements', 'System Requirements', 'Specifications'
            ],
            'label_patterns' => [
                '/(?:Technical\s+Specifications?|Technical\s+Requirements?|Functional\s+Specifications?|System\s+Requirements?)\s*[:\t\- ]*([^\r\n]{10,250})/iu'
            ]
        ],

        'important_conditions' => [
            'name'     => 'Important Tender Conditions',
            'synonyms' => [
                'Important Conditions', 'Key Terms and Conditions', 'Special Conditions of Contract',
                'SCC', 'General Conditions of Contract', 'GCC', 'Penalty Clauses', 'Liquidated Damages'
            ],
            'label_patterns' => [
                '/(?:Special\s+Conditions\s+of\s+Contract|Important\s+(?:Tender\s+)?Conditions?|Liquidated\s+Damages|Penalty\s+Clause)\s*[:\t\- ]*([^\r\n]{10,250})/iu'
            ]
        ],

        'keywords_summary' => [
            'name'     => 'Keywords and Summary',
            'synonyms' => [
                'Keywords', 'Summary', 'Highlights', 'Key Points', 'Brief Overview'
            ],
            'label_patterns' => []
        ]
    ];

    /**
     * Get dictionary definition for a field or all fields
     *
     * @param string|null $field
     * @return array|null
     */
    public static function get($field = null)
    {
        if ($field === null) {
            return self::$dictionary;
        }
        return isset(self::$dictionary[$field]) ? self::$dictionary[$field] : null;
    }

    /**
     * Get all supported field keys
     *
     * @return array
     */
    public static function getSupportedFields()
    {
        return array_keys(self::$dictionary);
    }

    /**
     * Get all synonyms for a field
     *
     * @param string $field
     * @return array
     */
    public static function getSynonyms($field)
    {
        return isset(self::$dictionary[$field]['synonyms']) ? self::$dictionary[$field]['synonyms'] : [];
    }

    /**
     * Get all label patterns for a field
     *
     * @param string $field
     * @return array
     */
    public static function getLabelPatterns($field)
    {
        return isset(self::$dictionary[$field]['label_patterns']) ? self::$dictionary[$field]['label_patterns'] : [];
    }

    /**
     * Get exclude patterns for a field
     *
     * @param string $field
     * @return array
     */
    public static function getExcludePatterns($field)
    {
        return isset(self::$dictionary[$field]['exclude_patterns']) ? self::$dictionary[$field]['exclude_patterns'] : [];
    }
}
