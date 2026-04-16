<?php

include_once __DIR__ . "/../config/config_routes.php";

/**
 * Displays a JavaScript alert with the given text.
 *
 * @param string $text The text to display in the alert.
 * @return bool Always returns true.
 */
function show_alert($text) {
    echo "<script> alert(`" . $text . "`); </script>";
    return true;
}

/**
 * Reloads the current page by setting the location.href to the current request URI.
 *
 * This function outputs a JavaScript snippet that redirects the browser to the same URL,
 * effectively reloading the page. It then returns true.
 *
 * @return bool Always returns true.
 */
function reload_page() {
    echo "<script> location.href = `".$_SERVER['REQUEST_URI']."`; </script>";
    return true;
}


/**
 * Redirects the user to a specified URL by setting the location.href property in JavaScript.
 *
 * @param string $url The URL to redirect to.
 * @return bool Always returns true.
 */
function redirect_to($url) {
    echo "<script> location.href = `?".ROUTE_PARAM."=".$url."`; </script>";
    return true;
}

/**
 * Formats a given datetime string into a Thai date format.
 *
 * @param string $datetime The datetime string to format.
 * @param int $style The style of the formatted date:
 *                   1 - "d month Y H:i:s" (default)
 *                   2 - "d month Y"
 *                   3 - "d/month/Y H:i:s"
 *                   4 - "d/month/Y"
 * @return string The formatted date string in Thai.
 *
 * @example echo format_datetime_thai('2023-10-05 14:30:00', 2); // Outputs: "05 ตุลาคม 2566"
 */
function format_datetime_thai($datetime, $style = 1) {
    $date = new DateTime($datetime);
    $thai_months = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    $day = $date->format('d');
    $month = $thai_months[(int)$date->format('m') - 1];
    $year = $date->format('Y') + 543;
    $time = $date->format('H:i:s');

    switch ($style) {
        case 1:
            return "$day $month $year $time";
        case 2:
            return "$day $month $year";
        case 3:
            return "$day/$month/$year $time";
        case 4:
            return "$day/$month/$year";
        default:
            return "$day $month $year $time";
    }
}

/**
 * Formats an order ID into a display format.
 *
 * @param int $order_id The numeric order ID from the database
 * @return string The formatted order ID (e.g., "#ORDER-2")
 */
function format_order_id($order_id) {
    return "#ORDER-" . $order_id;
}