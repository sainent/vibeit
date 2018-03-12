<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = $this->language->get('text_logged') . ' ' .$this->customer->getFirstName();
		$data['action_login'] = $this->url->link('account/login', '', true);

		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/account', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/account', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['forgotten'] = $this->url->link('account/forgotten');
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->url->link('product/search', '', true);
		$data['menu'] = $this->load->controller('common/menu');
		$data['cart'] = $this->load->controller('common/cart');
		
		// Custom
        $this->load->model('setting/setting');
        $theme_custom = $this->model_setting_setting->getSetting('theme_custom');
        if (empty($theme_custom['theme_custom_primary_color']) || !$theme_custom['theme_custom_status']) {
            $theme_custom['theme_custom_primary_color'] = '#78c8a0';
        }
        if (empty($theme_custom['theme_custom_secondary_color']) || !$theme_custom['theme_custom_status']) {
            $theme_custom['theme_custom_secondary_color'] = '#00a0be';
        }

        if(empty($theme_custom['theme_custom_banner']) || !$theme_custom['theme_custom_status']) {
            $theme_custom['theme_custom_banner'] = 'catalog/demo/banners/iPhone6.jpg';
        }

        if(empty($theme_custom['theme_custom_logo']) || !$theme_custom['theme_custom_status']) {
            $theme_custom['theme_custom_logo'] = 'catalog/opencart-logo.png';
        }

        if(empty($theme_custom['theme_custom_favicon']) || !$theme_custom['theme_custom_status']) {
            $theme_custom['theme_custom_favicon'] = 'catalog/cart.png';
        }

        $theme_custom['theme_custom_css'] = '';
        if(!empty($theme_custom['theme_custom_status'])) {
            $theme_custom['theme_custom_css'] = '<link href="catalog/view/theme/vibeit/stylesheet/custom.css" type="text/css" rel="stylesheet" />';
        }

        $data['theme_custom'] = $theme_custom;

        unset($data['links']['http://vibeit.dev/image/catalog/cart.png']);

		return $this->load->view('common/header', $data);
	}
}
