<?php
namespace Opencart\Install\Controller\Upgrade;
class Upgrade2 extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('upgrade/upgrade');

		$json = [];

		if (isset($this->request->get['version'])) {
			$version = $this->request->get['version'];
		} else {
			$version = '';
		}

		if (isset($this->request->get['admin'])) {
			$admin = basename($this->request->get['admin']);
		} else {
			$admin = 'admin';
		}

		// Get directory constants
		$config = [];

		$lines = file(DIR_OPENCART . 'config.php');

		foreach ($lines as $number => $line) {
			if (preg_match('/define\(\'(.*)\',\s+(.*)\)/', $line, $match, PREG_OFFSET_CAPTURE)) {
				$config[$match[1][0]] = $match[2][0];
			}
		}

		$file = DIR_DOWNLOAD . 'opencart-' . $version . '.zip';

		if (is_file($file)) {
			$this->session->data['upgrade'] = [];

			// Unzip the files
			$zip = new \ZipArchive();

			if ($zip->open($file)) {
				$remove = 'opencart-' . $version . '/upload/';

				// Check if any of the files already exist.
				for ($i = 0; $i < $zip->numFiles; $i++) {
					$source = $zip->getNameIndex($i);

					if (substr($source, 0, strlen($remove)) == $remove) {
						// Only extract the contents of the upload folder
						$destination = str_replace('\\', '/', substr($source, strlen($remove)));

						if (substr($destination, 0, 8) != 'install/') {
							// Default copy location
							$path = DIR_OPENCART . $destination;

							// Fixes admin folder being under a different name
							if (substr($destination, 0, 6) == 'admin/') {
								$path = DIR_OPENCART . $admin . '/' . substr($destination, 6);
							}

							// We need to use a different path for vendor folders.
							if (substr($destination, 0, 15) == 'system/storage/') {
								if (isset($config['DIR_STORAGE'])) {
									$path = $config['DIR_STORAGE'] . substr($destination, 15);
								} else {
									$path = DIR_OPENCART . $destination;
								}
							}

							$this->session->data['upgrade'] = [
								'source'      => $source,
								'destination' => $path
							];
						}
					}
				}

				$zip->close();
			} else {
				$json['error'] = $this->language->get('error_unzip');
			}
		}

		if (!$json) {
			$json['text'] = sprintf($this->language->get('text_progress'), 2, 2, 8);

			$url = '';

			if (isset($this->request->get['version'])) {
				$url .= '&version=' . $this->request->get['version'];
			}

			if (isset($this->request->get['admin'])) {
				$url .= '&admin=' . $this->request->get['admin'];
			}

			$json['next'] = $this->url->link('upgrade/upgrade_2|extract', $url, true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function extract(): void {
		$this->load->language('tool/upgrade');

		$json = [];

		if (isset($this->request->get['version'])) {
			$version = $this->request->get['version'];
		} else {
			$version = '';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$file = DIR_DOWNLOAD . 'opencart-' . $version . '.zip';

		if (!is_file($file)) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!isset() {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {

			$total = count($files);

			$files = array_slice($this->session->data['upgrade'], ($page - 1) * 200, 200);

			// Check if any of the files already exist.
			foreach ($files as $file) {

				if (substr($file['destination'], 0, 8) == 'install/') {


					// Only extract the contents of the upload folder
					$destination = str_replace('\\', '/', substr($entry, strlen($remove)));

					// Only get the files from install directory

					// Default copy location
					$path = DIR_OPENCART . $destination;

					// Must not have a path before files and directories can be moved
					if (substr($path, -1) == '/') {
						if (!is_dir($path) && !mkdir($path, 0777)) {
							$json['error'] = sprintf($this->language->get('error_directory'), $path);
						}
					}

					// Check if the path is not directory and check there is no existing file
					if (substr($path, -1) != '/') {
						if (is_file($path)) {
							unlink($path);
						}

						if (!copy('zip://' . $file . '#' . $entry, $path)) {
							$json['error'] = sprintf($this->language->get('error_copy'), $entry, $path);
						}
					}
				}
			}
		}

		if (($page * 200) >= $total) {


			$json['text'] = sprintf($this->language->get('text_backup'), $table, ($page - 1) * 200, $record_total);

			$json['next'] = $this->url->link('tool/backup|backup', 'user_token=' . $this->session->data['user_token'] . '&version=' . urlencode($version) . '&table=' . $table . '&page=1', true);



		} else {
			$json['text'] = sprintf($this->language->get('text_backup'), $table, ($page - 1) * 200, $page * 200);


			unlink($file);

			$json['next'] = $this->url->link('tool/backup|backup', 'user_token=' . $this->session->data['user_token'] . '&filename=' . urlencode($filename) . '&table=' . $table . '&page=' . ($page + 1), true);
		}


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
