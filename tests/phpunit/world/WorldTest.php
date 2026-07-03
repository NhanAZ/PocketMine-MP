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
use pocketmine\world\format\Chunk;
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

	private function createWorldForSetChunk(int $chunkX, int $chunkZ, Chunk $oldChunk) : World{
		$world = (new \ReflectionClass(World::class))->newInstanceWithoutConstructor();
		self::assertInstanceOf(World::class, $world);

		self::setPrivateProperty($world, "folderName", "test");
		self::setPrivateProperty($world, "displayName", "test");
		self::setPrivateProperty($world, "provider", $this->createMock(WritableWorldProvider::class));
		self::setPrivateProperty($world, "logger", $this->createMock(\Logger::class));
		self::setPrivateProperty($world, "minY", World::Y_MIN);
		self::setPrivateProperty($world, "maxY", World::Y_MAX);
		self::setPrivateProperty($world, "blockStateRegistry", RuntimeBlockStateRegistry::getInstance());
		self::setPrivateProperty($world, "chunks", [World::chunkHash($chunkX, $chunkZ) => $oldChunk]);
		self::setPrivateProperty($world, "chunkLoaders", [World::chunkHash($chunkX, $chunkZ) => [1 => new \stdClass()]]);
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

	public function testSetChunkRebasesReplacementChunkTiles() : void{
		$world = $this->createWorldForSetChunk(-2, 1, new Chunk([], true));
		$tile = new WorldTestTile($world, new Vector3(3, 64, 4));
		$originalPosition = $tile->getPosition();
		$replacementChunk = new Chunk([], true);
		$replacementChunk->addTile($tile);

		$world->setChunk(-2, 1, $replacementChunk);

		self::assertSame($originalPosition, $tile->getPosition());
		self::assertSame(-29, $tile->getPosition()->getFloorX());
		self::assertSame(64, $tile->getPosition()->getFloorY());
		self::assertSame(20, $tile->getPosition()->getFloorZ());
		self::assertSame($tile, $replacementChunk->getTile(3, 64, 4));
	}
}

final class WorldTestTile extends Tile{
	public function readSaveData(CompoundTag $nbt) : void{

	}

	protected function writeSaveData(CompoundTag $nbt) : void{

	}

	public function __destruct(){

	}
}
