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
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\world\Position;
use pocketmine\world\World;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class PlayerSpawnTest extends TestCase{

	public function testBedSpawnValidationRequiresBedBlock() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);

		$bed = VanillaBlocks::BED();
		$bed->position($world, 5, 64, 5);
		$world->method("getBlock")->willReturn($bed);

		$validate = $this->getBedSpawnValidator($this->createPlayer());

		self::assertTrue($validate(new Position(5, 64, 5, $world)));
	}

	public function testBedSpawnValidationRejectsMissingBedBlock() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$world->method("getBlock")->willReturn(VanillaBlocks::AIR());

		$validate = $this->getBedSpawnValidator($this->createPlayer());

		self::assertFalse($validate(new Position(5, 64, 5, $world)));
	}

	public function testSetSpawnClearsBedSpawnValidation() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$spawn = new Position(5, 64, 5, $world);
		$player = $this->createPlayer();
		$this->setPlayerProperty($player, "spawnPositionIsBedSpawn", true);

		$session = $this->createMock(NetworkSession::class);
		$session->expects(self::once())->method("syncPlayerSpawnPoint")->with($spawn);
		$this->setPlayerProperty($player, "networkSession", $session);

		$player->setSpawn($spawn);

		self::assertFalse($this->getPlayerProperty($player, "spawnPositionIsBedSpawn"));
	}

	private function createPlayer() : PlayerSpawnTestPlayer{
		return (new ReflectionClass(PlayerSpawnTestPlayer::class))->newInstanceWithoutConstructor();
	}

	/**
	 * @return \Closure(Position) : bool
	 */
	private function getBedSpawnValidator(Player $player) : \Closure{
		return (new ReflectionMethod(Player::class, "isBedSpawnValid"))->getClosure($player);
	}

	private function setPlayerProperty(Player $player, string $property, mixed $value) : void{
		$ref = new ReflectionProperty(Player::class, $property);
		$ref->setValue($player, $value);
	}

	private function getPlayerProperty(Player $player, string $property) : mixed{
		$ref = new ReflectionProperty(Player::class, $property);
		return $ref->getValue($player);
	}
}

final class PlayerSpawnTestPlayer extends Player{
	public function __destruct(){
		//NOOP: this test double is never constructed as a real player.
	}
}
