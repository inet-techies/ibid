<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Oem extends MX_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->library('email');
		$this->load->database();
		$this->load->library('form_validation');
		$this->load->library('pagination');
		// $this->load->library('pdf');
		$check_session = $this->session->userdata('logged_in');
		$this->load->helper('cookie');
		$this->load->helper('string');
		$this->load->library('encrypt');
		$this->load->library('user_agent');
		date_default_timezone_set("Asia/Kolkata");
		$this->load->model('bid/bid_model');
		$this->load->model('oem/oem_model');

		$roleId = $this->session->userdata('RoleId'); // make sure role_id exists in session
		//echo "<pre>"; print_r($check_session); exit;
		$menus = $this->bid_model->getMenusByRole($check_session['RoleId']);
		// $currentUrl = uri_string();
		// 	//echo "<pre>currentUrl"; print_r($currentUrl); exit;
		// $data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);
		// // echo "<pre>currentUrl"; print_r($data['breadcrumb']); exit;
		$menuTree = [];

			foreach ($menus as $menu) {
			    if ($menu['ParentId'] === NULL) {
			        $menuTree[$menu['Bmid']] = $menu;
			        $menuTree[$menu['Bmid']]['children'] = [];
			    }
			}

			foreach ($menus as $menu) {
			    if ($menu['ParentId'] !== NULL && isset($menuTree[$menu['ParentId']])) {
			        $menuTree[$menu['ParentId']]['children'][] = $menu;
			    }
			}
		$this->load->vars('menuTree', $menuTree);

	} //Construct

	public function index()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			redirect($this->config->item('base_url')."oem/category");
		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	


	public function category()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

			$inps = $this->input->post();

			if($this->input->post())
			{
				$categoryName = $inps['category_name'];

				if(!empty($categoryName))
				{
					$insert = $this->oem_model->insertCategory($categoryName);

					if($insert)
					{
						$this->session->set_flashdata('true', 'Category Added Successfully...!');
						redirect($this->config->item('base_url').'oem/category');
					} else
					{
						$this->session->set_flashdata('err', 'Failed to add category. Please try again..!!');
						redirect($this->config->item('base_url').'oem/category');
					}
				} else
				{
					$this->session->set_flashdata('err', 'Category name cannot be empty..!');
					redirect($this->config->item('base_url').'oem/category');
				}
			}

			$data['categories'] = $this->oem_model->getCategories();

			$this->template->write_view('content', 'oem/category', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function toggleCategoryStatus()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id     = $this->input->post('id') ?: $this->input->post('c_id');
			$status = $this->input->post('status');

			if(!$id)
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Invalid Category ID'
				]);
				return;
			}

			$newStatus = ($status == 1) ? 0 : 1;
			$update = $this->oem_model->updateCategoryStatus($id, $newStatus);

			if($update)
			{
				echo json_encode([
					'status'    => true,
					'success'   => true,
					'newStatus' => $newStatus,
					'message'   => 'Category status updated successfully'
				]);
			} else
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Something went wrong'
				]);
			}

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function updateCategory()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$inps = $this->input->post();
			$cId  = (int)($inps['c_id'] ?? $inps['id'] ?? 0);
			$name = trim($inps['category_name'] ?? '');

			if($cId > 0 && !empty($name))
			{
				$update = $this->oem_model->updateCategory($cId, $name);
				if($update)
				{
					$this->session->set_flashdata('true', 'Category Updated Successfully...!');
				} else
				{
					$this->session->set_flashdata('err', 'Failed to update category. Please try again..!!');
				}
			} else
			{
				$this->session->set_flashdata('err', 'Category name and ID are required..!');
			}
			redirect($this->config->item('base_url').'oem/category');
		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function deleteCategory()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id = (int)($this->input->post('id') ?: $this->input->post('c_id'));
			if(!$id)
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid Category ID']);
				return;
			}

			$delete = $this->oem_model->deleteCategory($id);
			if($delete)
			{
				echo json_encode(['status' => true, 'success' => true, 'message' => 'Category deleted successfully']);
			} else
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Failed to delete category']);
			}
		} else
		{
			echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid session']);
		}
	}

	

	public function product()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

			$inps = $this->input->post();

			if($this->input->post())
			{
				$productList = $inps['product_name'];
				$categoryId  = $inps['category_name'];
				$oemId       = !empty($inps['oem_name']) ? $inps['oem_name'] : null;

				if(!empty($productList) && !empty($categoryId))
				{
					$insert = $this->oem_model->insertProduct($productList, $categoryId, $oemId);

					if($insert)
					{
						$this->session->set_flashdata('true', 'Product Registered Successfully...!');
						redirect($this->config->item('base_url').'oem/product');
					} else
					{
						$this->session->set_flashdata('err', 'Failed to register product. Please try again..!!');
						redirect($this->config->item('base_url').'oem/product');
					}
				} else
				{
					$this->session->set_flashdata('err', 'Product Name and Category are required..!');
					redirect($this->config->item('base_url').'oem/product');
				}
			}

			$data['categories'] = $this->oem_model->getCategories(true);
			$data['oems']       = $this->oem_model->getOems(['status' => 1]);
			$data['products']   = $this->oem_model->getProducts();

			$this->template->write_view('content', 'oem/product', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function toggleProductStatus()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id     = $this->input->post('id') ?: $this->input->post('p_id');
			$status = $this->input->post('status');

			if(!$id)
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Invalid Product ID'
				]);
				return;
			}

			$newStatus = ($status == 1) ? 0 : 1;
			$update = $this->oem_model->updateProductStatus($id, $newStatus);

			if($update)
			{
				echo json_encode([
					'status'    => true,
					'success'   => true,
					'newStatus' => $newStatus,
					'message'   => 'Product status updated successfully'
				]);
			} else
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Something went wrong'
				]);
			}

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function updateProduct()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$inps        = $this->input->post();
			$pId         = (int)($inps['p_id'] ?? $inps['id'] ?? 0);
			$productList = trim($inps['product_name'] ?? '');
			$categoryId  = (int)($inps['category_name'] ?? $inps['category_id'] ?? 0);
			$oemId       = !empty($inps['oem_name']) ? (int)$inps['oem_name'] : (!empty($inps['oem_id']) ? (int)$inps['oem_id'] : null);
			$status      = isset($inps['status']) ? (int)$inps['status'] : 1;

			if($pId > 0 && !empty($productList) && $categoryId > 0)
			{
				$updateData = [
					'ProductList' => $productList,
					'CategoryId'  => $categoryId,
					'OemId'       => $oemId,
					'Status'      => $status
				];

				$update = $this->oem_model->updateProduct($pId, $updateData);
				if($update)
				{
					$this->session->set_flashdata('true', 'Product Updated Successfully...!');
				} else
				{
					$this->session->set_flashdata('err', 'Failed to update product. Please try again..!!');
				}
			} else
			{
				$this->session->set_flashdata('err', 'Product Name, Category, and valid Product ID are required..!');
			}
			redirect($this->config->item('base_url').'oem/product');
		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function deleteProduct()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id = (int)($this->input->post('id') ?: $this->input->post('p_id'));
			if(!$id)
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid Product ID']);
				return;
			}

			$delete = $this->oem_model->deleteProduct($id);
			if($delete)
			{
				echo json_encode(['status' => true, 'success' => true, 'message' => 'Product deleted successfully']);
			} else
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Failed to delete product']);
			}
		} else
		{
			echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid session']);
		}
	}

	

	

	public function oemMaster()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

			$inps = $this->input->post();

			if($this->input->post())
			{
				$oemName = $inps['oem_name'];

				if(!empty($oemName))
				{
					$insertData = [
						'OemName'      => $oemName,
						'MobileNumber' => $inps['mobile_number'] ?? '',
						'EmailId'      => $inps['email_id'] ?? '',
						'Address'      => $inps['address'] ?? '',
						'Status'       => isset($inps['status']) ? (int)$inps['status'] : 1
					];

					$insert = $this->oem_model->insertOem($insertData);

					if($insert)
					{
						$this->session->set_flashdata('true', 'OEM Registered Successfully...!');
						redirect($this->config->item('base_url').'oem/oemMaster');
					} else
					{
						$this->session->set_flashdata('err', 'Failed to register OEM. Please try again..!!');
						redirect($this->config->item('base_url').'oem/oemMaster');
					}
				} else
				{
					$this->session->set_flashdata('err', 'OEM Name is required..!');
					redirect($this->config->item('base_url').'oem/oemMaster');
				}
			}

			$filter_inps = [
				'status' => $this->input->get('status', TRUE) ?? 'all',
				'search' => $this->input->get('search', TRUE) ?? ''
			];

			$data['oems']        = $this->oem_model->getOems($filter_inps);
			$data['filter_inps'] = $filter_inps;

			$this->template->write_view('content', 'oem/oem_master', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function updateOem()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$inps = $this->input->post();
			$sNo  = $inps['s_no'] ?? null;

			if(!empty($sNo))
			{
				$updateData = [
					'OemName'      => $inps['oem_name'],
					'MobileNumber' => $inps['mobile_number'] ?? '',
					'EmailId'      => $inps['email_id'] ?? '',
					'Address'      => $inps['address'] ?? '',
					'Status'       => (int)$inps['status']
				];

				$update = $this->oem_model->updateOem($sNo, $updateData);

				if($update)
				{
					$this->session->set_flashdata('true', 'OEM Details Updated Successfully...!');
					redirect($this->config->item('base_url').'oem/oemMaster');
				} else
				{
					$this->session->set_flashdata('err', 'Failed to update OEM. Please try again..!!');
					redirect($this->config->item('base_url').'oem/oemMaster');
				}
			} else
			{
				$this->session->set_flashdata('err', 'Invalid OEM record..!');
				redirect($this->config->item('base_url').'oem/oemMaster');
			}

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function toggleOemStatus()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id     = $this->input->post('id') ?: $this->input->post('s_no');
			$status = $this->input->post('status');

			if(!$id)
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Invalid OEM ID'
				]);
				return;
			}

			$newStatus = ($status == 1) ? 0 : 1;
			$update = $this->oem_model->updateOemStatus($id, $newStatus);

			if($update)
			{
				echo json_encode([
					'status'    => true,
					'success'   => true,
					'newStatus' => $newStatus,
					'message'   => 'OEM status updated successfully'
				]);
			} else
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Something went wrong'
				]);
			}

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function deleteOem()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id = (int)($this->input->post('id') ?: $this->input->post('s_no'));
			if(!$id)
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid OEM ID']);
				return;
			}

			$delete = $this->oem_model->deleteOem($id);
			if($delete)
			{
				echo json_encode(['status' => true, 'success' => true, 'message' => 'OEM Vendor deleted successfully']);
			} else
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Failed to delete OEM vendor']);
			}
		} else
		{
			echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid session']);
		}
	}

	
	
	

	public function materialMaster()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

			$inps = $this->input->post();

			if($this->input->post())
			{
				$materialName = $inps['material_name'];
				$categoryId   = $inps['category_id'];
				$productId    = $inps['product_id'];

				if(!empty($materialName) && !empty($categoryId) && !empty($productId))
				{
					$insertData = [
						'CategoryId'   => $categoryId,
						'ProductId'    => $productId,
						'MaterialName' => $materialName
					];

					$insert = $this->oem_model->insertMaterial($insertData);

					if($insert)
					{
						$this->session->set_flashdata('true', 'Material Added Successfully...!');
						redirect($this->config->item('base_url').'oem/materialMaster');
					} else
					{
						$this->session->set_flashdata('err', 'Failed to add material. Please try again..!!');
						redirect($this->config->item('base_url').'oem/materialMaster');
					}
				} else
				{
					$this->session->set_flashdata('err', 'Category, Product, and Material Name are required..!');
					redirect($this->config->item('base_url').'oem/materialMaster');
				}
			}

			$data['categories'] = $this->oem_model->getCategories(true);
			$data['products']   = $this->oem_model->getProducts(null, true);
			$data['materials']  = $this->oem_model->getMaterials();

			$this->template->write_view('content', 'oem/material_master', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function toggleMaterialStatus()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id     = $this->input->post('id') ?: $this->input->post('m_id');
			$status = $this->input->post('status');

			if(!$id)
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Invalid Material ID'
				]);
				return;
			}

			$newStatus = ($status == 1) ? 0 : 1;
			$update = $this->oem_model->updateMaterialStatus($id, $newStatus);

			if($update)
			{
				echo json_encode([
					'status'    => true,
					'success'   => true,
					'newStatus' => $newStatus,
					'message'   => 'Material status updated successfully'
				]);
			} else
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Something went wrong'
				]);
			}

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function updateMaterial()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$inps         = $this->input->post();
			$mId          = (int)($inps['m_id'] ?? $inps['id'] ?? 0);
			$materialName = trim($inps['material_name'] ?? '');
			$categoryId   = (int)($inps['category_id'] ?? 0);
			$productId    = (int)($inps['product_id'] ?? 0);
			$status       = isset($inps['status']) ? (int)$inps['status'] : 1;

			if($mId > 0 && !empty($materialName) && $categoryId > 0 && $productId > 0)
			{
				$updateData = [
					'CategoryId'   => $categoryId,
					'ProductId'    => $productId,
					'MaterialName' => $materialName,
					'Status'       => $status
				];

				$update = $this->oem_model->updateMaterial($mId, $updateData);
				if($update)
				{
					$this->session->set_flashdata('true', 'Material Updated Successfully...!');
				} else
				{
					$this->session->set_flashdata('err', 'Failed to update material. Please try again..!!');
				}
			} else
			{
				$this->session->set_flashdata('err', 'Category, Product, Material Name, and ID are required..!');
			}
			redirect($this->config->item('base_url').'oem/materialMaster');
		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function deleteMaterial()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id = (int)($this->input->post('id') ?: $this->input->post('m_id'));
			if(!$id)
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid Material ID']);
				return;
			}

			$delete = $this->oem_model->deleteMaterial($id);
			if($delete)
			{
				echo json_encode(['status' => true, 'success' => true, 'message' => 'Material deleted successfully']);
			} else
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Failed to delete material']);
			}
		} else
		{
			echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid session']);
		}
	}

	

	

	public function project()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

			$inputs = $this->input->post();

			if($this->input->post())
			{
				$projectData = [
					'ProjectName'       => $inputs['project_name'],
					'TenderNumber'      => $inputs['tender_number'],
					'State'             => $inputs['state'],
					'Department'        => $inputs['department'],
					'PortalLink'        => $inputs['portal_link'] ?? '',
					'ServiceBidOem'     => $inputs['service_bid'] ?? '',
					'RfbPublishedDate'  => !empty($inputs['rfb_published_date']) ? date('Y-m-d', strtotime($inputs['rfb_published_date'])) : null,
					'RfbSubmissionDate' => !empty($inputs['rfb_submission_date']) ? date('Y-m-d', strtotime($inputs['rfb_submission_date'])) : null,
					'Status'            => (int)$inputs['status'],
					'ProjectAwarded'    => $inputs['project_awarded'] ?? ''
				];

				$projectId = $this->oem_model->insertProject($projectData);

				if($projectId > 0)
				{
					// Save Bill of Materials
					$products   = $this->input->post('material_product');
					$quantities = $this->input->post('material_quantity');
					$units      = $this->input->post('material_unit');

					if(!empty($products) && is_array($products))
					{
						$batch = [];
						for ($i = 0; $i < count($products); $i++)
						{
							$pId = (int)$products[$i];
							if($pId > 0)
							{
								$prod = $this->oem_model->getProductById($pId);
								$batch[] = [
									'ProjectId'  => $projectId,
									'CategoryId' => $prod['CategoryId'] ?? null,
									'PId'        => $pId,
									'MId'        => null,
									'Quantity'   => !empty($quantities[$i]) ? (float)$quantities[$i] : 1.0,
									'Unit'       => !empty($units[$i]) ? trim($units[$i]) : 'Nos',
									'CreatedAt'  => date('Y-m-d H:i:s')
								];
							}
						}
						if(!empty($batch))
						{
							$this->oem_model->saveProjectMaterials($projectId, $batch);
						}
					}

					$this->session->set_flashdata('true', 'Project and Materials Created Successfully...!');
					redirect($this->config->item('base_url').'oem/project');
				} else
				{
					$this->session->set_flashdata('err', 'Failed to create project. Please try again..!!');
					redirect($this->config->item('base_url').'oem/project');
				}
			}

			$data['projects'] = $this->oem_model->getProjects();
			$data['products'] = $this->oem_model->getProducts(null, true);

			$this->template->write_view('content', 'oem/project', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function updateProject()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$inputs = $this->input->post();
			$sNo    = (int)($inputs['s_no'] ?? 0);

			if($sNo > 0)
			{
				$projectData = [
					'ProjectName'       => $inputs['project_name'],
					'TenderNumber'      => $inputs['tender_number'],
					'State'             => $inputs['state'],
					'Department'        => $inputs['department'],
					'PortalLink'        => $inputs['portal_link'] ?? '',
					'ServiceBidOem'     => $inputs['service_bid'] ?? '',
					'RfbPublishedDate'  => !empty($inputs['rfb_published_date']) ? date('Y-m-d', strtotime($inputs['rfb_published_date'])) : null,
					'RfbSubmissionDate' => !empty($inputs['rfb_submission_date']) ? date('Y-m-d', strtotime($inputs['rfb_submission_date'])) : null,
					'Status'            => (int)$inputs['status'],
					'ProjectAwarded'    => $inputs['project_awarded'] ?? ''
				];

				$this->oem_model->updateProject($sNo, $projectData);

				// Update Bill of Materials
				$products   = $this->input->post('material_product');
				$quantities = $this->input->post('material_quantity');
				$units      = $this->input->post('material_unit');

				if(!empty($products) && is_array($products))
				{
					$batch = [];
					for ($i = 0; $i < count($products); $i++)
					{
						$pId = (int)$products[$i];
						if($pId > 0)
						{
							$prod = $this->oem_model->getProductById($pId);
							$batch[] = [
								'ProjectId'  => $sNo,
								'CategoryId' => $prod['CategoryId'] ?? null,
								'PId'        => $pId,
								'MId'        => null,
								'Quantity'   => !empty($quantities[$i]) ? (float)$quantities[$i] : 1.0,
								'Unit'       => !empty($units[$i]) ? trim($units[$i]) : 'Nos',
								'CreatedAt'  => date('Y-m-d H:i:s')
							];
						}
					}
					$this->oem_model->saveProjectMaterials($sNo, $batch);
				}

				$this->session->set_flashdata('true', 'Project Details Updated Successfully...!');
				redirect($this->config->item('base_url').'oem/project');
			} else
			{
				$this->session->set_flashdata('err', 'Invalid project record..!');
				redirect($this->config->item('base_url').'oem/project');
			}

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function deleteProject()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$id = (int)($this->input->post('id') ?: $this->input->post('s_no'));
			if(!$id)
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid Project ID']);
				return;
			}

			$delete = $this->oem_model->deleteProject($id);
			if($delete)
			{
				echo json_encode(['status' => true, 'success' => true, 'message' => 'Project and associated BOM records deleted successfully']);
			} else
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Failed to delete project']);
			}
		} else
		{
			echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid session']);
		}
	}

	public function projectDetails($projectId = null)
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb('oem/project');

			$project = $this->oem_model->getProjectById($projectId);

			if(!$project)
			{
				$this->session->set_flashdata('err', 'Project not found..!');
				redirect($this->config->item('base_url').'oem/project');
				return;
			}

			$data['project']   = $project;
			$data['materials'] = $this->oem_model->getProjectMaterials($projectId);
			$data['prices']    = $this->oem_model->getProjectMaterialPrices($projectId);

			$this->template->write_view('content', 'oem/project_details', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	
	
	

	public function projectMaterials()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

			$data['projectMaterials'] = $this->oem_model->getAllProjectMaterials();
			$data['projects']         = $this->oem_model->getProjects();

			$this->template->write_view('content', 'oem/project_materials', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function deleteProjectMaterial()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$projectId = (int)$this->input->post('project_id');
			$pId       = (int)($this->input->post('p_id') ?: $this->input->post('id'));

			if(!$projectId || !$pId)
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid Project or Material reference']);
				return;
			}

			$delete = $this->oem_model->deleteProjectMaterial($projectId, $pId);
			if($delete)
			{
				echo json_encode(['status' => true, 'success' => true, 'message' => 'Equipment removed from project successfully']);
			} else
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Failed to remove equipment']);
			}
		} else
		{
			echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid session']);
		}
	}

	
	
	

	public function priceEntries()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb($currentUrl);

			$inputs = $this->input->post();

			if($this->input->post())
			{
				$projectId    = (int)($inputs['project_name'] ?? 0);
				$tenderNo     = $inputs['tender_number'] ?? '';
				$categories   = $inputs['inputCategory'] ?? [];
				$products     = $inputs['inputProduct'] ?? [];
				$oems         = $inputs['inputOEM'] ?? [];
				$prices       = $inputs['inputPrice'] ?? [];
				$units        = $inputs['inputUnit'] ?? [];
				$changeReason = $inputs['inputchange_reason'] ?? [];

				if($projectId > 0 && !empty($categories) && is_array($categories))
				{
					// Duplicate quote validation
					for ($i = 0; $i < count($categories); $i++)
					{
						$exists = $this->oem_model->checkPriceDuplicate($projectId, $oems[$i], $prices[$i], $products[$i]);
						if($exists)
						{
							$this->session->set_flashdata('err', 'A quote for this Project, Product, OEM and Price already exists..!');
							redirect($this->config->item('base_url').'oem/priceEntries');
							return;
						}
					}

					$batch = [];
					for ($i = 0; $i < count($categories); $i++)
					{
						$batch[] = [
							'ProjectId'    => $projectId,
							'TId'          => (int)$tenderNo,
							'CId'          => (int)$categories[$i],
							'PId'          => (int)$products[$i],
							'MId'          => null,
							'OemId'        => (int)$oems[$i],
							'Price'        => (float)$prices[$i],
							'ChangeReason' => trim($changeReason[$i] ?? ''),
							'Unit'         => trim($units[$i] ?? 'Nos'),
							'Status'       => 1,
							'CreatedAt'    => date('Y-m-d H:i:s')
						];
					}

					$insertBatch = $this->oem_model->insertPriceEntriesBatch($batch);

					if($insertBatch)
					{
						$this->session->set_flashdata('true', 'Price Entries Recorded Successfully...!');
						redirect($this->config->item('base_url').'oem/priceEntries');
					} else
					{
						$this->session->set_flashdata('err', 'Failed to save price entries. Please try again..!!');
						redirect($this->config->item('base_url').'oem/priceEntries');
					}
				} else
				{
					$this->session->set_flashdata('err', 'Please select a project and at least one quote..!');
					redirect($this->config->item('base_url').'oem/priceEntries');
				}
			}

			$data['projects']   = $this->oem_model->getProjects();
			$data['categories'] = $this->oem_model->getCategories(true);
			$data['products']   = $this->oem_model->getProducts(null, true);
			$data['oems']       = $this->oem_model->getOems(['status' => 1]);

			$this->template->write_view('content', 'oem/price_entries', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function getExistingPriceEntries()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$tenderNo  = $this->input->post('tender_no');
			$projectId = (int)$this->input->post('project_id');

			$entries = $this->oem_model->getExistingPriceEntries($projectId, $tenderNo);
			echo json_encode($entries);

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function updatePriceEntry()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$sNo          = (int)($this->input->post('s_no') ?: $this->input->post('id'));
			$newPrice     = (float)$this->input->post('price');
			$changeReason = trim($this->input->post('change_reason', TRUE));

			if($sNo > 0 && !empty($changeReason))
			{
				$update = $this->oem_model->updatePriceEntry($sNo, $newPrice, $changeReason);

				if($update)
				{
					echo json_encode([
						'status'  => true,
						'success' => true,
						'message' => 'Price updated successfully'
					]);
				} else
				{
					echo json_encode([
						'status'  => false,
						'success' => false,
						'message' => 'Failed to update price'
					]);
				}
			} else
			{
				echo json_encode([
					'status'  => false,
					'success' => false,
					'message' => 'Missing required fields or reason'
				]);
			}

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function deletePriceEntry()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$sNo = (int)($this->input->post('s_no') ?: $this->input->post('id'));

			if(!$sNo)
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid Price Entry ID']);
				return;
			}

			$delete = $this->oem_model->deletePriceEntry($sNo);
			if($delete)
			{
				echo json_encode(['status' => true, 'success' => true, 'message' => 'Price entry deleted successfully']);
			} else
			{
				echo json_encode(['status' => false, 'success' => false, 'message' => 'Failed to delete price entry']);
			}
		} else
		{
			echo json_encode(['status' => false, 'success' => false, 'message' => 'Invalid session']);
		}
	}

	public function priceHistory()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb('oem/priceEntries');

			$filter_inps = [
				'project'   => $this->input->get('project', TRUE),
				'product'   => $this->input->get('product', TRUE),
				'oem'       => $this->input->get('oem', TRUE),
				'search'    => $this->input->get('search', TRUE),
				'date_from' => $this->input->get('date_from', TRUE),
				'date_to'   => $this->input->get('date_to', TRUE)
			];

			$data['priceEntries'] = $this->oem_model->getPriceEntries($filter_inps);
			$data['projects']     = $this->oem_model->getProjects();
			$data['products']     = $this->oem_model->getProducts(null, true);
			$data['oems']         = $this->oem_model->getOems(['status' => 1]);
			$data['filter_inps']  = $filter_inps;

			$this->template->write_view('content', 'oem/price_history', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function priceComparison()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			//Get current URL
			$currentUrl = strtolower(uri_string());

			//Fetch breadcrumb data
			$data['currentUrlArray'] = $this->bid_model->getBreadcrumb('oem/priceEntries');

			$projectId = (int)$this->input->get('project_id');
			$data['selectedProjectId']  = $projectId;
			$data['projects']           = $this->oem_model->getProjects();
			$data['projectsWithPrices'] = $this->oem_model->getProjectsWithPriceSummary();

			$quoteCounts = [];
			if (!empty($data['projectsWithPrices'])) {
				foreach ($data['projectsWithPrices'] as $pwp) {
					$quoteCounts[$pwp['SNo']] = [
						'quotes' => (int)$pwp['QuoteCount'],
						'oems'   => (int)$pwp['OemCount'],
					];
				}
			}
			$data['quoteCounts'] = $quoteCounts;

			if($projectId > 0)
			{
				$data['project'] = $this->oem_model->getProjectById($projectId);
				$data['quotes']  = $this->oem_model->getProjectMaterialPrices($projectId);
			} else
			{
				$data['project'] = null;
				$data['quotes']  = [];
			}

			$this->template->write_view('content', 'oem/price_comparison', $data);
			$this->template->render();

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}


	


	public function getProjectName()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$tenderNumber = $this->input->post('tender_number', TRUE);
			$project = $this->oem_model->getProjectByTender($tenderNumber);
			echo json_encode($project ? [$project] : []);

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function getProductsByCategory()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$categoryId = (int)$this->input->post('category_id');
			$products = $this->oem_model->getProductsByCategory($categoryId);
			echo json_encode($products);

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function getMaterialsByProduct()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$productId = (int)$this->input->post('product_id');
			$materials = $this->oem_model->getMaterialsByProduct($productId);
			echo json_encode($materials);

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

	public function getProjectMaterials()
	{
		$Bid_Session = $this->session->userdata('logged_in');

		if(isset($Bid_Session) && !empty($Bid_Session))
		{
			$projectId = (int)$this->input->post('project_id');
			$materials = $this->oem_model->getProjectMaterials($projectId);
			echo json_encode($materials);

		} else
		{
			$this->session->set_flashdata('err','Invalid Session.Please Login Again..!!');
			redirect($this->config->item('base_url')."bid/index");
		}
	}

} //Main Class
