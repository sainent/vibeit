<?php
class ControllerExtensionModuleSlideshow extends Controller {
	public function index($setting) {
		static $module = 0;		

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');
		
		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		$data['module'] = $module++;

		 // Custom
        $this->load->model('setting/setting');
        $theme_custom = $this->model_setting_setting->getSetting('theme_custom');

        if(empty($theme_custom['theme_custom_banner']) || !$theme_custom['theme_custom_status']) {
            $theme_custom['theme_custom_banner'] = 'catalog/demo/banners/iPhone6.jpg';
        }
        $data['theme_custom'] = $theme_custom;

		return $this->load->view('extension/module/slideshow', $data);
	}
}