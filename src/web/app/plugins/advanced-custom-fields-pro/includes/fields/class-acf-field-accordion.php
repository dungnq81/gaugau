<?php

if (! class_exists('acf_field__accordion')) :

    class acf_field__accordion extends acf_field
    {
        public $show_in_rest = false;

        /**
         * initialize
         *
         * This function will setup the field type data
         *
         * @date  30/10/17
         *
         * @since 5.6.3
         *
         * @param  n/a
         *
         * @return n/a
         */
        public function initialize()
        {
            // vars
            $this->name          = 'accordion';
            $this->label         = __('Accordion', 'acf');
            $this->category      = 'layout';
            $this->description   = __('Allows you to group and organize custom fields into collapsable panels that are shown while editing content. Useful for keeping large datasets tidy.', 'acf');
            $this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-accordion.png';
            $this->doc_url       = acf_add_url_utm_tags('https://www.advancedcustomfields.com/resources/accordion/', 'docs', 'field-type-selection');
            $this->supports      = [
                'required' => false,
                'bindings' => false,
            ];
            $this->defaults      = [
                'open'         => 0,
                'multi_expand' => 0,
                'endpoint'     => 0,
            ];
        }

        /**
         * render_field
         *
         * Create the HTML interface for your field
         *
         * @date  30/10/17
         *
         * @since 5.6.3
         *
         * @param array $field
         *
         * @return n/a
         */
        public function render_field($field)
        {
            // vars
            $atts = [
                'class'             => 'acf-fields',
                'data-open'         => $field['open'],
                'data-multi_expand' => $field['multi_expand'],
                'data-endpoint'     => $field['endpoint'],
            ];

            ?>
		<div <?php echo acf_esc_attrs($atts); ?>></div>
			<?php
        }

        /**
         * Create extra options for your field. This is rendered when editing a field.
         * The value of $field['name'] can be used (like bellow) to save extra data to the $field
         *
         * @param $field - an array holding all the field's data
         *
         * @type action
         *
         * @since   3.6
         *
         * @date    23/01/13
         */
        public function render_field_settings($field)
        {
            acf_render_field_setting(
                $field,
                [
                    'label'        => __('Open', 'acf'),
                    'instructions' => __('Display this accordion as open on page load.', 'acf'),
                    'name'         => 'open',
                    'type'         => 'true_false',
                    'ui'           => 1,
                ]
            );

            acf_render_field_setting(
                $field,
                [
                    'label'        => __('Multi-Expand', 'acf'),
                    'instructions' => __('Allow this accordion to open without closing others.', 'acf'),
                    'name'         => 'multi_expand',
                    'type'         => 'true_false',
                    'ui'           => 1,
                ]
            );

            acf_render_field_setting(
                $field,
                [
                    'label'        => __('Endpoint', 'acf'),
                    'instructions' => __('Define an endpoint for the previous accordion to stop. This accordion will not be visible.', 'acf'),
                    'name'         => 'endpoint',
                    'type'         => 'true_false',
                    'ui'           => 1,
                ]
            );
        }

        /**
         * This filter is appied to the $field after it is loaded from the database
         *
         * @type filter
         *
         * @since   3.6
         *
         * @date    23/01/13
         *
         * @param $field - the field array holding all the field options
         *
         * @return $field - the field array holding all the field options
         */
        public function load_field($field)
        {
            // remove name to avoid caching issue
            $field['name'] = '';

            // remove required to avoid JS issues
            $field['required'] = 0;

            // set value other than 'null' to avoid ACF loading / caching issue
            $field['value'] = false;

            // return
            return $field;
        }
    }


    // initialize
    acf_register_field_type('acf_field__accordion');
endif; // class_exists check

?>
