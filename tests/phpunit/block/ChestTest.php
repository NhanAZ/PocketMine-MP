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
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\World;

final class ChestTest extends TestCase{

	public function testLivingEntityOnTopBlocksOpening() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);

		$chest = $this->createChestBlock($world, 5, 64, 5);
		$tile = new TileChest($world, new Vector3(5, 64, 5));
		$living = $this->createLivingEntity();

		$world->method("getTile")->willReturn($tile);
		$world->method("getBlockAt")->willReturn(VanillaBlocks::AIR());
		$world->expects(self::once())->method("getNearbyEntities")
			->with(self::callback(fn(AxisAlignedBB $bb) => $this->matchesBlockAbove($bb, 5, 64, 5)))
			->willReturn([$living]);

		$player = $this->createPlayer();
		self::assertTrue($chest->onInteract(VanillaItems::APPLE(), Facing::UP, new Vector3(0.5, 0.5, 0.5), $player));
		self::assertSame(0, $player->setCurrentWindowCalls);
	}

	public function testNonLivingEntityOnTopDoesNotBlockOpening() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);

		$chest = $this->createChestBlock($world, 5, 64, 5);
		$tile = new TileChest($world, new Vector3(5, 64, 5));
		$entity = $this->createEntity();

		$world->method("getTile")->willReturn($tile);
		$world->method("getBlockAt")->willReturn(VanillaBlocks::AIR());
		$world->method("getNearbyEntities")->willReturn([$entity]);

		$player = $this->createPlayer();
		self::assertTrue($chest->onInteract(VanillaItems::APPLE(), Facing::UP, new Vector3(0.5, 0.5, 0.5), $player));
		self::assertSame(1, $player->setCurrentWindowCalls);
		self::assertSame($tile->getInventory(), $player->lastWindow);
	}

	public function testLivingEntityOnPairedChestBlocksOpening() : void{
		$world = $this->createMock(World::class);
		$world->method("isLoaded")->willReturn(true);

		$chest = $this->createChestBlock($world, 5, 64, 5);
		$pairBlock = $this->createChestBlock($world, 6, 64, 5);
		$living = $this->createLivingEntity();

		$tile = null;
		$pairTile = null;
		$world->method("getTile")->willReturnCallback(function(Vector3 $position) use (&$tile, &$pairTile) : ?TileChest{
			return $position->getFloorX() === 5 ? $tile : $pairTile;
		});
		$world->method("getTileAt")->willReturnCallback(function(int $x, int $y, int $z) use (&$tile, &$pairTile) : ?TileChest{
			return $x === 5 ? $tile : ($x === 6 ? $pairTile : null);
		});
		$world->method("getBlock")->willReturnCallback(fn(Vector3 $position) => $position->getFloorX() === 5 ? $chest : $pairBlock);
		$world->method("getBlockAt")->willReturn(VanillaBlocks::AIR());
		$world->expects(self::exactly(2))->method("getNearbyEntities")
			->willReturnCallback(fn(AxisAlignedBB $bb) => $this->matchesBlockAbove($bb, 6, 64, 5) ? [$living] : []);

		$tile = new TileChest($world, new Vector3(5, 64, 5));
		$pairTile = new TileChest($world, new Vector3(6, 64, 5));
		$this->setPair($tile, 6, 5);
		$this->setPair($pairTile, 5, 5);

		$player = $this->createPlayer();
		self::assertTrue($chest->onInteract(VanillaItems::APPLE(), Facing::UP, new Vector3(0.5, 0.5, 0.5), $player));
		self::assertSame(0, $player->setCurrentWindowCalls);
	}

	private function createChestBlock(World $world, int $x, int $y, int $z) : Chest{
		$chest = VanillaBlocks::CHEST();
		$chest->position($world, $x, $y, $z);

		return $chest;
	}

	private function createEntity() : Entity{
		return (new \ReflectionClass(ChestTestEntity::class))->newInstanceWithoutConstructor();
	}

	private function createLivingEntity() : Living{
		return (new \ReflectionClass(ChestTestLivingEntity::class))->newInstanceWithoutConstructor();
	}

	private function createPlayer() : ChestTestPlayer{
		return (new \ReflectionClass(ChestTestPlayer::class))->newInstanceWithoutConstructor();
	}

	private function setPair(TileChest $chest, int $x, int $z) : void{
		$pairX = new \ReflectionProperty(TileChest::class, "pairX");
		$pairZ = new \ReflectionProperty(TileChest::class, "pairZ");
		$pairX->setValue($chest, $x);
		$pairZ->setValue($chest, $z);
	}

	private function matchesBlockAbove(AxisAlignedBB $bb, int $x, int $y, int $z) : bool{
		return $bb->minX === (float) $x &&
			$bb->minY === (float) ($y + 1) &&
			$bb->minZ === (float) $z &&
			$bb->maxX === (float) ($x + 1) &&
			$bb->maxY === (float) ($y + 2) &&
			$bb->maxZ === (float) ($z + 1);
	}
}

final class ChestTestEntity extends Entity{
	public static function getNetworkTypeId() : string{ return EntityIds::ITEM; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(1.0, 1.0); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function __destruct(){
		//NOOP: this test double is never constructed as a real entity.
	}
}

final class ChestTestLivingEntity extends Living{
	public static function getNetworkTypeId() : string{ return EntityIds::PLAYER; }

	public function getName() : string{ return "Chest Test Living"; }

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(1.0, 1.0); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function __destruct(){
		//NOOP: this test double is never constructed as a real entity.
	}
}

final class ChestTestPlayer extends \pocketmine\player\Player{
	public int $setCurrentWindowCalls = 0;
	public ?Inventory $lastWindow = null;

	public function setCurrentWindow(Inventory $inventory) : bool{
		++$this->setCurrentWindowCalls;
		$this->lastWindow = $inventory;
		return true;
	}

	public function __destruct(){
		//NOOP: this test double is never constructed as a real player.
	}
}
