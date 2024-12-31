<?php

namespace Addons\Optimizer\Lazy_Load;

\defined('ABSPATH') || die;

/**
 * @author SiteGround
 * Modified by HD Team
 */
abstract class Abstract_Lazy_Load
{
    /**
     * Regex for class matching.
     *
     * @var string
     */
    public string $regex_classes = '/class=["\'](.*?)["\']/is';

    public string $regexp;

    public string $regex_replaced;

    public array $patterns;

    public array $replacements;

    /** ---------------------------------------- */

    /**
     * Add class-name to the html element.
     *
     * @param $element
     *
     * @return string|array
     */
    abstract public function add_lazyload_class($element): string|array;

    /** ---------------------------------------- */

    /**
     * @param $content
     *
     * @return bool
     */
    public function should_process($content): bool
    {
        return empty($content)               ||
               $this->is_lazy_url_excluded() ||
               is_feed()                     ||
               is_admin()                    ||
               wp_doing_ajax()               ||
               \is_amp_enabled($content);
    }

    /** ---------------------------------------- */

    /**
     * @return bool
     */
    public function is_lazy_url_excluded(): bool
    {
        // Get the urls where a lazy load is excluded.
        $excluded_urls = \filter_setting_options('lazy_load_exclude_urls', []);

        // Bail if no excluding are found, or we do not have a match.
        return ! (empty($excluded_urls) && ! in_array(\get_current_url(), $excluded_urls, false));
    }

    /** ---------------------------------------- */

    /**
     * @param $content
     *
     * @return array|mixed|string|string[]
     */
    public function filter_html($content): mixed
    {
        // Bail if it's feed, ajax, admin, amp... or if the content is empty.
        if ($this->should_process($content)) {
            return $content;
        }

        // Check for items.
        preg_match_all($this->regexp, $content, $matches);

        $search  = [];
        $replace = [];

        // Check for specific asset being excluded.
        $optimizer_options = get_option('optimizer__options');
        $exclude_lazyload  = $optimizer_options['exclude_lazyload'] ?? [];
        $excluded_all      = array_unique(
            array_merge(
                \filter_setting_options('lazy_load_exclude', []),
                $exclude_lazyload
            )
        );

        foreach ($matches[0] as $item) {
            // Skip already replaced item.
            if (preg_match($this->regex_replaced, $item)) {
                continue;
            }

            // Check if we have a filter for excluding specific asset from being lazily loaded.
            if (! empty($excluded_all)) {
                // Match the url of the asset.
                preg_match('~(?:src=")([^"]*)"~', $item, $src_match);

                // If we have a match and the array is part of the excluded assets bail from lazy loading.
                if (! empty($src_match)) {
                    $item_filename = basename($src_match[1]);
                    if (in_array($item_filename, $excluded_all, false)) {
                        continue;
                    }
                }
            }

            // Do some checking if there are any class matches.
            preg_match($this->regex_classes, $item, $class_matches);

            if (! empty($class_matches[1])) {
                $classes = $class_matches[1];

                // Convert all classes to array.
                $item_classes = explode(' ', $class_matches[1]);

                // Check if the item has ignored class and bail if it has.
                if (array_intersect($item_classes, $excluded_all)) {
                    continue;
                }

                $orig_item = str_replace($classes, $classes . ' lazy', $item);
            } else {
                $orig_item = $this->add_lazyload_class($item);
            }

            // Finally, do the search/replace and return modified content.
            $new_item = preg_replace(
                $this->patterns,
                $this->replacements,
                $orig_item
            );

            $search[]  = $item;
            $replace[] = $new_item;
        }

        return str_replace($search, $replace, $content);
    }
}
