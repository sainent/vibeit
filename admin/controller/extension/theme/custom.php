<?php
class ControllerExtensionThemeCustom extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/theme/custom');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('theme_custom', $this->request->post, $this->request->get['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

            $this->saveCss();

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/theme/custom', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true)
		);

		$data['action'] = $this->url->link('extension/theme/custom', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->request->get['store_id'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=theme', true);

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$setting_info = $this->model_setting_setting->getSetting('theme_custom', $this->request->get['store_id']);
		}

        if (isset($this->request->post['theme_custom_status'])) {
            $data['theme_custom_status'] = $this->request->post['theme_custom_status'];
        } elseif (!empty($setting_info['theme_custom_status'])) {
            $data['theme_custom_status'] = $setting_info['theme_custom_status'];
        } else {
            $data['theme_custom_status'] = '';
        }

		if (isset($this->request->post['theme_custom_primary_color'])) {
            $data['theme_custom_primary_color'] = $this->request->post['theme_custom_primary_color'];
        } elseif (!empty($setting_info['theme_custom_primary_color'])) {
            $data['theme_custom_primary_color'] = $setting_info['theme_custom_primary_color'];
        } else {
            $data['theme_custom_primary_color'] = '#78c8a0';
        }

        if (isset($this->request->post['theme_custom_secondary_color'])) {
            $data['theme_custom_secondary_color'] = $this->request->post['theme_custom_secondary_color'];
        } elseif (!empty($setting_info['theme_custom_secondary_color'])) {
            $data['theme_custom_secondary_color'] = $setting_info['theme_custom_secondary_color'];
        } else {
            $data['theme_custom_secondary_color'] = '#00a0be';
        }

        $this->load->model('tool/image');
        if (isset($this->request->post['theme_custom_banner']) && is_file(DIR_IMAGE . $this->request->post['theme_custom_banner'])) {
            $data['banner_thumb'] = $this->model_tool_image->resize($this->request->post['theme_custom_banner'], 300, 200);
        } elseif (!empty($setting_info['theme_custom_banner']) && is_file(DIR_IMAGE . $setting_info['theme_custom_banner'])) {
            $data['banner_thumb'] = $this->model_tool_image->resize($setting_info['theme_custom_banner'], 300, 200);
            $data['theme_custom_banner'] = $setting_info['theme_custom_banner'];
        } else {
            $data['banner_thumb'] = $this->model_tool_image->resize('catalog///demo/banners/iPhone6.jpg', 300, 200);
        }

        if (isset($this->request->post['theme_custom_logo']) && is_file(DIR_IMAGE . $this->request->post['theme_custom_logo'])) {
            $data['logo_thumb'] = $this->model_tool_image->resize($this->request->post['theme_custom_logo'], 100, 100);
        } elseif (!empty($setting_info['theme_custom_logo']) && is_file(DIR_IMAGE . $setting_info['theme_custom_logo'])) {
            $data['logo_thumb'] = $this->model_tool_image->resize($setting_info['theme_custom_logo'], 100, 100);
            $data['theme_custom_logo'] = $setting_info['theme_custom_logo'];
        } else {
            $data['logo_thumb'] = $this->model_tool_image->resize('catalog/opencart-logo.png', 100, 100);
        }

        if (isset($this->request->post['theme_custom_favicon']) && is_file(DIR_IMAGE . $this->request->post['theme_custom_favicon'])) {
            $data['favicon_thumb'] = $this->model_tool_image->resize($this->request->post['theme_custom_favicon'], 32, 32);
        } elseif (!empty($setting_info['theme_custom_favicon']) && is_file(DIR_IMAGE . $setting_info['theme_custom_favicon'])) {
            $data['favicon_thumb'] = $this->model_tool_image->resize($setting_info['theme_custom_favicon'], 32, 32);
            $data['theme_custom_favicon'] = $setting_info['theme_custom_favicon'];
        } else {
            $data['favicon_thumb'] = $this->model_tool_image->resize('catalog/cart.png', 32, 32);
        }
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/theme/custom', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/theme/custom')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function saveCss() {
        /*
        $post = $this->request->post;

        $css = '';
        $css .= 'a {
	color: #23a1d1;
}';
        $css .= '.dropdown-menu li > a:hover {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
	background-image: linear-gradient(to bottom, '.$this->request->post['theme_custom_primary_color'].', '.$this->request->post['theme_custom_primary_color'].') !important;
}';
        $css .= '#top #form-language .language-select:hover {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
	background-image: linear-gradient(to bottom, '.$this->request->post['theme_custom_primary_color'].', '.$this->request->post['theme_custom_primary_color'].') !important;
}
';
        $css .= '#menu {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
	background-image: linear-gradient(to bottom, '.$this->request->post['theme_custom_primary_color'].', '.$this->request->post['theme_custom_primary_color'].') !important;
	border-color: '.$this->request->post['theme_custom_primary_color'].' '.$this->request->post['theme_custom_primary_color'].' '.$this->request->post['theme_custom_primary_color'].' !important;
	min-height: 40px;
}';
        $css .= '#menu .see-all:hover, #menu .see-all:focus {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
	background-image: linear-gradient(to bottom, '.$this->request->post['theme_custom_primary_color'].', '.$this->request->post['theme_custom_primary_color'].' !important);
}';
        $css .= '#menu .btn-navbar {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
	background-image: linear-gradient(to bottom, '.$this->request->post['theme_custom_primary_color'].', '.$this->request->post['theme_custom_primary_color'].') !important;
	border-color: '.$this->request->post['theme_custom_primary_color'].' '.$this->request->post['theme_custom_primary_color'].' '.$this->request->post['theme_custom_primary_color'].' !important;
}
#menu .btn-navbar:hover, #menu .btn-navbar:focus, #menu .btn-navbar:active, #menu .btn-navbar.disabled, #menu .btn-navbar[disabled] {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
}';
        $css .= '.btn-primary {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
	background-image: linear-gradient(to bottom, '.$this->request->post['theme_custom_primary_color'].', '.$this->request->post['theme_custom_primary_color'].') !important;
	border-color: '.$this->request->post['theme_custom_primary_color'].' '.$this->request->post['theme_custom_primary_color'].' '.$this->request->post['theme_custom_primary_color'].' !important;
}
.btn-primary:hover, .btn-primary:active, .btn-primary.active, .btn-primary.disabled, .btn-primary[disabled] {
	background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
}';
        $css .= '.product-layout .button-group .btn-buy {
    border: 3px solid '.$this->request->post['theme_custom_secondary_color'].' !important;
  }
  .product-layout .button-group .btn-add {
    border: 3px solid '.$this->request->post['theme_custom_primary_color'].' !important;
  }
  .product-layout .button-group .btn-buy:hover {
    background-color: '.$this->request->post['theme_custom_secondary_color'].' !important;
  }
  .product-layout .button-group .btn-add:hover {
    background-color: '.$this->request->post['theme_custom_primary_color'].' !important;
  }';

        $folder = realpath(__DIR__.'/../../../../catalog/view/theme/vibeit/stylesheet/');
        $handle = fopen($folder.'/custom.css', "w") or die("Unable to open file!");
        fwrite($handle, $css);
        fclose($handle);
        */
    }
}