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
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\math\AxisAlignedBB;
use pocketmine\world\World;
use ReflectionClass;
use ReflectionProperty;

final class PlayerGroundStateTest extends TestCase{

	public function testRefreshGroundStateUsesCurrentCollision() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$world->expects(self::exactly(2))->method("getCollisionBlocks")
			->with(self::callback(fn(AxisAlignedBB $bb) => $this->matchesGroundProbe($bb)), true)
			->willReturnOnConsecutiveCalls([VanillaBlocks::STONE()], []);
		$player = $this->createPlayer($world);

		$player->refreshGroundStateForTest();
		self::assertTrue($player->isOnGround());

		$player->refreshGroundStateForTest();
		self::assertFalse($player->isOnGround());
	}

	public function testNearbyBlockChangeRefreshesGroundStateWithoutMovement() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$world->updateEntities = [];
		$world->expects(self::once())->method("getCollisionBlocks")
			->with(self::callback(fn(AxisAlignedBB $bb) => $this->matchesGroundProbe($bb)), true)
			->willReturn([]);
		$player = $this->createPlayer($world);
		$player->onGround = true;

		$player->onNearbyBlockChange();

		self::assertFalse($player->isOnGround());
		self::assertSame([123 => $player], $world->updateEntities);
	}

	private function createPlayer(World $world) : PlayerGroundStateTestPlayer{
		$player = (new ReflectionClass(PlayerGroundStateTestPlayer::class))->newInstanceWithoutConstructor();
		self::assertInstanceOf(PlayerGroundStateTestPlayer::class, $player);

		$this->setEntityProperty($player, "id", 123);
		$this->setEntityProperty($player, "location", new Location(5.5, 65.0, 5.5, $world, 0.0, 0.0));
		$this->setEntityProperty($player, "boundingBox", new AxisAlignedBB(5.2, 65.0, 5.2, 5.8, 66.8, 5.8));
		$this->setPlayerProperty($player, "blockCollision", true);

		return $player;
	}

	private function matchesGroundProbe(AxisAlignedBB $bb) : bool{
		return $bb->minX === 5.2 &&
			$bb->minY === 64.8 &&
			$bb->minZ === 5.2 &&
			$bb->maxX === 5.8 &&
			$bb->maxY === 65.2 &&
			$bb->maxZ === 5.8;
	}

	private function setEntityProperty(Player $player, string $property, mixed $value) : void{
		$ref = new ReflectionProperty(Entity::class, $property);
		$ref->setValue($player, $value);
	}

	private function setPlayerProperty(Player $player, string $property, mixed $value) : void{
		$ref = new ReflectionProperty(Player::class, $property);
		$ref->setValue($player, $value);
	}
}

final class PlayerGroundStateTestPlayer extends Player{
	public function refreshGroundStateForTest() : void{
		$this->refreshGroundState();
	}

	public function __destruct(){
		//NOOP: this test double is never constructed as a real player.
	}
}
