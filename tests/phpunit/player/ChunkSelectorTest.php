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

namespace pocketmine\player;

use PHPUnit\Framework\TestCase;
use pocketmine\world\World;
use function count;

final class ChunkSelectorTest extends TestCase{

	public function testSelectChunksYieldsCompleteConcentricRings() : void{
		$radius = 8;
		$centerX = 13;
		$centerZ = -7;

		$seen = [];
		$previousRing = -1;
		foreach((new ChunkSelector())->selectChunks($radius, $centerX, $centerZ) as $ring => $hash){
			self::assertGreaterThanOrEqual($previousRing, $ring);
			self::assertArrayNotHasKey($hash, $seen);

			World::getXZ($hash, $chunkX, $chunkZ);
			$offsetX = $chunkX >= $centerX ? $chunkX - $centerX : $centerX - $chunkX - 1;
			$offsetZ = $chunkZ >= $centerZ ? $chunkZ - $centerZ : $centerZ - $chunkZ - 1;
			$distanceSquared = $offsetX ** 2 + $offsetZ ** 2;

			self::assertGreaterThanOrEqual($ring ** 2, $distanceSquared);
			self::assertLessThan(($ring + 1) ** 2, $distanceSquared);

			$seen[$hash] = true;
			$previousRing = $ring;
		}

		$expected = [];
		for($chunkX = $centerX - $radius; $chunkX < $centerX + $radius; ++$chunkX){
			for($chunkZ = $centerZ - $radius; $chunkZ < $centerZ + $radius; ++$chunkZ){
				$offsetX = $chunkX >= $centerX ? $chunkX - $centerX : $centerX - $chunkX - 1;
				$offsetZ = $chunkZ >= $centerZ ? $chunkZ - $centerZ : $centerZ - $chunkZ - 1;
				if($offsetX ** 2 + $offsetZ ** 2 < $radius ** 2){
					$expected[World::chunkHash($chunkX, $chunkZ)] = true;
				}
			}
		}

		self::assertCount(count($expected), $seen);
		foreach($expected as $hash => $_){
			self::assertArrayHasKey($hash, $seen);
		}
	}
}
