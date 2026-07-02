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

namespace pocketmine\network\mcpe\protocol;

use PHPUnit\Framework\TestCase;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use function bin2hex;
use function hex2bin;

class BossEventPacketTest extends TestCase{
	private const SHOW_PACKET_HEX = "4a02000004426f737304426f73730000003f0500";
	private const HIDE_PACKET_HEX = "4a0200020000000000000000";

	public function testShowPacketUsesV1001FixedPayload() : void{
		$packet = BossEventPacket::show(1, "Boss", 0.5, BossBarColor::PURPLE, 0);

		$out = new ByteBufferWriter();
		$packet->encode($out);

		self::assertSame(self::SHOW_PACKET_HEX, bin2hex($out->getData()));
	}

	public function testHidePacketUsesV1001FixedPayload() : void{
		$packet = BossEventPacket::hide(1);

		$out = new ByteBufferWriter();
		$packet->encode($out);

		self::assertSame(self::HIDE_PACKET_HEX, bin2hex($out->getData()));
	}

	public function testShowPacketDecodesV1001FixedPayload() : void{
		$packet = new BossEventPacket();
		$packet->decode(new ByteBufferReader(self::fromHex(self::SHOW_PACKET_HEX)));

		self::assertSame(1, $packet->bossActorUniqueId);
		self::assertSame(0, $packet->playerActorUniqueId);
		self::assertSame(BossEventPacket::TYPE_SHOW, $packet->eventType);
		self::assertSame("Boss", $packet->title);
		self::assertSame("Boss", $packet->filteredTitle);
		self::assertSame(0.5, $packet->healthPercent);
		self::assertSame(BossBarColor::PURPLE, $packet->color);
		self::assertSame(0, $packet->overlay);
	}

	private static function fromHex(string $hex) : string{
		$result = hex2bin($hex);
		if($result === false){
			self::fail("Invalid packet hex fixture");
		}
		return $result;
	}
}
