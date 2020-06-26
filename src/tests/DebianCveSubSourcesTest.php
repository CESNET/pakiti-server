<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/* XXX this should be autoload'ed */
require __DIR__ . "/../modules/vds/sources/CveSubSources/Debian.php";

final class DebianCveSubSourcesTest extends TestCase
{
	/**
	  * @dataProvider provideIndices
	  */
	public function testIndexParsing(string $input, array $expected): void
	{
		$packages = array();
		Debian::update_packages($input, $packages);
		$this->assertEquals($packages, $expected);
	}

	public function testIndexParsingWithAFieldMissing(): void
	{
		$this->expectExceptionMessage("Missing field Binary");
		$packages = array();

		$input = 
"Package: source_name
Version: 1.0";
		Debian::update_packages($input, $packages);

		$input = "Version: 1.0";
		Debian::update_packages($input, $packages);
	}

	public function provideIndices()
	{
		$tests = [
		"standardFormat" => [
			"input" =>
"Package: source_name
Binary: aa,
 bb, cc , ,
 dd
Version: 1.0",
			"expected" => [ "source_name" => ["aa", "bb", "cc", "dd"]],
		],

		"lastLine" => [
			"input" =>
"Package: source_name
Binary: aa,
 bb, cc , ,
 dd",
 			"expected" => [ "source_name" => ["aa", "bb", "cc", "dd"]],
		],

		"multipleParagraphs" => [
			"input" =>
"Package: source_name_a
Version: 1.1
Binary: aa , bb
	cc,

Package: source_name_b
Binary: xx, yy,
 zz
Version: 1.1",
			"expected" => [
				"source_name_a" => ["aa", "bb", "cc"],
				"source_name_b" => ["xx", "yy", "zz"],
			],
		],

		];

		return $tests;
	}
}
