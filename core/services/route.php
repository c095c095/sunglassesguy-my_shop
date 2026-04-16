<?php

include_once __DIR__ . "/../config/config_routes.php";

/**
 * Retrieves the current page from the URL parameters.
 *
 * This function checks the URL parameters for a specific route parameter
 * defined by the constant `ROUTE_PARAM`. If the parameter is not present,
 * it defaults to the value defined by the constant `DEFAULT_ROUTE`.
 *
 * @return string The current page identifier.
 */
function get_current_page() {
    $page = $_GET[ROUTE_PARAM] ?? DEFAULT_ROUTE;
    return $page;
}

/**
 * Retrieves the title of the current page based on the routing configuration.
 *
 * This function determines the current page using the `get_current_page` function,
 * and then looks up the corresponding title in the `ROUTES` array. If the current
 * page is not found in the `ROUTES` array, it returns the title for the `ERROR_ROUTE`.
 *
 * @return string The title of the current page or the error page title if the page is not found.
 */
function get_page_title() {
    $page = get_current_page();

    if (array_key_exists($page, ROUTES)) {
        return ROUTES[$page];
    }

    return ROUTES[ERROR_ROUTE];
}

/**
 * Retrieves the file path for the current page based on predefined routes.
 *
 * This function determines the current page using the `get_current_page()` function.
 * It then checks if the page exists in the `ROUTES` array. If the page exists,
 * it constructs and returns the file path using the `ROUTE_PATH`, the page name,
 * and the `ROUTE_EXTENSION`. If the page does not exist in the `ROUTES` array,
 * it returns the file path for the error route.
 *
 * @return string The file path for the current page or the error route.
 */
function get_page_path() {
    $page = get_current_page();
    
    if (array_key_exists($page, ROUTES)) {
        return ROUTE_PATH . $page . ROUTE_EXTENSION;
    }

    return ROUTE_PATH . ERROR_ROUTE . ROUTE_EXTENSION;
}

/**
 * Retrieves the title of the current admin page.
 *
 * This function determines the current page and returns the corresponding
 * title from the ADMIN_ROUTES array. If the current page is not found in
 * the ADMIN_ROUTES array, it returns the title for the error route.
 *
 * @return string The title of the current admin page or the error route title.
 */
function get_admin_page_title() {
    $page = get_current_page();

    if (array_key_exists($page, ADMIN_ROUTES)) {
        return ADMIN_ROUTES[$page];
    }

    return ADMIN_ROUTES[ERROR_ROUTE];
}

/**
 * Get the file path for the current admin page.
 *
 * This function retrieves the current page and checks if it exists in the
 * ADMIN_ROUTES array. If it does, it constructs and returns the file path
 * for that admin page. If the page does not exist in the ADMIN_ROUTES array,
 * it returns the file path for the error route.
 *
 * @return string The file path for the current admin page or the error route.
 */
function get_admin_page_path() {
    $page = get_current_page();
    
    if (array_key_exists($page, ADMIN_ROUTES)) {
        return __DIR__ . '/../../' . ADMIN_ROUTE_PATH . $page . ROUTE_EXTENSION;
    }

    return __DIR__ . '/../../' . ADMIN_ROUTE_PATH . ERROR_ROUTE . ROUTE_EXTENSION;
}