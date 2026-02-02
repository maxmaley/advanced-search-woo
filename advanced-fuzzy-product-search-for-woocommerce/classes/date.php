<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DateAfsw {
	public static function _( $time = null ) {
		if (is_null($time)) {
			$time = time();
		}
		return gmdate(AFSW_DATE_FORMAT_HIS, $time);
	}

}
