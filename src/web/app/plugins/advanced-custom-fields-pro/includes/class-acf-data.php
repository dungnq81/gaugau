<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (! class_exists('ACF_Data')) :
    #[AllowDynamicProperties]
    class ACF_Data
    {
        /** @var string Unique identifier. */
        public $cid = '';

        /** @var array Storage for data. */
        public $data = [];

        /** @var array Storage for data aliases. */
        public $aliases = [];

        /** @var boolean Enables unique data per site. */
        public $multisite = false;

        /**
         * __construct
         *
         * Sets up the class functionality.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param array $data Optional data to set.
         *
         * @return void
         */
        public function __construct($data = false)
        {
            // Set cid.
            $this->cid = acf_uniqid();

            // Set data.
            if ($data) {
                $this->set($data);
            }

            // Initialize.
            $this->initialize();
        }

        /**
         * initialize
         *
         * Called during constructor to setup class functionality.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param   void
         *
         * @return void
         */
        public function initialize()
        {
            // Do nothing.
        }

        /**
         * prop
         *
         * Sets a property for the given name and returns $this for chaining.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param (string|array) $name The data name or an array of data.
         * @param mixed $value The data value.
         *
         * @return ACF_Data
         */
        public function prop($name = '', $value = null)
        {
            // Update property.
            $this->{$name} = $value;

            // Return this for chaining.
            return $this;
        }

        /**
         * _key
         *
         * Returns a key for the given name allowing aliasses to work.
         *
         * @date    18/1/19
         *
         * @since   5.7.10
         *
         * @param type $var Description. Default.
         * @param mixed $name
         *
         * @return type Description.
         */
        public function _key($name = '')
        {
            return isset($this->aliases[ $name ]) ? $this->aliases[ $name ] : $name;
        }

        /**
         * has
         *
         * Returns true if this has data for the given name.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param string $name The data name.
         *
         * @return boolean
         */
        public function has($name = '')
        {
            $key = $this->_key($name);

            return isset($this->data[ $key ]);
        }

        /**
         * is
         *
         * Similar to has() but does not check aliases.
         *
         * @date    7/2/19
         *
         * @since   5.7.11
         *
         * @param type $var Description. Default.
         * @param mixed $key
         *
         * @return type Description.
         */
        public function is($key = '')
        {
            return isset($this->data[ $key ]);
        }

        /**
         * get
         *
         * Returns data for the given name of null if doesn't exist.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param string $name The data name.
         *
         * @return mixed
         */
        public function get($name = false)
        {
            // Get all.
            if ($name === false) {
                return $this->data;

                // Get specific.
            } else {
                $key = $this->_key($name);

                return isset($this->data[ $key ]) ? $this->data[ $key ] : null;
            }
        }

        /**
         * get_data
         *
         * Returns an array of all data.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param   void
         *
         * @return array
         */
        public function get_data()
        {
            return $this->data;
        }

        /**
         * set
         *
         * Sets data for the given name and returns $this for chaining.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param (string|array) $name The data name or an array of data.
         * @param mixed $value The data value.
         *
         * @return ACF_Data
         */
        public function set($name = '', $value = null)
        {
            // Set multiple.
            if (is_array($name)) {
                $this->data = array_merge($this->data, $name);

                // Set single.
            } else {
                $this->data[ $name ] = $value;
            }

            // Return this for chaining.
            return $this;
        }

        /**
         * append
         *
         * Appends data for the given name and returns $this for chaining.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param mixed $value The data value.
         *
         * @return ACF_Data
         */
        public function append($value = null)
        {
            // Append.
            $this->data[] = $value;

            // Return this for chaining.
            return $this;
        }

        /**
         * remove
         *
         * Removes data for the given name.
         *
         * @date    9/1/19
         *
         * @since   5.7.10
         *
         * @param string $name The data name.
         *
         * @return ACF_Data
         */
        public function remove($name = '')
        {
            // Remove data.
            unset($this->data[ $name ]);

            // Return this for chaining.
            return $this;
        }

        /**
         * reset
         *
         * Resets the data.
         *
         * @date    22/1/19
         *
         * @since   5.7.10
         *
         * @param   void
         *
         * @return void
         */
        public function reset()
        {
            $this->data    = [];
            $this->aliases = [];
        }

        /**
         * count
         *
         * Returns the data count.
         *
         * @date    23/1/19
         *
         * @since   5.7.10
         *
         * @param   void
         *
         * @return integer
         */
        public function count()
        {
            return count($this->data);
        }

        /**
         * query
         *
         * Returns a filtered array of data based on the set of key => value arguments.
         *
         * @date    23/1/19
         *
         * @since   5.7.10
         *
         * @param   void
         * @param mixed $args
         * @param mixed $operator
         *
         * @return integer
         */
        public function query($args, $operator = 'AND')
        {
            return wp_list_filter($this->data, $args, $operator);
        }

        /**
         * alias
         *
         * Sets an alias for the given name allowing data to be found via multiple identifiers.
         *
         * @date    18/1/19
         *
         * @since   5.7.10
         *
         * @param type $var Description. Default.
         * @param mixed $name
         *
         * @return type Description.
         */
        public function alias($name = '' /*, $alias, $alias2, etc */)
        {
            // Get all aliases.
            $args = func_get_args();
            array_shift($args);

            // Loop over aliases and add to data.
            foreach ($args as $alias) {
                $this->aliases[ $alias ] = $name;
            }

            // Return this for chaining.
            return $this;
        }

        /**
         * switch_site
         *
         * Triggered when switching between sites on a multisite installation.
         *
         * @date    13/2/19
         *
         * @since   5.7.11
         *
         * @param integer $site_id New blog ID.
         * @param   int prev_blog_id Prev blog ID.
         * @param mixed $prev_site_id
         *
         * @return void
         */
        public function switch_site($site_id, $prev_site_id)
        {
            // Bail early if not multisite compatible.
            if (! $this->multisite) {
                return;
            }

            // Bail early if no change in blog ID.
            if ($site_id === $prev_site_id) {
                return;
            }

            // Create storage.
            if (! isset($this->site_data)) {
                $this->site_data    = [];
                $this->site_aliases = [];
            }

            // Save state.
            $this->site_data[ $prev_site_id ]    = $this->data;
            $this->site_aliases[ $prev_site_id ] = $this->aliases;

            // Reset state.
            $this->data    = [];
            $this->aliases = [];

            // Load state.
            if (isset($this->site_data[ $site_id ])) {
                $this->data    = $this->site_data[ $site_id ];
                $this->aliases = $this->site_aliases[ $site_id ];
                unset($this->site_data[ $site_id ]);
                unset($this->site_aliases[ $site_id ]);
            }
        }
    }

endif; // class_exists check
