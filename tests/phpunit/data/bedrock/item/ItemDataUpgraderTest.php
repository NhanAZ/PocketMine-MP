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

namespace pocketmine\data\bedrock\item;

use PHPUnit\Framework\TestCase;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class ItemDataUpgraderTest extends TestCase{

	/** @return iterable<string, array{int, MobHeadType}> */
	public static function legacySkullTypeProvider() : iterable{
		yield "skeleton" => [0, MobHeadType::SKELETON];
		yield "wither skeleton" => [1, MobHeadType::WITHER_SKELETON];
		yield "zombie" => [2, MobHeadType::ZOMBIE];
		yield "player" => [3, MobHeadType::PLAYER];
		yield "creeper" => [4, MobHeadType::CREEPER];
		yield "dragon" => [5, MobHeadType::DRAGON];
		yield "piglin" => [6, MobHeadType::PIGLIN];
	}

	/** @dataProvider legacySkullTypeProvider */
	public function testLegacySkullUsesRegisteredBlockDefaults(int $meta, MobHeadType $expectedType) : void{
		$legacyData = CompoundTag::create()
			->setString(SavedItemData::TAG_NAME, "minecraft:skull")
			->setShort(SavedItemData::TAG_DAMAGE, $meta)
			->setByte(SavedItemStackData::TAG_COUNT, 1);

		$upgradedData = GlobalItemDataHandlers::getUpgrader()->upgradeItemStackNbt($legacyData);
		self::assertNotNull($upgradedData);
		$blockData = $upgradedData->getTypeData()->getBlock();
		self::assertNotNull($blockData);
		self::assertSame([], $blockData->getStates());

		$actual = GlobalItemDataHandlers::getDeserializer()->deserializeStack($upgradedData);
		$expected = VanillaBlocks::MOB_HEAD()->setMobHeadType($expectedType)->asItem();
		self::assertTrue($expected->equalsExact($actual));
	}
}
