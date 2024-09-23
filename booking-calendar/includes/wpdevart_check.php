<?php
class wpdevartCeck {
	
	private $theme;
	private $data;
	private $day_count;
	private $hour_count;
	private $hour = false;
	private $multiple = false;
	private $max_available = false;
	
	public function __construct($theme, $data) {
		$this->theme = $theme;
		$this->data = $data;
	}
	
	public function check_price() {
		$data = $this->data;
		$price = 0;
		$main_price = $this->get_main_price();
		
		if(!empty($data['count_item'])) {
			if ($data['count_item'] > $this->max_available){
				return false;
			}
			
			$main_price = $main_price * $data['count_item'];
		}
		
		if(!empty($data['extras'])) {
			$extra_price = $this->get_extra_data($data['extras'], $main_price);
			if ($extra_price != $data['extras_price']){
				return false;
			}
			
			$price = $main_price + $extra_price;
		}
		if($this->multiple) {
			$price = $this->get_discount_price($price);
		}
		
		return $price == $this->data['total_price'];
	}
		
	private function get_extra_data($extras, $price) {
		global $wpdb;
		$extra_price = 0;
		$data = $this->data;
		$calendar_id = $data["calendar_id"];
		$extra_id = $wpdb->get_var($wpdb->prepare('SELECT extra_id FROM ' . $wpdb->prefix . 'wpdevart_calendars WHERE id="%d"', $calendar_id));
		$extra_info = $wpdb->get_var($wpdb->prepare('SELECT data FROM ' . $wpdb->prefix . 'wpdevart_extras WHERE id="%d"', $extra_id));
		$extra_info = json_decode($extra_info, true);
		$extras = json_decode($extras, true);
		if(isset($extra_info['apply']) || isset($extra_info['save']))	{
			array_shift($extra_info);
		}
		foreach($extras as $key=>$extra_value) { 
			if(isset($extra_info[$key])) {
				if($extra_value['price_type'] == "percent") {
					$tmp = ($price * $extra_value['price_percent'])/100;
					$extra_price = $extra_value['operation'] == '+' ? $extra_price + $tmp : $extra_price - $tmp;
				} else {
					$tmp = $extra_value['price_percent'];
					$extra_price = $extra_value['operation'] == '+' ? $extra_price + $tmp : $extra_price - $tmp;
				}
			}
		}
		return $extra_price;
	} 
	
	private function get_discount_price($price) { 
		$data = $this->data;
		if($data['sale_percent']){
			if($data['sale_type'] == "price"){
				$price = $price - $data['sale_percent'];
			}else{
				$price = $price - ($price * $data['sale_percent'])/100;
			}
		}
		return $price;
	}
	
	private function get_main_price() { 
		global $wpdb;
		$price = 0;
		$max_available = 0;
		$data = $this->data;
		if($data['single_day']) {
			$date = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_dates WHERE calendar_id="%d" and day="%s"', $data['calendar_id'], $data['single_day']),ARRAY_A);
			if ($date) {
				$date_array = json_decode($date['data'],true);
				if($data['start_hour']) {
					$this->hour = true;
					if(isset($date_array['hours']) && isset($date_array['hours'][$data['start_hour']])) {
						/*Multiple hour*/
						if($data['end_hour']) {
							$this->multiple = true;
							$count_hour = $this->get_hour_count($date_array['hours'], $data['start_hour'],$data['end_hour']);
							$price = $count_hour['price'];
							$max_available = $count_hour['max_available'];
						} else {
						/*Single hour*/
							$price = $date_array['hours'][$data['start_hour']]["price"];
							$max_available = $date_array['hours'][$data['start_hour']]["available"];
						}
					}
				} else {
					/*Single day*/
					if($date_array['price']){
						$price = $date_array['price'];			
					}		
					$max_available = $date_array["available"];	
				}
			}
		} else {
			/*Multiple day*/
			$this->multiple = true;
			$dates = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_dates WHERE calendar_id="%d" and day BETWEEN %s AND %s', $data['calendar_id'], $data['check_in'], $data['check_out']),ARRAY_A);
			$count = 0;
			if ($dates) {
				foreach($dates as $key => $value){
					if ($count == (count($dates) - 1) && count($dates) >= 2 && $data['price_for_night']) {
						break;
					}
					$array = json_decode($value['data'],true);
					if ($count == 0 || $array["available"] < $max_available) {
						$max_available = $array["available"];
					}
					if($array['price']){
						$price += $array['price'];			
					}	
					
					$count += 1;
				}
			}
			$this->day_count = $count;
		}
		$this->max_available = $max_available;
		return (int)$price;
	}
	
	private function get_hour_count($hours, $start_hour, $end_hour) {
		$start = 0;
		$count = 0;	
		$price = 0;			
		$max_available = 0;			
		foreach($hours as $key => $hour) {
			if($key == $start_hour) {
				$start = 1;
				$max_available = $hour["available"];
			} 
			if($start == 1) {
				$price += $hour["price"];
				if($hour["available"] < $max_available) {
					$max_available = $hour["available"];
				}
				$count += 1;
			}
			if($key == $end_hour) {
				$start = 0;
			}
		}
		$this->hour_count = $count;
		return array(
			'price' => $price,
			'count' => $count,
			'max_available' => $max_available
		);
	}
	
}
?>