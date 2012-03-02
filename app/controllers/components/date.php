<?php
/*
 * Date functions
 *
 * Version: 1.1
 *
 */
define("TIME_FORMAT", "Y-m-d G:i:s");
define("DATE_FORMAT", "Y-m-d");
if (!defined("DAY_TIMESTAMP")) {
	define("DAY_TIMESTAMP", (60*60*24));
}

class DateComponent extends Object {

	function age($date_start) {
		$num_days = $this->num_days($date_start, date(DATE_FORMAT));

		$num_days--;	// Subtract 1 since we don't want inclusive

		return $num_days;
	}

	/* Depricated */
	function get_age($date_start) {
		return $this->age($date_start);
	}

	/* Get number of days in between (inclusively) */
	function num_days_timestamp($time_start, $time_end) {
		// Need PHP 5.3
		$date_start = new DateTime(date(DATE_FORMAT, $time_start));
		$date_end = new DateTime(date(DATE_FORMAT, $time_end));
		$interval = $date_start->diff($date_end);

		return ($interval->d + 1); // Add 1 since this in inclusive
	}

	/* Depricated */
	function get_num_days_time($time_start, $time_end) {
		return $this->num_days_timestamp($time_start, $time_end);
	}

	/* Get number of days in between (inclusively) */
	function num_days($date_start, $date_end) {
		$time_start = strtotime($date_start);
		$time_end = strtotime($date_end);
                
		return $this->num_days_timestamp($time_start, $time_end);
	}

	/* Depricated */
	function get_num_days($date_start, $date_end) {
		return $this->num_days($date_start, $date_end);
	}

	function num_weeks($date_start, $date_end) {
		return floor($this->num_days($date_start, $date_end) / 7);
	}

	function date_add($base_date, $num_days) {
		$start_time = strtotime($base_date);
		
		$day = date(DATE_FORMAT, strtotime("+".$num_days." days", $start_time));

		return $day;
	}

	function date_subtract($base_date, $num_days) {
		$day = $this->date_add($base_date, -1 * $num_days);

		return $day;
	}

	function days_ago($num_days) {
		$day = $this->date_subtract($this->today(), $num_days);

		return $day;
	}

	function is_today($date) {
		if (date(DATE_FORMAT, strtotime("midnight ".$date)) == date(DATE_FORMAT)) {
			return 1;
		} else {
			return 0;
		}
	}

	function time_minutes_before($minutes) {
		return date(TIME_FORMAT, strtotime("-".$minutes." minutes"));
	}

	/* Depricated */
	function get_time_minutes_before($minutes) {
		return $this->time_minutes_before($minutes);
	}

	function day_before($date) {
		return date(TIME_FORMAT, strtotime("-1 days midnight", strtotime($date)));
	}

	/* Depricated */
	function get_day_before($date) {
		return $this->day_before($date);
	}

	function yesterday() {
		return date(DATE_FORMAT, strtotime("yesterday midnight"));
	}

	function yesterday_start() {
		return $this->day_start($this->yesterday());
	}

	/* Depricated */
	function get_yesterday() {
		return $this->yesterday();
	}

	function yesterday_end() {
		return $this->day_end($this->yesterday());
	}

	function day_start($date) {
		return date(TIME_FORMAT, strtotime("midnight", strtotime($date)));
	}

	/* Depricated */
	function get_day_start($date) {
		return $this->day_start($date);
	}

	function day_end($date) {
		return date(TIME_FORMAT, strtotime("23:59:59", strtotime($date)));
	}

	/* Depricated */
	function get_day_end($date) {
		return $this->day_end($date);
	}

	function now() {
		return date(TIME_FORMAT);
	}

	function format($date) {
		return date(DATE_FORMAT, strtotime($date));
	}

	function timestamp_format($datetime) {
		return date(DATE_FORMAT, $datetime);
	}

	function time_format($datetime) {
		return date(TIME_FORMAT, strtotime($datetime));
	}

	function timestamp_time_format($datetime) {
		return date(TIME_FORMAT, $datetime);
	}

	function today() {
		return date(DATE_FORMAT);
	}

        /**
        * Method to format datetime string or timestamp passed to $date into database compatible representation
        * @param string|int $date Either datetime string or int timestamp,
        * if this parameter is invalid value of time() will be used instead
        * @param bool $timeAlso [Optional] If set to true will add time part also, defaults to false - only date
        * @return string The database compatible datetime
        */
        public function toMYSQL($date, $timeAlso = false){
            $date = trim($date);
            if(empty($date)){
                $date = time();
            }
            if(!is_numeric($date)){
                $date = strtotime($date);
            }
            if((bool)$timeAlso === true){
                return gmdate("Y-m-d H:i:s", $date);
            }else{
                return gmdate("Y-m-d", $date);
            }
        }
}
?>
