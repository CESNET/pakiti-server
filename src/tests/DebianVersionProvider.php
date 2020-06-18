<?php

class DebianVersionProvider implements Iterator
{
	protected $file;
	protected $value;
	protected $index = 0;
	protected $line_no = 0;

	private function split_debian_version(string $deb_version): array
	{
		$split = explode("-", $deb_version);
		if (count($split) == 1)
			return [$deb_version, ""];

		$release = array_pop($split);
		$version = implode("-", $split);
		return [$version, $release];
	}

	private function parse_operator(string $op): int
	{
		switch ($op) {
			case "LESS":
				return -1;
			case "EQUAL":
				return 0;
			case "GREATER":
				return -1;
			default:
				throw new Exception(sprintf("Invalid operator at line %d", $this->line_no));
		}
	}

	public function __construct($file)
	{
		$this->file = fopen($file, 'r');
		if ($this->file === False)
			throw new Exception(sprintf("Failed to open file '%s'", $file));
	}

	public function __destruct()
	{
		fclose($this->file);
	}

	public function rewind()
	{
		rewind($this->file);
		$this->index = 0;
		$this->line_no = 0;
		$this->next();
	}

	public function valid()
	{
		return !feof($this->file);
	}

	public function key()
	{
		return $this->index;
	}

	public function current()
	{
		return $this->value;
	}

	public function next()
	{
		while (True) {
			$line = fgets($this->file);
			if ($line === False)
				return;
			$this->line_no++;

			$line = trim($line);
			if ($line == "" || $line[0] == '#')
				continue;

			break;
		}

		$ret = preg_match('/EXPECT_VERSION\("(.+)", (.+), "(.+)"\)/', $line, $matches);
		if ($ret !== 1) {
			print($line);
			throw new Exception(sprintf("Unexpected versions definition at line %d", $this->line_no));
		}

		[$first_ver, $first_rel] = self::split_debian_version(trim($matches[1]));
		[$second_ver, $second_rel] = self::split_debian_version(trim($matches[3]));

		$this->value = [ $first_ver, $first_rel, $second_ver, $second_rel, self::parse_operator(trim($matches[2])) ];
		$this->index++;
	}
}
