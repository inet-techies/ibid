<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Oem_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }


    //  CATEGORY METHODS


    public function getCategories($onlyActive = false)
    {
        $this->db->select('*');
        $this->db->from('Category');
        if ($onlyActive) {
            $this->db->where('Status', 1);
        }
        $this->db->order_by('CategoryList', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getCategoryById($cId)
    {
        return $this->db->get_where('Category', ['CId' => $cId])->row_array();
    }

    public function insertCategory($categoryName)
    {
        $data = [
            'CategoryList' => trim($categoryName),
            'Status'       => 1,
            'CreatedAt'    => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('Category', $data);
    }

    public function updateCategory($cId, $categoryName)
    {
        $this->db->where('CId', $cId);
        return $this->db->update('Category', ['CategoryList' => trim($categoryName)]);
    }

    public function updateCategoryStatus($cId, $status)
    {
        $this->db->where('CId', $cId);
        return $this->db->update('Category', ['Status' => $status]);
    }

    public function deleteCategory($cId)
    {
        $this->db->where('CId', $cId);
        return $this->db->delete('Category');
    }

    
    //  PRODUCT METHODS
  

    public function getProducts($categoryId = null, $onlyActive = false)
    {
        $this->db->select('p.PId, p.ProductList, p.CategoryId, p.OemId, p.Status, p.CreatedAt, c.CategoryList, o.OemName');
        $this->db->from('Product as p');
        $this->db->join('Category as c', 'c.CId = p.CategoryId', 'left');
        $this->db->join('OemTable as o', 'o.SNo = p.OemId', 'left');

        if (!empty($categoryId)) {
            $this->db->where('p.CategoryId', $categoryId);
        }
        if ($onlyActive) {
            $this->db->where('p.Status', 1);
        }
        $this->db->order_by('p.ProductList', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getProductById($pId)
    {
        $this->db->select('p.*, c.CategoryList, o.OemName');
        $this->db->from('Product as p');
        $this->db->join('Category as c', 'c.CId = p.CategoryId', 'left');
        $this->db->join('OemTable as o', 'o.SNo = p.OemId', 'left');
        $this->db->where('p.PId', $pId);
        return $this->db->get()->row_array();
    }

    public function getProductsByCategory($categoryId)
    {
        $this->db->select('PId, ProductList, CategoryId, OemId');
        $this->db->from('Product');
        $this->db->where('CategoryId', $categoryId);
        $this->db->where('Status', 1);
        $this->db->order_by('ProductList', 'ASC');
        return $this->db->get()->result_array();
    }

    public function insertProduct($productList, $categoryId, $oemId = null)
    {
        $data = [
            'ProductList' => trim($productList),
            'CategoryId'  => $categoryId,
            'OemId'       => !empty($oemId) ? $oemId : null,
            'Status'      => 1,
            'CreatedAt'   => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('Product', $data);
    }

    public function updateProduct($pId, $data)
    {
        $updateData = [
            'ProductList' => trim($data['ProductList']),
            'CategoryId'  => $data['CategoryId'],
            'OemId'       => !empty($data['OemId']) ? $data['OemId'] : null
        ];
        if (isset($data['Status'])) {
            $updateData['Status'] = (int)$data['Status'];
        }
        $this->db->where('PId', $pId);
        return $this->db->update('Product', $updateData);
    }

    public function updateProductStatus($pId, $status)
    {
        $this->db->where('PId', $pId);
        return $this->db->update('Product', ['Status' => $status]);
    }

    public function deleteProduct($pId)
    {
        $this->db->where('PId', $pId);
        return $this->db->delete('Product');
    }

  
    //  OEM 
  

    public function getOems($inps = [])
    {
        $this->db->select('*');
        $this->db->from('OemTable as o');

        if (!empty($inps['status']) && $inps['status'] !== 'all') {
            $this->db->where('o.Status', $inps['status']);
        }
        if (!empty($inps['search'])) {
            $this->db->group_start();
            $this->db->like('o.OemName', $inps['search']);
            $this->db->or_like('o.MobileNumber', $inps['search']);
            $this->db->or_like('o.EmailId', $inps['search']);
            $this->db->group_end();
        }

        $this->db->order_by('o.OemName', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getOemById($sNo)
    {
        return $this->db->get_where('OemTable', ['SNo' => $sNo])->row_array();
    }

    public function insertOem($data)
    {
        $insertData = [
            'OemName'      => trim($data['OemName']),
            'MobileNumber' => trim($data['MobileNumber'] ?? ''),
            'EmailId'      => trim($data['EmailId'] ?? ''),
            'Address'      => trim($data['Address'] ?? ''),
            'Status'       => isset($data['Status']) ? (int)$data['Status'] : 1,
            'CreationDate' => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('OemTable', $insertData);
    }

    public function updateOem($sNo, $data)
    {
        $updateData = [
            'OemName'      => trim($data['OemName']),
            'MobileNumber' => trim($data['MobileNumber'] ?? ''),
            'EmailId'      => trim($data['EmailId'] ?? ''),
            'Address'      => trim($data['Address'] ?? ''),
            'Status'       => isset($data['Status']) ? (int)$data['Status'] : 1
        ];
        $this->db->where('SNo', $sNo);
        return $this->db->update('OemTable', $updateData);
    }

    public function updateOemStatus($sNo, $status)
    {
        $this->db->where('SNo', $sNo);
        return $this->db->update('OemTable', ['Status' => $status]);
    }

    public function deleteOem($sNo)
    {
        $this->db->where('SNo', $sNo);
        return $this->db->delete('OemTable');
    }

    
    //  MATERIAL
 

    public function getMaterials($categoryId = null, $productId = null, $onlyActive = false)
    {
        $this->db->select('m.MId, m.CategoryId, m.ProductId, m.MaterialName, m.Status, m.CreatedAt, c.CategoryList, p.ProductList');
        $this->db->from('MaterialMaster as m');
        $this->db->join('Category as c', 'c.CId = m.CategoryId', 'left');
        $this->db->join('Product as p', 'p.PId = m.ProductId', 'left');

        if (!empty($categoryId)) {
            $this->db->where('m.CategoryId', $categoryId);
        }
        if (!empty($productId)) {
            $this->db->where('m.ProductId', $productId);
        }
        if ($onlyActive) {
            $this->db->where('m.Status', 1);
        }

        $this->db->order_by('m.MaterialName', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getMaterialById($mId)
    {
        $this->db->select('m.*, c.CategoryList, p.ProductList');
        $this->db->from('MaterialMaster as m');
        $this->db->join('Category as c', 'c.CId = m.CategoryId', 'left');
        $this->db->join('Product as p', 'p.PId = m.ProductId', 'left');
        $this->db->where('m.MId', $mId);
        return $this->db->get()->row_array();
    }

    public function getMaterialsByProduct($productId)
    {
        $this->db->select('MId, MaterialName, CategoryId, ProductId');
        $this->db->from('MaterialMaster');
        $this->db->where('ProductId', $productId);
        $this->db->where('Status', 1);
        $this->db->order_by('MaterialName', 'ASC');
        return $this->db->get()->result_array();
    }

    public function insertMaterial($data)
    {
        $insertData = [
            'CategoryId'   => $data['CategoryId'],
            'ProductId'    => $data['ProductId'],
            'MaterialName' => trim($data['MaterialName']),
            'Status'       => 1,
            'CreatedAt'    => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('MaterialMaster', $insertData);
    }

    public function updateMaterial($mId, $data)
    {
        $updateData = [
            'CategoryId'   => $data['CategoryId'],
            'ProductId'    => $data['ProductId'],
            'MaterialName' => trim($data['MaterialName']),
            'Status'       => isset($data['Status']) ? (int)$data['Status'] : 1
        ];
        $this->db->where('MId', $mId);
        return $this->db->update('MaterialMaster', $updateData);
    }

    public function updateMaterialStatus($mId, $status)
    {
        $this->db->where('MId', $mId);
        return $this->db->update('MaterialMaster', ['Status' => $status]);
    }

    public function deleteMaterial($mId)
    {
        $this->db->where('MId', $mId);
        return $this->db->delete('MaterialMaster');
    }

    
    //  PROJECT METHODS
   

    public function getProjects($inps = [])
    {
        $this->db->select('*');
        $this->db->from('ProjectTable');

        if (!empty($inps['status'])) {
            $this->db->where('Status', $inps['status']);
        }
        if (!empty($inps['search'])) {
            $this->db->group_start();
            $this->db->like('ProjectName', $inps['search']);
            $this->db->or_like('TenderNumber', $inps['search']);
            $this->db->or_like('Department', $inps['search']);
            $this->db->group_end();
        }

        $this->db->order_by('SNo', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getProjectById($sNo)
    {
        return $this->db->get_where('ProjectTable', ['SNo' => $sNo])->row_array();
    }

    public function getProjectByTender($tenderNumber)
    {
        return $this->db->get_where('ProjectTable', ['TenderNumber' => $tenderNumber])->row_array();
    }

    public function insertProject($data)
    {
        $insertData = [
            'State'             => trim($data['State'] ?? ''),
            'Department'        => trim($data['Department'] ?? ''),
            'ProjectName'       => trim($data['ProjectName']),
            'TenderNumber'      => trim($data['TenderNumber']),
            'PortalLink'        => trim($data['PortalLink'] ?? ''),
            'ServiceBidOem'     => trim($data['ServiceBidOem'] ?? ''),
            'RfbPublishedDate'  => !empty($data['RfbPublishedDate']) ? $data['RfbPublishedDate'] : null,
            'RfbSubmissionDate' => !empty($data['RfbSubmissionDate']) ? $data['RfbSubmissionDate'] : null,
            'Status'            => isset($data['Status']) ? (int)$data['Status'] : 1,
            'ProjectAwarded'    => trim($data['ProjectAwarded'] ?? ''),
            'CreationDate'      => date('Y-m-d H:i:s')
        ];
        if ($this->db->insert('ProjectTable', $insertData)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function updateProject($sNo, $data)
    {
        $updateData = [
            'State'             => trim($data['State'] ?? ''),
            'Department'        => trim($data['Department'] ?? ''),
            'ProjectName'       => trim($data['ProjectName']),
            'TenderNumber'      => trim($data['TenderNumber']),
            'PortalLink'        => trim($data['PortalLink'] ?? ''),
            'ServiceBidOem'     => trim($data['ServiceBidOem'] ?? ''),
            'RfbPublishedDate'  => !empty($data['RfbPublishedDate']) ? $data['RfbPublishedDate'] : null,
            'RfbSubmissionDate' => !empty($data['RfbSubmissionDate']) ? $data['RfbSubmissionDate'] : null,
            'Status'            => isset($data['Status']) ? (int)$data['Status'] : 1,
            'ProjectAwarded'    => trim($data['ProjectAwarded'] ?? '')
        ];
        $this->db->where('SNo', $sNo);
        return $this->db->update('ProjectTable', $updateData);
    }

    public function deleteProject($sNo)
    {
        $this->db->where('ProjectId', $sNo)->delete('ProjectMaterials');
        $this->db->where('ProjectId', $sNo)->delete('PriceEntries');
        $this->db->where('SNo', $sNo);
        return $this->db->delete('ProjectTable');
    }

    
    // PROJECT MATERIALS 
   

    public function getProjectMaterials($projectId)
    {
        $this->db->select('pm.*, p.ProductList, c.CategoryList, m.MaterialName');
        $this->db->from('ProjectMaterials as pm');
        $this->db->join('Product as p', 'p.PId = pm.PId', 'left');
        $this->db->join('Category as c', 'c.CId = pm.CategoryId', 'left');
        $this->db->join('MaterialMaster as m', 'm.MId = pm.MId', 'left');
        $this->db->where('pm.ProjectId', $projectId);
        $this->db->order_by('p.ProductList', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getAllProjectMaterials()
    {
        $this->db->select('pm.*, pt.ProjectName, pt.TenderNumber, p.ProductList, c.CategoryList, m.MaterialName');
        $this->db->from('ProjectMaterials as pm');
        $this->db->join('ProjectTable as pt', 'pt.SNo = pm.ProjectId', 'left');
        $this->db->join('Product as p', 'p.PId = pm.PId', 'left');
        $this->db->join('Category as c', 'c.CId = pm.CategoryId', 'left');
        $this->db->join('MaterialMaster as m', 'm.MId = pm.MId', 'left');
        $this->db->order_by('pt.ProjectName', 'ASC');
        return $this->db->get()->result_array();
    }

    public function saveProjectMaterials($projectId, $materialsBatch)
    {
        $this->db->where('ProjectId', $projectId)->delete('ProjectMaterials');
        if (!empty($materialsBatch)) {
            return $this->db->insert_batch('ProjectMaterials', $materialsBatch);
        }
        return true;
    }

    public function deleteProjectMaterial($projectId, $pId)
    {
        $this->db->where('ProjectId', $projectId);
        $this->db->where('PId', $pId);
        return $this->db->delete('ProjectMaterials');
    }

    
    //  PRICE ENTRIES 
    

    public function getPriceEntries($inps = [])
    {
        $this->db->select('pe.*, pt.ProductList, o.OemName, pro.ProjectName, pro.TenderNumber, c.CategoryList, m.MaterialName');
        $this->db->from('PriceEntries as pe');
        $this->db->join('Product as pt', 'pt.PId = pe.PId', 'left');
        $this->db->join('OemTable as o', 'o.SNo = pe.OemId', 'left');
        $this->db->join('ProjectTable as pro', 'pro.SNo = pe.ProjectId', 'left');
        $this->db->join('Category as c', 'c.CId = pe.CId', 'left');
        $this->db->join('MaterialMaster as m', 'm.MId = pe.MId', 'left');

        if (!empty($inps['product'])) {
            $this->db->where('pe.PId', $inps['product']);
        }
        if (!empty($inps['oem'])) {
            $this->db->where('pe.OemId', $inps['oem']);
        }
        if (!empty($inps['project'])) {
            $this->db->where('pe.ProjectId', $inps['project']);
        }
        if (!empty($inps['search'])) {
            $this->db->group_start();
            $this->db->like('o.OemName', $inps['search']);
            $this->db->or_like('pt.ProductList', $inps['search']);
            $this->db->or_like('pro.ProjectName', $inps['search']);
            $this->db->group_end();
        }
        if (!empty($inps['date_from'])) {
            $this->db->where('DATE(pe.CreatedAt) >=', $inps['date_from']);
        }
        if (!empty($inps['date_to'])) {
            $this->db->where('DATE(pe.CreatedAt) <=', $inps['date_to']);
        }

        $this->db->order_by('pe.CreatedAt', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getExistingPriceEntries($projectId = null, $tenderNo = null)
    {
        $this->db->select('pe.SNo, pe.ProjectId, pe.TId, pe.CId, pe.PId, pe.MId, pe.OemId, pe.Price, pe.Unit, pe.ChangeReason, pe.CreatedAt, c.CategoryList, p.ProductList, o.OemName, m.MaterialName');
        $this->db->from('PriceEntries as pe');
        $this->db->join('Category as c', 'c.CId = pe.CId', 'left');
        $this->db->join('Product as p', 'p.PId = pe.PId', 'left');
        $this->db->join('OemTable as o', 'o.SNo = pe.OemId', 'left');
        $this->db->join('MaterialMaster as m', 'm.MId = pe.MId', 'left');

        if (!empty($projectId)) {
            $this->db->where('pe.ProjectId', $projectId);
        } elseif (!empty($tenderNo)) {
            $this->db->where('pe.TId', $tenderNo);
        }

        $this->db->order_by('pe.CreatedAt', 'DESC');
        return $this->db->get()->result_array();
    }

    public function checkPriceDuplicate($projectId, $oemId, $price, $pId = null)
    {
        $this->db->where('ProjectId', $projectId);
        $this->db->where('OemId', $oemId);
        $this->db->where('Price', $price);
        if (!empty($pId)) {
            $this->db->where('PId', $pId);
        }
        return $this->db->get('PriceEntries')->row();
    }

    public function insertPriceEntriesBatch($batch)
    {
        return $this->db->insert_batch('PriceEntries', $batch);
    }

    public function updatePriceEntry($sNo, $price, $changeReason)
    {
        $data = [
            'Price'        => $price,
            'ChangeReason' => $changeReason
        ];
        $this->db->where('SNo', $sNo);
        return $this->db->update('PriceEntries', $data);
    }

    public function deletePriceEntry($sNo)
    {
        $this->db->where('SNo', $sNo);
        return $this->db->delete('PriceEntries');
    }

   
    //  PRICE COMPARISON
   

    public function getProjectMaterialPrices($projectId, $categoryId = null, $productId = null)
    {
        $this->db->select('pe.SNo, pe.ProjectId, pe.CId, pe.PId, pe.MId, pe.OemId, pe.Price, pe.Unit, pe.CreatedAt, o.OemName, pt.ProjectName, p.ProductList, c.CategoryList');
        $this->db->from('PriceEntries as pe');
        $this->db->join('OemTable as o', 'o.SNo = pe.OemId', 'left');
        $this->db->join('ProjectTable as pt', 'pt.SNo = pe.ProjectId', 'left');
        $this->db->join('Product as p', 'p.PId = pe.PId', 'left');
        $this->db->join('Category as c', 'c.CId = pe.CId', 'left');
        $this->db->where('pe.ProjectId', $projectId);

        if (!empty($categoryId)) {
            $this->db->where('pe.CId', $categoryId);
        }
        if (!empty($productId)) {
            $this->db->where('pe.PId', $productId);
        }

        $this->db->order_by('pe.Price', 'ASC');
        return $this->db->get()->result_array();
    }

    public function getProjectsWithPriceSummary()
    {
        $this->db->select('pt.SNo, pt.ProjectName, pt.TenderNumber, COUNT(pe.SNo) as QuoteCount, COUNT(DISTINCT pe.OemId) as OemCount');
        $this->db->from('ProjectTable as pt');
        $this->db->join('PriceEntries as pe', 'pe.ProjectId = pt.SNo', 'inner');
        $this->db->group_by(['pt.SNo', 'pt.ProjectName', 'pt.TenderNumber']);
        $this->db->order_by('pt.ProjectName', 'ASC');
        return $this->db->get()->result_array();
    }
}

