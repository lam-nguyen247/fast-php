<?php
namespace Fast\Translator;

class Translator
{
	private array $storage = [];

	public function setTranslation(string $key, $value): Translator
	{
		$this->storage[$key] = $value;
		return $this;
	}

	/**
	 * @throws TranslationException
	 */
	public function trans(string $key, array $params, string $lang = 'en'): mixed
	{
		$keys = explode('.', $key);
		$file = array_shift($keys);
		$key = $file. DIRECTORY_SEPARATOR . $lang;

		if(!$this->checkTranslation($key)) {
			throw new TranslationException("Translator param {$key} not found");
		}
		$value = $this->getTranslation($key);

		for( $i = 0; $i <= count($keys) - 1; $i++) {
			if(isset($value[$keys[$i]])) {
				$value = $value[$keys[$i]];
			}else {
				throw new TranslationException("Key $keys[$i] not found");
			}
		}
		
		foreach ($params as $key => $param) {
			$value = str_replace(":{$key}", $param, $value);
		}

		return $value;
	}

	protected function getStorage(): array
	{
		return $this->storage;
	}

	/**
	 * Check exists translation
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function checkTranslation(string $key): bool
	{
		return isset($this->storage[$key]);
	}

	/**
	 * Get value from key translation.
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	protected function getTranslation(string $key): mixed {
		return $this->storage[$key] ?? null;
	}
}