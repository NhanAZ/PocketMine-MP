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

namespace pocketmine\command\defaults;

use PHPUnit\Framework\TestCase;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;

final class TestableVanillaCommand extends VanillaCommand{
	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{}

	public function parseInteger(CommandSender $sender, string $value, int $min, int $max) : int{
		return $this->getInteger($sender, $value, $min, $max);
	}

	public function parseBoundedInt(CommandSender $sender, string $value, int $min, int $max) : ?int{
		return $this->getBoundedInt($sender, $value, $min, $max);
	}
}

final class VanillaCommandTest extends TestCase{

	private function createCommand() : TestableVanillaCommand{
		return new TestableVanillaCommand("test");
	}

	public function testIntegerIsClampedBeforeOutOfRangeStringIsCast() : void{
		$command = $this->createCommand();
		$sender = $this->createMock(CommandSender::class);

		self::assertSame(100, $command->parseInteger($sender, "300000000000000000000", -100, 100));
		self::assertSame(-100, $command->parseInteger($sender, "-300000000000000000000", -100, 100));
	}

	public function testBoundedIntRejectsOutOfRangeStringBeforeCast() : void{
		$command = $this->createCommand();
		$sender = $this->createMock(CommandSender::class);
		$sender->expects(self::exactly(2))->method("sendMessage");

		self::assertNull($command->parseBoundedInt($sender, "300000000000000000000", -100, 100));
		self::assertNull($command->parseBoundedInt($sender, "-300000000000000000000", -100, 100));
	}

	public function testBoundedIntPreservesNumericStringBehaviour() : void{
		$command = $this->createCommand();
		$sender = $this->createMock(CommandSender::class);

		self::assertSame(12, $command->parseBoundedInt($sender, "12.75", 0, 200));
		self::assertSame(100, $command->parseBoundedInt($sender, "1e2", 0, 200));
	}

	public function testBoundedIntRejectsNonNumericString() : void{
		$this->expectException(InvalidCommandSyntaxException::class);

		$this->createCommand()->parseBoundedInt($this->createMock(CommandSender::class), "not-a-number", 0, 100);
	}
}
