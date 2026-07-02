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
use pocketmine\entity\object\AreaEffectCloud;
use pocketmine\entity\object\EndCrystal;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\Painting;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\projectile\ExperienceBottle;
use pocketmine\entity\projectile\IceBomb;
use pocketmine\entity\projectile\Snowball;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\entity\projectile\Trident;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

final class EntityFactoryTest extends TestCase{

	/**
	 * @return string[][]
	 * @phpstan-return array<string, array{class-string<Entity>, string}>
	 */
	public static function coreEntitySaveIdProvider() : array{
		return [
			AreaEffectCloud::class => [AreaEffectCloud::class, EntityIds::AREA_EFFECT_CLOUD],
			Arrow::class => [Arrow::class, EntityIds::ARROW],
			Egg::class => [Egg::class, EntityIds::EGG],
			EndCrystal::class => [EndCrystal::class, EntityIds::ENDER_CRYSTAL],
			EnderPearl::class => [EnderPearl::class, EntityIds::ENDER_PEARL],
			ExperienceBottle::class => [ExperienceBottle::class, EntityIds::XP_BOTTLE],
			ExperienceOrb::class => [ExperienceOrb::class, EntityIds::XP_ORB],
			FallingBlock::class => [FallingBlock::class, EntityIds::FALLING_BLOCK],
			Human::class => [Human::class, EntityIds::PLAYER],
			IceBomb::class => [IceBomb::class, EntityIds::ICE_BOMB],
			ItemEntity::class => [ItemEntity::class, EntityIds::ITEM],
			Painting::class => [Painting::class, EntityIds::PAINTING],
			PrimedTNT::class => [PrimedTNT::class, EntityIds::TNT],
			Snowball::class => [Snowball::class, EntityIds::SNOWBALL],
			SplashPotion::class => [SplashPotion::class, EntityIds::SPLASH_POTION],
			Squid::class => [Squid::class, EntityIds::SQUID],
			Trident::class => [Trident::class, EntityIds::THROWN_TRIDENT],
			Villager::class => [Villager::class, EntityIds::VILLAGER],
			Zombie::class => [Zombie::class, EntityIds::ZOMBIE],
		];
	}

	/**
	 * @dataProvider coreEntitySaveIdProvider
	 *
	 * @phpstan-param class-string<Entity> $class
	 */
	public function testCoreEntitiesUseBedrockSaveIds(string $class, string $expectedSaveId) : void{
		self::assertSame($expectedSaveId, EntityFactory::getInstance()->getSaveId($class));
	}
}
