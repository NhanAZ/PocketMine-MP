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

namespace pocketmine\network\mcpe\handler;

use PHPUnit\Framework\TestCase;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeFactory;
use pocketmine\entity\AttributeMap;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\PredictedResult;
use pocketmine\network\mcpe\protocol\types\inventory\TriggerType;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\player\Player;
use ReflectionClass;

final class InGamePacketHandlerTest extends TestCase{
	public function testCreativePredictDestroyAttacksWhenStartBreakWasMissing() : void{
		$blockPosition = new BlockPosition(1, 2, 3);
		$player = $this->createPlayer(true);
		$handler = $this->createHandler($player);

		self::assertTrue($handler->handlePlayerAction($this->createPlayerAction(PlayerAction::PREDICT_DESTROY_BLOCK, $blockPosition)));
		self::assertSame([
			["attack", 1, 2, 3, Facing::UP],
			["break", 1, 2, 3],
		], $player->getCalls());
		self::assertSame([false], $player->getSetUsingItemValues());
	}

	public function testCreativePredictDestroyDoesNotAttackAgainAfterStartBreak() : void{
		$blockPosition = new BlockPosition(1, 2, 3);
		$player = $this->createPlayer(true);
		$handler = $this->createHandler($player);

		self::assertTrue($handler->handlePlayerAction($this->createPlayerAction(PlayerAction::START_BREAK, $blockPosition)));
		self::assertTrue($handler->handlePlayerAction($this->createPlayerAction(PlayerAction::PREDICT_DESTROY_BLOCK, $blockPosition)));
		self::assertSame([
			["attack", 1, 2, 3, Facing::UP],
			["break", 1, 2, 3],
		], $player->getCalls());
		self::assertSame([false, false], $player->getSetUsingItemValues());
	}

	public function testSurvivalPredictDestroyDoesNotSynthesizeAttack() : void{
		$blockPosition = new BlockPosition(1, 2, 3);
		$player = $this->createPlayer(false);
		$handler = $this->createHandler($player);

		self::assertTrue($handler->handlePlayerAction($this->createPlayerAction(PlayerAction::PREDICT_DESTROY_BLOCK, $blockPosition)));
		self::assertSame([
			["break", 1, 2, 3],
		], $player->getCalls());
		self::assertSame([false], $player->getSetUsingItemValues());
	}

	public function testFailedBlockInteractionResynchronizesHunger() : void{
		$player = $this->createPlayer(false);
		$hunger = $player->initializeHungerAttribute();
		$player->setInteractBlockResult(false);

		self::assertTrue($this->createHandler($player)->handleInventoryTransaction($this->createBlockInteractionTransaction()));
		self::assertTrue($hunger->isDesynchronized());
	}

	public function testSuccessfulBlockInteractionDoesNotResynchronizeHunger() : void{
		$player = $this->createPlayer(false);
		$hunger = $player->initializeHungerAttribute();
		$player->setInteractBlockResult(true);

		self::assertTrue($this->createHandler($player)->handleInventoryTransaction($this->createBlockInteractionTransaction()));
		self::assertFalse($hunger->isDesynchronized());
	}

	private function createPlayer(bool $creative) : InGamePacketHandlerTestPlayer{
		/** @var InGamePacketHandlerTestPlayer $player */
		$player = (new ReflectionClass(InGamePacketHandlerTestPlayer::class))->newInstanceWithoutConstructor();
		$player->setCreative($creative);
		return $player;
	}

	private function createHandler(Player $player) : InGamePacketHandler{
		return new InGamePacketHandler(
			$player,
			self::createStub(NetworkSession::class),
			self::createStub(InventoryManager::class)
		);
	}

	private function createPlayerAction(int $action, BlockPosition $blockPosition) : PlayerActionPacket{
		return PlayerActionPacket::create(1, $action, $blockPosition, $blockPosition, Facing::UP);
	}

	private function createBlockInteractionTransaction() : InventoryTransactionPacket{
		return InventoryTransactionPacket::create(0, null, UseItemTransactionData::new(
			[],
			UseItemTransactionData::ACTION_CLICK_BLOCK,
			TriggerType::PLAYER_INPUT,
			new BlockPosition(1, 2, 3),
			Facing::UP,
			0,
			ItemStackWrapper::legacy(ItemStack::null()),
			new Vector3(1, 2, 3),
			new Vector3(0.5, 0.5, 0.5),
			0,
			PredictedResult::FAILURE,
			0
		));
	}
}

final class InGamePacketHandlerTestPlayer extends Player{
	private bool $creative;
	private bool $interactBlockResult = true;
	/** @var list<array{string, int, int, int, 4?: int}> */
	private array $calls = [];
	/** @var list<bool> */
	private array $setUsingItemValues = [];

	public function setCreative(bool $creative) : void{
		$this->creative = $creative;
	}

	public function isCreative(bool $literal = false) : bool{
		return $this->creative;
	}

	public function initializeHungerAttribute() : Attribute{
		$this->attributeMap = new AttributeMap();
		$hunger = AttributeFactory::getInstance()->mustGet(Attribute::HUNGER);
		$hunger->markSynchronized();
		$this->attributeMap->add($hunger);
		return $hunger;
	}

	public function setInteractBlockResult(bool $interactBlockResult) : void{
		$this->interactBlockResult = $interactBlockResult;
	}

	public function selectHotbarSlot(int $hotbarSlot) : bool{
		return true;
	}

	public function interactBlock(Vector3 $pos, int $face, Vector3 $clickOffset) : bool{
		return $this->interactBlockResult;
	}

	public function attackBlock(Vector3 $pos, int $face) : bool{
		$this->calls[] = ["attack", (int) $pos->x, (int) $pos->y, (int) $pos->z, $face];
		return true;
	}

	public function breakBlock(Vector3 $pos) : bool{
		$this->calls[] = ["break", (int) $pos->x, (int) $pos->y, (int) $pos->z];
		return true;
	}

	public function setUsingItem(bool $value) : void{
		$this->setUsingItemValues[] = $value;
	}

	/**
	 * @return list<array{string, int, int, int, 4?: int}>
	 */
	public function getCalls() : array{
		return $this->calls;
	}

	/**
	 * @return list<bool>
	 */
	public function getSetUsingItemValues() : array{
		return $this->setUsingItemValues;
	}

	public function __destruct(){
		//NOOP
	}
}
