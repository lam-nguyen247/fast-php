<?php

namespace Fast\View;

class ViewCompiler {
	/**
	 * Path of the view
	 *
	 * @var string
	 */
	protected string $path;

	/**
	 * Content of html
	 *
	 * @var string
	 */
	protected string $html;

	/**
	 * Initial constructor of view compiler
	 *
	 * @param string $path
	 *
	 * @method @setPath()
	 * @method @setHtml()
	 *
	 * @return void
	 */
	public function __construct(string $path) {
		$this->setPath($path);
		$this->setHtml();
	}

	/**
	 * Set path of view
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	protected function setPath(string $path): void {
		$this->path = $path;
	}

	/**
	 * Set html content view
	 *
	 * @return void
	 * @property $this->path
	 *
	 * @property $this->html
	 */
	protected function setHtml(): void {
		$this->html = file_get_contents($this->path);
	}

	/**
	 * Compile echo
	 *
	 * @return void
	 */
	public final function compileEcho(): void {
		$newViewData = [];

		foreach (explode(PHP_EOL, $this->getHtml()) as $line) {
			if (!str_contains($line, " //  ")) {
				$line = preg_replace('/\{\{\{(.+?)\}\}\}/', '<?php echo this->htmlentities($1); ?>', $line);

				$newViewData[] = preg_replace('/\{\{(.+?)\}\}/', '<?php echo $1; ?>', $line);
			} else {
				$newViewData[] = $line;
			}
		}

		$this->resetHtml(implode(PHP_EOL, $newViewData));
	}

	/**
	 * Compile php tag
	 *
	 * @param array $start_tags
	 * @param array $end_tags
	 *
	 * @return void
	 */
	public final function compilePhpTag(array $start_tags, array $end_tags): void {
		foreach ($start_tags as $tag) {
			$html = str_replace($tag, '<?php', $this->getHtml());
		}
		foreach ($end_tags as $tag) {
			$html = str_replace($tag, '?>', $html);
		}

		$this->resetHtml($html);
	}

	/**
	 * Compile special tags
	 *
	 * @return void
	 */
	public final function compileSpecialTags(): void {
		$newViewData = [];

		foreach (explode(PHP_EOL, $this->getHtml()) as $line) {
			switch (true) {
				case str_contains($line, '@if('):
				case str_contains($line, '@foreach('):
					$line = str_replace('@', '', $line);
					$newViewData[] = "<?php {$line}: ?>";
					break;
				case str_contains($line, '@endif'):
				case str_contains($line, '@endforeach'):
					$line = str_replace('@', '', $line);
					$newViewData[] = "<?php {$line}; ?>";
					break;
				default:
					$newViewData[] = $line;
					break;
			}
		}

		$this->resetHtml(implode(PHP_EOL, $newViewData));
	}

	/**
	 * Compile comment
	 *
	 * @return void
	 */
	public final function compileComment(): void {
		$html = preg_replace('/\{\{--(.+?)(--\}\})?\n/', "<?php // $1 ?>\n", $this->getHtml());

		$html = preg_replace('/\{\{--((.|\s)*?)--\}\}/', "<?php /* $1 */ ?>\n", $html);

		$html = preg_replace('/\<\!\-\-(.+?)(\-\-\>)?\n/', "<?php // $1 ?>\n", $html);

		$html = preg_replace('/\<\!--((.|\s)*?)--\>/', "<?php /* $1 */ ?>\n", $html);

		$this->resetHtml($html);
	}

	/**
	 * Reset html
	 *
	 * @param string $html
	 *
	 * @return void
	 */
	protected final function resetHtml(string $html): void {
		$this->html = $html;
	}

	/**
	 * Get html
	 *
	 * @return string
	 */
	public final function getHtml(): string {
		return $this->html;
	}
}
