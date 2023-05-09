<?php

namespace Fast\Supports;

use Fast\Container;

class ConsoleOutput {
	/**
	 * Can color for terminal
	 *
	 * @var bool
	 */
	protected bool $canColor = true;

	/**
	 * List of foreground colors
	 *
	 * @var array
	 */
	private array $foreground_colors = [];

	/**
	 * List of background colors
	 *
	 * @var array
	 */
	private array $background_colors = [];

	/**
	 * Init initial foreground and background colors
	 */
	public function __construct() {
		// Set up shell colors
		$this->foreground_colors['black'] = '0;30';
		$this->foreground_colors['dark_gray'] = '1;30';
		$this->foreground_colors['blue'] = '0;34';
		$this->foreground_colors['light_blue'] = '1;34';
		$this->foreground_colors['green'] = '0;32';
		$this->foreground_colors['light_green'] = '1;32';
		$this->foreground_colors['cyan'] = '0;36';
		$this->foreground_colors['light_cyan'] = '1;36';
		$this->foreground_colors['red'] = '0;31';
		$this->foreground_colors['light_red'] = '1;31';
		$this->foreground_colors['purple'] = '0;35';
		$this->foreground_colors['light_purple'] = '1;35';
		$this->foreground_colors['brown'] = '0;33';
		$this->foreground_colors['yellow'] = '1;33';
		$this->foreground_colors['light_gray'] = '0;37';
		$this->foreground_colors['white'] = '1;37';

		$this->background_colors['black'] = '40';
		$this->background_colors['red'] = '41';
		$this->background_colors['green'] = '42';
		$this->background_colors['yellow'] = '43';
		$this->background_colors['blue'] = '44';
		$this->background_colors['magenta'] = '45';
		$this->background_colors['cyan'] = '46';
		$this->background_colors['light_gray'] = '47';
	}

	/**
	 * Returns colored string
	 *
	 * @param string $string
	 * @param string|null $foreground_color
	 * @param string|null $background_color
	 *
	 * @return string
	 */
	public function getColoredString(string $string, ?string $foreground_color = null, ?string $background_color = null): string {
		if (Container::getInstance()->isWindows()) {
			return $string;
		}

		$colored_string = "";

		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground_color])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
		}
		// Check if given background color found
//		if(isset($this->background_colors[$background_color])) {
//			$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
//		}

		// Add string and end coloring
		$colored_string .= $string . "\033[0m";

		return $colored_string;
	}

	/**
	 * Returns all foreground color names
	 *
	 * @return array
	 */
	public function getForegroundColors(): array {
		return array_keys($this->foreground_colors);
	}

	/**
	 * Returns all background color names
	 *
	 * @return array
	 */
	public function getBackgroundColors(): array {
		return array_keys($this->background_colors);
	}

	/**
	 * Print a message with normal color
	 *
	 * @param string $msg
	 *
	 * @return void
	 */
	public function print(string $msg): void {
		echo $this->getColoredString($msg, "green", "black") . "\n";
	}

	/**
	 * Print a message with error color
	 *
	 * @param string $error
	 *
	 * @return void
	 */
	public function printError(string $error): void {
		echo $this->getColoredString($error, "red", "black") . "\n";
	}

	/**
	 * Print a message with warning color
	 *
	 * @param string $warning
	 *
	 * @return void
	 */
	public function printWarning(string $warning): void {
		echo $this->getColoredString($warning, "black", "yellow") . "\n";
	}

	/**
	 * Print a message with highlights color
	 *
	 * @param string $highlights
	 *
	 * @return void
	 */
	public function printHighlights(string $highlights): void {
		echo $this->getColoredString($highlights, "brown", "") . "\n";
	}

	/**
	 * Print a message with success color
	 *
	 * @param string $success
	 *
	 * @return void
	 */
	public function printSuccess(string $success): void {
		echo $this->getColoredString($success, "green", "black") . "\n";
	}

	/**
	 * Print a message with success no background color
	 *
	 * @param string $success
	 *
	 * @return void
	 */
	public function printSuccessNoBackground(string $success): void {
		echo $this->getColoredString($success, "green", "") . "\n";
	}
}
