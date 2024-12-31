<?php

namespace Addons\Security;

\defined('ABSPATH') || die;

/**
 * Class that manages the Readme.html.
 *
 * @author SiteGround Security
 */
final class Readme
{
    /**
     * Check if the file exists in the root directory of the WP Installation
     *
     * @return bool true if the file exists, false otherwise.
     */
    public function readme_exist(): bool
    {
        // Check if the readme.html file exists at the root of the application.
        return file_exists(ABSPATH . 'readme.html');
    }

    // --------------------------------------------------

    /**
     * Remove the readme.html file from the root directory of WP Installation.
     *
     * @return bool true if the file was removed, false otherwise.
     */
    public function delete_readme(): bool
    {
        // Check if the readme.html file exists in the root of the application.
        if (! $this->readme_exist()) {
            return true;
        }

        // Check if file permissions are set accordingly.
        if (600 >= (int) substr(sprintf('%o', fileperms(ABSPATH . 'readme.html')), -3)) {
            return false;
        }

        // Try to remove the file.
        if (@unlink(ABSPATH . 'readme.html') === false) {
            return false;
        }

        return true;
    }
}
