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

} //Main Class
