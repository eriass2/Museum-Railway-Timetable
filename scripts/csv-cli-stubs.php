<?php
/**
 * WordPress function stubs for CSV CLI scripts.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( string $string ): string {
		return rtrim( $string, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * @param mixed $data
	 */
	function wp_json_encode( $data, int $options = 0, int $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
	function wp_mkdir_p( string $target ): bool {
		if ( is_dir( $target ) ) {
			return true;
		}
		return mkdir( $target, 0755, true );
	}
}

if ( ! function_exists( 'wp_delete_file' ) ) {
	function wp_delete_file( string $file ): bool {
		return file_exists( $file ) && unlink( $file );
	}
}

if ( ! function_exists( 'wp_generate_password' ) ) {
	function wp_generate_password( int $length = 12, bool $special_chars = true ): string {
		unset( $special_chars );
		return bin2hex( random_bytes( max( 1, (int) ceil( $length / 2 ) ) ) );
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( string $title ): string {
		return MRT_csv_slugify( $title );
	}
}
