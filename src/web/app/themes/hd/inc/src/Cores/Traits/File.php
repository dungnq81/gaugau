<?php

namespace Cores\Traits;

use Random\RandomException;

\defined('ABSPATH') || die;

trait File
{
	use Base;

	// --------------------------------------------------

	/**
	 * @return bool
	 */
	public static function htAccess(): bool
	{
		global $is_apache;

		if ($is_apache) {
			return true;
		}

		// Check if the custom HTACCESS environment variable is set
		if (isset($_SERVER['HTACCESS']) && $_SERVER['HTACCESS'] === 'on') {
			return true;
		}

		return false;
	}

	// --------------------------------------------------

	/**
	 * @return mixed
	 */
	public static function wpFileSystem(): mixed
	{
		global $wp_filesystem;

		// Initialize the WP filesystem, no more using the 'file-put-contents' function.
		if (empty($wp_filesystem)) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem;
	}

	// --------------------------------------------------

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function fileCreate(string $path): bool
	{
		$wp_filesystem = self::wpFileSystem();

		if (empty($wp_filesystem)) {
			return false;
		}

		// Bail if the file already exists.
		if ($wp_filesystem->is_file($path)) {
			return true;
		}

		// Create the file.
		return $wp_filesystem->touch($path);
	}

	// --------------------------------------------------

	/**
	 * Reads an entire file into a string
	 *
	 * @param string $file Name of the file to read.
	 *
	 * @return null|string Read data on success, null on failure.
	 */
	public static function fileRead(string $file): ?string
	{
		$wp_filesystem = self::wpFileSystem();

		if (empty($wp_filesystem)) {
			return null;
		}

		// Bail if we are unable to create the file.
		if (! self::fileCreate($file)) {
			return null;
		}

		// Read `file`
		return $wp_filesystem->get_contents($file) ?: null;
	}

	// --------------------------------------------------

	/**
	 * Update a file
	 *
	 * @param string $path Full path to the file
	 * @param string $content File content
	 */
	public static function fileUpdate(string $path, string $content = ''): void
	{
		$wp_filesystem = self::wpFileSystem();

		if (empty($wp_filesystem)) {
			return;
		}

		// Bail if we are unable to create the file.
		if (! self::fileCreate($path)) {
			return;
		}

		// Add the new content into the file.
		$wp_filesystem->put_contents($path, $content);
	}

	// --------------------------------------------------

	/**
	 * Lock a file and write something in it.
	 *
	 * @param string $path Path to the file.
	 * @param string $content Content to add.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function doLockWrite(string $path, string $content = ''): bool
	{
		$fp = fopen($path, 'wb+');
		if ($fp === false) {
			return false;
		}

		if (flock($fp, LOCK_EX)) {
			fwrite($fp, $content);
			flock($fp, LOCK_UN);
			fclose($fp);

			return true;
		}

		fclose($fp);

		return false;
	}

	// --------------------------------------------------

	/**
	 * @param string $filename
	 * @param bool $include_dot
	 *
	 * @return string
	 */
	public static function fileExtension(string $filename, bool $include_dot = false): string
	{
		if (empty($filename)) {
			return '';
		}

		$dot = $include_dot ? '.' : '';

		return $dot . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}

	// --------------------------------------------------

	/**
	 * @param string $filename
	 * @param bool $include_ext
	 *
	 * @return string
	 */
	public static function fileName(string $filename, bool $include_ext = false): string
	{
		if (empty($filename)) {
			return '';
		}

		return $include_ext
			? pathinfo($filename, PATHINFO_FILENAME) . self::fileExtension($filename, true)
			: pathinfo($filename, PATHINFO_FILENAME);
	}

	// --------------------------------------------------

	/**
	 * @param string $dirname
	 *
	 * @return bool
	 */
	public static function isEmptyDir(string $dirname): bool
	{
		if (! is_dir($dirname) || ! is_readable($dirname)) {
			return false;
		}

		foreach (scandir($dirname, SCANDIR_SORT_NONE) as $file) {
			if (! in_array($file, ['.', '..', '.svn', '.git'], false)) {
				return false;
			}
		}

		return true;
	}

	// --------------------------------------------------

	/**
	 * @param string $directory
	 *
	 * @return bool
	 */
	public static function createDirectory(string $directory): bool
	{
		if (! is_writable(dirname($directory))) {
			self::errorLog(sprintf('Cannot write to the parent directory: %s.', dirname($directory)));

			return false;
		}

		$is_directory_created = wp_mkdir_p($directory);

		if (! $is_directory_created) {
			self::errorLog(sprintf('Cannot create directory: %s.', $directory));
		}

		return $is_directory_created;
	}

	// --------------------------------------------------

	/**
	 * @param string $fileUrl
	 * @param array|null $allowedTypes
	 * @param int|null $maxFileSize
	 * @param string|null $specificDir
	 *
	 * @return array|null
	 */
	public static function uploadFileFromUrl(string $fileUrl, ?array $allowedTypes = null, ?int $maxFileSize = null, ?string $specificDir = null): ?array
	{
		// Retrieve the file from the URL
		$response = wp_remote_get($fileUrl, ['timeout' => 10]);

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			return null;
		}

		$fileContent = wp_remote_retrieve_body($response);

		if (empty($fileContent)) {
			return null;
		}

		// Determine file name and directory
		$filename  = basename(parse_url($fileUrl, PHP_URL_PATH));
		$uploadDir = wp_upload_dir();

		if ($specificDir) {
			$directory = trailingslashit($uploadDir['basedir']) . trim($specificDir, '/');
			self::createDirectory($directory);
		} else {
			$directory = $uploadDir['path'];
		}

		$filePath = trailingslashit($directory) . $filename;

		// Check file size if applicable
		if ($maxFileSize !== null && mb_strlen($fileContent) > $maxFileSize) {
			return null;
		}

		// Write the file to the filesystem
		if (! self::doLockWrite($filePath, $fileContent)) {
			return null;
		}

		// Get a file type
		$filetype = wp_check_filetype($filePath);

		if ($allowedTypes !== null && (! $filetype['type'] || ! in_array($filetype['type'], $allowedTypes, false))) {
			return null;
		}

		// Prepare attachment data
		$attachment = [
			'guid'           => $uploadDir['url'] . '/' . $filename,
			'post_mime_type' => $filetype['type'],
			'post_title'     => self::fileName($filename, false),
			'post_content'   => '',
			'post_status'    => 'inherit',
		];

		// Insert the attachment into the Media Library
		$attachId = wp_insert_attachment($attachment, $filePath);

		if (! function_exists('wp_generate_attachment_metadata')) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$attachData = wp_generate_attachment_metadata($attachId, $filePath);
		wp_update_attachment_metadata($attachId, $attachData);

		return [
			'id'  => $attachId,
			'url' => wp_get_attachment_url($attachId),
		];
	}
}
