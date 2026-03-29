<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Friendlink_Apply_Backlink {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function check_backlink($site_url) {
        $site_url = rtrim($site_url, '/');
        $current_site_url = get_site_url();
        $current_site_url = rtrim($current_site_url, '/');
        
        $parsed_current = parse_url($current_site_url);
        $current_domain = isset($parsed_current['host']) ? $parsed_current['host'] : '';
        
        if (empty($current_domain)) {
            return null;
        }
        
        $all_domains = $this->get_domain_variants($current_domain);
        $all_domains_lower = array_map('strtolower', array_filter($all_domains));
        
        $home_url_lower = strtolower(home_url());
        $current_site_url_lower = strtolower($current_site_url);
        
        $found_in_static = $this->check_static_html($site_url, $home_url_lower, $current_site_url_lower, $all_domains_lower, $current_domain);
        if ($found_in_static['found']) {
            return array(
                'has_backlink' => true,
                'source' => 'static',
                'response_time' => $found_in_static['response_time'],
                'site_status' => $found_in_static['site_status'],
                'debug' => $found_in_static['debug']
            );
        }
        
        $found_in_dynamic = $this->check_dynamic_links($site_url, $home_url_lower, $current_site_url_lower, $all_domains_lower);
        if ($found_in_dynamic['found']) {
            return array(
                'has_backlink' => true,
                'source' => 'dynamic',
                'response_time' => $found_in_static['response_time'],
                'site_status' => $found_in_static['site_status'],
                'debug' => $found_in_dynamic['debug']
            );
        }
        
        return array(
            'has_backlink' => false,
            'source' => null,
            'response_time' => $found_in_static['response_time'],
            'site_status' => $found_in_static['site_status'],
            'debug' => array_merge($found_in_static['debug'], $found_in_dynamic['debug'])
        );
    }
    
    public function check_backlink_simple($site_url) {
        $result = $this->check_backlink($site_url);
        return $result['has_backlink'];
    }
    
    public function get_site_info($site_url) {
        $site_url = rtrim($site_url, '/');
        $start_time = microtime(true);
        
        $response = wp_remote_get($site_url, array(
            'timeout' => 10,
            'sslverify' => false,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ));
        
        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000);
        
        $site_status = 'error';
        if (!is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code >= 200 && $response_code < 400) {
                $site_status = 'ok';
            } elseif ($response_code >= 400 && $response_code < 500) {
                $site_status = 'client_error';
            } elseif ($response_code >= 500) {
                $site_status = 'server_error';
            }
        }
        
        return array(
            'response_time' => $response_time,
            'site_status' => $site_status,
            'response_code' => is_wp_error($response) ? 0 : wp_remote_retrieve_response_code($response)
        );
    }
    
    private function get_domain_variants($domain) {
        $www_domain = strpos($domain, 'www.') === 0 ? $domain : 'www.' . $domain;
        $non_www_domain = str_replace('www.', '', $domain);
        
        return array_unique(array_filter(array(
            $domain,
            $www_domain,
            $non_www_domain
        )));
    }
    
    private function check_static_html($site_url, $home_url_lower, $current_site_url_lower, $all_domains_lower, $current_domain) {
        $debug = array();
        $found = false;
        $response_time = 0;
        $site_status = 'error';
        
        $start_time = microtime(true);
        
        $response = wp_remote_get($site_url, array(
            'timeout' => 10,
            'sslverify' => false,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ));
        
        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000);
        
        if (is_wp_error($response)) {
            $debug[] = 'Static HTML check failed: ' . $response->get_error_message();
            return compact('found', 'debug', 'response_time', 'site_status');
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code >= 200 && $response_code < 400) {
            $site_status = 'ok';
        } elseif ($response_code >= 400 && $response_code < 500) {
            $site_status = 'client_error';
        } elseif ($response_code >= 500) {
            $site_status = 'server_error';
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (empty($body)) {
            $debug[] = 'Static HTML body is empty';
            return compact('found', 'debug', 'response_time', 'site_status');
        }
        
        $dom = new DOMDocument();
        @$dom->loadHTML($body);
        $links = $dom->getElementsByTagName('a');
        
        foreach ($links as $a) {
            $href = $a->getAttribute('href');
            if (empty($href)) continue;
            
            $decoded_href = $this->decode_url($href);
            if ($decoded_href) {
                $match_result = $this->match_url($decoded_href, $home_url_lower, $current_site_url_lower, $all_domains_lower);
                if ($match_result['matched']) {
                    $found = true;
                    $debug[] = 'Found in static HTML (decoded): ' . $match_result['reason'];
                    break;
                }
            }
            
            $match_result = $this->match_url($href, $home_url_lower, $current_site_url_lower, $all_domains_lower);
            if ($match_result['matched']) {
                $found = true;
                $debug[] = 'Found in static HTML: ' . $match_result['reason'];
                break;
            }
        }
        
        if (!$found) {
            if (preg_match('/data-local-links="([a-zA-Z0-9+\/=]+)"/i', $body, $matches)) {
                $local_links_json = @base64_decode($matches[1]);
                if ($local_links_json) {
                    $local_links = @json_decode($local_links_json, true);
                    if (is_array($local_links)) {
                        $debug[] = 'Found data-local-links attribute with ' . count($local_links) . ' links';
                        foreach ($local_links as $link) {
                            if (!isset($link['url'])) continue;
                            $match_result = $this->match_url($link['url'], $home_url_lower, $current_site_url_lower, $all_domains_lower);
                            if ($match_result['matched']) {
                                $found = true;
                                $debug[] = 'Found in data-local-links: ' . $match_result['reason'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        if (!$found) {
            $debug[] = 'Not found in static HTML';
        }
        
        return compact('found', 'debug', 'response_time', 'site_status');
    }
    
    private function decode_url($url) {
        $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
        
        if (preg_match('/golink=([a-zA-Z0-9+\/=]+)/i', $url, $matches)) {
            $encoded = $matches[1];
            $decoded = @base64_decode($encoded);
            if ($decoded && filter_var($decoded, FILTER_VALIDATE_URL)) {
                return $decoded;
            }
        }
        
        if (preg_match('/[a-zA-Z0-9+\/]{20,}={0,2}/', $url, $matches)) {
            $decoded = @base64_decode($matches[0]);
            if ($decoded && filter_var($decoded, FILTER_VALIDATE_URL)) {
                return $decoded;
            }
        }
        
        return null;
    }
    
    private function check_dynamic_links($site_url, $home_url_lower, $current_site_url_lower, $all_domains_lower) {
        $debug = array();
        $found = false;
        
        $parsed_site = parse_url($site_url);
        $site_host = isset($parsed_site['host']) ? $parsed_site['host'] : '';
        $site_scheme = isset($parsed_site['scheme']) ? $parsed_site['scheme'] : 'https';
        
        if (!$site_host) {
            $debug[] = 'Cannot parse site URL for dynamic check';
            return compact('found', 'debug');
        }
        
        $ajax_url = $site_scheme . '://' . $site_host . '/wp-admin/admin-ajax.php';
        
        $ajax_response = wp_remote_post($ajax_url, array(
            'timeout' => 10,
            'sslverify' => false,
            'body' => array(
                'action' => 'AeroCore',
                'fun' => 'getLinkListLinks',
                'append_wp_links' => '1',
                'links' => '[]'
            ),
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ));
        
        if (is_wp_error($ajax_response)) {
            $debug[] = 'Dynamic links API error: ' . $ajax_response->get_error_message();
            return compact('found', 'debug');
        }
        
        $ajax_body = wp_remote_retrieve_body($ajax_response);
        $ajax_data = json_decode($ajax_body, true);
        
        if (!$ajax_data || !isset($ajax_data['data']['links']) || !is_array($ajax_data['data']['links'])) {
            $debug[] = 'Dynamic links API returned no links';
            return compact('found', 'debug');
        }
        
        $debug[] = 'Found AeroCore dynamic links API';
        
        foreach ($ajax_data['data']['links'] as $link) {
            if (!isset($link['url'])) continue;
            
            $match_result = $this->match_url($link['url'], $home_url_lower, $current_site_url_lower, $all_domains_lower);
            if ($match_result['matched']) {
                $found = true;
                $debug[] = 'Found in dynamic links: ' . $match_result['reason'];
                break;
            }
        }
        
        if (!$found) {
            $debug[] = 'Not found in dynamic links';
        }
        
        return compact('found', 'debug');
    }
    
    private function match_url($url, $home_url_lower, $current_site_url_lower, $all_domains_lower) {
        $matched = false;
        $reason = '';
        
        $url_lower = strtolower($url);
        
        if (strpos($url_lower, $home_url_lower) === 0) {
            $matched = true;
            $reason = "URL prefix match (home_url): $url";
            return compact('matched', 'reason');
        }
        
        if (strpos($url_lower, $current_site_url_lower) === 0) {
            $matched = true;
            $reason = "URL prefix match (site_url): $url";
            return compact('matched', 'reason');
        }
        
        $url_for_parse = $url;
        if (!preg_match('~^https?://~i', $url_for_parse)) {
            if (strpos($url_for_parse, '//') === 0) {
                $url_for_parse = 'https:' . $url_for_parse;
            } elseif (strpos($url_for_parse, '/') !== 0) {
                $url_for_parse = 'https://' . $url_for_parse;
            }
        }
        
        $parsed_url = parse_url($url_for_parse);
        if (isset($parsed_url['host'])) {
            $url_host = strtolower($parsed_url['host']);
            foreach ($all_domains_lower as $domain_lower) {
                if ($url_host === $domain_lower) {
                    $matched = true;
                    $reason = "Host match: $url_host";
                    return compact('matched', 'reason');
                }
            }
        }
        
        return compact('matched', 'reason');
    }
}
