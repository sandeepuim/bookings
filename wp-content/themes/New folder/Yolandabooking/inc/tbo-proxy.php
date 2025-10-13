<?php
/**
 * YolandaTheme - Lightweight TBO Holidays API proxy
 * Adds AJAX handlers for fetching HotelCodes and performing Search with chunking,
 * caching and basic error handling. Keep credentials in wp-config.php as constants:
 *  - TBO_API_AUTH_TOKEN (preferred, base64 'username:password') OR
 *  - TBO_API_USER and TBO_API_PASS (will be base64-encoded)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Yolanda_TBO_API {
    private $base = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/';
    private $auth_header;

    public function __construct() {
        // Prefer explicit username/password basic auth if provided
        if (defined('TBO_API_USER') && defined('TBO_API_PASS') && constant('TBO_API_USER') !== '' && constant('TBO_API_PASS') !== '') {
            $this->auth_header = 'Basic ' . base64_encode(constant('TBO_API_USER') . ':' . constant('TBO_API_PASS'));
        } elseif (defined('TBO_API_AUTH_TOKEN') && constant('TBO_API_AUTH_TOKEN')) {
            $token = constant('TBO_API_AUTH_TOKEN');
            // If token already contains 'Basic ' prefix, use it as-is; otherwise prefix it
            if (stripos($token, 'basic ') === 0) {
                $this->auth_header = $token;
            } else {
                $this->auth_header = 'Basic ' . $token;
            }
        } else {
            // Fail-safe placeholder (do not ship to production with this)
            $this->auth_header = 'Basic ' . base64_encode('USERNAME:PASSWORD');
        }
    }

    // Expose auth header for debug (masked display only)
    public function get_auth_header() {
        return $this->auth_header;
    }

    /**
     * Make request to TBO endpoint. Returns decoded JSON or WP_Error.
     */
    public function request($endpoint, $data = array(), $method = 'POST') {
        $url = $this->base . $endpoint;

        $args = array(
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $this->auth_header,
            ),
            'timeout' => 35,
        );

        if (strtoupper($method) === 'GET') {
            // Some TBO endpoints are consumed as GET with empty body; we still allow query args.
            if (!empty($data)) {
                $url = add_query_arg($data, $url);
            }
            $response = wp_remote_get($url, $args);
        } else {
            $args['body'] = wp_json_encode($data ?: new stdClass());
            $response = wp_remote_post($url, $args);
        }

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code < 200 || $code >= 300) {
            return new WP_Error('http_error', "TBO HTTP {$code}", array('body' => $body));
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON from TBO', array('raw' => $body));
        }

        return $decoded;
    }
}

// Helper: normalize hotel codes structure from TBO response
function yolanda_extract_hotel_codes(array $resp) {
    // Prefer explicit arrays of codes returned by the API
    $candidates = array();

    if (!empty($resp['HotelCodes']) && is_array($resp['HotelCodes'])) {
        $candidates = $resp['HotelCodes'];
    } elseif (!empty($resp['HotelCodeList']) && is_array($resp['HotelCodeList'])) {
        // items may be strings or objects
        foreach ($resp['HotelCodeList'] as $it) {
            if (is_string($it) || is_numeric($it)) $candidates[] = $it;
            elseif (is_array($it) && (isset($it['Code']) || isset($it['HotelCode']))) {
                $candidates[] = isset($it['Code']) ? $it['Code'] : $it['HotelCode'];
            }
        }
    } else {
        // If response contains a list of hotel objects, extract their HotelCode/Code fields
        $possibleLists = array('HotelList', 'Hotels', 'Hotel');
        foreach ($possibleLists as $k) {
            if (!empty($resp[$k]) && is_array($resp[$k])) {
                foreach ($resp[$k] as $item) {
                    if (is_array($item)) {
                        if (isset($item['HotelCode'])) $candidates[] = $item['HotelCode'];
                        elseif (isset($item['Code'])) $candidates[] = $item['Code'];
                        elseif (isset($item['HotelCodeId'])) $candidates[] = $item['HotelCodeId'];
                    }
                }
                if (!empty($candidates)) break;
            }
        }
    }

    // Final fallback: scan for numeric strings with reasonable length (avoid tiny numbers)
    if (empty($candidates)) {
        array_walk_recursive($resp, function($v, $k) use (&$candidates) {
            if ((is_string($v) || is_numeric($v))) {
                $s = (string) $v;
                // accept numeric tokens of length >=5 (reduces accidental matches)
                if (preg_match('/^\d{5,}$/', $s)) {
                    $candidates[] = $s;
                }
            }
        });
    }

    // Ensure unique and strings
    $candidates = array_values(array_unique(array_map('strval', $candidates)));
    return $candidates;
}

// AJAX: get hotel codes for a city
function yolanda_ajax_get_hotel_codes() {
    check_ajax_referer('tbo_nonce', 'nonce');

    $city = isset($_POST['city_code']) ? sanitize_text_field($_POST['city_code']) : '';
    if (!$city) {
        wp_send_json_error('city_code required');
    }

    $transient_key = 'yola_hotelcodes_' . md5($city);
    $cached = get_transient($transient_key);
    if ($cached !== false) {
        wp_send_json_success(array('hotelCodes' => $cached, 'cached' => true));
    }

    $api = new Yolanda_TBO_API();
    // Try HotelCodeList with GET first (some environments expect GET), if empty, retry POST
    $resp = $api->request('HotelCodeList', array('CityCode' => $city), 'GET');

    // Debug logging: enabled when WP_DEBUG or when client requests debug for this city
    $debug_city = isset($_POST['debug_city']) ? sanitize_text_field($_POST['debug_city']) : '';
    $should_log = (defined('WP_DEBUG') && WP_DEBUG) || ($debug_city && $debug_city === $city);

    // If request failed, log and return error
    if (is_wp_error($resp)) {
        if ($should_log) error_log('TBO HotelCodeList error for city ' . $city . ' (GET): ' . $resp->get_error_message());
        // don't return yet — we'll attempt POST fallback below
    }

    if ($should_log) {
        $short = is_string($resp) ? $resp : wp_json_encode($resp);
        error_log('TBO HotelCodeList raw (GET) for city ' . $city . ': ' . substr($short, 0, 4000));
    }

    // Extract codes from initial response
    $codes = array();
    if (!is_wp_error($resp)) {
        $codes = yolanda_extract_hotel_codes((array) $resp);
    }

    // Fallback: if no codes, try POST
    if (empty($codes)) {
        if ($should_log) error_log('TBO HotelCodeList returned no codes for city ' . $city . ' on GET — trying POST fallback');
        $resp_post = $api->request('HotelCodeList', array('CityCode' => $city), 'POST');
        if (is_wp_error($resp_post)) {
            if ($should_log) error_log('TBO HotelCodeList error for city ' . $city . ' (POST): ' . $resp_post->get_error_message());
        } else {
            if ($should_log) error_log('TBO HotelCodeList raw (POST) for city ' . $city . ': ' . substr(wp_json_encode($resp_post), 0, 4000));
            $codes = yolanda_extract_hotel_codes((array) $resp_post);
        }
    }

    $codes = yolanda_extract_hotel_codes((array) $resp);

    // Cache codes for 6 hours. If payload is very large, compress before storing to avoid MySQL packet errors.
    $json_codes = wp_json_encode($codes);
    if (strlen($json_codes) > 500000) { // ~500KB threshold
        $store = array('__gz' => true, 'data' => base64_encode(gzcompress($json_codes)));
        set_transient($transient_key, $store, 6 * HOUR_IN_SECONDS);
    } else {
        set_transient($transient_key, $codes, 6 * HOUR_IN_SECONDS);
    }

    wp_send_json_success(array('hotelCodes' => $codes));
}
add_action('wp_ajax_tbo_get_hotel_codes', 'yolanda_ajax_get_hotel_codes');
add_action('wp_ajax_nopriv_tbo_get_hotel_codes', 'yolanda_ajax_get_hotel_codes');

// Utility: chunk array into pieces
function yolanda_array_chunk(array $arr, $size) {
    $out = array();
    $temp = array();
    $i = 0;
    foreach ($arr as $v) {
        $temp[] = $v;
        $i++;
        if ($i % $size === 0) {
            $out[] = $temp;
            $temp = array();
        }
    }
    if (!empty($temp)) $out[] = $temp;
    return $out;
}

// AJAX: perform search (chunks hotel codes <=200, merges results)
function yolanda_ajax_search_hotels() {
    check_ajax_referer('tbo_nonce', 'nonce');

    // Try to avoid timeouts for slow API responses during large searches
    if (function_exists('set_time_limit')) @set_time_limit(120);

    $city = isset($_POST['city_code']) ? sanitize_text_field($_POST['city_code']) : '';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;

    if (!$city || !$check_in || !$check_out) {
        wp_send_json_error('city_code, check_in and check_out are required');
    }

    // Short cache key for identical searches
    $hash = md5($city . '|' . $check_in . '|' . $check_out . '|' . $adults);
    $cache_key = 'yola_search_' . $hash;
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success(array('hotels' => $cached, 'cached' => true));
    }

    $api = new Yolanda_TBO_API();

    $debug_city = isset($_POST['debug_city']) ? sanitize_text_field($_POST['debug_city']) : '';
    $should_log = (defined('WP_DEBUG') && WP_DEBUG) || ($debug_city && $debug_city === $city);

    // Get hotel codes (prefer transient)
    $codes_transient = get_transient('yola_hotelcodes_' . md5($city));
    if ($codes_transient !== false) {
        // handle compressed storage
        if (is_array($codes_transient) && !empty($codes_transient['__gz']) && !empty($codes_transient['data'])) {
            $decoded = @gzuncompress(base64_decode($codes_transient['data']));
            $hotel_codes = $decoded ? json_decode($decoded, true) : array();
        } else {
            $hotel_codes = (array) $codes_transient;
        }
    } else {
        $hc_resp = $api->request('HotelCodeList', array('CityCode' => $city), 'GET');
        if (is_wp_error($hc_resp)) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log('TBO HotelCodeList error: ' . $hc_resp->get_error_message());
            wp_send_json_error('Failed to get hotel codes');
        }
        $hotel_codes = yolanda_extract_hotel_codes((array) $hc_resp);
        // cache short; compress if too large
        $json_codes = wp_json_encode($hotel_codes);
        if (strlen($json_codes) > 500000) {
            $store = array('__gz' => true, 'data' => base64_encode(gzcompress($json_codes)));
            set_transient('yola_hotelcodes_' . md5($city), $store, 6 * HOUR_IN_SECONDS);
        } else {
            set_transient('yola_hotelcodes_' . md5($city), $hotel_codes, 6 * HOUR_IN_SECONDS);
        }
    }

    if (empty($hotel_codes)) {
        wp_send_json_success(array('hotels' => array()));
    }

    // Optional: allow caller to limit number of hotel codes for testing
    $max_codes = isset($_POST['max_codes']) ? intval($_POST['max_codes']) : 0;
    if ($max_codes > 0 && count($hotel_codes) > $max_codes) {
        if ($should_log) error_log('Limiting hotel_codes from ' . count($hotel_codes) . ' to ' . $max_codes);
        $hotel_codes = array_slice($hotel_codes, 0, $max_codes);
    }

    // Optional: allow caller to limit number of hotel codes for testing
    $max_codes = isset($_POST['max_codes']) ? intval($_POST['max_codes']) : 0;
    // If caller didn't specify, default to 10 to avoid excessive load during tests
    if ($max_codes <= 0) $max_codes = 10;

    // Limit hotel codes early
    if (!empty($hotel_codes) && count($hotel_codes) > $max_codes) {
        if ($should_log) error_log('Trimming hotel_codes to ' . $max_codes . ' for search');
        $hotel_codes = array_slice($hotel_codes, 0, $max_codes);
    }

    // Chunk into groups of up to 200 codes
    $chunks = yolanda_array_chunk($hotel_codes, 200);
    $all_results = array();

    try {

    foreach ($chunks as $chunk) {
        $payload = array(
            'CheckIn' => $check_in,
            'CheckOut' => $check_out,
            'HotelCodes' => implode(',', $chunk),
            'GuestNationality' => 'IN',
            'PaxRooms' => array(array('Adults' => max(1, $adults))),
            'ResponseTime' => 20,
            'IsDetailedResponse' => true,
        );

        if ($should_log) {
            error_log('TBO Search chunk for city ' . $city . ' sending ' . count($chunk) . " codes: " . implode(',', array_slice($chunk,0,50)));
        }

        $resp = $api->request('Search', $payload, 'POST');
        if (is_wp_error($resp)) {
            if ($should_log) error_log('TBO Search error for city ' . $city . ': ' . $resp->get_error_message());
            // Skip failed chunk but continue
            continue;
        }

        if ($should_log) {
            $body = is_string($resp) ? $resp : wp_json_encode($resp);
            // log first 2000 chars to avoid huge logs
            error_log('TBO Search response for city ' . $city . ' (truncated): ' . substr($body, 0, 2000));
        }

        // Extract hotels from common shapes
        $hotels = array();
        if (isset($resp['HotelSearchResult']) && is_array($resp['HotelSearchResult'])) {
            $hsr = $resp['HotelSearchResult'];
            if (isset($hsr['Hotel']) && is_array($hsr['Hotel'])) {
                $hotels = $hsr['Hotel'];
            } elseif (isset($hsr['Hotels']) && is_array($hsr['Hotels'])) {
                $hotels = $hsr['Hotels'];
            }
        } elseif (isset($resp['Hotels']) && is_array($resp['Hotels'])) {
            $hotels = $resp['Hotels'];
        } elseif (isset($resp['Hotel']) && is_array($resp['Hotel'])) {
            $hotels = $resp['Hotel'];
        } else {
            // try to find hotel-like items in response
            foreach ($resp as $k => $v) {
                if (is_array($v) && !empty($v) && isset($v[0]['HotelCode'])) {
                    $hotels = $v;
                    break;
                }
            }
        }

        // Normalize each hotel entry to a minimal shape (avoid storing huge 'raw' blobs)
        foreach ($hotels as $h) {
            if (!is_array($h)) continue;
            $code = isset($h['HotelCode']) ? strval($h['HotelCode']) : (isset($h['Code']) ? strval($h['Code']) : '');
            $name = isset($h['HotelName']) ? $h['HotelName'] : (isset($h['Name']) ? $h['Name'] : '');
            $price = null;
            // try common price paths
            if (isset($h['HotelPrice']) && isset($h['HotelPrice']['Price'])) $price = $h['HotelPrice']['Price'];
            if ($price === null && isset($h['Price'])) $price = $h['Price'];
            // fallback: search numeric values
            if ($price === null) {
                foreach ($h as $vk => $vv) {
                    if (is_numeric($vv)) { $price = $vv; break; }
                }
            }

            $all_results[] = array(
                'code' => $code,
                'name' => $name,
                'price' => $price !== null ? floatval($price) : null,
            );
        }
    }

    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) error_log('Search handler exception: ' . $e->getMessage());
        wp_send_json_error('Search failed: ' . $e->getMessage());
    }

    // Deduplicate by code keeping lowest price
    $merged = array();
    foreach ($all_results as $r) {
        if (empty($r['code'])) continue;
        if (!isset($merged[$r['code']])) {
            $merged[$r['code']] = $r;
            continue;
        }
        $existing = $merged[$r['code']];
        if ($r['price'] !== null && ($existing['price'] === null || $r['price'] < $existing['price'])) {
            $merged[$r['code']] = $r;
        }
    }

    // Convert to indexed array and sort by price asc
    $final = array_values($merged);
    usort($final, function($a, $b){
        $pa = isset($a['price']) ? $a['price'] : INF;
        $pb = isset($b['price']) ? $b['price'] : INF;
        return $pa <=> $pb;
    });

    // Cache short-term (60 seconds) to improve repeated UX
    set_transient($cache_key, $final, 60);

    wp_send_json_success(array('hotels' => $final));
}
add_action('wp_ajax_tbo_search_hotels', 'yolanda_ajax_search_hotels');
add_action('wp_ajax_nopriv_tbo_search_hotels', 'yolanda_ajax_search_hotels');
