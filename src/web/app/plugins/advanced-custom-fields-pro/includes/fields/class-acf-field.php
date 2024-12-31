<?php

if (! class_exists('acf_field')) :
    #[AllowDynamicProperties]
    class acf_field
    {
        // field information properties.
        public $name          = '';

        public $label         = '';

        public $category      = 'basic';

        public $description   = '';

        public $doc_url       = false;

        public $tutorial_url  = false;

        public $preview_image = false;

        public $pro           = false;

        public $defaults      = [];

        public $l10n          = [];

        public $public        = true;

        public $show_in_rest  = true;

        public $supports      = [
            'escaping_html' => false, // Set true when a field handles its own HTML escaping in format_value
            'required'      => true,
        ];

        /**
         * Initializes the `acf_field` class. To initialize a field type that is
         * extending this class, use the `initialize()` method in the child class instead.
         *
         * @since 5.0.0
         */
        public function __construct()
        {
            // Initialize the field type.
            $this->initialize();

            // Register info about the field type.
            acf_register_field_type_info(
                [
                    'label'         => $this->label,
                    'name'          => $this->name,
                    'category'      => $this->category,
                    'description'   => $this->description,
                    'doc_url'       => $this->doc_url,
                    'tutorial_url'  => $this->tutorial_url,
                    'preview_image' => $this->preview_image,
                    'pro'           => $this->pro,
                    'public'        => $this->public,
                ]
            );

            // value
            $this->add_field_filter('acf/load_value', [$this, 'load_value'], 10, 3);
            $this->add_field_filter('acf/update_value', [$this, 'update_value'], 10, 3);
            $this->add_field_filter('acf/format_value', [$this, 'format_value'], 10, 4);
            $this->add_field_filter('acf/validate_value', [$this, 'validate_value'], 10, 4);
            $this->add_field_action('acf/delete_value', [$this, 'delete_value'], 10, 3);

            // field
            $this->add_field_filter('acf/validate_rest_value', [$this, 'validate_rest_value'], 10, 3);
            $this->add_field_filter('acf/validate_field', [$this, 'validate_field'], 10, 1);
            $this->add_field_filter('acf/load_field', [$this, 'load_field'], 10, 1);
            $this->add_field_filter('acf/update_field', [$this, 'update_field'], 10, 1);
            $this->add_field_filter('acf/duplicate_field', [$this, 'duplicate_field'], 10, 1);
            $this->add_field_action('acf/delete_field', [$this, 'delete_field'], 10, 1);
            $this->add_field_action('acf/render_field', [$this, 'render_field'], 9, 1);
            $this->add_field_action('acf/render_field_settings', [$this, 'render_field_settings'], 9, 1);
            $this->add_field_filter('acf/prepare_field', [$this, 'prepare_field'], 10, 1);
            $this->add_field_filter('acf/translate_field', [$this, 'translate_field'], 10, 1);

            // input actions
            $this->add_action('acf/input/admin_enqueue_scripts', [$this, 'input_admin_enqueue_scripts'], 10, 0);
            $this->add_action('acf/input/admin_head', [$this, 'input_admin_head'], 10, 0);
            $this->add_action('acf/input/form_data', [$this, 'input_form_data'], 10, 1);
            $this->add_filter('acf/input/admin_l10n', [$this, 'input_admin_l10n'], 10, 1);
            $this->add_action('acf/input/admin_footer', [$this, 'input_admin_footer'], 10, 1);

            // field group actions
            $this->add_action('acf/field_group/admin_enqueue_scripts', [$this, 'field_group_admin_enqueue_scripts'], 10, 0);
            $this->add_action('acf/field_group/admin_head', [$this, 'field_group_admin_head'], 10, 0);
            $this->add_action('acf/field_group/admin_footer', [$this, 'field_group_admin_footer'], 10, 0);

            // Add field global settings configurable by supports on specific field types.
            $this->add_field_action('acf/field_group/render_field_settings_tab/validation', [$this, 'render_required_setting'], 5);
            $this->add_field_action('acf/field_group/render_field_settings_tab/presentation', [$this, 'render_bindings_setting'], 5);

            foreach (acf_get_combined_field_type_settings_tabs() as $tab_key => $tab_label) {
                $this->add_field_action("acf/field_group/render_field_settings_tab/{$tab_key}", [$this, "render_field_{$tab_key}_settings"], 9, 1);
            }
        }

        /**
         * Initializes the field type. Overridden in child classes.
         *
         * @since 5.6.0
         */
        public function initialize()
        {
            /* do nothing */
        }

        /**
         * Checks a function `is_callable()` before adding the filter, since
         * classes that extend `acf_field` might not implement all filters.
         *
         * @since 5.0.0
         *
         * @param string $tag The name of the filter to add the callback to.
         * @param string $function_to_add The callback to be run when the filter is applied.
         * @param integer $priority The priority to add the filter on.
         * @param integer $accepted_args The number of args to pass to the function.
         *
         * @return void
         */
        public function add_filter($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1)
        {
            // Bail early if not callable.
            if (! is_callable($function_to_add)) {
                return;
            }

            add_filter($tag, $function_to_add, $priority, $accepted_args);
        }

        /**
         * Adds a filter specific to the current field type.
         *
         * @since 5.4.0
         *
         * @param string $tag The name of the filter to add the callback to.
         * @param string $function_to_add The callback to be run when the filter is applied.
         * @param integer $priority The priority to add the filter on.
         * @param integer $accepted_args The number of args to pass to the function.
         *
         * @return void
         */
        public function add_field_filter($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1)
        {
            // Append the field type name to the tag before adding the filter.
            $tag .= '/type=' . $this->name;
            $this->add_filter($tag, $function_to_add, $priority, $accepted_args);
        }

        /**
         * Checks a function `is_callable()` before adding the action, since
         * classes that extend `acf_field` might not implement all actions.
         *
         * @since 5.0.0
         *
         * @param string $tag The name of the action to add the callback to.
         * @param string $function_to_add The callback to be run when the action is ran.
         * @param integer $priority The priority to add the action on.
         * @param integer $accepted_args The number of args to pass to the function.
         *
         * @return void
         */
        public function add_action($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1)
        {
            // Bail early if not callable
            if (! is_callable($function_to_add)) {
                return;
            }

            add_action($tag, $function_to_add, $priority, $accepted_args);
        }

        /**
         * Adds an action specific to the current field type.
         *
         * @since 5.4.0
         *
         * @param string $tag The name of the action to add the callback to.
         * @param string $function_to_add The callback to be run when the action is ran.
         * @param integer $priority The priority to add the action on.
         * @param integer $accepted_args The number of args to pass to the function.
         *
         * @return void
         */
        public function add_field_action($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1)
        {
            // Append the field type name to the tag before adding the action.
            $tag .= '/type=' . $this->name;
            $this->add_action($tag, $function_to_add, $priority, $accepted_args);
        }

        /**
         * Appends default settings to a field.
         * Runs on `acf/validate_field/type={$this->name}`.
         *
         * @since 3.6
         *
         * @param array $field The field array.
         *
         * @return array $field
         */
        public function validate_field($field)
        {
            // Bail early if no defaults.
            if (! is_array($this->defaults)) {
                return $field;
            }

            // Merge in defaults but keep order of $field keys.
            foreach ($this->defaults as $k => $v) {
                if (! isset($field[ $k ])) {
                    $field[ $k ] = $v;
                }
            }

            return $field;
        }

        /**
         * Append l10n text translations to an array which is later passed to JS.
         * Runs on `acf/input/admin_l10n`.
         *
         * @since 3.6
         *
         * @param array $l10n
         *
         * @return array $l10n
         */
        public function input_admin_l10n($l10n)
        {
            // Bail early if no defaults.
            if (empty($this->l10n)) {
                return $l10n;
            }

            // Append.
            $l10n[ $this->name ] = $this->l10n;

            return $l10n;
        }

        /**
         * Add additional validation for fields being updated via the REST API.
         *
         * @param boolean $valid The current validity booleean
         * @param integer $value The value of the field
         * @param array $field The field array
         *
         * @return boolean|WP_Error
         */
        public function validate_rest_value($valid, $value, $field)
        {
            return $valid;
        }

        /**
         * Return the schema array for the REST API.
         *
         * @param array $field
         *
         * @return array
         */
        public function get_rest_schema(array $field)
        {
            $schema = [
                'type'     => ['string', 'null'],
                'required' => ! empty($field['required']),
            ];

            if (isset($field['default_value']) && '' !== $field['default_value']) {
                $schema['default'] = $field['default_value'];
            }

            return $schema;
        }

        /**
         * Return an array of links for addition to the REST API response. Each link is an array and must have both `rel` and
         * `href` keys. The `href` key must be a REST API resource URL. If a link is marked as `embeddable`, the `_embed` URL
         * parameter will trigger WordPress to dispatch an internal sub request and load the object within the same request
         * under the `_embedded` response property.
         *
         * e.g;
         *   [
         *       [
         *           'rel' => 'acf:post',
         *           'href' => 'https://example.com/wp-json/wp/v2/posts/497',
         *           'embeddable' => true,
         *       ],
         *       [
         *           'rel' => 'acf:user',
         *           'href' => 'https://example.com/wp-json/wp/v2/users/2',
         *           'embeddable' => true,
         *       ],
         *   ]
         *
         * @param mixed $value The raw (unformatted) field value.
         * @param string|integer $post_id
         * @param array $field
         *
         * @return array
         */
        public function get_rest_links($value, $post_id, array $field)
        {
            return [];
        }

        /**
         * Apply basic formatting to prepare the value for default REST output.
         *
         * @param mixed $value
         * @param string|integer $post_id
         * @param array $field
         *
         * @return mixed
         */
        public function format_value_for_rest($value, $post_id, array $field)
        {
            return $value;
        }

        /**
         * Renders the "Required" setting on the field type "Validation" settings tab.
         *
         * @since 6.2.5
         *
         * @param array $field The field type being rendered.
         *
         * @return void
         */
        public function render_required_setting($field)
        {
            $supports_required = acf_field_type_supports($field['type'], 'required', true);

            // Only prevent rendering if explicitly disabled.
            if (! $supports_required) {
                return;
            }

            acf_render_field_setting(
                $field,
                [
                    'label'        => __('Required', 'acf'),
                    'instructions' => '',
                    'type'         => 'true_false',
                    'name'         => 'required',
                    'ui'           => 1,
                    'class'        => 'field-required',
                ],
                true
            );
        }

        /**
         * Renders the "Allow in Bindings" setting on the field type "Presentation" settings tab.
         *
         * @since 6.3.6
         *
         * @param array $field The field type being rendered.
         *
         * @return void
         */
        public function render_bindings_setting($field)
        {
            $supports_bindings = acf_field_type_supports($field['type'], 'bindings', true);

            // Only prevent rendering if explicitly disabled.
            if (! $supports_bindings) {
                return;
            }

            /* translators: %s A "Learn More" link to documentation explaining the setting further. */
            $binding_string       = esc_html__('Allow content editors to access and display the field value in the editor UI using Block Bindings or the ACF Shortcode. %s', 'acf');
            $binding_url          = '<a target="_blank" href="' . acf_add_url_utm_tags('https://www.advancedcustomfields.com/resources/bindings-security/', 'docs', 'field-settings') . '">' . esc_html__('Learn more.', 'acf') . '</a>';
            $binding_instructions = sprintf(
                $binding_string,
                $binding_url
            );

            // This field setting has a unique behaviour. If the value isn't defined on the field object, it defaults to true, but for new fields, it defaults to off.
            if (! isset($field['allow_in_bindings'])) {
                if (empty($field['ID'])) {
                    $field['allow_in_bindings'] = false;
                } else {
                    $field['allow_in_bindings'] = true;
                }
            }

            acf_render_field_setting(
                $field,
                [
                    'label'        => __('Allow Access to Value in Editor UI', 'acf'),
                    'instructions' => $binding_instructions,
                    'type'         => 'true_false',
                    'name'         => 'allow_in_bindings',
                    'ui'           => 1,
                    'class'        => 'field-show-in-bindings',
                ],
                true
            );
        }
    }

endif; // class_exists check
