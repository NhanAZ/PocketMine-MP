<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_| |_|\___|     |_|  |_|_|
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

namespace pocketmine\event;

use PHPUnit\Framework\TestCase;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\block\BlockExplodeEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\world\Position;
use pocketmine\world\World;

final class ExplosionEventTest extends TestCase{

	public function testEntityExplodeEventRejectsOutOfWorldBlocks() : void{
		$world = $this->createWorld();
		$event = new EntityExplodeEvent($this->createEntity(), new Position(0, 0, 0, $world), [$this->createBlock($world, World::Y_MIN)], 100.0);

		$this->assertRejectsOutOfWorldBlock(function() use ($event, $world) : void{
			$event->setBlockList([$this->createBlock($world, World::Y_MIN - 1)]);
		});
		$this->assertRejectsOutOfWorldBlock(function() use ($event, $world) : void{
			$event->setIgnitions([$this->createBlock($world, World::Y_MAX)]);
		});
	}

	public function testBlockExplodeEventRejectsOutOfWorldBlocks() : void{
		$world = $this->createWorld();
		$block = $this->createBlock($world, World::Y_MIN);
		$event = new BlockExplodeEvent($block, new Position(0, 0, 0, $world), [$block], 100.0, []);

		$this->assertRejectsOutOfWorldBlock(function() use ($event, $world) : void{
			$event->setAffectedBlocks([$this->createBlock($world, World::Y_MIN - 1)]);
		});
		$this->assertRejectsOutOfWorldBlock(function() use ($event, $world) : void{
			$event->setIgnitions([$this->createBlock($world, World::Y_MAX)]);
		});
	}

	public function testExplodeEventConstructorsRejectOutOfWorldBlocks() : void{
		$world = $this->createWorld();
		$invalidBlock = $this->createBlock($world, World::Y_MIN - 1);

		$this->assertRejectsOutOfWorldBlock(function() use ($world, $invalidBlock) : void{
			new EntityExplodeEvent($this->createEntity(), new Position(0, 0, 0, $world), [$invalidBlock], 100.0);
		});
		$this->assertRejectsOutOfWorldBlock(function() use ($world, $invalidBlock) : void{
			new BlockExplodeEvent($this->createBlock($world, World::Y_MIN), new Position(0, 0, 0, $world), [$invalidBlock], 100.0, []);
		});
	}

	/**
	 * @param \Closure() : void $callback
	 */
	private function assertRejectsOutOfWorldBlock(\Closure $callback) : void{
		try{
			$callback();
			self::fail("Expected out-of-world block rejection");
		}catch(\InvalidArgumentException $e){
			self::assertStringContainsString("outside of the world bounds", $e->getMessage());
		}
	}

	private function createWorld() : World{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$world->method("isInWorld")->willReturnCallback(fn(int $x, int $y, int $z) => $y >= World::Y_MIN && $y < World::Y_MAX);
		return $world;
	}

	private function createBlock(World $world, int $y) : Block{
		$block = VanillaBlocks::STONE();
		$block->position($world, 0, $y, 0);
		return $block;
	}

	private function createEntity() : Entity{
		return (new \ReflectionClass(ExplosionEventTestEntity::class))->newInstanceWithoutConstructor();
	}
}

final class ExplosionEventTestEntity extends Entity{
	public static function getNetworkTypeId() : string{ return "test:explosion_event"; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(1.0, 1.0); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function __destruct(){
		//NOOP: this test double is never constructed as a real entity.
	}
}
