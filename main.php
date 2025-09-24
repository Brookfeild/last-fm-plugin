<?php
/*
Plugin Name: Last.fm Weekly Charts
Description: Display weekly chart lists for a Last.fm username.
Version: 1.0
Author: Tyler
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$lastfm_api_key = 'fd4bc04c5f3387f5b0b5f4f7bae504b9'; // Replace with your key

// === Enqueue JS for AJAX ===
function lastfm_enqueue_scripts() {
    wp_enqueue_script('lastfm-ajax', plugin_dir_url(__FILE__) . 'lastfm.js', array('jquery'), '1.0', true);
    wp_localize_script('lastfm-ajax', 'lastfm_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'api_key' => $GLOBALS['lastfm_api_key']
    ));
}
add_action('wp_enqueue_scripts', 'lastfm_enqueue_scripts');

// === Shortcode to display search form and result container ===
function lastfm_weekly_chart_form() {
    $html = '<div id="lastfm-weekly-chart">';
    $html .= '<input type="text" id="lastfm-username" placeholder="Enter Last.fm username" />';
    $html .= '<input type="date" id="lastfm-date" />';
    $html .= '<button id="lastfm-search-btn">Get Weekly Chart List</button>';
    $html .= '<div id="lastfm-result"></div>';
    $html .= '</div>';
    return $html;
}
add_shortcode('lastfm_weekly_chart', 'lastfm_weekly_chart_form');

// === AJAX Handler Function ===
function get_weekly_chart_list() {
    if (!isset($_POST['username']) || empty($_POST['username'])) {
        wp_send_json_error('No username provided.');
    }
    if (!isset($_POST['date']) || empty($_POST['date'])) {
        wp_send_json_error('No date provided.');
    }

    $username = sanitize_text_field($_POST['username']);
    $date = sanitize_text_field($_POST['date']);
    $api_key = $GLOBALS['lastfm_api_key'];

    $timestamp = strtotime($date);

    $url = "https://ws.audioscrobbler.com/2.0/?method=user.getweeklychartlist&user={$username}&api_key={$api_key}&format=json";

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        wp_send_json_error('Error fetching weekly chart list: ' . $response->get_error_message());
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($data['weeklychartlist']['chart'])) {
        wp_send_json_error('No weekly chart data found.');
    }

    // Fetch weekly tracks
    $week = null;
    foreach ($data['weeklychartlist']['chart'] as $chart) {
        if ($timestamp >= $chart['from'] && $timestamp <= $chart['to']) {
            $week = $chart;
            break;
        }
    }

    if (!$week) {
        wp_send_json_error('No week found for the selected date.');
    }

    $from = $week['from'];
    $to = $week['to'];
    $url_tracks = "https://ws.audioscrobbler.com/2.0/?method=user.getweeklytrackchart&user={$username}&from={$from}&to={$to}&api_key={$api_key}&format=json";
    $response_tracks = wp_remote_get($url_tracks);
    if (is_wp_error($response_tracks)) {
        wp_send_json_error('Error fetching weekly tracks: ' . $response_tracks->get_error_message());
    }

    $tracks_data = json_decode(wp_remote_retrieve_body($response_tracks), true);
    if (empty($tracks_data['weeklytrackchart']['track'])) {
        wp_send_json_error('No tracks found for this week.');
    }

    // Build HTML output
    $output = "<h3>Top Tracks for Week " . date('Y-m-d', $from) . " → " . date('Y-m-d', $to) . "</h3>";
    $output .= "<ol>";
    foreach ($tracks_data['weeklytrackchart']['track'] as $track) {
        $name = esc_html($track['name']);
        $artist = esc_html($track['artist']['#text']);
        $playcount = intval($track['playcount']);
        $output .= "<li>{$name} by {$artist} ({$playcount} plays)</li>";
    }
    $output .= "</ol>";

    wp_send_json_success($output);
}
add_action('wp_ajax_get_weekly_chart_list', 'get_weekly_chart_list');
add_action('wp_ajax_nopriv_get_weekly_chart_list', 'get_weekly_chart_list');

?>