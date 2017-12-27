<?php

/**
 * Class Shortcodes_Custom_Css
 * Responsible for creating custom css for Visual Composer shortcodes
 */
class Shortcodes_Custom_Css {

    /**
     * Order of shortcodes appearing on page so we can generate unique id for shortcode
     * @var int
     */
    private static $order_ids = array();

    /**
     * List of shortcodes to check for in post content
     * @var array
     */
    private static $shortcodes = array();

    /**
     * Generated custom css
     * @var string
     */
    private $transient_key_cache = 'shortcodes_custom_css';

    /**
     * List of shortcodes to check into post content.
     * It is cached self::$shortcodes to check for changes and regenerate cache
     * @var string
     */
    private $transient_key_shortcodes = 'shortcodes_custom_css_shordcodes';

    /**
     * Post edit time saved when css is generated.
     * Use to compare if page is changed to regenerate cache
     * @var string
     */
    private $transient_key_edit_time = 'shortcodes_custom_css_edit_time';

    /**
     * String to store instead of generated css to skip checking page for shortcodes.
     * Used if page at first has no shortcodes we are listing in self::$shortcodes
     * @var string
     */
    private $skip_string = 'skip';

    /**
     * Current $post object id
     * @var int
     */
    private $post_id;

    /**
     * Current $post object meta
     * @var array
     */
    private $post_meta;

    /**
     * Shortcodes_Custom_Css constructor.
     *
     * @hooked to wp_head to add page check
     */
    public function __construct() {
        add_action( 'wp_head', array( $this, 'custom_css' ), 1500 );
    }

    /**
     * Static function used to add shortcodes tags to list to check on page.
     * This function is used in after vc_map for shortcode is done
     * to alert this script to check for that shortcode
     * @param array $tag
     */
    public static function add_to_queue( $tag ) {
        self::$shortcodes = array_merge( self::$shortcodes, $tag );
    }

    /**
     * Check if css is cached and validate there are no changes.
     * Echo custom styles in head if listed shortcodes are used on page and
     * shortcode params are used in required combination
     * @return string|void
     */
    public function custom_css() {
        
        /**
         * Check only on post types
         */
        if ( ! is_singular() ) {
            return;
        }

        /**
         * Check if any VC shortcode added to queue
         */
        if ( empty( self::$shortcodes ) ) {
            return;
        }

        global $post;

        /**
         * Check if page is using Visual Composer
         */
        if ( ! $post && ! preg_match( '/vc_row/', $post->post_content ) ) {
            return;
        }

        $this->post_id = $post->ID;
        $this->post_meta = get_post_meta( $post->ID );

        // Post types such as product that does not have meta _edit_lock
        if ( ! isset( $this->post_meta['_edit_lock'] ) ) {
            return;
        }

        // Get css from cache if exists
        $css = $this->get_cache( 'css' );

        /**
         * If any cache, validate that no changes are made
         *
         * Please keep this order in condition below
         */
        if ( ! empty( $css ) ) {

            if (
                self::$shortcodes  !== $this->get_cache( 'shortcodes' ) // Check if we changed map shortcodes list queue
                ||  $this->post_meta['_edit_lock'] !== $this->get_cache( 'edit_time' ) // Check if user edited the post
            ) {
                $css = '';
            }

        }

        /**
         * If up to here we still have no string in cache - regenerate it
         */
        if ( empty( $css ) ) {

            /**
             * Generate custom css for listed shortcodes
             */
            $css = $this->generate_custom_css();

        }

        /**
         * If page is not using listed shortcodes and is using Visual Composer,
         * "skip" string will be returned and cached to skip check until changes are made
         */
        if ( $this->skip_string === $css || empty( $css ) ) {
            return;
        }

        /**
         * Echo styles in head with very late priority
         */
        echo $css;

    }

    /**
     * Generate custom css from listed shortcodes if they are used on page
     * and their params are in right combination.
     * @return string
     */
    public function generate_custom_css() {

        global $post;

        /**
         * Start parsing
         */
        $css = array();

        /**
         * If this variable is true at the end of function, than page has no listed shortcodes
         */
        $page_has_shortcodes = false;

        /**
         * Parse post content for every shortcode added to self::$shortcodes
         */
        foreach ( self::$shortcodes as $shortcode_tag => $shortcode_params ) {

            /**
             * Check if page has any of listed shortcodes
             */
            if ( ! has_shortcode( $post->post_content, $shortcode_tag ) ) {
                continue;
            } else {
                // If page has at least one shortcodes we are searching
                $page_has_shortcodes = true;
            }

            /**
             * Get shortcode params
             */
            //$shortcode_info = vc_get_shortcode( $shortcode_tag );

            /**
             * Parse all shortcode instances on page
             */
            $matches = array();
            preg_match_all( '/' . get_shortcode_regex( array( $shortcode_tag ) ) . '/', $post->post_content, $matches, PREG_SET_ORDER );

            $parsed_shortcodes = array();
            foreach ($matches as $match) {
                $parsed_shortcodes[] = $match[0];
            }

            /**
             * Start parsing params and generating custom css for each marked param
             */
            foreach ( $parsed_shortcodes as $shortcode_order_id => $parse_shortcode ) {

                // We need unique class to target only one shortcode of all instances
                $unique_css_class = '.' . self::generate_shortcode_class( $shortcode_tag, __CLASS__ );

                /**
                 * Now when we have shortcode info and map params lets separate just map params that have 'shortcode_element'
                 */
                foreach ( $shortcode_params as $param ) {

                    if ( ! isset( $param['shortcode_element'] ) && empty( $param['shortcode_element'] ) ) {
                        continue;
                    }

                    /**
                     * Get param value
                     */
                    $attr = '';
                    preg_match( '/' . $param['param_name'] . '="(.*?)"/', $parse_shortcode, $attr );

                    if ( ! isset( $attr[1] ) || empty( $attr[1] ) ) {
                        continue;
                    }

                    // If user set alpha param convert value to rgba
                    $attr[1] = isset( $param['alpha'] ) && !empty( $param['alpha'] ) ? $this->hex2rgba( $attr[1], $param['alpha'] ) : $attr[1];
                    // If usr set important param, add it to value
                    $value = isset( $param['important'] ) && $param['important'] ? $attr[1] . ' !important' : $attr[1];
                    // If user set shortcode class sufix
                    $sufix = isset( $param['shortcode_class_sufix'] ) && ! empty( $param['shortcode_class_sufix'] ) ? $param['shortcode_class_sufix'] : '';

                    /**
                     * Generate custom css for that param for this instance of shortcode
                     */
                    $css[] = $unique_css_class . sanitize_text_field( $sufix ) . ' ' . sanitize_text_field( $param['shortcode_element'] ) . '{' . sanitize_text_field( $param['property'] ) . ':' . esc_attr( $value ) . ';}';

                }

            }

        }

        /**
         * Set some content for the page meta to avoid checking for shortcodes until expired time
         *
         * We check only if cache is empty so this will disable regex parsing until new changes or transient expired
         *
         * Condition is true if no listed shortcodes on page or no params in shortcodes are in right combination to trigger css
         */
        if ( ! $page_has_shortcodes || empty( $css ) ) {
            // Set "skip" so we can check
            $this->set_cache( 'css', $this->skip_string );
            $this->set_cache( 'shortcodes', self::$shortcodes );
            $this->set_cache( 'edit_time', $this->post_meta['_edit_lock'] );
            return $this->skip_string;
        }

        /**
         * Generate custom css style and cache all
         */
        $parsed_css = '<style type="text/css" data-type="shortcodes-custom-css">' . $this->compress_css( implode( ' ', $css ) ) . '</style>';
        $this->set_cache( 'css', $parsed_css );
        $this->set_cache( 'shortcodes', self::$shortcodes );
        $this->set_cache( 'edit_time', $this->post_meta['_edit_lock'] );

        return $parsed_css;

    }

    /**
     * Generate shortcode unique class based on:
     * - Shortcode tag
     * - Shortcode class
     * - Shortcode order on page while parsing
     *
     * @param string $tag Shortcode tag
     * @param string $class Shortcode class
     * @return string
     */
    public static function generate_shortcode_class( $tag, $class ) {

        $key = $class . '_' . $tag;

        if ( ! isset( self::$order_ids[ $key ] ) ) {
            self::$order_ids[ $key ] = 0;
        } else {
            self::$order_ids[ $key ]++;
        }

        return sanitize_text_field( $tag . '--' . self::$order_ids[ $key ] );
    }

    /**
     * Compress css
     * @param string $css
     * @return string
     */
    private function compress_css( $css ) {
        /* remove comments */
        $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

        /* remove tabs, spaces, newlines, etc. */
        $css = str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css );

        return $css;
    }



    /**
     * Set post type meta to use as cache per page
     * @param string $type Type of meta data
     * @param mixed $value
     */
    private function set_cache( $type, $value ) {

        switch ( $type ) {

            case 'css':
                update_post_meta( $this->post_id, $this->transient_key_cache, $value );
                break;

            case 'shortcodes':
                update_post_meta( $this->post_id, $this->transient_key_shortcodes, $value );
                break;

            case 'edit_time':
                update_post_meta( $this->post_id, $this->transient_key_edit_time, $value );
                break;
        }

    }

    /**
     * Get post type meta
     * @param string $type Type of meta data
     * @return mixed
     */
    private function get_cache( $type ) {

        switch ( $type ) {

            case 'css':
                return get_post_meta( $this->post_id, $this->transient_key_cache, true );
                break;

            case 'shortcodes':
                return get_post_meta( $this->post_id, $this->transient_key_shortcodes, true );
                break;

            case 'edit_time':
                return get_post_meta( $this->post_id, $this->transient_key_edit_time, true );
                break;
        }

    }
    
    /**
     * Revert hex color to rgba color
     *
     * @param $color - hexa color, $opacity - opacity for alhpa
     */
    private function hex2rgba($color, $opacity = false) {

        $default = 'rgb(0,0,0)';

        //Return default if no color provided
        if(empty($color))
            return $default;

        //Sanitize $color if "#" is provided
        if ($color[0] == '#' ) {
            $color = substr( $color, 1 );
        }

        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        //Check if opacity is set(rgba or rgb)
        if($opacity){
            if(abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
            $output = 'rgb('.implode(",",$rgb).')';
        }

        //Return rgb(a) color string
        return $output;
    }

    /**
     * TODO remove all meta data on theme uninstal
     */
    private function remove_all_cache() {

        delete_post_meta( $this->post_id, $this->transient_key_cache );
        delete_transient( $this->post_id, $this->transient_key_shortcodes );
        delete_transient( $this->post_id, $this->transient_key_edit_time );

    }

}
new Shortcodes_Custom_Css();