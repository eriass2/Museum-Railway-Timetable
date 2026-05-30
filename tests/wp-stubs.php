<?php
/**
 * Minimal WordPress API stubs for PHPUnit (no core load).
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

if (!class_exists('WP_Term')) {
    class WP_Term {
        /** @var int */
        public $term_id = 0;

        /** @var string */
        public $name = '';

        /** @var string */
        public $slug = '';
    }
}

if (!class_exists('WP_Post')) {
    class WP_Post {
        /** @var int */
        public $ID = 0;

        /** @var string */
        public $post_type = '';

        /**
         * @param object|array<string, mixed>|null $data
         */
        public function __construct($data = null) {
            if (is_object($data)) {
                foreach (get_object_vars($data) as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error {
        /** @var array<string, array<int, string>> */
        public $errors = [];

        /**
         * @param string|int $code
         * @param string     $message
         * @param mixed      $data
         */
        public function __construct($code = '', $message = '', $data = '') {
            unset($data);
            if ((string) $code !== '') {
                $this->errors[(string) $code] = [(string) $message];
            }
        }

        public function get_error_code(): string {
            $keys = array_keys($this->errors);

            return (string) ($keys[0] ?? '');
        }

        public function get_error_message(): string {
            $messages = reset($this->errors);

            return is_array($messages) ? (string) ($messages[0] ?? '') : '';
        }
    }
}

if (!function_exists('is_wp_error')) {
    /**
     * @param mixed $thing
     */
    function is_wp_error($thing): bool {
        return $thing instanceof WP_Error;
    }
}

if (!function_exists('sanitize_text_field')) {
    /**
     * @param string $str
     */
    function sanitize_text_field($str): string {
        return is_string($str) ? trim(wp_strip_all_tags($str)) : '';
    }
}

if (!function_exists('wp_strip_all_tags')) {
    /**
     * @param string $str
     */
    function wp_strip_all_tags($str): string {
        return strip_tags($str);
    }
}

if (!function_exists('wp_unslash')) {
    /**
     * @param mixed $value
     * @return mixed
     */
    function wp_unslash($value) {
        return is_string($value) ? stripslashes($value) : $value;
    }
}

if (!function_exists('sanitize_key')) {
    /**
     * @param string $key
     */
    function sanitize_key($key): string {
        $key = strtolower((string) $key);

        return (string) preg_replace('/[^a-z0-9_\-]/', '', $key);
    }
}

if (!function_exists('_n')) {
    /**
     * @param string $single
     * @param string $plural
     * @param int    $number
     * @param string $domain
     */
    function _n($single, $plural, $number, $domain = 'default') {
        unset($domain);
        return $number === 1 ? $single : $plural;
    }
}

if (!function_exists('date_i18n')) {
    /**
     * @param string   $format
     * @param int|bool $timestamp
     */
    function date_i18n($format, $timestamp = false) {
        $ts = $timestamp === false ? time() : (int) $timestamp;
        return date($format, $ts);
    }
}

if (!function_exists('get_the_title')) {
    /**
     * @param int|WP_Post $post
     */
    function get_the_title($post = 0): string {
        $id = is_object($post) ? (int) $post->ID : (int) $post;

        return $id > 0 ? 'Post ' . $id : '';
    }
}

if (!function_exists('get_post')) {
    /**
     * Test overrides via $GLOBALS['mrt_test_posts'][ post_id ].
     *
     * @param int $post
     * @return object|null
     */
    function get_post($post = null, $output = 'OBJECT', $filter = 'raw') {
        unset($output, $filter);
        $id = is_object($post) ? (int) $post->ID : (int) $post;
        if (isset($GLOBALS['mrt_test_posts']) && is_array($GLOBALS['mrt_test_posts']) && array_key_exists($id, $GLOBALS['mrt_test_posts'])) {
            return $GLOBALS['mrt_test_posts'][$id];
        }

        return null;
    }
}

if (!function_exists('get_option')) {
    /**
     * Test overrides via $GLOBALS['mrt_test_options'][ option_name ]
     *
     * @param string $option
     * @param mixed  $default
     * @return mixed
     */
    function get_option($option, $default = false) {
        if (isset($GLOBALS['mrt_test_options']) && is_array($GLOBALS['mrt_test_options']) && array_key_exists($option, $GLOBALS['mrt_test_options'])) {
            return $GLOBALS['mrt_test_options'][$option];
        }

        return $default;
    }
}

if (!function_exists('update_option')) {
    /**
     * Test storage via $GLOBALS['mrt_test_options'][ option_name ]
     *
     * @param string $option
     * @param mixed  $value
     * @return bool
     */
    function update_option($option, $value) {
        if (!isset($GLOBALS['mrt_test_options']) || !is_array($GLOBALS['mrt_test_options'])) {
            $GLOBALS['mrt_test_options'] = [];
        }
        $GLOBALS['mrt_test_options'][$option] = $value;

        return true;
    }
}

if (!function_exists('get_post_meta')) {
    /**
     * Test overrides via $GLOBALS['mrt_test_post_meta'][ "{$id}|{$key}" ]
     *
     * @param int    $post_id
     * @param string $key
     * @param bool   $single
     * @return mixed
     */
    function get_post_meta($post_id, $key, $single = false) {
        $k = (int) $post_id . '|' . $key;
        if (isset($GLOBALS['mrt_test_post_meta'][$k])) {
            return $GLOBALS['mrt_test_post_meta'][$k];
        }

        return $single ? '' : [];
    }
}

if (!function_exists('get_posts')) {
    /**
     * @param array<string, mixed> $args
     * @return array<int, int>
     */
    function get_posts($args = []) {
        if (isset($GLOBALS['mrt_test_get_posts']) && is_callable($GLOBALS['mrt_test_get_posts'])) {
            return $GLOBALS['mrt_test_get_posts']($args);
        }

        return [];
    }
}

if (!function_exists('current_user_can')) {
    /**
     * Test overrides via $GLOBALS['mrt_test_current_user_can'] callable or bool.
     *
     * @param string $capability
     * @param mixed  ...$args
     */
    function current_user_can($capability, ...$args): bool {
        $GLOBALS['mrt_test_current_user_can_calls'][] = [$capability, $args];
        if (isset($GLOBALS['mrt_test_current_user_can']) && is_callable($GLOBALS['mrt_test_current_user_can'])) {
            return (bool) $GLOBALS['mrt_test_current_user_can']($capability, ...$args);
        }
        if (isset($GLOBALS['mrt_test_current_user_can'])) {
            return (bool) $GLOBALS['mrt_test_current_user_can'];
        }

        return true;
    }
}

if (!function_exists('get_term')) {
    /**
     * @param int|string $term_id
     * @param string     $taxonomy
     * @return object|null
     */
    function get_term($term_id, $taxonomy = '') {
        unset($taxonomy);
        $terms = $GLOBALS['mrt_test_terms'] ?? [];
        $id = (int) $term_id;
        return $terms[$id] ?? null;
    }
}

if (!function_exists('wp_get_post_terms')) {
    /**
     * @param int    $post_id
     * @param string $taxonomy
     * @param array<string, mixed> $args
     * @return array<int, mixed>
     */
    function wp_get_post_terms($post_id, $taxonomy, $args = []) {
        unset($taxonomy, $args);
        $map = $GLOBALS['mrt_test_post_terms'] ?? [];
        $key = (int) $post_id;
        if (!isset($map[$key])) {
            return [];
        }
        $term_ids = (array) $map[$key];
        $terms = $GLOBALS['mrt_test_terms'] ?? [];
        $out = [];
        foreach ($term_ids as $tid) {
            $tid = (int) $tid;
            if (isset($terms[$tid])) {
                $out[] = $terms[$tid];
            }
        }
        return $out;
    }
}

if (!function_exists('apply_filters')) {
    /**
     * @param string $hook_name
     * @param mixed  $value
     * @param mixed  ...$args
     * @return mixed
     */
    function apply_filters($hook_name, $value, ...$args) {
        unset($hook_name, $args);

        return $value;
    }
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
    function wp_create_nonce( $action = -1 ) {
        unset( $action );
        return 'unit-test-nonce';
    }
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
    /**
     * @return int|false
     */
    function wp_verify_nonce( $nonce, $action = -1 ) {
        unset( $action );
        return $nonce === 'unit-test-nonce' ? 1 : false;
    }
}

if ( ! function_exists( 'current_time' ) ) {
    /**
     * @param string $type
     * @return int|string
     */
    function current_time( $type ) {
        if ( isset( $GLOBALS['mrt_test_current_timestamp'] ) ) {
            $timestamp = (int) $GLOBALS['mrt_test_current_timestamp'];
        } else {
            $timestamp = time();
        }
        if ( $type === 'timestamp' ) {
            return $timestamp;
        }
        return date( 'Y-m-d H:i:s', $timestamp );
    }
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
    class WP_REST_Request {
        /** @var array<string, string> */
        private array $headers = array();

        /** @var array<string, mixed> */
        private array $params = array();

        /** @var array<string, mixed> */
        private array $json_params = array();

        public function __construct( string $method = 'GET', string $route = '' ) {
            unset( $method, $route );
        }

        public function set_header( string $key, string $value ): void {
            $this->headers[ $key ] = $value;
        }

        /** @param mixed $value */
        public function set_param( string $key, $value ): void {
            $this->params[ $key ] = $value;
        }

        /** @param array<string, mixed> $params */
        public function set_json_params( array $params ): void {
            $this->json_params = $params;
        }

        /** @return string|array<int, string>|null */
        public function get_header( string $key ) {
            return $this->headers[ $key ] ?? null;
        }

        /** @return mixed */
        public function get_param( string $key ) {
            return $this->params[ $key ] ?? null;
        }

        /** @return array<string, mixed> */
        public function get_params(): array {
            return $this->params;
        }

        /** @return array<string, mixed> */
        public function get_json_params(): array {
            return $this->json_params;
        }
    }
}

if ( ! function_exists( 'rest_ensure_response' ) ) {
    /**
     * @param mixed $data
     * @return mixed
     */
    function rest_ensure_response( $data ) {
        return $data;
    }
}

if ( ! function_exists( 'sanitize_title' ) ) {
    function sanitize_title( string $title ): string {
        $title = strtolower( trim( $title ) );
        return (string) preg_replace( '/[^a-z0-9]+/', '-', $title );
    }
}

if ( ! function_exists( 'get_term_meta' ) ) {
    /**
     * @return mixed
     */
    function get_term_meta( int $term_id, string $key, bool $single = true ) {
        $k = $term_id . '|' . $key;
        if ( isset( $GLOBALS['mrt_test_term_meta'][ $k ] ) ) {
            return $GLOBALS['mrt_test_term_meta'][ $k ];
        }
        return $single ? '' : array();
    }
}

if ( ! function_exists( 'update_term_meta' ) ) {
    function update_term_meta( int $term_id, string $key, $value ): bool {
        if ( ! isset( $GLOBALS['mrt_test_term_meta'] ) || ! is_array( $GLOBALS['mrt_test_term_meta'] ) ) {
            $GLOBALS['mrt_test_term_meta'] = array();
        }
        $GLOBALS['mrt_test_term_meta'][ $term_id . '|' . $key ] = $value;
        return true;
    }
}

if ( ! function_exists( 'delete_term_meta' ) ) {
    function delete_term_meta( int $term_id, string $key ): bool {
        unset( $GLOBALS['mrt_test_term_meta'][ $term_id . '|' . $key ] );
        return true;
    }
}

if ( ! function_exists( 'update_post_meta' ) ) {
    function update_post_meta( int $post_id, string $key, $value ): bool {
        if ( ! isset( $GLOBALS['mrt_test_post_meta'] ) || ! is_array( $GLOBALS['mrt_test_post_meta'] ) ) {
            $GLOBALS['mrt_test_post_meta'] = array();
        }
        $GLOBALS['mrt_test_post_meta'][ (int) $post_id . '|' . $key ] = $value;
        return true;
    }
}

if ( ! function_exists( 'delete_post_meta' ) ) {
    function delete_post_meta( int $post_id, string $key ): bool {
        unset( $GLOBALS['mrt_test_post_meta'][ (int) $post_id . '|' . $key ] );
        return true;
    }
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
    function sanitize_textarea_field( string $str ): string {
        return trim( wp_strip_all_tags( $str ) );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( string $text ): string {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_attr__' ) ) {
    function esc_attr__( string $text, string $domain = 'default' ): string {
        unset( $domain );
        return esc_attr( $text );
    }
}

if ( ! function_exists( 'esc_html__' ) ) {
    function esc_html__( string $text, string $domain = 'default' ): string {
        unset( $domain );
        return esc_html( $text );
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( string $text ): string {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( string $url ): string {
        return $url;
    }
}

if ( ! class_exists( 'WP_Query' ) ) {
    class WP_Query {
        /** @var array<int, mixed> */
        public array $posts = array();

        /**
         * @param array<string, mixed>|null $args
         */
        public function __construct( $args = null ) {
            if ( isset( $GLOBALS['mrt_test_wp_query_posts'] ) && is_array( $GLOBALS['mrt_test_wp_query_posts'] ) ) {
                $this->posts = $GLOBALS['mrt_test_wp_query_posts'];
                return;
            }
            if ( isset( $GLOBALS['mrt_test_get_posts'] ) && is_callable( $GLOBALS['mrt_test_get_posts'] ) ) {
                $this->posts = $GLOBALS['mrt_test_get_posts']( is_array( $args ) ? $args : array() );
                return;
            }
            $this->posts = array();
        }
    }
}
