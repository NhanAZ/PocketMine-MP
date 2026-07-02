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

namespace pocketmine\entity\projectile;

use PHPUnit\Framework\TestCase;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginManager;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\world\World;

final class ProjectileCloseDuringHitTest extends TestCase{
	private Plugin $mockPlugin;
	private PluginManager $pluginManager;

	protected function setUp() : void{
		HandlerListManager::global()->unregisterAll();
		Timings::init();

		$mockServer = $this->createMock(Server::class);
		$this->mockPlugin = self::createStub(Plugin::class);
		$this->mockPlugin->method("isEnabled")->willReturn(true);

		$this->pluginManager = new PluginManager($mockServer, null);
	}

	protected function tearDown() : void{
		HandlerListManager::global()->unregisterAll();
	}

	public function testCloseDuringBlockHitEventStopsHitProcessing() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$world->method("getCollidingEntities")->willReturn([]);

		$block = VanillaBlocks::STONE();
		$block->position($world, 0, 0, 0);
		$world->method("getBlockAt")->willReturn($block);

		$projectile = $this->newProjectile($world, $this->newRayTraceResult());
		$world->expects(self::once())->method("removeEntity")->with($projectile);
		$world->expects(self::never())->method("onEntityMoved");

		$this->pluginManager->registerEvent(
			ProjectileHitBlockEvent::class,
			function(ProjectileHitBlockEvent $event) : void{
				$event->getEntity()->close();
			},
			EventPriority::NORMAL,
			$this->mockPlugin
		);

		$projectile->moveForTest(1.0, 0.0, 0.0);

		self::assertTrue($projectile->isClosed());
		self::assertFalse($projectile->onHitCalled);
		self::assertFalse($projectile->onHitBlockCalled);
	}

	public function testCloseDuringEntityHitEventStopsHitProcessing() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);

		$air = VanillaBlocks::AIR();
		$air->position($world, 0, 0, 0);
		$world->method("getBlockAt")->willReturn($air);

		$target = (new \ReflectionClass(CloseDuringHitTargetEntity::class))->newInstanceWithoutConstructor();
		$target->initializeForHitTest(
			new Location(0.7, 0.0, 0.0, $world, 0.0, 0.0),
			new AxisAlignedBB(0.6, -0.25, -0.25, 0.8, 0.25, 0.25)
		);
		$world->method("getCollidingEntities")->willReturn([$target]);

		$projectile = $this->newProjectile($world, null);
		$world->expects(self::once())->method("removeEntity")->with($projectile);
		$world->expects(self::never())->method("onEntityMoved");

		$this->pluginManager->registerEvent(
			ProjectileHitEntityEvent::class,
			function(ProjectileHitEntityEvent $event) : void{
				$event->getEntity()->close();
			},
			EventPriority::NORMAL,
			$this->mockPlugin
		);

		$projectile->moveForTest(1.0, 0.0, 0.0);

		self::assertTrue($projectile->isClosed());
		self::assertFalse($projectile->onHitCalled);
		self::assertFalse($projectile->onHitEntityCalled);
	}

	private function newProjectile(World $world, ?RayTraceResult $blockHitResult) : CloseDuringHitTestProjectile{
		$projectile = (new \ReflectionClass(CloseDuringHitTestProjectile::class))->newInstanceWithoutConstructor();
		$projectile->initializeForHitTest(
			new Location(0.0, 0.0, 0.0, $world, 0.0, 0.0),
			new AxisAlignedBB(-0.125, -0.125, -0.125, 0.125, 0.125, 0.125),
			new Vector3(1.0, 0.0, 0.0),
			$blockHitResult
		);
		return $projectile;
	}

	private function newRayTraceResult() : RayTraceResult{
		return new RayTraceResult(
			new AxisAlignedBB(0.0, 0.0, 0.0, 1.0, 1.0, 1.0),
			Facing::WEST,
			new Vector3(0.0, 0.0, 0.0)
		);
	}
}

final class CloseDuringHitTestProjectile extends Projectile{
	public bool $onHitCalled = false;
	public bool $onHitBlockCalled = false;
	public bool $onHitEntityCalled = false;

	private ?RayTraceResult $blockHitResult = null;

	public static function getNetworkTypeId() : string{ return "test:projectile_close_during_hit"; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.25, 0.25); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function initializeForHitTest(Location $location, AxisAlignedBB $boundingBox, Vector3 $motion, ?RayTraceResult $blockHitResult) : void{
		$this->id = 1;
		$this->location = $location;
		$this->motion = $motion;
		$this->boundingBox = $boundingBox;
		$this->size = $this->getInitialSizeInfo();
		$this->blockHitResult = $blockHitResult;
	}

	public function moveForTest(float $dx, float $dy, float $dz) : void{
		$this->move($dx, $dy, $dz);
	}

	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
		return $this->blockHitResult;
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$this->onHitCalled = true;
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		$this->onHitBlockCalled = true;
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$this->onHitEntityCalled = true;
	}
}

final class CloseDuringHitTargetEntity extends Entity{
	public static function getNetworkTypeId() : string{ return "test:projectile_close_target"; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(1.0, 1.0); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function initializeForHitTest(Location $location, AxisAlignedBB $boundingBox) : void{
		$this->id = 2;
		$this->location = $location;
		$this->boundingBox = $boundingBox;
		$this->closed = true;
	}
}
