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

namespace pocketmine\inventory;

use PHPUnit\Framework\TestCase;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;

class BaseInventoryTest extends TestCase{

	public function testAddItemDifferentUserData() : void{
		$inv = new SimpleInventory(1);
		$item1 = VanillaItems::ARROW()->setCount(1);
		$item2 = VanillaItems::ARROW()->setCount(1)->setCustomName("TEST");

		$inv->addItem(clone $item1);
		self::assertFalse($inv->canAddItem($item2), "Item WITHOUT userdata should not stack with item WITH userdata");
		self::assertNotEmpty($inv->addItem($item2));

		$inv->clearAll();
		self::assertEmpty($inv->getContents());

		$inv->addItem(clone $item2);
		self::assertFalse($inv->canAddItem($item1), "Item WITH userdata should not stack with item WITHOUT userdata");
		self::assertNotEmpty($inv->addItem($item1));
	}

	/**
	 * @return Item[]
	 */
	private function getTestItems() : array{
		return [
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16)
		];
	}

	public function testAddMultipleItemsInOneCall() : void{
		$inventory = new SimpleInventory(1);
		$leftover = $inventory->addItem(...$this->getTestItems());
		self::assertCount(0, $leftover);
		self::assertTrue($inventory->getItem(0)->equalsExact(VanillaItems::APPLE()->setCount(64)));
	}

	public function testAddMultipleItemsInOneCallWithLeftover() : void{
		$inventory = new SimpleInventory(1);
		$inventory->setItem(0, VanillaItems::APPLE()->setCount(20));
		$leftover = $inventory->addItem(...$this->getTestItems());
		self::assertCount(2, $leftover); //the leftovers are not currently stacked - if they were given separately, they'll be returned separately
		self::assertTrue($inventory->getItem(0)->equalsExact(VanillaItems::APPLE()->setCount(64)));

		$leftoverCount = 0;
		foreach($leftover as $item){
			self::assertTrue($item->equals(VanillaItems::APPLE()));
			$leftoverCount += $item->getCount();
		}
		self::assertSame(20, $leftoverCount);
	}

	public function testAddItemWithOversizedCount() : void{
		$inventory = new SimpleInventory(10);
		$leftover = $inventory->addItem(VanillaItems::APPLE()->setCount(100));
		self::assertCount(0, $leftover);

		$count = 0;
		foreach($inventory->getContents() as $item){
			self::assertTrue($item->equals(VanillaItems::APPLE()));
			$count += $item->getCount();
		}
		self::assertSame(100, $count);
	}

	public function testGetAddableItemQuantityStacking() : void{
		$inventory = new SimpleInventory(1);
		$inventory->addItem(VanillaItems::APPLE()->setCount(60));
		self::assertSame(2, $inventory->getAddableItemQuantity(VanillaItems::APPLE()->setCount(2)));
		self::assertSame(4, $inventory->getAddableItemQuantity(VanillaItems::APPLE()->setCount(6)));
	}

	public function testGetAddableItemQuantityEmptyStack() : void{
		$inventory = new SimpleInventory(1);
		$item = VanillaItems::APPLE();
		$item->setCount($item->getMaxStackSize());
		self::assertSame($item->getMaxStackSize(), $inventory->getAddableItemQuantity($item));
	}

	public function testContainsCachesSearchItemTags() : void{
		NbtSerializationCountingItem::$serializeCompoundTagCalls = 0;
		$inventory = new SimpleInventory(4);
		for($i = 0; $i < 4; $i++){
			$inventory->setItem($i, VanillaItems::APPLE()->setCount(1)->setCustomName("needle"));
		}

		$search = new NbtSerializationCountingItem(new ItemIdentifier(ItemTypeIds::APPLE), "Apple");
		$search->setCount(4);
		$search->setCustomName("needle");

		self::assertTrue($inventory->contains($search));
		self::assertSame(1, NbtSerializationCountingItem::$serializeCompoundTagCalls);
	}

	public function testRemoveCachesSearchItemTags() : void{
		NbtSerializationCountingItem::$serializeCompoundTagCalls = 0;
		$inventory = new SimpleInventory(4);
		for($i = 0; $i < 4; $i++){
			$inventory->setItem($i, VanillaItems::APPLE()->setCount(1)->setCustomName("needle"));
		}

		$search = new NbtSerializationCountingItem(new ItemIdentifier(ItemTypeIds::APPLE), "Apple");
		$search->setCustomName("needle");

		$inventory->remove($search);

		self::assertSame(1, NbtSerializationCountingItem::$serializeCompoundTagCalls);
		self::assertSame([], $inventory->getContents());
	}

	public function testRejectsPlainItemUsingBlockTypeIdOnSetItem() : void{
		$inventory = new SimpleInventory(1);
		$item = new Item(new ItemIdentifier(ItemTypeIds::fromBlockTypeId(BlockTypeIds::GRASS)), "Grass");

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("reserved for ItemBlock instances");

		$inventory->setItem(0, $item);
	}

	public function testRejectsPlainItemUsingBlockTypeIdOnSetContents() : void{
		$inventory = new SimpleInventory(1);
		$item = new Item(new ItemIdentifier(ItemTypeIds::fromBlockTypeId(BlockTypeIds::GRASS)), "Grass");

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage("reserved for ItemBlock instances");

		$inventory->setContents([
			0 => $item
		]);
	}

	public function testAcceptsBlockItemUsingBlockTypeId() : void{
		$inventory = new SimpleInventory(1);
		$item = VanillaBlocks::GRASS()->asItem();

		$inventory->setItem(0, $item);

		self::assertTrue($item->equalsExact($inventory->getItem(0)));
	}
}

final class NbtSerializationCountingItem extends Item{
	public static int $serializeCompoundTagCalls = 0;

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		self::$serializeCompoundTagCalls++;
		parent::serializeCompoundTag($tag);
	}
}
