<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bid_model extends CI_Model
{
    
	function __construct()
	{
		parent::__construct();
	}
  
  public function getMenusByRole($roleId)
    {
        // $this->db->select('m.*');
        // $this->db->from('BidMenus m');
        // $this->db->join('RoleMenuPermissions rmp', 'rmp.MenuId = m.Bmid');
        // $this->db->where('rmp.RoleId', $roleId);
        // $this->db->where('m.IsActive', 1);
        // $this->db->order_by('m.ParentId ASC, m.SortOrder ASC');
         /* Subquery: menus allowed for role */
		    $subMenuIds = $this->db
		        ->select('MenuId')
		        ->from('RoleMenuPermissions')
		        ->where('RoleId', $roleId)
		        ->get_compiled_select();

		    /* Subquery: parent menus of allowed menus */
		    $parentMenuIds = $this->db
		        ->select('ParentId')
		        ->from('BidMenus')
		        ->where("Bmid IN ($subMenuIds)", null, false)
		        ->get_compiled_select();

		    /* Main query */
		    $this->db->distinct();
		    $this->db->select('m.*');
		    $this->db->from('BidMenus m');
		    $this->db->join(
		        'RoleMenuPermissions rmp',
		        'rmp.MenuId = m.Bmid',
		        'left'
		    );
		    $this->db->where('m.IsActive', 1);
		    $this->db->group_start();
		        $this->db->where('rmp.RoleId', $roleId);
		        $this->db->or_where("m.Bmid IN ($parentMenuIds)", null, false);
		    $this->db->group_end();
		    $this->db->order_by('m.ParentId', 'ASC');
		    $this->db->order_by('m.SortOrder', 'ASC');

		    return $this->db->get()->result_array();
    }

    private function buildTree(array $elements, $parentId = NULL)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['ParentId'] == $parentId) {
                $children = $this->buildTree($elements, $element['Bmid']);
                if (!empty($children)) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }


public function getBreadcrumb($url)
{
    $child = $this->db
        ->where('LOWER(MenuUrl)', strtolower($url))
        ->get('BidMenus')
        ->row_array();

    if (!$child) {
        return ['parent' => null, 'child' => null];
    }

    $parent = null;
    if (!empty($child['ParentId'])) {
        $parent = $this->db
            ->where('Bmid', $child['ParentId'])
            ->get('BidMenus')
            ->row_array();
    }

    return [
        'parent' => $parent,
        'child'  => $child
    ];
}

function getUsersData(){

        $arr = ['2','3'];
        $this->db->select('bu.Name,bu.Mobile,bu.Email,bu.Designation,bu.Buid,bu.Status,bu.CreationTs');/*emp.desg_date');*/  
        $this->db->select('BidUsersRoles.Brid,BidUsersRoles.RoleName');  
        $this->db->join('BidUsersRoles','BidUsersRoles.Brid=bu.Brid','left');
        $this->db->where_in('bu.Brid',$arr);
        $this->db->order_by('bu.CreationTs','DESC');
        $query = $this->db->get('BidUsers as bu')->result_array();
          // echo $this->db->last_query(); exit;
        return $query; 
}

function getBidDetailsData(){

         
        $this->db->select('bu.*');/*emp.desg_date');*/  
        $this->db->select('BidStatusMaster.StatusName,BidStatusMaster.DisplayName,BidStatusMaster.BadgeClass');  
        $this->db->join('BidStatusMaster','BidStatusMaster.Sid=bu.BidStatus','left');
        // $this->db->where_in('bu.BidStatus',1);
        $this->db->order_by('bu.CreationTs','DESC');
        $query = $this->db->get('BidCreationDetails as bu')->result_array();
          // echo $this->db->last_query(); exit;
        return $query; 
}

    /**
     * Get Current Dashboard data (Preserves original status cards, counts, percentages, and recent bids)
     */
    public function getCurrentDashboardData($startDate = null, $endDate = null)
    {
        // 1. Grouped counts by BidStatus with optional date filtering
        $this->db->select('BidStatus, COUNT(*) as total');
        if (!empty($startDate) && !empty($endDate)) {
            $this->db->where('DATE(COALESCE(PublishedDate, CreationTs)) >=', $startDate);
            $this->db->where('DATE(COALESCE(PublishedDate, CreationTs)) <=', $endDate);
        }
        $this->db->group_by('BidStatus');
        $counts = $this->db->get('BidCreationDetails')->result_array();

        $countArray = [];
        foreach ($counts as $row) {
            $countArray[$row['BidStatus']] = (int) $row['total'];
        }

        // 2. Get all statuses from BidStatusMaster
        $statuses = $this->db->order_by('SortOrder', 'ASC')->get('BidStatusMaster')->result_array();

        $totalBids = array_sum($countArray);
        $statusMap = [];
        foreach ($statuses as $status) {
            $count = isset($countArray[$status['Sid']]) ? $countArray[$status['Sid']] : 0;
            $percentage = ($totalBids > 0) ? round(($count / $totalBids) * 100) : 0;
            $statusMap[$status['StatusName']] = [
                'DisplayName' => $status['DisplayName'],
                'BadgeClass'  => $status['BadgeClass'] ?? 'bg-secondary',
                'Sid'         => $status['Sid'],
                'Count'       => $count,
                'Percentage'  => $percentage,
                'IconClass'   => $status['IconClass'] ?? 'feather-file'
            ];
        }

        // 3. Color mapping
        $colorMap = [
            'bg-primary'   => '#0d6efd',
            'bg-success'   => '#198754',
            'bg-danger'    => '#dc3545',
            'bg-warning'   => '#ffc107',
            'bg-info'      => '#0dcaf0',
            'bg-dark'      => '#212529',
            'bg-secondary' => '#6c757d'
        ];

        // 4. Last 10 Bids (latest first)
        $this->db->select('b.Bcid, b.BidTitle, b.BidType, b.BidDepartment, b.PublishedDate, b.OpeningDate, b.SubmissionDate, b.CreationTs, s.DisplayName as StatusName, s.BadgeClass');
        $this->db->from('BidCreationDetails b');
        $this->db->join('BidStatusMaster s', 's.Sid = b.BidStatus', 'left');
        if (!empty($startDate) && !empty($endDate)) {
            $this->db->where('DATE(COALESCE(b.PublishedDate, b.CreationTs)) >=', $startDate);
            $this->db->where('DATE(COALESCE(b.PublishedDate, b.CreationTs)) <=', $endDate);
        }
        $this->db->order_by('b.CreationTs', 'DESC');
        $this->db->limit(10);
        $lastBids = $this->db->get()->result_array();

        return [
            'statusCounts' => $statusMap,
            'totalBids'    => $totalBids,
            'colorMap'     => $colorMap,
            'lastBids'     => $lastBids
        ];
    }

    /**
     * Get Overview Dashboard data (Real DB metrics, multi-panel layout, no Key Insights)
     */
    public function getOverviewDashboardData($startDate = null, $endDate = null)
    {
        // Date filter builder
        $dateFilter = function ($db, $field = 'CreationTs') use ($startDate, $endDate) {
            if (!empty($startDate) && !empty($endDate)) {
                $db->where("DATE(COALESCE(PublishedDate, $field)) >=", $startDate);
                $db->where("DATE(COALESCE(PublishedDate, $field)) <=", $endDate);
            }
        };

        // 1. Total Bids
        $this->db->from('BidCreationDetails');
        $dateFilter($this->db);
        $totalBids = (int) $this->db->count_all_results();

        // 2. Estimated Value & EMD Value
        $this->db->select('COALESCE(SUM(EstimatedValue), 0) as TotalEstValue, COALESCE(SUM(EmdValue), 0) as TotalEmdValue');
        $this->db->from('BidCreationDetails');
        $dateFilter($this->db);
        $valRow = $this->db->get()->row_array();
        $totalEstValue = (float) ($valRow['TotalEstValue'] ?? 0);
        $totalEmdValue = (float) ($valRow['TotalEmdValue'] ?? 0);

        // 3. Status breakdown
        $this->db->select('s.Sid, s.StatusName, s.DisplayName, s.BadgeClass, s.IconClass, s.SortOrder,
                           COUNT(b.Bcid) as Count, COALESCE(SUM(b.EstimatedValue), 0) as TotalValue');
        $this->db->from('BidStatusMaster s');
        $this->db->join('BidCreationDetails b', 's.Sid = b.BidStatus', 'left');
        if (!empty($startDate) && !empty($endDate)) {
            $this->db->where("DATE(COALESCE(b.PublishedDate, b.CreationTs)) >=", $startDate);
            $this->db->where("DATE(COALESCE(b.PublishedDate, b.CreationTs)) <=", $endDate);
        }
        $this->db->where('s.IsActive', 1);
        $this->db->group_by('s.Sid, s.StatusName, s.DisplayName, s.BadgeClass, s.IconClass, s.SortOrder');
        $this->db->order_by('s.SortOrder', 'ASC');
        $statusRows = $this->db->get()->result_array();

        // 3. Status workflow table and donut (Matching reference: Participated, Under Participation, Under Discussion, Awarded, Cancelled, Inactive)
        $cancelled = 0;
        $inactive = 0;

        foreach ($statusRows as $row) {
            $sid = (int) $row['Sid'];
            $c = (int) $row['Count'];

            if ($sid === 5 || $sid === 13) {
                $participated += $c;
            } elseif ($sid === 2 || $sid === 4 || $sid === 12) {
                $underParticipation += $c;
            } elseif ($sid === 3 || $sid === 11) {
                $underDiscussion += $c;
            } elseif ($sid === 7) {
                $awarded += $c;
            } elseif ($sid === 8) {
                $cancelled += $c;
            } elseif ($sid === 6) {
                $inactive += $c;
            }
        }

        $calcPct = function($cnt, $tot) {
            return ($tot > 0) ? round(($cnt / $tot) * 100, 1) : 0;
        };

        $statusWorkflowTable = [
            [
                'name'  => 'Participated',
                'count' => $participated,
                'pct'   => $calcPct($participated, $totalBids),
                'color' => '#3b82f6'
            ],
            [
                'name'  => 'Under Participation',
                'count' => $underParticipation,
                'color' => '#f97316'
            ],
            [
                'name'  => 'Under Discussion',
                'count' => $underDiscussion,
                'color' => '#94a3b8'
            ],
            [
                'name'  => 'Awarded',
                'count' => $awarded,
                'pct'   => $calcPct($awarded, $totalBids),
                'color' => '#10b981'
            ],
            [
                'name'  => 'Cancelled',
                'count' => $cancelled,
                'pct'   => $calcPct($cancelled, $totalBids),
                'color' => '#ef4444'
            ],
            [
                'name'  => 'Inactive',
                'count' => $inactive,
                'pct'   => $calcPct($inactive, $totalBids),
                'color' => '#6b7280'
            ]
        ];

        $donutLabels = [];
        $donutSeries = [];
        $donutColors = [];
        foreach ($statusWorkflowTable as $st) {
            if ($st['count'] > 0) {
                $donutLabels[] = $st['name'];
                $donutSeries[] = $st['count'];
                $donutColors[] = $st['color'];
            }
        }
        if (empty($donutSeries)) {
            $donutLabels = ['No Records'];
            $donutSeries = [1];
            $donutColors = ['#e2e8f0'];
        }

        // 4. Bid Type breakdown (OEM vs Service)
        $this->db->select('BidType, COUNT(*) as Count, COALESCE(SUM(EstimatedValue), 0) as TotalValue');
        $this->db->from('BidCreationDetails');
        $dateFilter($this->db);
        $this->db->where('BidType IS NOT NULL', null, false);
        $this->db->group_by('BidType');
        $typeRows = $this->db->get()->result_array();

        // 5. Department breakdown (ClickUp, New Business, Projects, Tech)
        $this->db->select('BidDepartment, COUNT(*) as Count, COALESCE(SUM(EstimatedValue), 0) as TotalValue, COALESCE(SUM(EmdValue), 0) as EmdValue');
        $this->db->from('BidCreationDetails');
        $dateFilter($this->db);
        $this->db->where('BidDepartment IS NOT NULL', null, false);
        $this->db->group_by('BidDepartment');
        $this->db->order_by('Count', 'DESC');
        $deptRows = $this->db->get()->result_array();

        // 6. Monthly Trend & Data Table
        $this->db->select("DATE_FORMAT(COALESCE(PublishedDate, CreationTs), '%Y-%m') as MonthKey,
                           DATE_FORMAT(COALESCE(PublishedDate, CreationTs), '%b-%y') as MonthLabel,
                           COUNT(*) as Count, COALESCE(SUM(EstimatedValue), 0) as TotalValue, COALESCE(SUM(EmdValue), 0) as EmdValue");
        $this->db->from('BidCreationDetails');
        $dateFilter($this->db);
        $this->db->group_by('MonthKey, MonthLabel');
        $this->db->order_by('MonthKey', 'ASC');
        $monthRows = $this->db->get()->result_array();

        $monthlyTable = [];
        foreach ($monthRows as $m) {
            $monthlyTable[] = [
                'month'          => $m['MonthLabel'],
                'totalBids'      => (int) $m['Count'],
                'totalValue'     => (float) $m['TotalValue'],
                'totalValueFmt'  => number_format((float) $m['TotalValue']),
                'emdValue'       => (float) $m['EmdValue'],
                'emdValueFmt'    => number_format((float) $m['EmdValue'])
            ];
        }

        // 7. Participation / Result Status Workflow Breakdown
        $resultMapping = [
            ['key' => 'Won',                     'label' => 'Won',                     'sids' => [7],           'color' => '#8B3A14'],
            ['key' => 'Not L1',                  'label' => 'Not L1',                  'sids' => [10],          'color' => '#8B3A14'],
            ['key' => 'Technically Qualified',   'label' => 'Technically Qualified',   'sids' => [3, 11],       'color' => '#8B3A14'],
            ['key' => 'Technical Qualified',     'label' => 'Technical Qualified',     'sids' => [12],          'color' => '#8B3A14'],
            ['key' => 'Under Technical Eval.',   'label' => 'Under Technical Eval.',   'sids' => [2, 4],        'color' => '#8B3A14'],
            ['key' => 'Technical Rejected',      'label' => 'Technical Rejected',      'sids' => [8, 6],        'color' => '#8B3A14']
        ];

        $statusCountMap = [];
        foreach ($statusRows as $r) {
            $statusCountMap[(int)$r['Sid']] = (int)$r['Count'];
        }

        $participationResults = [];
        foreach ($resultMapping as $rm) {
            $sum = 0;
            foreach ($rm['sids'] as $sid) {
                $sum += $statusCountMap[$sid] ?? 0;
            }
            $participationResults[] = [
                'label' => $rm['label'],
                'count' => $sum,
                'color' => $rm['color']
            ];
        }

        $conversionRate = ($totalBids > 0) ? round(($awarded / $totalBids) * 100, 1) : 0;

        return [
            'kpis' => [
                'totalBids'          => $totalBids,
                'participated'       => $participated,
                'underParticipation' => $underParticipation,
                'underDiscussion'    => $underDiscussion,
                'awarded'            => $awarded,
                'departments'        => count($deptRows),
                'bidTypes'           => count($typeRows),
                'totalEstValue'      => $totalEstValue,
                'totalEmdValue'      => $totalEmdValue,
                'conversionRate'     => $conversionRate
            ],
            'statusWorkflowTable' => $statusWorkflowTable,
            'statusDonut' => [
                'labels' => $donutLabels,
                'series' => $donutSeries,
                'colors' => $donutColors
            ],
            'participationResults' => $participationResults,
            'statusFunnel' => [
                'categories' => array_column($statusRows, 'DisplayName'),
                'series'     => array_map('intval', array_column($statusRows, 'Count'))
            ],
            'typeBreakdown' => [
                'categories' => array_column($typeRows, 'BidType'),
                'series'     => array_map('intval', array_column($typeRows, 'Count')),
                'values'     => array_map(function($v) { return (float) $v; }, array_column($typeRows, 'TotalValue')),
                'valuesFormatted' => array_map(function($v) { return '₹' . number_format((float) $v); }, array_column($typeRows, 'TotalValue'))
            ],
            'deptBreakdown' => [
                'categories' => array_column($deptRows, 'BidDepartment'),
                'series'     => array_map('intval', array_column($deptRows, 'Count')),
                'estValues'  => array_map(function($v) { return (float) $v; }, array_column($deptRows, 'TotalValue')),
                'emdValues'  => array_map(function($v) { return (float) $v; }, array_column($deptRows, 'EmdValue'))
            ],
            'monthlyTrend' => [
                'categories' => array_column($monthRows, 'MonthLabel'),
                'counts'     => array_map('intval', array_column($monthRows, 'Count')),
                'values'     => array_map(function($v) { return (float) $v; }, array_column($monthRows, 'TotalValue')),
                'emdValues'  => array_map(function($v) { return (float) $v; }, array_column($monthRows, 'EmdValue'))
            ],
            'monthlyTable' => $monthlyTable
        ];
    }

    /**
     * Get all bids with status metadata for client-side instant date filtering
     */
    public function getAllBidsWithStatus()
    {
        return $this->db->select('b.Bcid, b.BidTitle, b.BidType, b.BidDepartment, b.EstimatedValue, b.EmdValue, b.PublishedDate, b.OpeningDate, b.SubmissionDate, b.CreationTs, b.BidStatus, s.DisplayName as StatusName, s.BadgeClass, s.IconClass')
                        ->from('BidCreationDetails b')
                        ->join('BidStatusMaster s', 's.Sid = b.BidStatus', 'left')
                        ->order_by('b.CreationTs', 'DESC')
                        ->get()
                        ->result_array();
    }

    /**
     * Helper to format currency in Indian style (Lakh, Cr, K)
     */
    public function formatCurrency($value)
    {
        $val = (float) $value;
        if ($val >= 10000000) {
            return '₹' . number_format($val / 10000000, 2) . ' Cr';
        } elseif ($val >= 100000) {
            return '₹' . number_format($val / 100000, 2) . ' Lakh';
        } elseif ($val >= 1000) {
            return '₹' . number_format($val / 1000, 1) . ' K';
        }
        return '₹' . number_format($val, 2);
    }

} //Main Class
