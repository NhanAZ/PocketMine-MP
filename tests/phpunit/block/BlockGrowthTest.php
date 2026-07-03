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

namespace pocketmine\block;

use PHPUnit\Framework\TestCase;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\World;

final class BlockGrowthTest extends TestCase{

	public function testSugarcaneRandomTickGrowsOnlyOneBlockFromTop() : void{
		$blocks = [];
		$setBlocks = [];
		$world = $this->createBlockWorld($blocks, $setBlocks);

		$this->putBlock($blocks, $world, 0, 63, 0, VanillaBlocks::SAND());
		$this->putBlock($blocks, $world, 0, 64, 0, VanillaBlocks::SUGARCANE());
		$top = $this->putBlock($blocks, $world, 0, 65, 0, VanillaBlocks::SUGARCANE()->setAge(Sugarcane::MAX_AGE));

		$top->onRandomTick();

		$this->assertBlockWasSet($setBlocks, 0, 66, 0, BlockTypeIds::SUGARCANE);
		$this->assertBlockWasSet($setBlocks, 0, 65, 0, BlockTypeIds::SUGARCANE, 0);
		$this->assertBlockWasNotSet($setBlocks, 0, 67, 0);
	}

	public function testSugarcaneNonTopRandomTickDoesNotGrow() : void{
		$blocks = [];
		$setBlocks = [];
		$world = $this->createBlockWorld($blocks, $setBlocks);

		$bottom = $this->putBlock($blocks, $world, 0, 64, 0, VanillaBlocks::SUGARCANE()->setAge(Sugarcane::MAX_AGE));
		$this->putBlock($blocks, $world, 0, 63, 0, VanillaBlocks::SAND());
		$this->putBlock($blocks, $world, 1, 63, 0, VanillaBlocks::WATER());
		$this->putBlock($blocks, $world, 0, 65, 0, VanillaBlocks::SUGARCANE());

		$bottom->onRandomTick();

		self::assertSame([], $setBlocks);
	}

	public function testSugarcaneFertilizerCanGrowToMaximumHeight() : void{
		$blocks = [];
		$setBlocks = [];
		$world = $this->createBlockWorld($blocks, $setBlocks);

		$sugarcane = $this->putBlock($blocks, $world, 0, 64, 0, VanillaBlocks::SUGARCANE()->setAge(Sugarcane::MAX_AGE));
		$this->putBlock($blocks, $world, 0, 63, 0, VanillaBlocks::SAND());
		$this->putBlock($blocks, $world, 1, 63, 0, VanillaBlocks::WATER());

		$returnedItems = [];
		self::assertTrue($sugarcane->onInteract(VanillaItems::BONE_MEAL(), Facing::UP, new Vector3(0.5, 0.5, 0.5), returnedItems: $returnedItems));

		$this->assertBlockWasSet($setBlocks, 0, 65, 0, BlockTypeIds::SUGARCANE);
		$this->assertBlockWasSet($setBlocks, 0, 66, 0, BlockTypeIds::SUGARCANE);
		$this->assertBlockWasSet($setBlocks, 0, 64, 0, BlockTypeIds::SUGARCANE, 0);
	}

	public function testCactusRandomTickGrowsOnlyOneBlockFromTop() : void{
		$blocks = [];
		$setBlocks = [];
		$world = $this->createBlockWorld($blocks, $setBlocks);

		$this->putBlock($blocks, $world, 0, 63, 0, VanillaBlocks::SAND());
		$cactus = $this->putBlock($blocks, $world, 0, 64, 0, VanillaBlocks::CACTUS()->setAge(Cactus::MAX_AGE));

		$cactus->onRandomTick();

		$this->assertBlockWasSet($setBlocks, 0, 65, 0, BlockTypeIds::CACTUS);
		$this->assertBlockWasSet($setBlocks, 0, 64, 0, BlockTypeIds::CACTUS, 0);
		$this->assertBlockWasNotSet($setBlocks, 0, 66, 0);
	}

	public function testCactusNonTopRandomTickDoesNotGrow() : void{
		$blocks = [];
		$setBlocks = [];
		$world = $this->createBlockWorld($blocks, $setBlocks);

		$bottom = $this->putBlock($blocks, $world, 0, 64, 0, VanillaBlocks::CACTUS()->setAge(Cactus::MAX_AGE));
		$this->putBlock($blocks, $world, 0, 63, 0, VanillaBlocks::SAND());
		$this->putBlock($blocks, $world, 0, 65, 0, VanillaBlocks::CACTUS());

		$bottom->onRandomTick();

		self::assertSame([], $setBlocks);
	}

	/**
	 * @param array<string, Block>                                            $blocks
	 * @param list<array{x: int, y: int, z: int, block: Block, update: bool}> $setBlocks
	 */
	private function createBlockWorld(array &$blocks, array &$setBlocks) : World{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);
		$world->method("isInWorld")->willReturnCallback(fn(int $x, int $y, int $z) : bool => $y >= 0 && $y < 320);
		$world->method("getBlockAt")->willReturnCallback(function(int $x, int $y, int $z, bool $cached = true, bool $addToCache = true) use (&$blocks, $world) : Block{
			$block = clone ($blocks[self::blockKey($x, $y, $z)] ?? VanillaBlocks::AIR());
			$block->position($world, $x, $y, $z);
			return $block;
		});
		$world->method("getBlock")->willReturnCallback(function(Vector3 $pos, bool $cached = true, bool $addToCache = true) use (&$blocks, $world) : Block{
			$x = $pos->getFloorX();
			$y = $pos->getFloorY();
			$z = $pos->getFloorZ();
			$block = clone ($blocks[self::blockKey($x, $y, $z)] ?? VanillaBlocks::AIR());
			$block->position($world, $x, $y, $z);
			return $block;
		});
		$world->method("setBlock")->willReturnCallback(function(Vector3 $pos, Block $block, bool $update = true) use (&$blocks, &$setBlocks, $world) : void{
			$x = $pos->getFloorX();
			$y = $pos->getFloorY();
			$z = $pos->getFloorZ();
			$state = clone $block;
			$state->position($world, $x, $y, $z);
			$blocks[self::blockKey($x, $y, $z)] = $state;
			$setBlocks[] = ["x" => $x, "y" => $y, "z" => $z, "block" => $state, "update" => $update];
		});
		$world->method("useBreakOn")->willReturn(true);

		return $world;
	}

	/**
	 * @param array<string, Block> $blocks
	 * @phpstan-param TBlock $block
	 * @phpstan-return TBlock
	 *
	 * @template TBlock of Block
	 */
	private function putBlock(array &$blocks, World $world, int $x, int $y, int $z, Block $block) : Block{
		$block->position($world, $x, $y, $z);
		$blocks[self::blockKey($x, $y, $z)] = $block;
		return $block;
	}

	/**
	 * @param list<array{x: int, y: int, z: int, block: Block, update: bool}> $setBlocks
	 */
	private function assertBlockWasSet(array $setBlocks, int $x, int $y, int $z, int $typeId, ?int $age = null) : void{
		foreach($setBlocks as $entry){
			if($entry["x"] === $x && $entry["y"] === $y && $entry["z"] === $z){
				self::assertSame($typeId, $entry["block"]->getTypeId());
				if($age !== null){
					self::assertInstanceOf(utils\Ageable::class, $entry["block"]);
					self::assertSame($age, $entry["block"]->getAge());
				}
				return;
			}
		}

		self::fail("Expected block set at $x:$y:$z");
	}

	/**
	 * @param list<array{x: int, y: int, z: int, block: Block, update: bool}> $setBlocks
	 */
	private function assertBlockWasNotSet(array $setBlocks, int $x, int $y, int $z) : void{
		foreach($setBlocks as $entry){
			self::assertFalse($entry["x"] === $x && $entry["y"] === $y && $entry["z"] === $z);
		}
	}

	private static function blockKey(int $x, int $y, int $z) : string{
		return "$x:$y:$z";
	}
}
