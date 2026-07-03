<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\utils;

use PHPUnit\Framework\TestCase;
use function timezone_name_from_abbr;

final class TimezoneTest extends TestCase{

	public function testUtcIniTimezoneTriggersAutoDetection() : void{
		$resolve = (new \ReflectionMethod(Timezone::class, "resolveConfiguredTimezone"))->getClosure();

		self::assertNull($resolve(""));
		self::assertNull($resolve("UTC"));
		self::assertSame("Etc/UTC", $resolve("Etc/UTC"));
	}

	public function testConfiguredTimezoneResolution() : void{
		$resolve = (new \ReflectionMethod(Timezone::class, "resolveConfiguredTimezone"))->getClosure();
		$est = timezone_name_from_abbr("EST");
		self::assertIsString($est);

		self::assertSame("Europe/Berlin", $resolve("Europe/Berlin"));
		self::assertSame($est, $resolve("EST"));
		self::assertNull($resolve("Invalid/Zone"));
	}
}
