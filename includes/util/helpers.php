<?php
/**
 * Helper functions
 *
 * This file contains helper functions used throughout the plugin.
 *
 * @link https://github.com/Mwalek/super-light-cache-buster
 *
 * @package    WordPress
 * @subpackage Plugins
 * @since      1.0.0
 */

/**
 * Array insertion helper function.
 *
 * @param array $array The array in which to insert.
 * @param array $values The values to insert into the array.
 * @param int   $offset Specifies array offset position.
 * @return array The modified array.
 */
function array_insert( $array, $values, $offset ) {
	return array_slice( $array, 0, $offset, true ) + $values + array_slice( $array, $offset + 1, null, true );
}
