<?php

if( !class_exists( 'PIP_Layouts' ) ) {

    /**
     * Class PIP_Layouts
     */
    class PIP_Layouts {

        /**
         * Vars
         */
        var $layout_group_keys = array();

        /*
         * Construct
         */
        public function __construct() {

            // Current Screen
            add_action( 'current_screen', array( $this, 'current_screen' ) );

        }

        /**
         * Current Screen
         */
        public function current_screen() {

            if( !$this->is_layout_screen() ) {
                return;
            }

            $post_type = get_post_type_object( 'acf-field-group' );

            // Change title on flexible edition page
            $post_type->labels->name         = __( 'Layouts', 'pilopress' );
            $post_type->labels->edit_item    = __( 'Edit Layout', 'pilopress' );
            $post_type->labels->add_new_item = __( 'Add New Layout', 'pilopress' );

        }

        /*
         * Is Layout Screen
         */
        public function is_layout_screen() {

            global $typenow;

            if( $typenow !== 'acf-field-group' ) {
                return false;
            }

            $is_layout_list   = acf_is_screen( 'edit-acf-field-group' ) && acf_maybe_get_GET( 'layouts' ) === '1';
            $is_layout_single = acf_is_screen( 'acf-field-group' );

            if( $is_layout_list ) {

                return true;

            } elseif( $is_layout_single ) {

                $is_layout_single_new  = acf_maybe_get_GET( 'layout' ) === '1';
                $is_layout_single_edit = $this->is_layout( acf_maybe_get_GET( 'post' ) );
                $is_layout_single_save = isset( $_REQUEST['acf_field_group']['_pip_is_layout'] );

                if( $is_layout_single_new || $is_layout_single_edit || $is_layout_single_save ) {

                    return true;

                }

            }

            return false;

        }

        /**
         * Is Field Group Layout
         */
        public function is_layout( $post ) {

            $field_group = $post;

            // If ID/Key then Get Field Group
            if( !is_array( $post ) ) {

                $field_group = acf_get_field_group( $post );

            }

            // Field Group not found
            if( !$field_group ) {

                return false;

            }

            // Check Layout setting
            return acf_maybe_get( $field_group, '_pip_is_layout', false );

        }

        /*
         * Get Layouts
         */
        function get_layouts( $filter = false ) {

            $layouts = array();

            // Get field groups
            $field_groups = acf_get_field_groups();

            // If no field group, return
            if( !$field_groups ) {
                return $layouts;
            }

            // Browse all field groups
            foreach ( $field_groups as $field_group ) {

                // If not a layout, skip
                if( !$this->is_layout( $field_group ) ) {
                    continue;
                }

                $layouts[] = $field_group;

            }

            if( $filter ) {

                return wp_list_pluck( $layouts, $filter );

            }

            return $layouts;

        }

        /*
         * Get Layout
         */
        function get_layout( $post ) {

            $field_group = $post;

            // If ID/Key then Get Field Group
            if( !is_array( $post ) ) {

                $field_group = acf_get_field_group( $post );

            }

            // Field Group not found
            if( !$field_group || !$this->is_layout( $field_group ) ) {

                return false;

            }

            return $field_group;

        }

        /**
         * Get all layouts CSS files content
         *
         * @return string
         */
        public function get_layouts_css() {

            $css = '';

            // Get layouts CSS files
            $layouts_css_files = glob( PIP_THEME_LAYOUTS_PATH . '*/*.css' );

            // If no CSS files, return
            if( !$layouts_css_files ) {

                return $css;

            }

            // Store CSS contents
            foreach ( $layouts_css_files as $layouts_css_file ) {

                $css_file = file_get_contents( $layouts_css_file );

                if(!$css_file){
                    continue;
                }

                $css .= $css_file;

            }

            return $css;

        }

        /**
         * Setter: $layout_group_keys
         *
         * @param $layout_group_keys
         *
         * @return void
         */
        public function set_layout_group_keys( $layout_group_keys ) {

            if( $this->layout_group_keys ) {
                $this->layout_group_keys = array_merge( $this->layout_group_keys, $layout_group_keys );
            } else {
                $this->layout_group_keys = $layout_group_keys;
            }
        }

        /**
         * Getter: $layout_group_keys
         *
         * @return array
         */
        public function get_layout_group_keys() {

            return $this->layout_group_keys;
        }

        /**
         * Get layouts by location
         *
         * @param array $args
         *
         * @return array
         */
        public function get_layouts_by_location( array $args ) {

            $layouts = array();

            // Get layout keys
            $layout_keys = $this->get_layout_group_keys();
            if( !$layout_keys ) {
                return $layouts;
            }

            $pip_flexible = acf_get_instance( 'PIP_Flexible' );

            // Browse all layouts
            foreach ( $layout_keys as $layout_key ) {
                $layout = acf_get_field_group( $layout_key );
                if( !isset( $layout['location'] ) ) {
                    continue;
                }

                // Layout not assign to location
                if( !$pip_flexible->get_field_group_visibility( $layout, $args ) ) {
                    continue;
                }

                $layouts[] = $layout;
            }

            return $layouts;
        }

    }

    acf_new_instance( 'PIP_Layouts' );

}

function pip_is_layout_screen() {

    return acf_get_instance( 'PIP_Layouts' )->is_layout_screen();

}

function pip_is_layout( $post ) {

    return acf_get_instance( 'PIP_Layouts' )->is_layout( $post );

}

function pip_get_layouts( $filter = false ) {

    return acf_get_instance( 'PIP_Layouts' )->get_layouts( $filter );

}

function pip_get_layout( $post ) {

    return acf_get_instance( 'PIP_Layouts' )->get_layout( $post );

}

function pip_get_layouts_css() {

    return acf_get_instance( 'PIP_Layouts' )->get_layouts_css();

}
