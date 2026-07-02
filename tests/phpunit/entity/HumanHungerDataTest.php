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

namespace pocketmine\entity;

use PHPUnit\Framework\TestCase;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\tag\CompoundTag;

final class HumanHungerDataTest extends TestCase{

	/**
	 * @return CompoundTag[][]
	 * @phpstan-return array<string, array{CompoundTag}>
	 */
	public static function invalidHungerDataProvider() : array{
		return [
			"food below minimum" => [CompoundTag::create()->setInt("foodLevel", -1)],
			"exhaustion above maximum" => [CompoundTag::create()->setFloat("foodExhaustionLevel", 6.0)],
			"saturation above maximum" => [CompoundTag::create()->setFloat("foodSaturationLevel", 21.0)],
			"negative tick timer" => [CompoundTag::create()->setInt("foodTickTimer", -1)],
		];
	}

	/**
	 * @dataProvider invalidHungerDataProvider
	 */
	public function testInvalidHungerDataThrowsSavedDataLoadingException(CompoundTag $nbt) : void{
		$this->expectException(SavedDataLoadingException::class);
		$this->expectExceptionMessage("Invalid hunger data:");

		$this->loadHungerData($this->newHungerManager(), $nbt);
	}

	public function testValidHungerDataLoads() : void{
		$manager = $this->newHungerManager();
		$this->loadHungerData($manager, CompoundTag::create()
			->setInt("foodLevel", 12)
			->setFloat("foodExhaustionLevel", 3.5)
			->setFloat("foodSaturationLevel", 7.5)
			->setInt("foodTickTimer", 42));

		self::assertSame(12.0, $manager->getFood());
		self::assertSame(3.5, $manager->getExhaustion());
		self::assertSame(7.5, $manager->getSaturation());
		self::assertSame(42, $manager->getFoodTickTimer());
	}

	private function newHungerManager() : HungerManager{
		$human = (new \ReflectionClass(Human::class))->newInstanceWithoutConstructor();
		$closed = new \ReflectionProperty(Entity::class, "closed");
		$closed->setValue($human, true);
		$attributeMap = new \ReflectionProperty(Entity::class, "attributeMap");
		$attributeMap->setValue($human, new AttributeMap());
		return new HungerManager($human);
	}

	private function loadHungerData(HungerManager $manager, CompoundTag $nbt) : void{
		$load = (new \ReflectionMethod(Human::class, "loadHungerData"))->getClosure();
		$load($manager, $nbt);
	}
}
