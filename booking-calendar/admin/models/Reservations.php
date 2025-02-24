<?php
class wpdevart_bc_ModelReservations {
	private $user_id = 1;
	private $user_role = "";
	private $permission = false;

	public function __construct() {
		$current_user = get_current_user_id();
		$current_user_info = get_userdata($current_user);
		if ($current_user_info) {
			$current_user_info = $current_user_info->roles;
		}
		$role = isset($current_user_info[0]) ? $current_user_info[0] : "";
		$this->user_id = $current_user;
		$this->user_role = $role;
		$this->permission = wpdevart_bc_Library::page_access('calendar_page');
	}
	/*############  Reservations rows function ################*/

	public function get_reservations_rows($id, $type = "OBJECT") {
		if (isset($_POST['apply_filter']) && (! isset($_POST['_wpdevart_bc_nonce']) || ! wp_verify_nonce($_POST['_wpdevart_bc_nonce'], 'action_item'))) {
			die('Sorry, your nonce did not verify.');
		}
		global $wpdb;
		$id = (int) $id;
		$calendar_id = (int) wpdevart_bc_Library::get_value("calendar_id", 0);
		$where = array();
		$limit = (isset($_POST['wpdevart_page']) && $_POST['wpdevart_page']) ? (((int) $_POST['wpdevart_page'] - 1) * 20) : 0;
		$reserv_order_by = ((isset($_POST['order_by']) && $_POST['order_by'] != "") ? sanitize_sql_orderby($_POST['order_by']) :  'id');
		$reserv_order = ((isset($_POST['asc_desc']) && $_POST['asc_desc'] == 'asc') ? 'asc' : 'desc');
		$reserv_order_by = ' ORDER BY `' . $reserv_order_by . '` ' . $reserv_order;
		if ($calendar_id !== 0)
			$where[] = ' calendar_id=' . $calendar_id;
		if ($id != 0) {
			$where[] = ' id= ' . $id . '';
		}
		$where = implode(" AND ", $where);
		if ($where != '') {
			$_where = "WHERE " . $where;
			$where = $_where . " AND ";
		} else {
			$_where = "";
			$where = "WHERE ";
		}

		if (isset($_POST['reserv_status']) && count($_POST['reserv_status']) != '') {
			$reserv_status = implode("','", $_POST['reserv_status']);
			if ((isset($_POST["reserv_period_start"]) && $_POST["reserv_period_start"] != '') && (isset($_POST["reserv_period_end"]) && $_POST["reserv_period_end"] != '')) {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $where . " status IN ('%s') AND  (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') AND form LIKE %s " . $reserv_order_by . " LIMIT " . $limit . ",20", $reserv_status, $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"], $like);
				} else {
					$query = $wpdb->prepare("SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $where . " status IN ('%s') AND  (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') " . $reserv_order_by . " LIMIT " . $limit . ",20", $reserv_status, $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"]);
				}
			} else {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $where . " status IN ('%s') AND form LIKE %s " . $reserv_order_by . " LIMIT " . $limit . ",20", $reserv_status, $like);
				} else {
					$query = $wpdb->prepare("SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $where . " status IN ('%s') " . $reserv_order_by . " LIMIT " . $limit . ",20", $reserv_status);
				}
			}
		} else {
			if ((isset($_POST["reserv_period_start"]) && $_POST["reserv_period_start"] != '') && (isset($_POST["reserv_period_end"]) && $_POST["reserv_period_end"] != '')) {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $where . " (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') AND form LIKE %s " . $reserv_order_by . " LIMIT " . $limit . ",20",  $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"], $like);
				} else {
					$query = $wpdb->prepare("SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $where . " (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') " . $reserv_order_by . " LIMIT " . $limit . ",20",  $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"]);
				}
			} else {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $where . " form LIKE %s " . $reserv_order_by . " LIMIT " . $limit . ",20", $like);
				} else {
					$query = "SELECT " . $wpdb->prefix . "wpdevart_reservations.*, " . $wpdb->prefix . "wpdevart_payments.* FROM " . $wpdb->prefix . "wpdevart_reservations LEFT JOIN " . $wpdb->prefix . "wpdevart_payments ON " . $wpdb->prefix . "wpdevart_reservations.id=" . $wpdb->prefix . "wpdevart_payments.res_id " . $_where . " " . $reserv_order_by . " LIMIT " . $limit . ",20";
				}
			}
		}
		$rows = $wpdb->get_results($query, $type);
		return $rows;
	}

	public function get_reservations_for_export() {
		global $wpdb;
		$select_columns = 'id, calendar_id, single_day, check_in, check_out, start_hour, end_hour, count_item, price, total_price, extras_price, status, payment_method, payment_status, date_created,form, extras';
		$reserv_order_by = ((isset($_REQUEST['order_by']) && $_REQUEST['order_by'] != "") ? sanitize_sql_orderby($_REQUEST['order_by']) :  'id');
		$reserv_order = ((isset($_REQUEST['asc_desc']) && $_REQUEST['asc_desc'] == 'asc') ? 'asc' : 'desc');
		$reserv_order_by = ' ORDER BY `' . $reserv_order_by . '` ' . $reserv_order;
		$limit = (isset($_REQUEST['wpdevart_page']) && $_REQUEST['wpdevart_page'] && $_REQUEST['all_pages'] == 0) ? " LIMIT " . (((int) $_REQUEST['wpdevart_page'] - 1) * 20) . ',20' : '';
		$calendar_id = (int) wpdevart_bc_Library::get_value("calendar_id", 0);
		if ($calendar_id !== 0) {
			$_where = 'WHERE calendar_id=' . $calendar_id;
			$where = $_where . ' AND ';
		} else {
			$_where = '';
			$where = '';
		}
		if (isset($_REQUEST['reserv_status']) && $_REQUEST['reserv_status'] != "") {
			$reserv_status = json_decode(stripslashes($_REQUEST['reserv_status']), true);
			if (count($reserv_status)) {
				$reserv_status = implode("','", $reserv_status);
				if ((isset($_REQUEST["reserv_period_start"]) && ($_REQUEST["reserv_period_start"] != '')) && (isset($_REQUEST["reserv_period_end"]) && ($_REQUEST["reserv_period_end"] != ''))) {
					if (isset($_REQUEST['wpdevart_serch']) && ($_REQUEST['wpdevart_serch'] != '')) {
						$like = '%' . $wpdb->esc_like($_REQUEST['wpdevart_serch']) . '%';
						$query = $wpdb->prepare("SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " status IN ('%s') AND  (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') AND form LIKE %s " . $reserv_order_by . $limit, $reserv_status, $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"], $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"], $like);
					} else {
						$query = $wpdb->prepare("SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " status IN ('%s') AND  (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') " . $reserv_order_by . $limit, $reserv_status, $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"], $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"]);
					}
				} else {
					if (isset($_REQUEST['wpdevart_serch']) && ($_REQUEST['wpdevart_serch'] != '')) {
						$like = '%' . $wpdb->esc_like($_REQUEST['wpdevart_serch']) . '%';
						$query = $wpdb->prepare("SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " status IN ('%s') AND form LIKE %s " . $reserv_order_by . $limit, $reserv_status, $like);
					} else {
						$query = $wpdb->prepare("SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " status IN ('%s') " . $reserv_order_by . $limit, $reserv_status);
					}
				}
			} else {
				if ((isset($_REQUEST["reserv_period_start"]) && ($_REQUEST["reserv_period_start"] != '')) && (isset($_REQUEST["reserv_period_end"]) && ($_REQUEST["reserv_period_end"] != ''))) {
					if (isset($_REQUEST['wpdevart_serch']) && ($_REQUEST['wpdevart_serch'] != '')) {
						$like = '%' . $wpdb->esc_like($_REQUEST['wpdevart_serch']) . '%';
						$query = $wpdb->prepare("SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') AND form LIKE %s " . $reserv_order_by . $limit, $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"], $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"], $like);
					} else {
						$query = $wpdb->prepare("SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') " . $reserv_order_by . $limit,  $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"], $_REQUEST["reserv_period_start"], $_REQUEST["reserv_period_end"]);
					}
				} else {
					if (isset($_REQUEST['wpdevart_serch']) && ($_REQUEST['wpdevart_serch'] != '')) {
						$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
						$query = $wpdb->prepare("SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " form LIKE %s " . $reserv_order_by . $limit,  $like);
					} else {
						$query = "SELECT " . $select_columns . "  FROM " . $wpdb->prefix . "wpdevart_reservations " . $_where . " " . $reserv_order_by . $limit;
					}
				}
			}
		}

		$rows = $wpdb->get_results($query, ARRAY_A);
		return $rows;
	}

	/*############  Items navigation function ################*/
	public function items_nav($id = 0) {
		global $wpdb;
		$id = (int) $id;
		$calendar_id = (int) wpdevart_bc_Library::get_value("calendar_id", 0);
		$where = array();
		if ($calendar_id != 0)
			$where[] = ' calendar_id=' . $calendar_id;
		if ($id != 0)
			$where[] = ' id= ' . $id . '';

		$where = implode(" AND ", $where);
		if ($where != '') {
			$_where = "WHERE " . $where;
			$where = $_where . " AND ";
		} else {
			$_where = "";
			$where = "WHERE ";
		}

		if (isset($_POST['reserv_status']) && count($_POST['reserv_status']) != 0) {
			$reserv_status = implode("','", $_POST['reserv_status']);
			if ((isset($_POST["reserv_period_start"]) && $_POST["reserv_period_start"] != '') && (isset($_POST["reserv_period_end"]) && $_POST["reserv_period_end"] != '')) {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . "  status IN ('%s') AND  (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') AND form LIKE %s", $reserv_status, $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"], $like);
				} else {
					$query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . "  status IN ('%s') AND  (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s')", $reserv_status, $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"]);
				}
			} else {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . "  status IN ('%s') AND form LIKE %s", $reserv_status, $like);
				} else {
					$query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . "  status IN ('%s')", $reserv_status);
				}
			}
		} else {
			if ((isset($_POST["reserv_period_start"]) && $_POST["reserv_period_start"] != '') && (isset($_POST["reserv_period_end"]) && $_POST["reserv_period_end"] != '')) {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s') AND form LIKE %s", $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"], $like);
				} else {
					$query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " (single_day BETWEEN '%s' AND '%s' OR check_in BETWEEN '%s' AND '%s')", $_POST["reserv_period_start"], $_POST["reserv_period_end"], $_POST["reserv_period_start"], $_POST["reserv_period_end"]);
				}
			} else {
				if (isset($_POST['wpdevart_serch']) && $_POST['wpdevart_serch'] != '') {
					$like = '%' . $wpdb->esc_like($_POST['wpdevart_serch']) . '%';
					$query = $wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $where . " form LIKE %s", $like);
				} else {
					$query = "SELECT COUNT(*) FROM " . $wpdb->prefix . "wpdevart_reservations " . $_where;
				}
			}
		}

		$total = $wpdb->get_var($query);
		$items_nav['total'] = $total;
		if (isset($_POST['wpdevart_page']) && $_POST['wpdevart_page']) {
			$limit = ((int)$_POST['wpdevart_page'] - 1) * 20;
		} else {
			$limit = 0;
		}
		$items_nav['limit'] = (int)($limit / 20 + 1);
		return $items_nav;
	}

	public function get_form_data($form, $id = 0, $extra_form_id = 0, $type = "") {
		global $wpdb;
		$calendar_id = wpdevart_bc_Library::get_value("calendar_id", 0);
		if ($form) {
			$form_value = json_decode($form, true);
			$cal_id = 0;
			if ($id == 0) {
				if (sanitize_text_field($calendar_id) != 0)
					$cal_id = $calendar_id;
			} else {
				$cal_id = $id;
			}
			if ($extra_form_id == 0) {
				$form_id = $wpdb->get_var($wpdb->prepare('SELECT form_id FROM ' . $wpdb->prefix . 'wpdevart_calendars WHERE id="%d"', $cal_id));
			} else {
				$form_id = $extra_form_id;
			}
			$form_info = $wpdb->get_var($wpdb->prepare('SELECT data FROM ' . $wpdb->prefix . 'wpdevart_forms WHERE id="%d"', $form_id));
			if ($form_info) {
				$form_info = json_decode($form_info, true);
				if (isset($form_info['apply']) || isset($form_info['save'])) {
					array_shift($form_info);
				}
				foreach ($form_info as $key => $form_fild_info) {
					if (isset($form_value["wpdevart_" . $type . $key])) {
						$form_info[$key]["value"] = $form_value["wpdevart_" . $type . $key];
					} else {
						$form_info[$key]["value"] = "";
					}
				}
			} else {
				$form_info = array();
			}
		} else {
			$form_info = array();
		}
		return $form_info;
	}

	public function get_form_data_new($reservations, $form_info, $type = "") {
		if ($form_info) {
			$form_info = json_decode($form_info, true);
			if (isset($form_info['apply']) || isset($form_info['save'])) {
				array_shift($form_info);
			}
			foreach ($reservations as $key => $reservation) {
				if (trim($reservations[$key]["extras"]) == "[]") {
					unset($reservations[$key]["extras"]);
				}
				if ($reservation["form"]) {
					$form_value = json_decode($reservation["form"], true);
					foreach ($form_info as $k => $form_fild_info) {
						if (isset($form_value["wpdevart_" . $type . $k])) {
							$reservations[$key][$k] = $form_value["wpdevart_" . $type . $k];
						} else {
							$reservations[$key][$k] = "";
						}
					}
				} else {
					$reservations[$key] = "";
				}
				unset($reservations[$key]["form"]);
			}
		}
		return $reservations;
	}

	public function get_extra_data_new($reservations, $extra_info, $currency) {
		if ($extra_info) {
			$extra_info = json_decode($extra_info, true);
			if (isset($extra_info['apply']) || isset($extra_info['save'])) {
				array_shift($extra_info);
			}
			foreach ($reservations as $key => $reservation) {
				if (isset($reservation["extras"])) {
					if ($reservation["extras"]) {
						$extras_value = json_decode($reservation["extras"], true);
						foreach ($extras_value as $k => $extra_value) {
							if (isset($extra_info[$k])) {
								if ($extra_value['price_type'] == "percent") {
									$reservations[$key][$k] = $extra_value["label"] . " " . $extra_value['operation'] . (($reservation["price"] * $extra_value['price_percent']) / 100) . html_entity_decode($currency);
								} else {
									$reservations[$key][$k] = $extra_value["label"] . " " . $extra_value['operation'] . $extra_value['price_percent'] . html_entity_decode($currency);
								}
							}
						}
					} else {
						$reservations[$key] = "";
					}
					unset($reservations[$key]["extras"]);
				}
			}
		}
		return $reservations;
	}

	public function get_labels($info) {
		$lables = array();
		if ($info) {
			$info = json_decode($info, true);
			if (isset($info['apply']) || isset($info['save'])) {
				array_shift($info);
			}
			foreach ($info as $key => $fild_info) {
				$lables[$key] = $fild_info["label"];
			}
		}
		return $lables;
	}


	public function get_extra_data($extras, $mail = "", $price = 0, $id = 0) {
		global $wpdb;
		$calendar_id = wpdevart_bc_Library::get_value("calendar_id", 0);
		if ($mail == "mail") {
			$extra = $extras;
			$price = $price;
		} elseif ($mail == "front") {
			$extra = $extras["extras"];
			$price = $extras["price"];
		} else {
			$extra = $extras->extras;
			$price = $extras->price;
		}
		if ($extra) {
			$extras_value = json_decode($extra, true);
			$cal_id = 0;
			if ($id == 0) {
				if (sanitize_text_field($calendar_id) != 0)
					$cal_id = $calendar_id;
			} else {
				$cal_id = $id;
			}
			$extra_id = $wpdb->get_var($wpdb->prepare('SELECT extra_id FROM ' . $wpdb->prefix . 'wpdevart_calendars WHERE id="%d"', $cal_id));
			$extra_info = $wpdb->get_var($wpdb->prepare('SELECT data FROM ' . $wpdb->prefix . 'wpdevart_extras WHERE id="%d"', $extra_id));
			$extra_info = $extra_info?json_decode($extra_info, true):array();
			if (isset($extra_info['apply']) || isset($extra_info['save'])) {
				array_shift($extra_info);
			}
			foreach ($extras_value as $key => $extra_value) {
				if (isset($extra_info[$key])) {
					$extras_value[$key]["group_label"] = $extra_info[$key]["label"];
					if ($extra_value['price_type'] == "percent") {
						$extras_value[$key]["price"] = ($price * $extra_value['price_percent']) / 100;
					} else {
						$extras_value[$key]["price"] = $extra_value['price_percent'];
					}
				} else {
					$extras_value[$key]["group_label"] = "";
				}
			}
		} else {
			$extras_value = array();
		}
		return $extras_value;
	}

	public function get_calendar_rows() {
		global $wpdb;
		if ($this->user_role != "administrator" && !$this->permission) {
			$row = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_calendars WHERE user_id="%d"', $this->user_id), ARRAY_A);
		} else {
			$row = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_calendars', ARRAY_A);
		}
		return $row;
	}

	public function get_reservation_row($id) {
		global $wpdb;
		$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_reservations WHERE id="%d"', $id), ARRAY_A);

		return $row;
	}

	public function get_new_res($id, $days_for_new) {
		global $wpdb;
		$today = self::get_now();
		if ($id != 0) {
			$ress = $wpdb->get_results($wpdb->prepare('SELECT id,date_created FROM ' . $wpdb->prefix . 'wpdevart_reservations  WHERE calendar_id="%d" AND is_new=1', $id), ARRAY_A);
			foreach ($ress as $res) {
				$date_diff = abs($this->get_date_diff($res["date_created"], $today));
				if ($date_diff > $days_for_new) {
					$wpdb->update(
						$wpdb->prefix . 'wpdevart_reservations',
						array('is_new' => 0),
						array('id' => $res["id"]),
						array('%d'),
						array('%d')
					);
				}
			}
		} else {
			$ress = $wpdb->get_results('SELECT id,calendar_id,date_created FROM ' . $wpdb->prefix . 'wpdevart_reservations  WHERE is_new=1', ARRAY_A);
			foreach ($ress as $res) {
				$date_diff = abs($this->get_date_diff($res["date_created"], $today));
				if ($date_diff > $days_for_new[$res['calendar_id']]) {
					$wpdb->update(
						$wpdb->prefix . 'wpdevart_reservations',
						array('is_new' => 0),
						array('id' => $res["id"]),
						array('%d'),
						array('%d')
					);
				}
			}
		}

		if ($id != 0) {
			$count = $wpdb->get_row($wpdb->prepare('SELECT ' . $wpdb->prefix . 'wpdevart_calendars.title,COUNT(' . $wpdb->prefix . 'wpdevart_reservations.id) AS countRes FROM ' . $wpdb->prefix . 'wpdevart_reservations LEFT JOIN ' . $wpdb->prefix . 'wpdevart_calendars ON ' . $wpdb->prefix . 'wpdevart_reservations.calendar_id=' . $wpdb->prefix . 'wpdevart_calendars.id WHERE ' . $wpdb->prefix . 'wpdevart_reservations.is_new=1 AND calendar_id="%d" GROUP BY title', $id), ARRAY_A);
			$count = array($count);
		} else {
			$count = $wpdb->get_results('SELECT ' . $wpdb->prefix . 'wpdevart_calendars.title,COUNT(' . $wpdb->prefix . 'wpdevart_reservations.id) AS countRes FROM ' . $wpdb->prefix . 'wpdevart_reservations LEFT JOIN ' . $wpdb->prefix . 'wpdevart_calendars ON ' . $wpdb->prefix . 'wpdevart_reservations.calendar_id=' . $wpdb->prefix . 'wpdevart_calendars.id WHERE ' . $wpdb->prefix . 'wpdevart_reservations.is_new=1 GROUP BY title', ARRAY_A);
		}

		return $count;
	}

	public function get_date_data($unique_id) {
		global $wpdb;
		$date_info = "";
		$row = $wpdb->get_row($wpdb->prepare('SELECT data FROM ' . $wpdb->prefix . 'wpdevart_dates WHERE unique_id="%s"', $unique_id), ARRAY_A);
		if (is_array($row) &&  isset($row["data"]))
			$date_info = $row["data"];
		return $date_info;
	}

	public function get_theme_rows($id = 0) {
		global $wpdb;
		$calendar_id = wpdevart_bc_Library::get_value("calendar_id", 0);
		$cal_id = 0;
		if ($id == 0) {
			if (sanitize_text_field($calendar_id) != 0)
				$cal_id = $calendar_id;
		} else {
			$cal_id = $id;
		}
		$theme_id = $wpdb->get_var($wpdb->prepare('SELECT theme_id FROM ' . $wpdb->prefix . 'wpdevart_calendars WHERE id="%d"', $cal_id));
		$theme_rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'wpdevart_themes WHERE id="%d"', $theme_id), ARRAY_A);
		if (isset($theme_rows[0])) {
			$them_options = json_decode($theme_rows[0]["value"], true);
		} else {
			$them_options = array();
		}
		return $them_options;
	}

	public function get_themes_rows() {
		global $wpdb;
		$theme_id = $wpdb->get_results('SELECT id,theme_id FROM ' . $wpdb->prefix . 'wpdevart_calendars', ARRAY_A);
		$a = array();
		$themes = array();
		$results = array();
		foreach ($theme_id as $theme) {
			$a[$theme["id"]] = $theme["theme_id"];
		}
		$str = implode(",", $a);
		$theme_rows = $wpdb->get_results('SELECT id,value FROM ' . $wpdb->prefix . 'wpdevart_themes WHERE id IN (' . $str . ')');
		foreach ($theme_rows as $theme_row) {
			if (isset($theme_row)) {
				$result = json_decode($theme_row->value, true);
				if (isset($result["days_for_new"]))
					$themes[$theme_row->id] = $result["days_for_new"];
				else
					$themes[$theme_row->id] = 30;
			}
		}
		foreach ($a as $key => $value) {
			$results[$key] = $themes[$value];
		}
		return $results;
	}

	public function get_calendar_title() {
		global $wpdb;
		$calendar_id = wpdevart_bc_Library::get_value("calendar_id", 0);
		$cal_id = 0;
		if (sanitize_text_field($calendar_id) != 0)
			$cal_id = $calendar_id;
		$row = $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . $wpdb->prefix . 'wpdevart_calendars WHERE id="%d"', $cal_id));
		return $row;
	}

	private function get_date_diff($date1, $date2) {
		$start = strtotime($date1);
		$end = strtotime($date2);
		$datediff = $start - $end;
		return floor($datediff / (60 * 60 * 24));
	}

	private static function get_now() {
		$now = date('Y-m-d H:i:s');
		$tz_string     = get_option('timezone_string');
		if ($tz_string) {
			try {
				$tz = new DateTimeZone($tz_string);
			} catch (Exception $e) {
				$tz = '';
			}

			if ($tz) {
				$now = new DateTime('now', $tz);
				$now = $now->format('Y-m-d H:i:s');
			}
		}
		return $now;
	}
}
