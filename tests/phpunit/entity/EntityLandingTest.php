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
use pocketmine\block\Slime;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\world\World;

final class EntityLandingTest extends TestCase{
	public function testItemEntityUsesBlockLandingBehaviour() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$slime = VanillaBlocks::SLIME();
		$slime->position($world, 0, 0, 0);
		$world->expects(self::once())->method("getBlock")->willReturn($slime);
		$world->expects(self::never())->method("addSound");

		$entity = (new \ReflectionClass(LandingTestItemEntity::class))->newInstanceWithoutConstructor();
		self::markClosed($entity);
		$entity->initializeForLanding(new Location(0, 0, 0, $world, 0, 0), new Vector3(0, -0.6, 0), 4.0);

		self::assertSame(0.6, $entity->hitGroundForTest());
		self::assertSame(0.0, $entity->getFallDistance());
	}

	public function testSlimeDoesNotBounceExperienceOrbs() : void{
		$entity = (new \ReflectionClass(ExperienceOrb::class))->newInstanceWithoutConstructor();
		self::markClosed($entity);
		$entity->setFallDistance(4.0);

		self::assertNull($this->getSlime()->onEntityLand($entity));
		self::assertSame(4.0, $entity->getFallDistance());
	}

	public function testSlimeDoesNotBounceSneakingLivingEntities() : void{
		$entity = $this->newLivingEntity(true);

		self::assertNull($this->getSlime()->onEntityLand($entity));
		self::assertSame(0.0, $entity->getFallDistance());
	}

	public function testSlimeBouncesNonSneakingLivingEntities() : void{
		$entity = $this->newLivingEntity(false);

		self::assertSame(0.6, $this->getSlime()->onEntityLand($entity));
		self::assertSame(0.0, $entity->getFallDistance());
	}

	private function getSlime() : Slime{
		return VanillaBlocks::SLIME();
	}

	private function newLivingEntity(bool $sneaking) : LandingTestLiving{
		$entity = (new \ReflectionClass(LandingTestLiving::class))->newInstanceWithoutConstructor();
		self::markClosed($entity);
		$entity->initializeForLanding(new Vector3(0, -0.6, 0), 4.0, $sneaking);
		return $entity;
	}

	private static function markClosed(Entity $entity) : void{
		$closed = new \ReflectionProperty(Entity::class, "closed");
		$closed->setValue($entity, true);
	}
}

final class LandingTestItemEntity extends ItemEntity{
	public function initializeForLanding(Location $location, Vector3 $motion, float $fallDistance) : void{
		$this->location = $location;
		$this->motion = $motion;
		$this->fallDistance = $fallDistance;
	}

	public function hitGroundForTest() : ?float{
		return $this->onHitGround();
	}
}

final class LandingTestLiving extends Living{
	public static function getNetworkTypeId() : string{ return "test:landing"; }

	public function getName() : string{ return "Landing Test"; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(1.0, 1.0); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function initializeForLanding(Vector3 $motion, float $fallDistance, bool $sneaking) : void{
		$this->motion = $motion;
		$this->fallDistance = $fallDistance;
		$this->sneaking = $sneaking;
	}
}
