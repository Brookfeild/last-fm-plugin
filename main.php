<?php
/*
Plugin Name: Last.fm Top Tracks
Description: Display your Last.fm top 10 artists and songs for the last 30 days (predefined username).
Version: 1.0
Author: Tyler Ricketts
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// === Predefined Last.fm Username and API Key ===
$lastfm_username = 'tylerjricketts'; // Replace with your Last.fm username
$lastfm_api_key  = 'fd4bc04c5f3387f5b0b5f4f7bae504b9';  // Replace with your Last.fm API key

// === Shortcode Function ===
function lastfm_top_shortcode() {
    global $lastfm_username, $lastfm_api_key;

    // Fetch top tracks
    $response_tracks = wp_remote_get("http://ws.audioscrobbler.com/2.0/?method=user.gettoptracks&user={$lastfm_username}&period=1month&limit=10&api_key={$lastfm_api_key}&format=json");
    if (is_wp_error($response_tracks)) return 'Error fetching top tracks.';
    $tracks_data = json_decode(wp_remote_retrieve_body($response_tracks), true);

    // Fetch top artists
    $response_artists = wp_remote_get("http://ws.audioscrobbler.com/2.0/?method=user.gettopartists&user={$lastfm_username}&period=1month&limit=10&api_key={$lastfm_api_key}&format=json");
    if (is_wp_error($response_artists)) return 'Error fetching top artists.';
    $artists_data = json_decode(wp_remote_retrieve_body($response_artists), true);

    // Build output
    $output = '<h3>Top Artists (Last 30 days)</h3><ul>';
    if (!empty($artists_data['topartists']['artist'])) {
        foreach ($artists_data['topartists']['artist'] as $artist) {
            $output .= '<li>' . esc_html($artist['name']) . ' (' . esc_html($artist['playcount']) . ' plays)</li>';
        }
    }
    $output .= '</ul>';

    $output .= '<h3>Top Tracks (Last 30 days)</h3><ul>';
    if (!empty($tracks_data['toptracks']['track'])) {
        foreach ($tracks_data['toptracks']['track'] as $track) {
            $output .= '<li>' . esc_html($track['name']) . ' by ' . esc_html($track['artist']['name']) . ' (' . esc_html($track['playcount']) . ' plays)</li>';
        }
    }
    $output .= '</ul>';

    return $output;
}

// === Register Shortcode ===
add_shortcode('lastfm_top', 'lastfm_top_shortcode');

?>