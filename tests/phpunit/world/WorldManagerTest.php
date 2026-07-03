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

namespace pocketmine\world;

use PHPUnit\Framework\TestCase;
use pocketmine\Server;
use pocketmine\ServerConfigGroup;
use pocketmine\utils\Config;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\YmlServerProperties;

final class WorldManagerTest extends TestCase{

	/**
	 * @phpstan-param non-empty-string $property
	 */
	private static function setPrivateProperty(WorldManager $manager, string $property, mixed $value) : void{
		$ref = new \ReflectionProperty(WorldManager::class, $property);
		$ref->setValue($manager, $value);
	}

	private function createWorldManager(int $chunkPopulationTaskLimit) : WorldManager{
		$pocketmineYml = $this->createMock(Config::class);
		$pocketmineYml->method("getNested")->willReturnCallback(
			static fn(string $key, mixed $default = null) : mixed => match($key){
				YmlServerProperties::CHUNK_GENERATION_POPULATION_QUEUE_SIZE => $chunkPopulationTaskLimit,
				default => $default
			}
		);

		$server = $this->createMock(Server::class);
		$server->method("getConfigGroup")->willReturn(new ServerConfigGroup($pocketmineYml, $this->createMock(Config::class)));

		return new WorldManager($server, "worlds", new WorldProviderManager());
	}

	public function testChunkPopulationTaskSlotsAreGlobal() : void{
		$manager = $this->createWorldManager(1);

		self::assertTrue($manager->hasChunkPopulationTaskSlot());
		self::assertTrue($manager->tryReserveChunkPopulationTaskSlot());
		self::assertFalse($manager->hasChunkPopulationTaskSlot());
		self::assertFalse($manager->tryReserveChunkPopulationTaskSlot());

		$manager->releaseChunkPopulationTaskSlot();

		self::assertTrue($manager->hasChunkPopulationTaskSlot());
		self::assertTrue($manager->tryReserveChunkPopulationTaskSlot());
	}

	public function testReleaseDrainsLoadedWorldPopulationQueuesUntilGlobalLimitIsFull() : void{
		$manager = $this->createWorldManager(1);

		$firstWorld = $this->getMockBuilder(World::class)
			->disableOriginalConstructor()
			->onlyMethods(["isLoaded", "drainPopulationRequestQueue"])
			->getMock();
		$firstWorld->method("isLoaded")->willReturn(true);
		$firstWorld->expects(self::once())
			->method("drainPopulationRequestQueue")
			->willReturnCallback(function() use ($manager) : void{
				self::assertTrue($manager->tryReserveChunkPopulationTaskSlot());
			});

		$secondWorld = $this->getMockBuilder(World::class)
			->disableOriginalConstructor()
			->onlyMethods(["isLoaded", "drainPopulationRequestQueue"])
			->getMock();
		$secondWorld->expects(self::never())->method("drainPopulationRequestQueue");

		self::setPrivateProperty($manager, "worlds", [1 => $firstWorld, 2 => $secondWorld]);

		self::assertTrue($manager->tryReserveChunkPopulationTaskSlot());
		$manager->releaseChunkPopulationTaskSlot();
		self::assertFalse($manager->hasChunkPopulationTaskSlot());
	}
}
