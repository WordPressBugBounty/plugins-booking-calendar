<?php
class wpdevart_bc_ModelForms {
  private $user_id = 1;	
  private $user_role = "";	
  private $permission = false;	
  private $no_access = false;	
	
  public function __construct() {
	$current_user = get_current_user_id();
	$current_user_info = get_userdata( $current_user ); 
	if($current_user_info){
		$current_user_info = $current_user_info->roles; 
	}
	$role = isset($current_user_info[0]) ? $current_user_info[0] : "";
	$this->user_id = $current_user;
	$this->user_role = $role;
	$this->permission = wpdevart_bc_Library::page_access('form_page');
      $pages = array(
          'booking_page_wpdevart-calendars',
          'booking_page_wpdevart-reservations',
          'booking_page_wpdevart-forms',
          'booking_page_wpdevart-extras',
          'booking_page_wpdevart-themes'
      );
      if (function_exists('get_current_screen')) {
          $current_page = get_current_screen();
          $this->no_access = $this->user_role != "administrator" && !$this->permission && $this->user_id !== 0
                             && is_admin() && isset($current_page->id) && in_array($current_page->id, $pages);
      }

  }
  
  public function get_forms_rows() {
    global $wpdb;
	
    $limit = (isset($_POST['wpdevart_page']) && $_POST['wpdevart_page'])? (((int) $_POST['wpdevart_page'] - 1) * 20) : 0;
    $order_by = ((isset($_POST['order_by']) && $_POST['order_by'] != "") ? sanitize_sql_orderby($_POST['order_by']) :  'id');
	$order = ((isset($_POST['asc_desc']) && $_POST['asc_desc'] == 'asc') ? 'asc' : 'desc');
    $order_by = ' ORDER BY `' . $order_by . '` ' . $order;
      if (isset($_POST['search_value']) && (sanitize_text_field($_POST['search_value']) != '')) {
          $like = '%' . $wpdb->esc_like( $_POST['search_value'] ) . '%';
          if($this->no_access) {
              $query = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_forms WHERE title LIKE %s AND user_id="%d" ' . $order_by . ' LIMIT ' . $limit . ',20', $like, $this->user_id);
          } else{
              $query = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_forms WHERE title LIKE %s ' . $order_by . ' LIMIT ' . $limit . ',20', $like);
          }
      } else {
          if($this->no_access) {
              $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpdevart_forms WHERE user_id='%d' " . $order_by . " LIMIT " . $limit . ",20", $this->user_id);
          } else {
              $query = "SELECT * FROM " . $wpdb->prefix . "wpdevart_forms  " . $order_by . " LIMIT " . $limit . ",20";
          }
      }
    $rows = $wpdb->get_results($query);
   
    return $rows;
  }	
  
  public function get_form_rows( $id ) {
    global $wpdb;
      if($this->no_access){
          $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_forms WHERE id="%d" AND user_id="%d"', $id, $this->user_id));
      }	else {
          $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_forms WHERE id="%d"', $id));
      }

    return $row;
  } 

    /*############  Item navigation function ################*/
	
  public function items_nav() {
    global $wpdb;
      if (isset($_POST['search_value']) && (sanitize_text_field($_POST['search_value']) != '')) {
          $like = '%' . $wpdb->esc_like( $_POST['search_value'] ) . '%';
          if($this->no_access) {
              $total = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpdevart_forms WHERE title LIKE %s AND user_id="%d"', $like, $this->user_id));
          } else {
              $total = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wpdevart_forms WHERE title LIKE %s', $like));
          }
      } else {
          if($this->no_access) {
              $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_forms WHERE user_id='%d'", $this->user_id));
          } else {
              $total = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."wpdevart_forms ");
          }
      }

    $items_nav['total'] = $total;
    if (isset($_POST['wpdevart_page']) && $_POST['wpdevart_page']) {
      $limit = ((int)$_POST['wpdevart_page'] - 1) * 20;
    }
    else {
      $limit = 0;
    }
    $items_nav['limit'] = (int)($limit / 20 + 1);
    return $items_nav;
  }
  
  public function check_exists( $form_id ) {
    global $wpdb;
	$exists = false;
    $rows = $wpdb->get_results('SELECT form_id FROM ' . $wpdb->prefix . 'wpdevart_calendars',ARRAY_A);
    foreach($rows as $row) {
		if(in_array($form_id,$row)){
			$exists = true;
			break;
		}
	}

    return $exists;
  }
  
}

?>
