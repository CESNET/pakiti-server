<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/* XXX this should be autoload'ed */
require __DIR__ . "/../common/Pakiti.php";
require __DIR__ . "/../managers/DefaultManager.php";
require __DIR__ . "/../managers/VulnerabilitiesManager.php";

require 'DebianVersionProvider.php';

final class VulnerabilitiesManagerTest extends TestCase
{

	/**
	  * @dataProvider provideDebianVersions
	  */
	public function testSortDebian(string $first_ver, string $first_rel, string $second_ver, string $second_rel, int $expected): void
	{
		$pakiti = new Pakiti();

		self::assertEquals(
			$expected,
			(new VulnerabilitiesManager($pakiti))->vercmp("dpkg", $first_ver, $first_rel, $second_ver, $second_rel));

		$expected *= -1;
		$this->assertEquals(
			$expected,
			(new VulnerabilitiesManager($pakiti))->vercmp("dpkg", $second_ver, $second_rel, $first_ver, $first_rel));
	}

	public function provideDebianVersions()
	{
		return new DebianVersionProvider(__DIR__ . "/deb-versions.lst");
	}
}
