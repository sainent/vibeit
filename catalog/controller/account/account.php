<?php
class ControllerAccountAccount extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		} 
		
		$this->showProfile();
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function showProfile($options = null) {
        $this->load->language('account/account');
        $this->document->setTitle($this->language->get('heading_title'));
        //customer
        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $data['firstname'] = $customer_info['firstname'];
        $data['lastname'] = $customer_info['lastname'];
        $data['email'] = $customer_info['email'];
        $data['telephone'] = $customer_info['telephone'];

        // addresses
        $this->load->language('account/address');
        $this->load->model('account/address');

        $data['addresses'] = array();

        $results = $this->model_account_address->getAddresses();

        foreach ($results as $result) {
            if ($result['address_format']) {
                $format = $result['address_format'];
            } else {
                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
            }

            $find = array(
                '{firstname}',
                '{lastname}',
                '{company}',
                '{address_1}',
                '{address_2}',
                '{city}',
                '{postcode}',
                '{zone}',
                '{zone_code}',
                '{country}',
                '{country_id}'
            );

            $replace = array(
                'firstname' => $result['firstname'],
                'lastname'  => $result['lastname'],
                'company'   => $result['company'],
                'address_1' => $result['address_1'],
                'address_2' => $result['address_2'],
                'city'      => $result['city'],
                'postcode'  => $result['postcode'],
                'zone'      => $result['zone'],
                'zone_code' => $result['zone_code'],
                'country'   => $result['country'],
                'country_id' => $result['country_id']
            );

            $data['addresses'][] = array(
                'address_id' => $result['address_id'],
                'address'    => str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format)))),
                'update'     => $this->url->link('account/address/edit', 'address_id=' . $result['address_id'], true),
                'edit'       => $this->url->link('account/account/address', 'address_id=' . $result['address_id'], true),
                'delete'     => $this->url->link('account/address/delete', 'address_id=' . $result['address_id'], true),
                'isEdit'     => isset($_GET['address_id']) && $_GET['address_id'] == $result['address_id'],
                'data'       => $replace
            );
        }

        $data['add_address'] = $this->url->link('account/account/addressadd', '', true);
        $data['is_address_add'] = isset($options['addressAdd']) && $options['addressAdd'];
        $data['new_address'] = $this->url->link('account/address/add', '', true);

        // wishlist
        $this->load->language('account/wishlist');
        $this->load->model('account/wishlist');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        if (isset($this->request->get['remove'])) {
            // Remove Wishlist
            $this->model_account_wishlist->deleteWishlist($this->request->get['remove']);

            $this->session->data['success'] = $this->language->get('text_remove');

            $this->response->redirect($this->url->link('account/wishlist'));
        }
        $data['products'] = array();

        $results = $this->model_account_wishlist->getWishlist();

        foreach ($results as $result) {
            $product_info = $this->model_catalog_product->getProduct($result['product_id']);

            if ($product_info) {
                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
                } else {
                    $image = false;
                }

                if ($product_info['quantity'] <= 0) {
                    $stock = $product_info['stock_status'];
                } elseif ($this->config->get('config_stock_display')) {
                    $stock = $product_info['quantity'];
                } else {
                    $stock = $this->language->get('text_instock');
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if ((float)$product_info['special']) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $special = false;
                }

                $data['products'][] = array(
                    'product_id' => $product_info['product_id'],
                    'thumb'      => $image,
                    'name'       => $product_info['name'],
                    'model'      => $product_info['model'],
                    'stock'      => $stock,
                    'price'      => $price,
                    'special'    => $special,
                    'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'remove'     => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
                );
            } else {
                $this->model_account_wishlist->deleteWishlist($result['product_id']);
            }
        }

        // orders
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $this->load->language('account/order');
        $data['orders'] = array();

        $this->load->model('account/order');

        $order_total = $this->model_account_order->getTotalOrders();

        $results = $this->model_account_order->getOrders(($page - 1) * 10, 10);

        foreach ($results as $result) {
            $product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
            $voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

            $data['orders'][] = array(
                'order_id'   => $result['order_id'],
                'name'       => $result['firstname'] . ' ' . $result['lastname'],
                'status'     => $result['status'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'products'   => ($product_total + $voucher_total),
                'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
                'view'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
            );
        }

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('account/order', 'page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $data['logout'] = $this->url->link('account/logout', '', true);
        
        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();

        $this->response->setOutput($this->load->view('account/account', $data));
    }

    public function address() {
        return $this->showProfile();
    }

    public function addressAdd() {
        return $this->showProfile(['addressAdd' => true]);
    }

}
