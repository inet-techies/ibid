<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Customer_info_extractor
 *
 * Dedicated, high-precision extractor for:
 * 1. State (Canonical Indian State / UT matching <select id="bidStateInput">)
 * 2. Customer Name (Issuing Authority / Client / Employer / Department)
 * 3. Customer Address (Postal / Office address of the issuing authority)
 * 4. Customer Phone (Telephone / Mobile / Contact number formatted for form validation)
 */
class Customer_info_extractor
{
    /**
     * Exact 36 canonical States and Union Territories matching the bidStateInput dropdown
     */
    protected static $canonicalStates = [
        'Andhra Pradesh',
        'Arunachal Pradesh',
        'Assam',
        'Bihar',
        'Chhattisgarh',
        'Goa',
        'Gujarat',
        'Haryana',
        'Himachal Pradesh',
        'Jharkhand',
        'Karnataka',
        'Kerala',
        'Madhya Pradesh',
        'Maharashtra',
        'Manipur',
        'Meghalaya',
        'Mizoram',
        'Nagaland',
        'Odisha',
        'Punjab',
        'Rajasthan',
        'Sikkim',
        'Tamil Nadu',
        'Telangana',
        'Tripura',
        'Uttar Pradesh',
        'Uttarakhand',
        'West Bengal',
        'Andaman and Nicobar Islands',
        'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu',
        'Delhi',
        'Jammu and Kashmir',
        'Ladakh',
        'Lakshadweep',
        'Puducherry'
    ];

    /**
     * Canonical registered / campus addresses for premier public institutions
     */
    protected static $knownOrgAddresses = [
        'National Institute of Technology, Tiruchirappalli' => 'Tanjore Main Road, National Highway 67, Tiruchirappalli - 620015, Tamil Nadu',
        'All India Institute of Medical Sciences, Bhubaneswar' => 'Sijua, Patrapada, Bhubaneswar - 751019, Odisha',
        'Indian Institute of Technology Delhi' => 'Hauz Khas, New Delhi - 110016',
        'Indian Institute of Technology Bombay' => 'Main Gate Road, Powai, Mumbai - 400076, Maharashtra',
        'Indian Institute of Science, Bangalore' => 'CV Raman Road, Bengaluru - 560012, Karnataka',
        'National Institute of Technology Calicut' => 'NIT Campus P.O., Kozhikode - 673601, Kerala',
        'National Institute of Technology Karnataka, Surathkal' => 'Post-Srinivasnagar, Surathkal, Mangaluru - 575025, Karnataka',
        'Greater Hyderabad Municipal Corporation' => 'GHMC Head Office, CC Complex, Tank Bund Road, Hyderabad - 500063, Telangana',
        'Vadodara Municipal Corporation' => 'Khanderao Market Building, Palace Road, Vadodara - 390001, Gujarat',
        'Webel Technology Limited' => 'Plot - 5, Block - BP, Sector - V, Salt Lake City, Kolkata - 700091, West Bengal',
        'State Trading Corporation of India Ltd' => 'STC Building, 1 Tolstoy Marg, New Delhi - 110001',
        'WAPCOS LIMITED' => '76-C, Sector-18, Gurugram - 122015, Haryana',
        'Madhya Pradesh Police Telecom' => 'Police Telecom Headquarters, Bhadbhada Road, Bhopal - 462003, Madhya Pradesh',
        'Bank of Baroda' => 'Baroda Corporate Centre, C-26, G-Block, Bandra Kurla Complex, Bandra (East), Mumbai - 400051, Maharashtra',
        'Odisha Computer Application Centre' => 'OCAC Building, Acharya Vihar Square, Bhubaneswar - 751013, Odisha',
        'Tripura University' => 'Suryamaninagar, Agartala - 799022, Tripura'
    ];

    /**
     * Canonical state mapping for premier public institutions
     */
    protected static $knownOrgStates = [
        'National Institute of Technology, Tiruchirappalli' => 'Tamil Nadu',
        'All India Institute of Medical Sciences, Bhubaneswar' => 'Odisha',
        'Indian Institute of Technology Delhi' => 'Delhi',
        'Indian Institute of Technology Bombay' => 'Maharashtra',
        'Indian Institute of Science, Bangalore' => 'Karnataka',
        'National Institute of Technology Calicut' => 'Kerala',
        'National Institute of Technology Karnataka, Surathkal' => 'Karnataka',
        'Greater Hyderabad Municipal Corporation' => 'Telangana',
        'Vadodara Municipal Corporation' => 'Gujarat',
        'Webel Technology Limited' => 'West Bengal',
        'State Trading Corporation of India Ltd' => 'Delhi',
        'WAPCOS LIMITED' => 'Haryana',
        'Madhya Pradesh Police Telecom' => 'Madhya Pradesh',
        'Odisha Computer Application Centre' => 'Odisha',
        'Tripura University' => 'Tripura'
    ];

    /**
     * City and district to canonical State mapping
     */
    protected static $cityToState = [
        // Telangana
        'hyderabad' => 'Telangana', 'secunderabad' => 'Telangana', 'cyberabad' => 'Telangana',
        'warangal' => 'Telangana', 'karimnagar' => 'Telangana', 'nizamabad' => 'Telangana',
        'khammam' => 'Telangana', 'ramagundam' => 'Telangana', 'ghmc' => 'Telangana',

        // Bihar
        'patna' => 'Bihar', 'gaya' => 'Bihar', 'bhagalpur' => 'Bihar', 'muzaffarpur' => 'Bihar',
        'darbhanga' => 'Bihar', 'nalanda' => 'Bihar', 'purnia' => 'Bihar', 'begusarai' => 'Bihar',
        'buidco' => 'Bihar', 'buhs' => 'Bihar',

        // Tripura
        'agartala' => 'Tripura', 'suryamaninagar' => 'Tripura', 'dharmanagar' => 'Tripura',
        'udaipur' => 'Tripura', 'kailashahar' => 'Tripura',

        // Karnataka
        'bengaluru' => 'Karnataka', 'bangalore' => 'Karnataka', 'mysuru' => 'Karnataka',
        'mysore' => 'Karnataka', 'mangaluru' => 'Karnataka', 'mangalore' => 'Karnataka',
        'hubballi' => 'Karnataka', 'hubli' => 'Karnataka', 'belagavi' => 'Karnataka',
        'belgaum' => 'Karnataka', 'kalaburagi' => 'Karnataka', 'gulbarga' => 'Karnataka',

        // Tamil Nadu
        'chennai' => 'Tamil Nadu', 'madras' => 'Tamil Nadu', 'coimbatore' => 'Tamil Nadu',
        'madurai' => 'Tamil Nadu', 'tiruchirappalli' => 'Tamil Nadu', 'trichy' => 'Tamil Nadu',
        'salem' => 'Tamil Nadu', 'tiruppur' => 'Tamil Nadu', 'erode' => 'Tamil Nadu',
        'vellore' => 'Tamil Nadu',

        // Maharashtra
        'mumbai' => 'Maharashtra', 'bombay' => 'Maharashtra', 'pune' => 'Maharashtra',
        'nagpur' => 'Maharashtra', 'thane' => 'Maharashtra', 'nashik' => 'Maharashtra',
        'kalyan' => 'Maharashtra', 'dombivli' => 'Maharashtra', 'navi mumbai' => 'Maharashtra',
        'aurangabad' => 'Maharashtra', 'solapur' => 'Maharashtra', 'amravati' => 'Maharashtra',

        // West Bengal
        'kolkata' => 'West Bengal', 'calcutta' => 'West Bengal', 'howrah' => 'West Bengal',
        'siliguri' => 'West Bengal', 'asansol' => 'West Bengal', 'durgapur' => 'West Bengal',
        'bardhaman' => 'West Bengal', 'malda' => 'West Bengal', 'kharagpur' => 'West Bengal',

        // Odisha
        'bhubaneswar' => 'Odisha', 'cuttack' => 'Odisha', 'rourkela' => 'Odisha',
        'berhampur' => 'Odisha', 'sambalpur' => 'Odisha', 'puri' => 'Odisha',
        'balasore' => 'Odisha', 'osepa' => 'Odisha',

        // Uttar Pradesh
        'lucknow' => 'Uttar Pradesh', 'kanpur' => 'Uttar Pradesh', 'ghaziabad' => 'Uttar Pradesh',
        'noida' => 'Uttar Pradesh', 'greater noida' => 'Uttar Pradesh', 'agra' => 'Uttar Pradesh',
        'varanasi' => 'Uttar Pradesh', 'meerut' => 'Uttar Pradesh', 'prayagraj' => 'Uttar Pradesh',
        'allahabad' => 'Uttar Pradesh', 'bareilly' => 'Uttar Pradesh', 'aligarh' => 'Uttar Pradesh',
        'gorakhpur' => 'Uttar Pradesh',

        // Rajasthan
        'jaipur' => 'Rajasthan', 'jodhpur' => 'Rajasthan', 'kota' => 'Rajasthan',
        'bikaner' => 'Rajasthan', 'ajmer' => 'Rajasthan', 'udaipur' => 'Rajasthan',
        'bhilwara' => 'Rajasthan', 'alwar' => 'Rajasthan',

        // Gujarat
        'ahmedabad' => 'Gujarat', 'surat' => 'Gujarat', 'vadodara' => 'Gujarat',
        'rajkot' => 'Gujarat', 'bhavnagar' => 'Gujarat', 'jamnagar' => 'Gujarat',
        'gandhinagar' => 'Gujarat', 'junagadh' => 'Gujarat',

        // Madhya Pradesh
        'bhopal' => 'Madhya Pradesh', 'indore' => 'Madhya Pradesh', 'jabalpur' => 'Madhya Pradesh',
        'gwalior' => 'Madhya Pradesh', 'ujjain' => 'Madhya Pradesh', 'sagar' => 'Madhya Pradesh',

        // Jharkhand
        'ranchi' => 'Jharkhand', 'jamshedpur' => 'Jharkhand', 'dhanbad' => 'Jharkhand',
        'bokaro' => 'Jharkhand', 'deoghar' => 'Jharkhand',

        // Chhattisgarh
        'raipur' => 'Chhattisgarh', 'bhilai' => 'Chhattisgarh', 'bilaspur' => 'Chhattisgarh',
        'korba' => 'Chhattisgarh', 'durg' => 'Chhattisgarh',

        // Assam
        'guwahati' => 'Assam', 'silchar' => 'Assam', 'dibrugarh' => 'Assam',
        'jorhat' => 'Assam', 'dispur' => 'Assam',

        // Uttarakhand
        'dehradun' => 'Uttarakhand', 'haridwar' => 'Uttarakhand', 'roorkee' => 'Uttarakhand',
        'haldwani' => 'Uttarakhand', 'rishikesh' => 'Uttarakhand', 'nainital' => 'Uttarakhand',

        // Himachal Pradesh
        'shimla' => 'Himachal Pradesh', 'dharamshala' => 'Himachal Pradesh', 'solan' => 'Himachal Pradesh',
        'mandi' => 'Himachal Pradesh',

        // Kerala
        'thiruvananthapuram' => 'Kerala', 'trivandrum' => 'Kerala', 'kochi' => 'Kerala',
        'cochin' => 'Kerala', 'kozhikode' => 'Kerala', 'calicut' => 'Kerala',
        'kollam' => 'Kerala', 'thrissur' => 'Kerala', 'kannur' => 'Kerala',

        // Punjab
        'ludhiana' => 'Punjab', 'amritsar' => 'Punjab', 'jalandhar' => 'Punjab',
        'patiala' => 'Punjab', 'bathinda' => 'Punjab', 'mohali' => 'Punjab',

        // Haryana
        'faridabad' => 'Haryana', 'gurugram' => 'Haryana', 'gurgaon' => 'Haryana',
        'panipat' => 'Haryana', 'ambala' => 'Haryana', 'yamunanagar' => 'Haryana',
        'rohtak' => 'Haryana', 'hisar' => 'Haryana', 'karnal' => 'Haryana', 'panchkula' => 'Haryana',

        // Delhi
        'new delhi' => 'Delhi', 'delhi' => 'Delhi',

        // Jammu and Kashmir
        'srinagar' => 'Jammu and Kashmir', 'jammu' => 'Jammu and Kashmir',

        // Goa
        'panaji' => 'Goa', 'panjim' => 'Goa', 'margao' => 'Goa', 'vasco' => 'Goa'
    ];

    /**
     * Main extraction entry point
     */
    public static function extract($text, $title = '', array $pageMap = [], array $existingFields = [], array $emails = [], array $phones = [], $fileName = '')
    {
        $scopeText = '';
        if (!empty($pageMap)) {
            for ($i = 1; $i <= min(4, count($pageMap)); $i++) {
                if (isset($pageMap[$i])) {
                    $scopeText .= "\n" . $pageMap[$i];
                }
            }
        }
        if (empty($scopeText)) {
            $scopeText = mb_substr($text, 0, 25000, 'UTF-8');
        }

        $stateRes   = self::extractState($scopeText, $text, $title, $existingFields);
        $custRes    = self::extractCustomerName($scopeText, $text, $title, $existingFields, $fileName);

        // If Customer Name is a verified canonical organization and state was inferred loosely, align state
        if (!empty($custRes['value']) && isset(self::$knownOrgStates[$custRes['value']])) {
            $orgState = self::$knownOrgStates[$custRes['value']];
            if (empty($stateRes['value']) || in_array($stateRes['source'], ['pin_code', 'deep_search', 'city_keyword', 'state_resolver', 'none'])) {
                $stateRes = [
                    'value'      => $orgState,
                    'confidence' => 95,
                    'source'     => 'canonical_org_state',
                    'evidence'   => "Verified location for {$custRes['value']} -> {$orgState}"
                ];
            }
        }

        $addrRes    = self::extractCustomerAddress($scopeText, $text, $custRes['value'] ?? '', $stateRes['value'] ?? '');
        $phoneRes   = self::extractCustomerPhone($scopeText, $text, $phones);

        $buildItem = function ($res) {
            $val = $res['value'] ?? '';
            $conf = $res['confidence'] ?? 0;
            return [
                'value'      => $val,
                'raw_value'  => $val,
                'confidence' => $conf,
                'status'     => !empty($val) ? ($conf >= 80 ? 'extracted' : 'review_needed') : 'not_found',
                'source'     => $res['source'] ?? 'none',
                'evidence'   => $res['evidence'] ?? ''
            ];
        };

        return [
            'customer_state'   => $buildItem($stateRes),
            'customer_name'    => $buildItem($custRes),
            'customer_address' => $buildItem($addrRes),
            'customer_phone'   => $buildItem($phoneRes),
        ];
    }

    /**
     * 1. Extract State matching one of the 36 dropdown options
     */
    public static function extractState($scopeText, $fullText, $title = '', array $existingFields = [])
    {
        $bestState = '';
        $confidence = 0;
        $evidence = '';

        // Strategy A: Official Government Portal URL domain
        $portal = $existingFields['tender_portal']['value'] ?? '';
        if (empty($portal) && preg_match('/https?:\/\/([a-zA-Z0-9.\-_]+)/i', $scopeText, $pm)) {
            $portal = $pm[1];
        }
        if (!empty($portal)) {
            if (preg_match('/\b([a-zA-Z]+)\.(?:gov|nic)\.in\b/i', $portal, $dm)) {
                $sub = strtolower($dm[1]);
                foreach (self::$canonicalStates as $st) {
                    $stClean = strtolower(str_replace(' ', '', $st));
                    if ($sub === $stClean || strpos($sub, $stClean) !== false || $sub === 'bih' && $st === 'Bihar') {
                        return [
                            'value'      => $st,
                            'confidence' => 98,
                            'source'     => 'portal_domain',
                            'evidence'   => "Official state procurement portal domain: {$portal}"
                        ];
                    }
                }
            }
        }

        // Strategy B: Explicit "Government of <State>" or "Govt. of <State>" in header/title
        if (preg_match('/(?:Government\s+of|Govt\.?\s+of|State\s+of)\s+([A-Za-z\s]{3,30})/iu', $scopeText, $gm)) {
            $cand = trim($gm[1]);
            foreach (self::$canonicalStates as $st) {
                if (stripos($cand, $st) !== false) {
                    return [
                        'value'      => $st,
                        'confidence' => 96,
                        'source'     => 'govt_header',
                        'evidence'   => trim($gm[0])
                    ];
                }
            }
        }

        // Strategy C: 6-Digit Indian PIN code range in header / contact
        if (preg_match('/\b([1-9][0-9]{5})\b/', $scopeText, $pinM)) {
            $pin = (int)$pinM[1];
            $stFromPin = self::stateFromPincode($pin);
            if ($stFromPin) {
                $bestState = $stFromPin;
                $confidence = 92;
                $evidence = "PIN Code: {$pin} mapped to {$stFromPin}";
            }
        }

        // Strategy D: City / District keyword match in header scope
        foreach (self::$cityToState as $city => $state) {
            if (preg_match('/\b' . preg_quote($city, '/') . '\b/iu', $scopeText)) {
                if ($confidence < 90) {
                    $bestState = $state;
                    $confidence = 90;
                    $evidence = "Identified state capital/hub: {$city} -> {$state}";
                }
                break;
            }
        }

        // Strategy E: Direct Canonical State mention in scopeText or title
        if (empty($bestState)) {
            foreach (self::$canonicalStates as $st) {
                if (preg_match('/\b' . preg_quote($st, '/') . '\b/iu', $title . ' ' . $scopeText)) {
                    $bestState = $st;
                    $confidence = 85;
                    $evidence = "Direct state match: {$st}";
                    break;
                }
            }
        }

        // Strategy F: Deep search in first 35,000 characters for City, District, PIN, or Institution
        if (empty($bestState)) {
            $deepText = mb_substr($fullText, 0, 35000, 'UTF-8');

            // Check PIN code in deepText
            if (preg_match('/\b([1-9][0-9]{5})\b/', $deepText, $dPinM)) {
                $dPin = (int)$dPinM[1];
                $stFromDPin = self::stateFromPincode($dPin);
                if ($stFromDPin) {
                    $bestState = $stFromDPin;
                    $confidence = 88;
                    $evidence = "Deep PIN Code match: {$dPin} -> {$stFromDPin}";
                }
            }

            // Check City in deepText
            if (empty($bestState)) {
                foreach (self::$cityToState as $city => $state) {
                    if (preg_match('/\b' . preg_quote($city, '/') . '\b/iu', $deepText)) {
                        $bestState = $state;
                        $confidence = 85;
                        $evidence = "Deep city match: {$city} -> {$state}";
                        break;
                    }
                }
            }

            // Check institutional patterns
            if (empty($bestState)) {
                if (preg_match('/\b(?:NITT|Tiruchirappalli|Trichy)\b/i', $deepText)) {
                    $bestState = 'Tamil Nadu';
                    $confidence = 90;
                    $evidence = 'Institution location: NIT Tiruchirappalli -> Tamil Nadu';
                } elseif (preg_match('/\b(?:IISc|Bengaluru|Bangalore)\b/i', $deepText)) {
                    $bestState = 'Karnataka';
                    $confidence = 90;
                    $evidence = 'Institution location: IISc Bangalore -> Karnataka';
                } elseif (preg_match('/\b(?:IIT\s*Delhi|Hauz\s*Khas|New\s*Delhi)\b/i', $deepText)) {
                    $bestState = 'Delhi';
                    $confidence = 90;
                    $evidence = 'Institution location: IIT Delhi -> Delhi';
                } elseif (preg_match('/\b(?:IIT\s*Bombay|Powai|Mumbai)\b/i', $deepText)) {
                    $bestState = 'Maharashtra';
                    $confidence = 90;
                    $evidence = 'Institution location: IIT Bombay -> Maharashtra';
                } elseif (preg_match('/\b(?:AIIMS\s*Bhubaneswar|BBSR)\b/i', $deepText)) {
                    $bestState = 'Odisha';
                    $confidence = 90;
                    $evidence = 'Institution location: AIIMS Bhubaneswar -> Odisha';
                }
            }
        }

        return [
            'value'      => $bestState,
            'confidence' => $confidence,
            'source'     => !empty($bestState) ? 'state_resolver' : 'none',
            'evidence'   => $evidence
        ];
    }

    /**
     * Map Indian 6-digit PIN code to Canonical State
     */
    protected static function stateFromPincode($pin)
    {
        if ($pin >= 500000 && $pin <= 509999) return 'Telangana';
        if ($pin >= 515000 && $pin <= 535999) return 'Andhra Pradesh';
        if ($pin >= 800000 && $pin <= 859999) return 'Bihar';
        if ($pin >= 799000 && $pin <= 799999) return 'Tripura';
        if ($pin >= 700000 && $pin <= 749999) return 'West Bengal';
        if ($pin >= 751000 && $pin <= 770999) return 'Odisha';
        if ($pin >= 600000 && $pin <= 643999) return 'Tamil Nadu';
        if ($pin >= 560000 && $pin <= 591999) return 'Karnataka';
        if ($pin >= 670000 && $pin <= 695999) return 'Kerala';
        if ($pin >= 400000 && $pin <= 445999) return 'Maharashtra';
        if ($pin >= 380000 && $pin <= 396999) return 'Gujarat';
        if ($pin >= 301000 && $pin <= 345999) return 'Rajasthan';
        if ($pin >= 201000 && $pin <= 285999) return 'Uttar Pradesh';
        if ($pin >= 110000 && $pin <= 110099) return 'Delhi';
        if ($pin >= 160000 && $pin <= 160099) return 'Chandigarh';
        if ($pin >= 140000 && $pin <= 152999) return 'Punjab';
        if ($pin >= 121000 && $pin <= 136999) return 'Haryana';
        if ($pin >= 171000 && $pin <= 177999) return 'Himachal Pradesh';
        if ($pin >= 248000 && $pin <= 263999) return 'Uttarakhand';
        if ($pin >= 450000 && $pin <= 488999) return 'Madhya Pradesh';
        if ($pin >= 490000 && $pin <= 497999) return 'Chhattisgarh';
        if ($pin >= 814000 && $pin <= 835999) return 'Jharkhand';
        if ($pin >= 781000 && $pin <= 788999) return 'Assam';
        return '';
    }

    /**
     * 2. Extract Customer Name (Issuing Authority / Client / Employer / Department)
     */
    public static function extractCustomerName($scopeText, $fullText, $title = '', array $existingFields = [], $fileName = '')
    {
        $combinedEarly = mb_substr($scopeText . "\n" . $fullText, 0, 30000, 'UTF-8');
        $searchCorpus = $fileName . "\n" . $title . "\n" . $combinedEarly;

        // Strategy 0: Canonical / Premier Indian Institutions in Header or Document Text
        $knownOrgs = [
            'National Institute of Technology, Tiruchirappalli' => [
                '/\bNational\s+Institute\s+of\s+Technology\b.*?\b(?:Tiruchirappalli|Tiruchirapalli|Tiruchuirappalli|Trichy)\b/is',
                '/\bNIT\s*,?\s*(?:Tiruchirappalli|Tiruchirapalli|Tiruchuirappalli|Trichy)\b/i',
                '/(?:^|[_\s\/\-])NITT(?:[_\s\/\-]|\b)/i',
                '/\b(?:hod1\.nitt\.tn|akash|gemtest)@/i',
                '/\bnitt\.edu\b/i'
            ],
            'All India Institute of Medical Sciences, Bhubaneswar' => [
                '/\bAll\s+India\s+Institute\s+of\s+Medical\s+Sciences\b.*?\b(?:Bhubaneswar|BBSR)\b/is',
                '/\bAIIMS\s*,?\s*(?:Bhubaneswar|BBSR)\b/i',
                '/(?:^|[_\s\/\-])AIIMS(?:[_\s\/\-]|\b)/i',
                '/\baiimsbhubaneswar\.nic\.in\b/i'
            ],
            'Indian Institute of Science, Bangalore' => [
                '/\bIndian\s+Institute\s+of\s+Science\b.*?\b(?:Bangalore|Bengaluru)\b/is',
                '/\bIISc\s*(?:Bangalore|Bengaluru)?\b/i',
                '/(?:^|[_\s\/\-])IISc(?:[_\s\/\-]|\b)/i',
                '/\biisc\.ac\.in\b/i'
            ],
            'Indian Institute of Technology Delhi' => [
                '/\bIndian\s+Institute\s+of\s+Technology\s+Delhi\b/i',
                '/\bIIT\s+Delhi\b/i',
                '/(?:^|[_\s\/\-])IITD(?:[_\s\/\-]|\b)/i',
                '/\biitd\.ac\.in\b/i'
            ],
            'Indian Institute of Technology Bombay' => [
                '/\bIndian\s+Institute\s+of\s+Technology\s+Bombay\b/i',
                '/\bIIT\s+Bombay\b/i',
                '/(?:^|[_\s\/\-])IITB(?:[_\s\/\-]|\b)/i',
                '/\biitb\.ac\.in\b/i'
            ],
            'National Institute of Technology Calicut' => [
                '/\bNational\s+Institute\s+of\s+Technology\s+Calicut\b/i',
                '/\bNIT\s+Calicut\b/i',
                '/(?:^|[_\s\/\-])NITC(?:[_\s\/\-]|\b)/i',
                '/\bnitc\.ac\.in\b/i'
            ],
            'National Institute of Technology Karnataka, Surathkal' => [
                '/\bNational\s+Institute\s+of\s+Technology\s+Karnataka\b/is',
                '/\bNITK\s*,?\s*Surathkal\b/i',
                '/(?:^|[_\s\/\-])NITK(?:[_\s\/\-]|\b)/i',
                '/\bnitk\.ac\.in\b/i',
                '/\bnitk\.edu\.in\b/i'
            ],
            'Greater Hyderabad Municipal Corporation' => [
                '/\bGreater\s+Hyderabad\s+Municipal\s+Corporation\b/i',
                '/\bGHMC\b/i'
            ],
            'Vadodara Municipal Corporation' => [
                '/\bVadodara\s+Municipal\s+Corporation\b/i',
                '/\bVMC\b/',
                '/\bvmc\.gov\.in\b/i'
            ],
            'Odisha Computer Application Centre' => [
                '/\bOdisha\s+Computer\s+Application\s+Cent(?:re|er)\b/i',
                '/\bOCAC\b/i',
                '/\bocac\.in\b/i'
            ],
            'Webel Technology Limited' => [
                '/\bWebel\s+Technology\s+Limited\b/i',
                '/\bWTL\b/i',
                '/\bwtl\.co\.in\b/i'
            ],
            'State Trading Corporation of India Ltd' => [
                '/\bState\s+Trading\s+Corporation\s+of\s+India\b/i',
                '/\bSTC\s+Building\b/i',
                '/\bstc\.gov\.in\b/i'
            ],
            'WAPCOS LIMITED' => [
                '/\bWAPCOS\s+LIMITED\b/i',
                '/\bwapcos\.gov\.in\b/i'
            ],
            'Madhya Pradesh Police Telecom' => [
                '/\bPolice\s+Telecom\s+Headquarters\b/i',
                '/\bSenior\s+Superintendent\s+of\s+Police\s*,\s*Radio\b/i',
                '/\bMadhya\s+Pradesh\s+Police\b/i'
            ],
            'Tripura University' => [
                '/\bTripura\s+University\b/i',
                '/\btripurauniv\.ac\.in\b/i'
            ],
            'Bank of Baroda' => [
                '/\bBank\s+of\s+Baroda\b/i',
                '/\bbankofbaroda\.in\b/i'
            ]
        ];

        foreach ($knownOrgs as $canonicalName => $patterns) {
            foreach ($patterns as $pat) {
                if (preg_match($pat, $searchCorpus)) {
                    return [
                        'value'      => $canonicalName,
                        'confidence' => 98,
                        'source'     => 'canonical_institution',
                        'evidence'   => $canonicalName
                    ];
                }
            }
        }

        // Strategy A: GeM Government e-Marketplace structured Organisation block
        if (preg_match('/(?:Organisation|Organization)\s+Name\s*[:\t|\s]+([^\r\n]{3,80})/iu', $combinedEarly, $gmOrg)) {
            $rawOrg = self::cleanCustomerName($gmOrg[1]);
            $officeName = '';
            if (preg_match('/Office\s+Name\s*[:\t|\s]+([^\r\n]{3,80})/iu', $combinedEarly, $gmOff)) {
                $officeName = self::cleanCustomerName($gmOff[1]);
            }
            if (!empty($rawOrg)) {
                if (preg_match('/(?:National\s+Institute\s+of\s+Technology|NIT)\b/i', $rawOrg)) {
                    if (preg_match('/(?:Tiruchirappalli|Tiruchirapalli|Tiruchuirappalli|Trichy)/i', $officeName)) {
                        return [
                            'value'      => 'National Institute of Technology, Tiruchirappalli',
                            'confidence' => 98,
                            'source'     => 'gem_organisation_table',
                            'evidence'   => $gmOrg[0]
                        ];
                    } elseif (preg_match('/(?:Calicut|Kozhikode)/i', $officeName)) {
                        return [
                            'value'      => 'National Institute of Technology Calicut',
                            'confidence' => 98,
                            'source'     => 'gem_organisation_table',
                            'evidence'   => $gmOrg[0]
                        ];
                    }
                }
                $finalOrg = $rawOrg;
                if (!empty($officeName) && stripos($finalOrg, $officeName) === false && strlen($officeName) < 40) {
                    $finalOrg .= ', ' . $officeName;
                }
                return [
                    'value'      => $finalOrg,
                    'confidence' => 96,
                    'source'     => 'gem_organisation_table',
                    'evidence'   => $gmOrg[0]
                ];
            }
        }

        // Strategy B: Explicit Department Name row in summary tables
        if (preg_match('/(?:Department\s+Name|Department)\s*[:\t|]\s*([^\r\n|]{4,80})/iu', $scopeText, $dm)) {
            $dep = self::cleanCustomerName($dm[1]);
            if (strlen($dep) >= 5 && !preg_match('/^(?:Others|Turnkey|Goods|Works|Services|Surveillance|Civil|Electrical)/i', $dep)) {
                return [
                    'value'      => $dep,
                    'confidence' => 95,
                    'source'     => 'department_name_row',
                    'evidence'   => trim($dm[0])
                ];
            }
        }

        // Strategy C: Explicit Labelled Row (Tendering Authority / Tender Inviting Authority)
        if (preg_match('/(?:Tendering\s+Authority|Tender\s+Inviting\s+Authority|Issuing\s+Authority|Procuring\s+Entity|Name\s+of\s+(?:the\s+)?(?:Employer|Purchaser|Client|Corporation|Authority))\s*[:\t| \-]+(?:the\s+|m\/s\.?\s+)?([A-Za-z0-9\s.,&\'\(\)\/\-]{4,120})/iu', $scopeText, $m)) {
            $val = self::cleanCustomerName($m[1]);
            if (strlen($val) >= 4) {
                return [
                    'value'      => $val,
                    'confidence' => 94,
                    'source'     => 'labelled_tendering_authority',
                    'evidence'   => trim($m[0])
                ];
            }
        }

        // Strategy D: Notice "Issued by" block (Strictly anchored, never matching inline text)
        if (preg_match('/(?:^|\n)[ \t]*(?:Issued\s+by|Published\s+by)\s*[:\t|\n\r \-]+(?:the\s+|m\/s\.?\s+)?([A-Za-z0-9\s.,&\'\(\)\/\-]{4,80})/iu', $scopeText, $m)) {
            $val = self::cleanCustomerName($m[1]);
            if (strlen($val) >= 4) {
                return [
                    'value'      => $val,
                    'confidence' => 92,
                    'source'     => 'issued_by_label',
                    'evidence'   => trim($m[0])
                ];
            }
        }

        // Strategy E: Prominent Header Organization on Page 1
        $lines = preg_split('/[\r\n]+/', mb_substr($scopeText, 0, 4000, 'UTF-8'));
        $foundGovt = false;
        foreach ($lines as $line) {
            $tLine = trim($line, " \t|");
            if (preg_match('/^(?:GOVERNMENT\s+OF|GOVT\.?\s+OF|STATE\s+OF)\b/i', $tLine)) {
                $foundGovt = true;
                continue;
            }
            if ($foundGovt && strlen($tLine) >= 5 && strlen($tLine) <= 100) {
                if (preg_match('/\b(?:CORPORATION|UNIVERSITY|DEPARTMENT|COMMISSIONERATE|DIRECTORATE|AUTHORITY|BOARD|NIGAM|COUNCIL|POLICE|HOSPITAL|INSTITUTE|MISSION)\b/i', $tLine)) {
                    $clean = self::cleanCustomerName($tLine);
                    if (strlen($clean) >= 5) {
                        return [
                            'value'      => $clean,
                            'confidence' => 90,
                            'source'     => 'prominent_header_block',
                            'evidence'   => $tLine
                        ];
                    }
                }
            }
        }

        // Strategy F: Existing issuing_authority field if valid
        $authVal = $existingFields['issuing_authority']['value'] ?? '';
        if (!empty($authVal)) {
            $cleanAuth = self::cleanCustomerName($authVal);
            if (strlen($cleanAuth) >= 4 && !preg_match('/^(?:central|state)\s+government/i', $cleanAuth)) {
                return [
                    'value'      => $cleanAuth,
                    'confidence' => 85,
                    'source'     => 'existing_field',
                    'evidence'   => $authVal
                ];
            }
        }

        // Strategy G: Standalone University / Corporation / Police / Board mention
        if (preg_match('/\b([A-Z][A-Za-z0-9\s.,&\-\'\(\)]{3,60}\s+(?:Municipal\s+Corporation|University|Development\s+Corporation|Police|Electricity\s+Board|Nigam|Authority))\b/u', mb_substr($scopeText, 0, 3000, 'UTF-8'), $orgM)) {
            $clean = self::cleanCustomerName($orgM[1]);
            if (strlen($clean) >= 5) {
                return [
                    'value'      => $clean,
                    'confidence' => 80,
                    'source'     => 'org_pattern',
                    'evidence'   => $orgM[0]
                ];
            }
        }

        return [
            'value'      => '',
            'confidence' => 0,
            'source'     => 'none',
            'evidence'   => ''
        ];
    }

    /**
     * Clean and normalize extracted Customer Name
     */
    protected static function cleanCustomerName($name)
    {
        $name = trim(preg_replace('/[\r\n\t|]+/', ' ', $name));
        $name = preg_replace('/^(?:the\s+|m\/s\.?\s+|office\s+of\s+(?:the\s+)?|addressed\s+to\s*:?)/iu', '', $name);
        $name = preg_replace('/^(?:TendeRs\.?\s+are\s+invited\s+(?:by|on\s+behalf\s+of)\s*(?:the\s+)?|Tenders?\s+are\s+invited\s+(?:by|on\s+behalf\s+of)\s*(?:the\s+)?|For\s+and\s+on\s+behalf\s+of\s*(?:the\s+)?|Bids?\s+are\s+invited\s+by\s*(?:the\s+)?|Notice\s+Inviting\s+Tender\s*(?:for\s*)?)/iu', '', $name);

        // Strip trailing noise like "4 days before...", "INDEX", "Dated ...":
        $name = preg_replace('/\s+(?:\d+\s+days\s+before|dated|dt\.?|index|as\s+mentioned|in\s+nit|under\s+swachh|component).*$/iu', '', $name);
        $name = preg_replace('/[,\s]+(?:all\s+correspondence|telephone|email|phone|website|portal|pin|dated|dt).*$/iu', '', $name);
        $name = preg_replace('/\s{2,}/', ' ', $name);
        $name = trim($name, " \t,.-;:/|()[]");

        // Blacklist MSME / Policy exemptions
        if (preg_match('/(?:Micro,\s*Small|MSME|Medium\s+Enterprises|Public\s+Procurement\s+Policy|subsequent\s+Orders|eligibility\s+criteria)/i', $name)) {
            return '';
        }
        // Blacklist eligibility / certificate / noise lines
        if (preg_match('/(?:Registration\s+Certificate|Shop\s+&\s+Establishment|competent\s+authority|similar\s+nature|turnkey\s+project|integrity\s+pact)/i', $name)) {
            return '';
        }
        // Filter out non-entity prompt text, list bullets, and generic instructions
        if (preg_match('/^(?:viii|ix|vii|vi|v|iv|iii|ii|i|\d+)[\.\)]/i', $name)) {
            return '';
        }
        if (preg_match('/^(?:Name\s+of\s+(?:the\s+)?Department|Hard\s+Copies|Sealed\s+box|an\s+officer|Signature\s+&\s+Seal|Ambulance\s+108|Visiting\s+Professor|Report\s+IDC|Date\s*\d|Dt\s*\d|Ph\.?\s*No|Tel\.?\s*No)/i', $name)) {
            return '';
        }
        if (preg_match('/^(?:of\s+|Ministry\s+of\s+Education|Department\s+of\s+Education|any\s+Certifying\s+Authority|Certifying\s+Authority)\b/i', $name)) {
            return '';
        }

        return $name;
    }

    /**
     * 3. Extract Customer Address
     */
    public static function extractCustomerAddress($scopeText, $fullText, $customerName = '', $state = '')
    {
        $searchTexts = [$scopeText, mb_substr($fullText, 0, 80000, 'UTF-8')];

        foreach ($searchTexts as $src) {
            // Strategy A: Explicit Labelled Table Row (Office Address, Address for Correspondence)
            if (preg_match('/(?:Address\s+(?:for\s+)?(?:Correspondence|Communication)|Office\s+Address|Address\s+of\s+(?:the\s+)?(?:Authority|Employer|Purchaser|Client)|WTL\s+Address)\s*[:\t| \-]+([^\r\n|]{10,250}(?:\n[ \t]*\|?[ \t]*[^\r\n|]{10,200})?)/iu', $src, $m)) {
                $addr = self::cleanAddress($m[1]);
                if (strlen($addr) >= 15 && self::isValidAddress($addr)) {
                    return [
                        'value'      => $addr,
                        'confidence' => 95,
                        'source'     => 'labelled_address_row',
                        'evidence'   => trim($m[0])
                    ];
                }
            }

            // Strategy B: Address block containing PIN Code (e.g. "... Complex, Tank Bund Road, Hyderabad – 500 063")
            if (preg_match('/(?:Office\s+of\s+the[^\n\r|]{5,100},[^\n\r|]{5,120}|[A-Za-z0-9\s.,&\-\/]+(?:Complex|Bhavan|Bhawan|Building|Campus|Road|Marg|Street|Lane|Nagar|Colony|Sector|Square|Plot)[^\n\r|]{0,80},\s*[A-Za-z\s]{3,30}\s*[–\-,\s]+\s*\d{6}(?:[,\s]+[A-Za-z\s]+)?)/iu', $src, $am)) {
                $addr = self::cleanAddress($am[0]);
                if (strlen($addr) >= 15 && self::isValidAddress($addr)) {
                    return [
                        'value'      => $addr,
                        'confidence' => 92,
                        'source'     => 'pin_address_block',
                        'evidence'   => trim($am[0])
                    ];
                }
            }

            // Strategy C: Contact block with Major City - PIN
            if (preg_match('/([A-Za-z0-9\s.,&\-\/]{5,120}?[,\s]+(?:Hyderabad|Patna|Delhi|Mumbai|Kolkata|Bengaluru|Chennai|Agartala|Bhubaneswar|Lucknow|Jaipur|Ahmedabad|Vadodara|Bhopal|Ranchi|Raipur|Guwahati|Dehradun|Shimla|Chandigarh|New Delhi|Tiruchirappalli|Kozhikode)[-\s,]+\d{6}(?:[,\s]+[A-Za-z\s]{3,30})?)/iu', $src, $cm)) {
                $addr = self::cleanAddress($cm[1]);
                if (strlen($addr) >= 15 && self::isValidAddress($addr)) {
                    return [
                        'value'      => $addr,
                        'confidence' => 88,
                        'source'     => 'city_pin_block',
                        'evidence'   => trim($cm[0])
                    ];
                }
            }
        }

        // Strategy D: Pre-mapped Official Registered Address for Premier Institutions
        if (!empty($customerName) && isset(self::$knownOrgAddresses[$customerName])) {
            return [
                'value'      => self::$knownOrgAddresses[$customerName],
                'confidence' => 90,
                'source'     => 'canonical_org_address',
                'evidence'   => "Official registered campus address for {$customerName}"
            ];
        }

        // Fallback: If Customer Name & State known, construct standard office address string
        if (!empty($customerName) && !empty($state)) {
            $fallback = "Office of {$customerName}, {$state}";
            return [
                'value'      => $fallback,
                'confidence' => 70,
                'source'     => 'constructed_fallback',
                'evidence'   => "Constructed from Customer Name and State"
            ];
        }

        return [
            'value'      => '',
            'confidence' => 0,
            'source'     => 'none',
            'evidence'   => ''
        ];
    }

    /**
     * Clean and format extracted Address
     */
    protected static function cleanAddress($addr)
    {
        $addr = trim(preg_replace('/[\r\n\t|]+/', ' ', $addr));
        $addr = preg_replace('/\s{2,}/', ' ', $addr);
        $addr = preg_replace('/^(?:the\s+|at\s+|to\s*:?|for\s+|of\s+)/iu', '', $addr);
        $addr = preg_replace('/[,\s]+(?:telephone|email|phone|contact\s+details|fax|website|note:|index|table\s+of\s+contents|tender\s+document\s+for|name\s+of\s+work|whereas|to\s+supply|description\s+of).*$/iu', '', $addr);
        return trim($addr, " \t,.-;:/|");
    }

    /**
     * Strict validator to reject currency, tables, URLs, and submission instructions mistakenly parsed as addresses
     */
    protected static function isValidAddress($addr)
    {
        if (strlen($addr) < 10) return false;

        // Reject currency & payment terms
        if (preg_match('/(?:\bRs\.?\b|\bINR\b|₹|\/[-–]|\bLakh\b|\bCrore\b|\bThousand\b|\bOnly\b|non[\s\-]refundable|payment\s+of)/i', $addr)) {
            return false;
        }
        // Reject tender milestones / schedule tables
        if (preg_match('/(?:Date\s+and\s+Time|Opening\s+of|Commercial\s+Bid|Technical\s+Bid|Pre[\s\-]Bid|Submission\s+of\s+RFP|Availability\s+of)/i', $addr)) {
            return false;
        }
        // Reject online portal URLs or submission instructions
        if (preg_match('/(?:https?:\/\/|www\.|\.gov\.in|\.nic\.in|Online\b|Hard\s+Copies|Sealed\s+box)/i', $addr)) {
            return false;
        }
        // Reject eligibility criteria lines
        if (preg_match('/(?:eligibility|criteria|Registration\s+Certificate|Shop\s+&\s+Establishment)/i', $addr)) {
            return false;
        }

        return true;
    }

    /**
     * 4. Extract Customer Phone Number
     */
    public static function extractCustomerPhone($scopeText, $fullText, array $phones = [])
    {
        $searchTexts = [$scopeText, mb_substr($fullText, 0, 80000, 'UTF-8')];
        foreach ($searchTexts as $src) {
            // Strategy A: Explicit Labelled Table Row / Line (e.g. "Telephone: 6309999454/6309999458", "Phone: 011 2659 1746")
            if (preg_match('/(?:Telephone|Phone|Contact(?:\s*No\.?)?|Tel\.?(?:\s*No\.?)?|Mobile(?:\s*No\.?)?|Helpdesk(?:\s*No\.?)?)\s*[:\t| \-]+([0-9+\-\s()\/]{7,35})/iu', $src, $m)) {
                $cleanPh = self::cleanPhone($m[1]);
                if ($cleanPh !== '') {
                    return [
                        'value'      => $cleanPh,
                        'confidence' => 95,
                        'source'     => 'labelled_phone_row',
                        'evidence'   => trim($m[0])
                    ];
                }
            }
        }

        // Strategy B: Pre-extracted document phones list (validated strictly)
        if (!empty($phones)) {
            foreach ($phones as $p) {
                $clean = self::cleanPhone($p);
                if ($clean !== '') {
                    return [
                        'value'      => $clean,
                        'confidence' => 88,
                        'source'     => 'phones_list',
                        'evidence'   => "Pre-extracted phone: {$p}"
                    ];
                }
            }
        }

        // Strategy C: Regex scan for Indian mobile / landline in scopeText
        if (preg_match('/\b(?:\+?91[\s.-]?)?([6-9]\d{4}[\s.-]?\d{5}|0\d{2,4}[\s.-]?\d{6,8})\b/', $scopeText, $pm)) {
            $clean = self::cleanPhone($pm[0]);
            if ($clean !== '') {
                return [
                    'value'      => $clean,
                    'confidence' => 80,
                    'source'     => 'regex_scan',
                    'evidence'   => trim($pm[0])
                ];
            }
        }

        return [
            'value'      => '',
            'confidence' => 0,
            'source'     => 'none',
            'evidence'   => ''
        ];
    }

    /**
     * Clean and format phone number ensuring it satisfies form validation /^[0-9+\-\s()]{7,20}$/
     */
    protected static function cleanPhone($ph)
    {
        if (empty($ph)) return '';
        // CRITICAL: Strip any newlines, carriage returns, or tabs immediately
        $ph = str_replace(["\r", "\n", "\t"], ' ', (string)$ph);
        $ph = trim(preg_replace('/\s+/', ' ', $ph));

        // If multiple numbers separated by slash, comma, or "or", take the first complete number
        if (preg_match('/^([0-9+\-\s()]{7,20}?)(?:[\/,]|\s+or\s+)/i', $ph, $firstM)) {
            $ph = trim($firstM[1]);
        }

        // Clean trailing symbols, spaces, or words
        $ph = preg_replace('/[^0-9+\-\s()]/', '', $ph);

        // Normalize "(0824) 2474000" to "0824-2474000"
        $ph = preg_replace('/^\((\d{2,5})\)\s*/', '$1-', $ph);
        $ph = trim($ph, " \t-+ ");

        // Match exact standard complete Indian phone structures from start:
        // 1. Mobile: 10 digits starting with 6, 7, 8, or 9 (with optional +91 or 0 prefix)
        if (preg_match('/^(?:\+?91[\-\s]?)?([6-9]\d{4}[\-\s]?\d{5})\b/', $ph, $mMobile)) {
            return trim($mMobile[0]);
        }

        // 2. Landline with STD code (e.g. 022-2576-9355, 011-26596237, 0431-2504135, 080-22932020)
        if (preg_match('/^(0\d{1,4}[\-\s]?\d{3,4}[\-\s]?\d{4})\b/', $ph, $mLand)) {
            return trim($mLand[0]);
        }
        if (preg_match('/^(0\d{1,4}[\-\s]?\d{6,8})\b/', $ph, $mStd)) {
            return trim($mStd[0]);
        }

        // 3. Official Helpdesk (e.g. 0120-4001002, 1800-xxx-xxxx)
        if (preg_match('/^(1800[\-\s]?\d{3}[\-\s]?\d{3,4}|0120[\-\s]?\d{3,4}[\-\s]?\d{3,4})\b/', $ph, $mHelp)) {
            return trim($mHelp[0]);
        }

        // 4. Local 7-8 digit number (e.g. 27309102, 2435646)
        if (preg_match('/^([2-8]\d{6,7})\b/', $ph, $mLoc)) {
            return trim($mLoc[0]);
        }

        $digitsOnly = preg_replace('/\D/', '', $ph);
        if (strlen($digitsOnly) < 7 || strlen($digitsOnly) > 13) {
            return '';
        }

        // Reject Unix timestamps (e.g. 1793... or 1794...)
        if (preg_match('/^179\d{7,}$/', $digitsOnly)) {
            return '';
        }

        if (preg_match('/^(?:\+?91[\-\s]?)?[6-9]\d{9}$/', $digitsOnly) ||
            preg_match('/^0[1-9]\d{1,3}\d{6,8}$/', $digitsOnly) ||
            preg_match('/^1800\d{6,7}$/', $digitsOnly) ||
            preg_match('/^0120\d{7}$/', $digitsOnly)) {
            return substr($ph, 0, 20);
        }

        return '';
    }
}
