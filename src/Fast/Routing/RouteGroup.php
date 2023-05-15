<?php

namespace Fast\Routing;

class RouteGroup {
	/**
	 * Format the namespace for the new group attributes.
	 *
	 * @param string $new
	 * @param string $old
	 * @return string|null
	 */
	public static function formatNamespace(string $new, string $old): ?string {
		if (!empty($new)) {
			return !empty($old) && !str_starts_with($new, '\\')
				? trim($old, '\\') . '\\' . trim($new, '\\')
				: trim($new, '\\');
		}

		return $old ?? null;
	}

	/**
	 * Format the prefix for the new group attributes.
	 *
	 * @param string $new
	 * @param string $old
	 * @param bool $prependExistingPrefix
	 * @return string|null
	 */
	public static function formatPrefix(string $new, string $old, bool $prependExistingPrefix = true): ?string {

		if ($prependExistingPrefix) {
			return !empty($new) ? trim($old, '/') . '/' . trim($new, '/') : $old;
		} else {
			return !empty($new) ? trim($new, '/') . '/' . trim($old, '/') : $old;
		}
	}

	/**
	 * Format the "wheres" for the new group attributes.
	 *
	 * @param array $new
	 * @param array $old
	 * @return array
	 */
	public static function formatWhere(array $new, array $old): array {
		return array_merge(
			$old ?? [],
			$new ?? []
		);
	}

	/**
	 * Format the "as" clause of the new group attributes.
	 *
	 * @param array $new
	 * @param array $old
	 * @return array
	 */
	public static function formatAs(array $new, array $old): array {
		if (!empty($old)) {
			$new = $old . ($new ?? '');
		}

		return $new;
	}
}
