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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

final class TileTest extends TestCase{

	/**
	 * @phpstan-return iterable<string, array{CompoundTag}>
	 */
	public static function invalidCustomBlockDataProvider() : iterable{
		yield "invalid value" => [CompoundTag::create()
			->setShort("PotionType", 0)
			->setShort("PotionId", 32767)];
		yield "invalid tag type" => [CompoundTag::create()
			->setString("PotionType", "invalid")];
	}

	#[DataProvider("invalidCustomBlockDataProvider")]
	public function testInvalidCustomBlockDataDoesNotEscapePlacement(CompoundTag $customBlockData) : void{
		$logger = $this->createMock(\Logger::class);
		$logger->expects(self::once())
			->method("error")
			->with(self::stringContains("Error loading custom block data"));
		$previousLogger = \GlobalLogger::get();
		\GlobalLogger::set($logger);

		try{
			$world = $this->createMock(World::class);
			$world->method("isLoaded")->willReturn(true);
			$tile = new Cauldron($world, new Vector3(0, 64, 0));
			$item = (new Item(new ItemIdentifier(ItemTypeIds::APPLE), "Test"))->setCustomBlockData($customBlockData);

			$tile->copyDataFromItem($item);

			self::assertNull($tile->getPotionItem());
		}finally{
			\GlobalLogger::set($previousLogger);
		}
	}
}
