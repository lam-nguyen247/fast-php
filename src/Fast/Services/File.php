<?php
namespace Fast\Services;

class File
{
	/**
	 * Raw file
	 * @var mixed
	 */
	private mixed $rawFile;

	/**
	 * Name of file
	 * @var string
	 */
	private string $name;

	/**
	 * Extension of file
	 * @var string
	 */
	private string $ext;

	/**
	 * Size of file
	 * @var int
	 */
	private int $size;

	/**
	 * Temporary name of file
	 * @var string
	 */
	private string $tmp_name;

	public function __construct(mixed $file)
	{
		$this->rawFile = $file;

		foreach ($file as $key => $value){
			$this->$key = $value;
		}

		$parseName = explode('.', $file['name']);
		$this->ext = end($parseName);
		$this->name = str_replace('.', $this->ext, '', $file['name']);
		$this->size = $file['size'] ?? 0;
		$this->tmp_name = $file['tmp_name'];
	}

	/**
	 * Get raw file
	 * @return mixed
	 */
	public function getRawFile(): mixed
	{
		return $this->rawFile;
	}

	/**
	 * Get name of file
	 * @return string
	 */
	public function getFileName(): string
	{
		return $this->name;
	}

	/**
	 * Get extension of file
	 * @return string
	 */
	public function getFileExtension(): string
	{
		return $this->ext;
	}

	/**
	 * Get file size
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	/**
	 * Get temporary name of file
	 * @return string
	 */
	public function getTmpName(): string
	{
		return $this->tmp_name;
	}

	/**
	 * Get property of file
	 * @param $name
	 * @return mixed
	 */
	public function __get($name): mixed
	{
		return $this->$name;
	}
}