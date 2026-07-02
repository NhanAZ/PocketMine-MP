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

namespace pocketmine\block;

use PHPUnit\Framework\TestCase;

final class FenceTest extends TestCase{

	public function testFenceConnectionsRespectMaterialFamilies() : void{
		$netherBrick = VanillaBlocks::NETHER_BRICK_FENCE();
		$oak = VanillaBlocks::OAK_FENCE();
		$birch = VanillaBlocks::BIRCH_FENCE();

		self::assertTrue($this->canConnect($netherBrick, VanillaBlocks::NETHER_BRICK_FENCE()));
		self::assertFalse($this->canConnect($netherBrick, $oak));
		self::assertFalse($this->canConnect($oak, $netherBrick));
		self::assertTrue($this->canConnect($oak, $birch));
		self::assertTrue($this->canConnect($birch, $oak));
	}

	private function canConnect(Fence $source, Fence $target) : bool{
		$method = new \ReflectionMethod($source, "canConnectToFence");
		return $method->invoke($source, $target) === true;
	}
}
