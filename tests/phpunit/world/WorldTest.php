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
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\tile\Tile;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\io\ChunkData;
use pocketmine\world\format\io\LoadedChunkData;
use pocketmine\world\format\io\WorldData;
use pocketmine\world\format\io\WritableWorldProvider;

final class WorldTest extends TestCase{

	/**
	 * @phpstan-param non-empty-string $property
	 */
	private static function setPrivateProperty(World $world, string $property, mixed $value) : void{
		$ref = new \ReflectionProperty(World::class, $property);
		$ref->setValue($world, $value);
	}

	private function createWorldForChunkLoad(WritableWorldProvider $provider) : World{
		$ref = new \ReflectionClass(World::class);
		$world = $ref->newInstanceWithoutConstructor();
		self::assertInstanceOf(World::class, $world);

		self::setPrivateProperty($world, "folderName", "test");
		self::setPrivateProperty($world, "provider", $provider);
		self::setPrivateProperty($world, "logger", $this->createMock(\Logger::class));
		self::setPrivateProperty($world, "minY", World::Y_MIN);
		self::setPrivateProperty($world, "maxY", World::Y_MAX);
		self::setPrivateProperty($world, "blockStateRegistry", RuntimeBlockStateRegistry::getInstance());
		$world->timings = new WorldTimings($world);

		return $world;
	}

	public function testLoadChunkSkipsOutOfBoundsLoadedTiles() : void{
		$tile = CompoundTag::create()
			->setString(Tile::TAG_ID, "Sign")
			->setInt(Tile::TAG_X, 0)
			->setInt(Tile::TAG_Y, World::Y_MAX)
			->setInt(Tile::TAG_Z, 0);

		$provider = $this->createMock(WritableWorldProvider::class);
		$worldData = $this->createMock(WorldData::class);
		$worldData->method("getSpawn")->willReturn(new Vector3(0, 0, 0));
		$provider->method("getWorldData")->willReturn($worldData);
		$provider->method("loadChunk")->with(0, 0)->willReturn(new LoadedChunkData(
			new ChunkData([], true, [], [$tile]),
			false,
			LoadedChunkData::FIXER_FLAG_NONE
		));

		$chunk = $this->createWorldForChunkLoad($provider)->loadChunk(0, 0);

		self::assertNotNull($chunk);
		self::assertCount(0, $chunk->getTiles());
	}
}
