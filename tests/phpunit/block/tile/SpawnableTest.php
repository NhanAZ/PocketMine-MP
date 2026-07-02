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

namespace pocketmine\block\tile;

use PHPUnit\Framework\TestCase;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\world\World;
use function spl_object_id;

final class SpawnableTest extends TestCase{

	public function testSerializedSpawnCompoundCacheUsesTypeConverterContext() : void{
		TileFactory::getInstance()->register(ContextRecordingSpawnable::class, ["ContextRecordingSpawnable"]);

		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);

		$tile = new ContextRecordingSpawnable($world, new Vector3(1, 2, 3));
		$typeConverterA = $this->createMock(TypeConverter::class);
		$typeConverterB = $this->createMock(TypeConverter::class);

		$firstA = $tile->getSerializedSpawnCompound($typeConverterA);
		$secondA = $tile->getSerializedSpawnCompound($typeConverterA);
		$firstB = $tile->getSerializedSpawnCompound($typeConverterB);

		self::assertSame($firstA, $secondA);
		self::assertNotSame($firstA, $firstB);
		self::assertSame(spl_object_id($typeConverterA), $firstA->getRoot()->getLong(ContextRecordingSpawnable::TAG_CONVERTER_ID));
		self::assertSame(spl_object_id($typeConverterB), $firstB->getRoot()->getLong(ContextRecordingSpawnable::TAG_CONVERTER_ID));
		self::assertSame(1, $tile->getSpawnCallCount($typeConverterA));
		self::assertSame(1, $tile->getSpawnCallCount($typeConverterB));

		$tile->clearSpawnCompoundCache();
		$thirdA = $tile->getSerializedSpawnCompound($typeConverterA);

		self::assertNotSame($firstA, $thirdA);
		self::assertSame(2, $tile->getSpawnCallCount($typeConverterA));
	}
}

final class ContextRecordingSpawnable extends Spawnable{
	public const TAG_CONVERTER_ID = "converterId";

	/** @phpstan-var array<int, int> */
	private array $spawnCalls = [];

	public function readSaveData(CompoundTag $nbt) : void{
		//NOOP
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		//NOOP
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt, TypeConverter $typeConverter) : void{
		$typeConverterId = spl_object_id($typeConverter);
		$this->spawnCalls[$typeConverterId] = ($this->spawnCalls[$typeConverterId] ?? 0) + 1;
		$nbt->setLong(self::TAG_CONVERTER_ID, $typeConverterId);
	}

	public function getSpawnCallCount(TypeConverter $typeConverter) : int{
		return $this->spawnCalls[spl_object_id($typeConverter)] ?? 0;
	}
}
