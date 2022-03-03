<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Helper {
	/**
	* Copy a file, or recursively copy a folder and its contents
	* @author      Aidan Lister <aidan@php.net>
	* @version     1.0.1
	* @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
	* @param       string   $source    Source path
	* @param       string   $dest      Destination path
	* @param       int      $permissions New folder creation permissions
	* @return      bool     Returns true on success, false on failure
	*/
	public static function xcopy($source, $dest, $permissions = 0755) {
		// Check for symlinks
		if (is_link($source)) {
			return @symlink(readlink($source), $dest);
		}

		// Simple copy for a file
		if (is_file($source)) {
			return @copy($source, $dest);
		}

		// Make destination directory
		if (!is_dir($dest)) {
			@mkdir($dest, $permissions);
		}

		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			self::xcopy("$source/$entry", "$dest/$entry", $permissions);
		}

		// Clean up
		$dir->close();
		return true;
	}
	
	/**
	* Delete a folder and its contents
	*/
	public static function xdelete($dir) {
		if(!is_dir($dir)) {
			throw new InvalidArgumentException("$dir must be a directory");
		}
		
		if(substr($dir, strlen($dir) - 1, 1) != '/') {
			$dir .= '/';
		}

		$files = glob($dir . '*', GLOB_MARK);
		foreach($files as $file) {
			if(is_dir($file)) {
				self::xdelete($file);
			} else {
				unlink($file);
			}
		}
		return rmdir($dir);
	}
}

abstract class Request {
	public static function getQuery($param = null, $default = null) {
		if ($param) {
			return isset($_GET[$param]) ? $_GET[$param] : $default;
		}
		return $_GET;
	}
	public static function getPost($param = null, $default = null) {
		if ($param) {
			return isset($_POST[$param]) ? $_POST[$param] : $default;
		}
		return $_POST;
	}
	public static function getFile($param = null, $default = null) {
		if ($param) {
			return isset($_FILES[$param]) ? $_FILES[$param] : $default;
		}
		return $_FILES;
	}
	public static function getPostContent() {
		$rawData = file_get_contents('php://input');
		return json_decode($rawData);
	}
	public static function getApiParam($param) {
		$oData = static::getPostContent();
		return isset($oData->$param) ? $oData->$param : null;
	}
	public static function getApiOrQueryParam($param) {
		return Request::getApiParam($param) ? Request::getApiParam($param) : Request::getQuery($param);
	}
}

class FileManager extends CI_Controller {
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	 
	public function index() {
		if(!$this->session->userdata('logged_in')) {
			redirect('login', 'refresh');
			return;
		}
		
		if (Request::getApiParam('action') === 'list') {
			$page = Request::getApiParam('page');
			$itemsPerPage = Request::getApiParam('itemsPerPage');
			$path = Request::getApiParam('path');
			
			
			$this->config->load('easyfile');
			$dir = $this->config->item('easyfile_upload_path') . (strlen($path) ? '/' . $path : '');
			$link = base_url() . $dir;
			
			
			$folders = array();
			$files = array();
			if(file_exists( $dir )) {
				foreach(scandir($dir) as $f) {
					if(!$f || $f[0] == '.') {
						continue;
					}
					
					$name = iconv('windows-1251', 'UTF-8', $f );
					
					if(is_dir($dir . '/' . $f)) {
						$folders[] = array(
							'id' => uniqid('',true),
							'name' => $name,
							'type' => 'folder',
							'size' => 0,
							'created_at' => date('Y-m-d h:i:s',filectime($dir . '/' . $f)),
							'updated_at' => date('Y-m-d h:i:s',filemtime($dir . '/' . $f)),
							'link' => $link
						);
					} else {
						$files[] = array(
							'id' => uniqid('',true),
							'name' => $name,
							'type' => 'file',
							'size' => filesize($dir . '/' . $f),
							'created_at' => date('Y-m-d h:i:s',filectime($dir . '/' . $f)),
							'updated_at' => date('Y-m-d h:i:s',filemtime($dir . '/' . $f)),
							'link' => $link
						);
					}
				}
			}
			$items = array_merge($folders, $files);
			
			
			// generate items for current page
			$itemsTotal = count($items);
			if($itemsTotal > 0) {
				$pages = ceil($itemsTotal / $itemsPerPage) - 1;
				
				if($page > $pages) {
					$page = $pages;
				} else if($page < 0) {
					$page = 0;
				}
				
				$items = array_slice($items, $itemsPerPage * $page, $itemsPerPage);
			}
			
			
			
			// generate path for breadcrumb
			$sections = explode('/', $dir);
			$pathArray = array();
			$path = '';
			$id = 0;
			
			foreach($sections as $section) {
				$path = ($id == 0 ? '' : $path . ($id > 1 ? '/' : '') . $section);
				$name = ($id == 0 ? 'Root' : $section);
				
				$pathArray[] = array(
					'id' => $id++,
					'name' => $name,
					'path' => $path
				);
			}
			
			$data['items'] = $items;
			$data['total'] = $itemsTotal;
			$data['page'] = $page;
			$data['itemsPerPage'] = $itemsPerPage;
			$data['path'] = $pathArray;
			
			echo json_encode($data);
			return;
		}
		
		if (Request::getApiParam('action') === 'rename') {
			$oldName = Request::getApiParam('oldname');
			$newName = Request::getApiParam('newname');
			$path = Request::getApiParam('path');
			
			$this->config->load('easyfile');
			$dir = $this->config->item('easyfile_upload_path') . (strlen($path) ? '/' . $path : '');
			
			$oldDir = $dir . '/' . iconv('UTF-8', 'windows-1251', $oldName );
			$newDir = $dir . '/' . iconv('UTF-8', 'windows-1251', $newName );
			
			$data['result'] = false;
			$data['name'] = $oldName;
			if(!file_exists($newDir)) {
				if(@rename($oldDir, $newDir)) {
					$data['result'] = true;
					$data['name'] = $newName;
				} else {
					$data['error'] = 'Error. Cannot rename, unknown error listing';
				}
			} else {
				$data['error'] = 'Error. Cannot rename because another file exists with the same name';
			}
			
			echo json_encode($data);
			return;
		}
		
		if (Request::getApiParam('action') === 'delete') {
			$items = Request::getApiParam('items');
			$path = Request::getApiParam('path');
			
			$this->config->load('easyfile');
			$dir = $this->config->item('easyfile_upload_path') . (strlen($path) ? '/' . $path : '');
			
			$data['error'] = '';
			
			$processed = array();
			$unprocessed = array();
			foreach ($items as $item) {
				$dir_or_file = $dir . '/' . iconv('UTF-8', 'windows-1251', $item->name );
				
				if (is_dir($dir_or_file)) {
					if(Helper::xdelete($dir_or_file)) {
						$processed[] = $item->id;
					} else {
						$unprocessed[] = $item->id;
						$data['error'] .= 'Error. Cannot delete the folder \'' . $item->name . '\', unknown error listing' . PHP_EOL;
					}
				} else {
					if (file_exists($dir_or_file)) {
						if(is_writable($dir_or_file)) {
							if(unlink($dir_or_file)) {
								$processed[] = $item->id;
							} else {
								$unprocessed[] = $item->id;
								$data['error'] .= 'Error. Cannot delete the file \'' . $item->name . '\', unknown error listing' . PHP_EOL;
							}
						} else {
							$unprocessed[] = $item->id;
							$data['error'] .= 'Error. Cannot delete the file \'' . $item->name . '\' because access is denied' . PHP_EOL;
						}
					} else {
						$unprocessed[] = $item->id;
						$data['error'] .= 'Error. Cannot delete the file \'' . $item->name . '\' because it doesn\'t exist' . PHP_EOL;
					}
				}
			}
			
			$data['processed'] = $processed;
			$data['unprocessed'] = $unprocessed;
			
			
			echo json_encode($data);
			return;
		}
		
		if (Request::getApiParam('action') === 'mkdir') {
			$name = Request::getApiParam('name');
			$path = Request::getApiParam('path');
			
			$this->config->load('easyfile');
			$dir = $this->config->item('easyfile_upload_path') . (strlen($path) ? '/' . $path : '');
			$link = base_url() . $dir;
			
			$dir = $dir . '/' . iconv('UTF-8', 'windows-1251', $name );
			
			
			$data['result'] = false;
			$data['item'] = null;
			$data['error'] = '';
			
			if(!file_exists($dir)) {
				if(@mkdir($dir)) {
					$data['result'] = true;
					$data['item'] = array(
						'id' => uniqid('',true),
						'name' => $name,
						'type' => 'folder',
						'size' => 0,
						'created_at' => date('Y-m-d h:i:s',filemtime($dir)),
						'updated_at' => date('Y-m-d h:i:s',filemtime($dir)),
						'link' => $link
					);
				} else {
					$data['error'] = 'Error. Cannot create the folder, unknown error listing';
				}
			} else {
				$data['error'] = 'Error. Cannot create the folder because another folder exists with the same name';
			}
			
			echo json_encode($data);
			return;
		}
		
		if (Request::getApiParam('action') === 'paste') {
			$items = Request::getApiParam('items');
			$operation = Request::getApiParam('operation');
			$path = Request::getApiParam('path');
			
			$this->config->load('easyfile');
			$baseDir = $this->config->item('easyfile_upload_path');
			
			$data['error'] = '';
			
			$processed = array();
			$unprocessed = array();
			foreach ($items as $item) {
				$name = iconv('UTF-8', 'windows-1251', $item->name );
				
				if($operation === 'cut') {
					$oldDir = $baseDir . (strlen($item->path) ? '/' . $item->path : '') . '/' . $name;
					$newDir = $baseDir . (strlen($path) ? '/' . $path : '') . '/' . $name;
				
					if(!file_exists($newDir)) {
						if(@rename($oldDir, $newDir)) {
							$processed[] = $item->id;
						} else {
							$unprocessed[] = $item->id;
							$data['error'] .= 'Error. Cannot paste \'' . $name . '\', unknown error listing' . PHP_EOL;
						}
					} else {
						$unprocessed[] = $item->id;
						$data['error'] .= 'Error. Cannot paste \'' . $name . '\', because another item exists with the same name' . PHP_EOL;
					}
				} else if($operation === 'copy') {
					$oldDir = $baseDir . (strlen($item->path) ? '/' . $item->path : '') . '/' . $name;
					$newDir = $baseDir . (strlen($path) ? '/' . $path : '') . '/' . $name;
					
					if(!file_exists($newDir)) {
						if(Helper::xcopy($oldDir, $newDir)) {
							$processed[] = $item->id;
						} else {
							$unprocessed[] = $item->id;
							$data['error'] .= 'Error. Cannot paste \'' . $name . '\', unknown error listing' . PHP_EOL;
						}
					} else {
						$unprocessed[] = $item->id;
						$data['error'] .= 'Error. Cannot copy \'' . $name . '\', because another item exists with the same name' . PHP_EOL;
					}
				}
			}
			
			$data['processed'] = $processed;
			$data['unprocessed'] = $unprocessed;
			
			echo json_encode($data);
			return;
		}
		
		if (Request::getPost('action') === 'upload') {
			$files = Request::getFile();
			$path = Request::getPost('path');
			
			$this->config->load('easyfile');
			$dir = $this->config->item('easyfile_upload_path') . (strlen($path) ? '/' . $path : '');
			$link = base_url() . $dir;
			
			$data['error'] = '';
			
			if($files) {
				foreach($files as $f) {
					$name = iconv('UTF-8', 'windows-1251', $f['name'] );
					
					$src = $f['tmp_name'];
					$dst = $dir .'/'. $name;
				
					if (!file_exists($dst)) {
						if(@move_uploaded_file($src, $dst)) {
							$data['result'] = true;
						} else {
							$data['result'] = false;
							$data['error'] .= 'Error. Cannot upload the file \'' . $f['name'] . '\', unknown error listing' . PHP_EOL;
						}
					} else {
						$data['error'] .= 'Error. Cannot upload the file \'' . $f['name'] . '\' because another file exists with the same name' . PHP_EOL;
					}
				}
			} else {
				$data['result'] = false;
			}
			
			echo json_encode($data);
			return;
		}
	}
}
?>