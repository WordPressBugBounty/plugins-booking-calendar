<?php 

/*############  Booking calendar Admin Menu Class ################*/

class wpdevart_bc_admin_menu{
	
	private $menu_name;

	function __construct($param){
		$this->menu_name = $param['menu_name'];
	}

    /*############  Create menu function ################*/
	
	public function create_menu(){
		if ( get_option( 'wpdevart_permissions' ) !== false ) {
			$permissions = get_option( 'wpdevart_permissions' );
			$permissions = json_decode($permissions,true);
		}
		$wpdevart_pages = array('calendar_page','reservation_page','form_page','extra_page','theme_page','global_settings_page');
		foreach($wpdevart_pages as $wpdevart_page) {
			if(!isset($permissions[$wpdevart_page])){
				$permissions[$wpdevart_page] = "publish_pages";
			}
		}
		global $wpdb;
		global $submenu;
		$count = 0;
		$version = get_option("wpdevart_booking_version");
		if ($version) {
			$count = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpdevart_reservations WHERE is_new=1');
		}
        $count_res = $count > 0 ? ' <span class="update-plugins count-' . $count . '"><span class="resrvation-count">' . $count . '</span></span>' : '';	
		
		$main_page = add_menu_page( $this->menu_name, $this->menu_name . $count_res, $permissions['calendar_page'], 'wpdevart-calendars', array($this, 'admin_functions'),WPDEVART_URL.'css/images/menu_icon.png');
		$page_bookings =	add_submenu_page('wpdevart-calendars',  "Calendars",  "Calendars", $permissions['calendar_page'], 'wpdevart-calendars', array($this, 'admin_functions'));
		$page_reservation = add_submenu_page( 'wpdevart-calendars', 'Reservations', 'Reservations' . $count_res, $permissions['reservation_page'], 'wpdevart-reservations', array($this, 'admin_functions'));
		$page_forms = add_submenu_page( 'wpdevart-calendars', 'Forms', 'Forms', $permissions['form_page'], 'wpdevart-forms', array($this, 'admin_functions'));
		$page_extra = add_submenu_page( 'wpdevart-calendars', 'Extras', 'Extras', $permissions['extra_page'], 'wpdevart-extras', array($this, 'admin_functions'));
		$page_themes = add_submenu_page( 'wpdevart-calendars', 'Themes', 'Themes', $permissions['theme_page'], 'wpdevart-themes', array($this, 'admin_functions'));
		$page_global = add_submenu_page( 'wpdevart-calendars', 'Global Settings', 'Global Settings', $permissions['global_settings_page'], 'wpdevart-global-settings', array($this, 'admin_functions'));
		$page_manage = add_submenu_page( 'wpdevart-calendars', 'User Permissions', 'User Permissions', 'manage_options', 'wpdevart-user-permissions', array($this, 'admin_functions'));
		$page_uninstall = add_submenu_page( 'wpdevart-calendars', 'Uninstall'  , 'Uninstall', 'manage_options', 'wpdevart-booking-uninstall', array($this, 'uninstall_booking'));
		$page_featured = add_submenu_page( 'wpdevart-calendars', 'Featured plugins', 'Featured plugins', 'manage_options', 'wpdevart-booking-featured-plugins', array($this, 'featured_plugins'));
		$featured_theme_page = add_submenu_page( 'wpdevart-calendars', 'Featured themes', 'Featured themes', 'manage_options', 'wpdevart-booking-featured-themes', array($this, 'featured_themes'));
		$page_hire_expert = add_submenu_page( 'wpdevart-calendars', 'Hire an Expert', '<span style="color:#00ff66" >Hire an Expert</span>', 'manage_options', 'wpdevart-booking-hire-expert', array($this, 'hire_expert'));
		
		add_action('admin_print_styles-' .$main_page, array($this,'calendar_requeried_scripts'));
		add_action('admin_print_styles-' .$page_bookings, array($this,'calendar_requeried_scripts'));	
		add_action('admin_print_styles-' .$page_reservation, array($this,'menu_requeried_scripts'));	
		add_action('admin_print_styles-' .$page_themes, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_global, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_manage, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_uninstall, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_forms, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_extra, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_featured, array($this,'featured_plugins_js_css'));
		add_action('admin_print_styles-' .$featured_theme_page, array($this,'featured_themes_js_css'));		
		add_action('admin_print_styles-' .$page_hire_expert, array($this,'hire_expert_scripts'));		
		if(isset($submenu['wpdevart-calendars'])){			
			add_submenu_page( 'wpdevart-calendars', "Support or Any Ideas?", "<span style='color:#00ff66' >Support or Any Ideas?</span>", 'manage_options',"wpdevart_booking_calendar_any_idea",array($this, 'any_ideas'),155);
			$count_pages = count($submenu['wpdevart-calendars'])-1;
			$submenu['wpdevart-calendars'][$count_pages][2]=wpdevart_booking_support_url;
		}
	}
	public function any_ideas(){
		
	}

    /*############  Menu featured scripts ################*/
	public function hire_expert_scripts(){
		wp_enqueue_style(WPDEVART_PLUGIN_PREFIX.'-hire-expert-admin-style', WPDEVART_URL.'css/hire_expert.css',array(),WPDEVART_VERSION);
	}
	public function featured_plugins_js_css(){
		wp_enqueue_style(WPDEVART_PLUGIN_PREFIX.'-featured-plugins-admin-style', WPDEVART_URL.'css/featured_plugins_css.css',array(),WPDEVART_VERSION);
	}
	public function featured_themes_js_css(){
		wp_enqueue_style(WPDEVART_PLUGIN_PREFIX.'-featured-themes-admin-style', WPDEVART_URL.'css/featured_themes_css.css',array(),WPDEVART_VERSION);
	}

	/*############  Menu Requeried Scripts function ################*/
	public function menu_requeried_scripts(){
		wp_enqueue_script('wp-color-picker');		
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_media(); 
		wp_enqueue_style(WPDEVART_PLUGIN_PREFIX.'-admin-style', WPDEVART_URL.'css/admin_style.css',array(),WPDEVART_VERSION);
		wp_enqueue_style(WPDEVART_PLUGIN_PREFIX.'calendar-style', WPDEVART_URL.'css/booking.css',array(),WPDEVART_VERSION);
		wp_register_script( WPDEVART_PLUGIN_PREFIX.'-admin-script', WPDEVART_URL.'js/admin_script.js', array("jquery"),WPDEVART_VERSION );
		wp_localize_script( WPDEVART_PLUGIN_PREFIX.'-admin-script', WPDEVART_PLUGIN_PREFIX."_admin", array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'ajaxNonce'   => wp_create_nonce( WPDEVART_PLUGIN_PREFIX . '_ajax_nonce' ),
			'required' => __("is required.",'booking-calendar'),
			'emailValid' => __("Enter the valid email address.",'booking-calendar'),
			'hour' => __("Hour",'booking-calendar'),
			'price' => __("Price",'booking-calendar'),
			'marked_price' => __("Marked Price",'booking-calendar'),
			'available' => __("Available",'booking-calendar'),
			'booked' => __("Booked",'booking-calendar'),
			'unavailable' => __("Unavailable",'booking-calendar'),
			'number_availability' => __("Number Availabile",'booking-calendar'),
			'h_info' => __("Hour Information",'booking-calendar'),
			'date' => __("Date",'booking-calendar')
		) );
		wp_enqueue_script( WPDEVART_PLUGIN_PREFIX.'-admin-script' );
		wp_register_script( 'wpdevart-booking-script', WPDEVART_URL.'js/booking.js', array("jquery"),WPDEVART_VERSION);
		wp_localize_script( 'wpdevart-booking-script', WPDEVART_PLUGIN_PREFIX, array(
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'ajaxNonce'       => wp_create_nonce( 'wpdevart_ajax_nonce' ),
			'required' => __("is required.",'booking-calendar'),
			'emailValid' => __("Enter the valid email address.",'booking-calendar'),
			'date' => __("Date",'booking-calendar'),
			'hour' => __("Hour",'booking-calendar')
		) );
		wp_enqueue_script( 'wpdevart-booking-script' );
		if (function_exists('add_thickbox')) add_thickbox();
	}
	public function calendar_requeried_scripts(){
		wp_enqueue_style('wpdevart-font-awesome', WPDEVART_URL . 'css/font-awesome/font-awesome.css',array(),WPDEVART_VERSION);
		wp_enqueue_style(WPDEVART_PLUGIN_PREFIX.'-admin-style', WPDEVART_URL.'css/admin_style.css',array(),WPDEVART_VERSION);
		wp_enqueue_style(WPDEVART_PLUGIN_PREFIX.'calendar-style', WPDEVART_URL.'css/booking.css',array(),WPDEVART_VERSION);
		wp_register_script( WPDEVART_PLUGIN_PREFIX.'-admin-calendar', WPDEVART_URL.'js/admin_calendar.js', array("jquery"),WPDEVART_VERSION );
		wp_localize_script( WPDEVART_PLUGIN_PREFIX.'-admin-calendar', WPDEVART_PLUGIN_PREFIX, array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'ajaxNonce'   => wp_create_nonce( WPDEVART_PLUGIN_PREFIX . '_ajax_nonce' ),
			'hour' => __("Hour",'booking-calendar'),
			'price' => __("Price",'booking-calendar'),
			'marked_price' => __("Marked Price",'booking-calendar'),
			'available' => __("Available",'booking-calendar'),
			'booked' => __("Booked",'booking-calendar'),
			'unavailable' => __("Unavailable",'booking-calendar'),
			'number_availability' => __("Number Availabile",'booking-calendar'),
			'h_info' => __("Hour Information",'booking-calendar'),
		) );
		wp_enqueue_script( WPDEVART_PLUGIN_PREFIX.'-admin-calendar' );
	}	
			
	public function admin_functions(){
		$name = WPDEVART_PRO == 'pro' ? 'Extended' : 'Premium';
		$header_args = array('title' => 'WpDevArt Booking Calendar ' . $name, 'desc' => 'Powerful and Customizable Booking System'); 
		wpdevart_bc_Library::wpdevart_header($header_args);
		$page = wpdevart_bc_Library::get_value('page', 'wpdevart-calendars');
		$page = ucfirst(str_replace(array('wpdevart-','-'), '', $page));
		$page = $page == "Globalsettings" ? "GlobalSettings" : ($page == "Userpermissions" ? "UserPermissions" : $page);
		if(file_exists(WPDEVART_PLUGIN_DIR . 'admin/controllers/' . $page . '.php')) {
			require_once(WPDEVART_PLUGIN_DIR . 'admin/controllers/' . $page . '.php');
			$controller_name = 'wpdevart_bc_Controller' . $page;
			$controller = new $controller_name;
			$controller->perform();
		}	
	}


	public function hire_expert() {
        $plugins_array = array(
            'custom_site_dev' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/1.png',
                'title' => 'Custom WordPress Development',
                'description' => 'Hire a WordPress expert and make any custom development for your WordPress website.',
            ),
            'custom_plug_dev' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/2.png',
                'title' => 'WordPress Plugin Development',
                'description' => 'Our developers can create any WordPress plugin from zero. Also, they can customize any plugin and add any functionality.',
            ),
            'custom_theme_dev' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/3.png',
                'title' => 'WordPress Theme Development',
                'description' => 'If you need an unique theme or any customizations for a ready theme, then our developers are ready.',
            ),
            'custom_theme_inst' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/4.png',
                'title' => 'WordPress Theme Installation and Customization',
                'description' => 'If you need a theme installation and configuration, then just let us know, our experts configure it.',
            ),
            'gen_wp_speed' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/5.png',
                'title' => 'General WordPress Support',
                'description' => 'Our developers can provide general support. If you have any problem with your website, then our experts are ready to help.',
            ),
            'speed_op' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/6.png',
                'title' => 'WordPress Speed Optimization',
                'description' => 'Hire an expert from WpDevArt and let him take care of your website speed optimization.',
            ),
            'mig_serv' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/7.png',
                'title' => 'WordPress Migration Services',
                'description' => 'Our developers can migrate websites from any platform to WordPress.',
            ),
            'page_seo' => array(
                'image_url' => WPDEVART_URL.'css/images/hire_expert/8.png',
                'title' => 'WordPress SEO',
                'description' => 'SEO is an important part of any website. Hire an expert and he will organize the SEO of your website.',
            ),
        );
        $content = '';
        $content .= '<h1 class="wpda_hire_exp_h1"> Hire an Expert from WpDevArt </h1>';
        $content .= '<div class="hire_expert_main">';
        foreach ($plugins_array as $key => $plugin) {
            $content .= '<div class="wpdevart_hire_main"><a target="_blank" class="wpda_hire_buklet" href="https://wpdevart.com/hire-a-wordpress-developer-online-submit-form/">';
            $content .= '<div class="wpdevart_hire_image"><img src="' . $plugin["image_url"] . '"></div>';
            $content .= '<div class="wpdevart_hire_information">';
            $content .= '<div class="wpdevart_hire_title">' . $plugin["title"] . '</div>';
            $content .= '<p class="wpdevart_hire_description">' . $plugin["description"] . '</p>';
            $content .= '</div></a></div>';
        }
        $content .= '<div><a target="_blank" class="wpda_hire_button" href="https://wpdevart.com/hire-a-wordpress-developer-online-submit-form/">Hire an Expert</a></div>';
        $content .= '</div>';
        echo $content;
    }

    public function featured_plugins() {
        $plugins_array=array(
			'pricing_table'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/pricing_table.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-pricing-table-plugin/',
						'title'			=>	'Pricing Table',
						'description'	=>	'WordPress Pricing Table plugin is a unique and awesome tool for creating responsive and nice pricing tables on your WordPress website.'
						),
			'coming_soon'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/coming_soon.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-coming-soon-plugin/',
						'title'			=>	'Coming soon and Maintenance mode',
						'description'	=>	'Coming soon and Maintenance mode plugin is an awesome tool to show your visitors that you are working on your website to make it better.'
						),
			'countdown_extendet'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/countdown_extendet.png',
						'site_url'		=>	'https://wordpress.org/plugins/countdown-wpdevart-extended/',
						'title'			=>	'Countdown Timer � Extended version, Popup Countdown',
						'description'	=>	'The most functional and beautiful Countdown Timer plugin for WordPress.'
						),
			'Contact forms'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/contact_forms.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-contact-form-plugin/',
						'title'			=>	'Contact Form',
						'description'	=>	'Contact Form plugin is an nice and handy tool for creating different types of contact forms on your WordPress websites.'
						),	
			 'gallery_album'=>array(
						'image_url'	=>	WPDEVART_URL.'css/images/featured_plugins/gallery.png',
						'site_url'	=>	'https://wpdevart.com/wordpress-gallery-plugin',
						'title'	=>	'WordPress Gallery plugin',
						'description'	=>	'Gallery plugin is an useful tool that will help you to create Galleries and Albums. Try our nice Gallery views and awesome animations.'
						),			
			'youtube'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/youtube.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-youtube-embed-plugin',
						'title'			=>	'WordPress YouTube Embed',
						'description'	=>	'YouTube Embed plugin is an convenient tool for adding video to your website. Use YouTube Embed plugin to add YouTube videos in posts/pages, widgets.'
						),
			'lightbox'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/lightbox.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-lightbox-plugin',
						'title'			=>	'WordPress Lightbox plugin',
						'description'	=>	'WordPress lightbox plugin is an high customizable and responsive product for displaying images and videos in popup.'
						),
			'countdown'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/countdown.jpg',
						'site_url'		=>	'https://wpdevart.com/wordpress-countdown-plugin/',
						'title'			=>	'WordPress Countdown plugin',
						'description'	=>	'WordPress Countdown plugin is an nice tool to create and insert countdown timers into your posts/pages and widgets.'
						),
            'facebook-comments'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/facebook-comments-icon.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-facebook-comments-plugin/',
						'title'			=>	'WordPress Facebook comments',
						'description'	=>	'Our Facebook comments plugin will help you to display Facebook Comments on your website. You can use Facebook Comments on your pages/posts.'
						),						
			'facebook'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/facebook.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-facebook-like-box-plugin',
						'title'			=>	'Facebook Like Box',
						'description'	=>	'Our Facebook like box plugin will help you to display Facebook like box on your wesite, just add Facebook Like box widget to your sidebar and use it..'
						),
			'poll'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/poll.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-polls-plugin',
						'title'			=>	'Poll',
						'description'	=>	'WordPress Polls plugin is an wonderful tool for creating polls and survey forms for your visitors. You can use our polls on widgets, posts and pages.'
						),		
			'vertical_menu'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/vertical-menu.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-vertical-menu-plugin/',
						'title'			=>	'WordPress Vertical Menu',
						'description'	=>	'WordPress Vertical Menu is a handy tool for adding nice vertical menus. You can add icons for your website vertical menus using our plugin.'
						),						
			'chart'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/chart-featured.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-organization-chart-plugin/',
						'title'			=>	'WordPress Organization Chart',
						'description'	=>	'WordPress organization chart plugin is a great tool for adding organizational charts to your WordPress websites.'
						),
			'duplicate_page'=>array(
						'image_url'		=>	WPDEVART_URL.'css/images/featured_plugins/featured-duplicate.png',
						'site_url'		=>	'https://wpdevart.com/wordpress-duplicate-page-plugin-easily-clone-posts-and-pages/',
						'title'			=>	'WordPress Duplicate page',
						'description'	=>	'Duplicate Page or Post is a great tool that allows duplicating pages and posts. Now you can do it with one click.'
						),						
			
		);
        $html = '';
        $html .= '<h1 class="wpda_featured_plugins_title">Featured Plugins</h1>';
        foreach ($plugins_array as $plugin) {
            $html .= '<div class="featured_plugin_main">';
            $html .= '<div class="featured_plugin_image"><a target="_blank" href="' . $plugin['site_url'] . '"><img src="' . $plugin['image_url'] . '"></a></div>';
            $html .= '<div class="featured_plugin_information">';
            $html .= '<div class="featured_plugin_title">';
            $html .= '<h4><a target="_blank" href="' . $plugin['site_url'] . '">' . $plugin['title'] . '</a></h4>';
            $html .= '</div>';
            $html .= '<p class="featured_plugin_description">' . $plugin['description'] . '</p>';
            $html .= '<a target="_blank" href="' . $plugin['site_url'] . '" class="blue_button">Check The Plugin</a>';
            $html .= '</div>';
            $html .= '<div style="clear:both"></div>';
            $html .= '</div>';
        }
        echo $html;
    }

    public function featured_themes() {
        $themes_array = array(
            'tistore' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/tistore.jpg',
                'site_url' => 'https://wpdevart.com/tistore-best-ecommerce-theme-for-wordpress/',
                'title' => 'TiStore',
                'description' => 'TiStore is one of the best eCommerce WordPress themes that is fully integrated with WooCommerce.',
            ),
            'megastore' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/megastore.jpg',
                'site_url' => 'https://wpdevart.com/megastore-best-woocommerce-theme-for-wordpress/',
                'title' => 'MegaStore',
                'description' => 'MegaStore is one of the best WooCommerce themes available for WordPress.',
            ),
            'jevstore' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/jevstore.jpg',
                'site_url' => 'https://wpdevart.com/jewstore-best-wordpress-jewelry-store-theme/',
                'title' => 'JewStore',
                'description' => 'JewStore is a WordPress WooCommerce theme designed for jewelry stores and blogs.',
            ),
            'cakeshop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/cakeshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-cake-shop-theme/',
                'title' => 'Cake Shop',
                'description' => 'WordPress Cake Shop is a multi-purpose WooCommerce-ready theme.',
            ),
            'flowershop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/flowershop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-flower-shop-theme/',
                'title' => 'Flower Shop',
                'description' => 'WordPress Flower Shop is a responsive and WooCommerce-ready theme developed by our team.',
            ),
            'coffeeshop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/coffeeshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-coffee-shop-cafe-theme/',
                'title' => 'Coffee Shop',
                'description' => 'It is a responsive and user-friendly theme designed specifically for coffee shop or cafe websites.',
            ),
            'weddingplanner' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/weddingplanner.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-wedding-planner-theme/',
                'title' => 'Wedding Planner',
                'description' => 'Wedding Planner is a responsive WordPress theme that is fully integrated with WooCommerce.',
            ),
            'Amberd' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/Amberd.jpg',
                'site_url' => 'https://wpdevart.com/amberd-wordpress-online-store-theme/',
                'title' => 'AmBerd',
                'description' => 'AmBerd has all the necessary features and functionality to create a beautiful WordPress website.',
            ),
            'bookshop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/bookshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-book-shop-theme/',
                'title' => 'Book Shop',
                'description' => 'The Book Shop WordPress theme is a fresh and well-designed theme for creating bookstores or book blogs.',
            ),
            'ecommercemodernstore' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/ecommercemodernstore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-ecommerce-modern-store-theme/',
                'title' => 'Ecommerce Modern Store',
                'description' => 'WordPress Ecommerce Modern Store theme is one of the best solutions if you want to create an online store.',
            ),
            'electrostore' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/electrostore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-electronics-store-electro-theme/',
                'title' => 'ElectroStore',
                'description' => 'This is a responsive and WooCommerce-ready electronic store theme.',
            ),
            'jewelryshop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/jewelryshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-jewelry-shop-theme/',
                'title' => 'Jewelry Shop',
                'description' => 'WordPress Jewelry Shop theme is designed specifically for jewelry websites, but of course, you can use this theme for other types of websites as well.',
            ),
            'fashionshop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/fashionshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-fashion-shop-theme/',
                'title' => 'Fashion Shop',
                'description' => 'The Fashion Shop is one of the best responsive WordPress WooCommerce themes for creating a fashion store website.',
            ),
            'barbershop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/barbershop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-barbershop-theme/',
                'title' => 'Barbershop',
                'description' => 'WordPress Barbershop is another responsive and functional theme developed by our team.',
            ),
            'furniturestore' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/furniturestore.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-furniture-store-theme/',
                'title' => 'Furniture Store',
                'description' => 'This is a great option to quickly create an online store using our theme and the WooCommerce plugin. Our theme is fully integrated with WooCommerce.',
            ),
            'clothing' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/clothing.jpg',
                'site_url' => 'https://wpdevart.com/tistore-best-ecommerce-theme-for-wordpress/',
                'title' => 'Clothing',
                'description' => 'The Clothing WordPress theme is one of the best responsive eCommerce themes available for WordPress.',
            ),
            'weddingphotography' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/weddingphotography.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-wedding-photography-theme/',
                'title' => 'Wedding Photography',
                'description' => 'WordPress Wedding Photography theme is one of the best themes specially designed for wedding photographers or photography companies.',
            ),
            'petshop' => array(
                'image_url' => WPDEVART_URL.'css/images/featured_themes/petshop.jpg',
                'site_url' => 'https://wpdevart.com/wordpress-pet-shop-theme/',
                'title' => 'Pet Shop',
                'description' => 'Pet Shop is a powerful and well-designed WooCommerce WordPress theme.',
            ),
        );
        $html = '';
        $html .= '<div class="wpdevart_main"><h1 class="wpda_featured_themes_title">Featured Themes</h1>';

        $html .= '<div class="div-container">';
        foreach ($themes_array as $theme) {
            $html .= '<div class="theme" data-slug="tistore"><div class="theme-img">';                
            $html .= ' <img src="'.$theme['image_url'].'" alt="' . $theme['title'] . '">';
            $html .= '</div>';
            $html .= '<div class="theme-description">' . $theme['description'] . '</div>';
            $html .= '<div class="theme-name-container">'; 
            $html .= '<h2 class="theme-name">' . $theme['title'] . '</h2>';
            $html .= '<div class="theme-actions">';
            $html .= '<a target="_blank" aria-label="Check theme" class="button button-primary load-customize" href="' . $theme['site_url'] . '">Check Theme</a>';
            $html .= '</div></div></div>';
            
            
        }
        $html .= '</div></div>';
        echo $html;
    }
	
	public function uninstall_booking() {
		global $wpdb;
		if(isset( $_POST['uninstall_booking_data'] )   && wp_verify_nonce( $_POST['uninstall_booking_data'], 'uninstall_booking')){
			$wpdb->query("DROP TABLE `" . $wpdb->prefix . "wpdevart_calendars`");
			$wpdb->query("DROP TABLE `" . $wpdb->prefix . "wpdevart_dates`");
			$wpdb->query("DROP TABLE `" . $wpdb->prefix . "wpdevart_forms`");
			$wpdb->query("DROP TABLE `" . $wpdb->prefix . "wpdevart_extras`");
			$wpdb->query("DROP TABLE `" . $wpdb->prefix . "wpdevart_themes`");
			$wpdb->query("DROP TABLE `" . $wpdb->prefix . "wpdevart_reservations`");
			$wpdb->query("DROP TABLE `" . $wpdb->prefix . "wpdevart_payments`");
			delete_option("wpdevart_booking_version");
			?>
			<div id="message" class="updated fade">
			  <p><?php _e('The following Database Tables successfully deleted:','booking-calendar'); ?></p>
			  <p><?php echo $wpdb->prefix; ?>wpdevart_calendars,</p>
			  <p><?php echo $wpdb->prefix; ?>wpdevart_dates,</p>
			  <p><?php echo $wpdb->prefix; ?>wpdevart_forms,</p>
			  <p><?php echo $wpdb->prefix; ?>wpdevart_extras,</p>
			  <p><?php echo $wpdb->prefix; ?>wpdevart_themes,</p>
			  <p><?php echo $wpdb->prefix; ?>wpdevart_reservations,</p>
			  <p><?php echo $wpdb->prefix; ?>wpdevart_payments,</p>
			</div>
			<div class="wrap">
			  <h1><?php _e('Uninstall Booking Calendar','booking-calendar'); ?></h1>
			  <p><strong><a href="<?php echo wp_nonce_url('plugins.php?action=deactivate&amp;plugin=booking-calendar-pro/booking_calendar.php', 'deactivate-plugin_booking-calendar-pro/booking_calendar.php'); ?>"><?php _e('Click Here','booking-calendar'); ?></a><?php _e(' To Finish the Uninstallation','booking-calendar'); ?></strong></p>
			</div>
		  <?php
			return;
		}
		?>
		<div id="wpdevart_uninstal_container" class="wpdevart-list-container">
			<form method="post" action="admin.php?page=wpdevart-booking-uninstall" style="width:99%;">
			 <?php wp_nonce_field('uninstall_booking','uninstall_booking_data'); ?>
			    <div class="div-for-clear">
					<span class="admin_logo"></span>
					<h1><?php _e('Uninstall Booking calendar','booking-calendar'); ?></h1>
				</div>
				<p><?php _e('Deactivating Booking calendar plugin does not remove any data that may have been created. To completely remove this plugin, you can uninstall it here.','booking-calendar'); ?></p>
				<p style="color: #7052fb;"><strong><?php _e('WARNING:','booking-calendar'); ?></strong><?php _e('Once uninstalled, this can\'t be undone. You should use a Database Backup plugin of WordPress to back up all the data first.','booking-calendar'); ?></p>
				<p style="color: #7052fb"><strong><?php _e('The following Database Tables will be deleted:','booking-calendar'); ?></strong></p>
				<table class="widefat">
				  <thead>
					<tr>
					  <th>Database Tables</th>
					</tr>
				  </thead>
				  <tr>
					<td valign="top">
					  <ol>
						  <li><?php echo $wpdb->prefix; ?>wpdevart_calendars</li>
						  <li><?php echo $wpdb->prefix; ?>wpdevart_dates</li>
						  <li><?php echo $wpdb->prefix; ?>wpdevart_forms</li>
						  <li><?php echo $wpdb->prefix; ?>wpdevart_extras</li>
						  <li><?php echo $wpdb->prefix; ?>wpdevart_themes</li>
						  <li><?php echo $wpdb->prefix; ?>wpdevart_reservations</li>
					  </ol>
					</td>
				  </tr>
				</table>
				<p style="text-align: center;">
				  <?php _e('Do you really want to uninstall Booking Calendar?','booking-calendar'); ?>
				</p>
				<p style="text-align: center;">
				  <input type="checkbox" id="check_yes" value="yes" />&nbsp;<label for="check_yes"><?php _e('Yes','booking-calendar'); ?></label>
				</p>
				<p style="text-align: center;">
				  <input type="submit" value="UNINSTALL" class="button-primary" onclick="if (check_yes.checked) {	if (!confirm('You are About to Uninstall booking calendar.\nThis Action Is Not Reversible.')) {return false; } } else { return false; }" />
				</p>
			</form>
		</div>
  <?php    
	}
	
	
}