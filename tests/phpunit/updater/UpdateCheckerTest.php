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

namespace pocketmine\updater;

use PHPUnit\Framework\TestCase;

final class UpdateCheckerTest extends TestCase{

	public function testChannelSuggestionsUseBuildChannel() : void{
		$resolve = (new \ReflectionMethod(UpdateChecker::class, "getChannelSuggestion"))->getClosure();

		self::assertNull($resolve("stable", "stable"));
		self::assertSame("stable", $resolve("stable", "beta"));
		self::assertSame("alpha", $resolve("alpha", "stable"));
		self::assertSame("beta", $resolve("beta", "stable"));
		self::assertSame("development", $resolve("development", "stable"));
		self::assertNull($resolve("alpha", "alpha"));
		self::assertNull($resolve("alpha", "development"));
	}
}
