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

namespace pocketmine\network\raklib;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use raklib\protocol\OpenConnectionReply1;
use raklib\protocol\OpenConnectionRequest1;
use raklib\protocol\OpenConnectionRequest2;
use raklib\protocol\Packet;
use raklib\protocol\PacketSerializer;
use raklib\server\Server as RakServer;
use raklib\server\ServerSession;
use raklib\server\SimpleProtocolAcceptor;
use raklib\server\UnconnectedMessageHandler;
use raklib\utils\InternetAddress;

class RakLibAntiSpoofCookieTest extends TestCase{
	private const RAKNET_PROTOCOL = 11;

	public function testOpenConnectionReply1RoundTripsOptionalCookie() : void{
		$withCookie = new OpenConnectionReply1();
		$withCookie->decode(new PacketSerializer(self::encodePacket(OpenConnectionReply1::create(123456, 0x01020304, 1492))));

		self::assertSame(123456, $withCookie->serverID);
		self::assertSame(0x01020304, $withCookie->cookie);
		self::assertSame(1492, $withCookie->mtuSize);

		$withoutCookie = new OpenConnectionReply1();
		$withoutCookie->decode(new PacketSerializer(self::encodePacket(OpenConnectionReply1::create(123456, null, 1492))));

		self::assertSame(123456, $withoutCookie->serverID);
		self::assertNull($withoutCookie->cookie);
		self::assertSame(1492, $withoutCookie->mtuSize);
	}

	public function testOpenConnectionRequest2RoundTripsCookieAndLegacyForms() : void{
		$withCookie = new OpenConnectionRequest2();
		$withCookie->serverAddress = new InternetAddress("127.0.0.1", 19132, 4);
		$withCookie->cookie = 0x01020304;
		$withCookie->mtuSize = 1400;
		$withCookie->clientID = 987654321;

		$decodedWithCookie = new OpenConnectionRequest2();
		$decodedWithCookie->decode(new PacketSerializer(self::encodePacket($withCookie)));

		self::assertSame(0x01020304, $decodedWithCookie->cookie);
		self::assertSame("127.0.0.1", $decodedWithCookie->serverAddress->getIp());
		self::assertSame(19132, $decodedWithCookie->serverAddress->getPort());
		self::assertSame(1400, $decodedWithCookie->mtuSize);
		self::assertSame(987654321, $decodedWithCookie->clientID);

		$legacy = new OpenConnectionRequest2();
		$legacy->serverAddress = new InternetAddress("127.0.0.1", 19132, 4);
		$legacy->mtuSize = 1400;
		$legacy->clientID = 987654321;

		$decodedLegacy = new OpenConnectionRequest2();
		$decodedLegacy->decode(new PacketSerializer(self::encodePacket($legacy)));

		self::assertNull($decodedLegacy->cookie);
		self::assertSame("127.0.0.1", $decodedLegacy->serverAddress->getIp());
		self::assertSame(19132, $decodedLegacy->serverAddress->getPort());
		self::assertSame(1400, $decodedLegacy->mtuSize);
		self::assertSame(987654321, $decodedLegacy->clientID);
	}

	public function testCookieProtectedHandshakeAcceptsIssuedCookie() : void{
		$sentPackets = [];
		$server = $this->createServerMock($sentPackets, 2);
		$clientAddress = new InternetAddress("203.0.113.7", 52000, 4);
		$handler = new UnconnectedMessageHandler($server, new SimpleProtocolAcceptor(self::RAKNET_PROTOCOL), true);

		self::assertTrue($handler->handleRaw(self::encodePacket(self::createRequest1()), $clientAddress));
		$reply1 = $sentPackets[0];
		self::assertInstanceOf(OpenConnectionReply1::class, $reply1);
		self::assertIsInt($reply1->cookie);

		$server->expects(self::once())
			->method('createSession')
			->with(self::identicalTo($clientAddress), 987654321, 1400)
			->willReturn(self::createStub(ServerSession::class));

		$request2 = self::createRequest2($reply1->cookie);
		self::assertTrue($handler->handleRaw(self::encodePacket($request2), $clientAddress));
	}

	public function testCookieProtectedHandshakeRejectsMismatchedCookie() : void{
		$sentPackets = [];
		$server = $this->createServerMock($sentPackets, 1);
		$clientAddress = new InternetAddress("203.0.113.8", 52001, 4);
		$handler = new UnconnectedMessageHandler($server, new SimpleProtocolAcceptor(self::RAKNET_PROTOCOL), true);

		self::assertTrue($handler->handleRaw(self::encodePacket(self::createRequest1()), $clientAddress));
		$reply1 = $sentPackets[0];
		self::assertInstanceOf(OpenConnectionReply1::class, $reply1);
		self::assertIsInt($reply1->cookie);

		$server->expects(self::never())->method('createSession');

		$request2 = self::createRequest2(($reply1->cookie + 1) & 0xffffffff);
		self::assertTrue($handler->handleRaw(self::encodePacket($request2), $clientAddress));
		self::assertSame(1, $handler->getCookieMismatchSinceLastRotation());
	}

	public function testCookieDisabledHandshakeAcceptsLegacyRequest2() : void{
		$sentPackets = [];
		$server = $this->createServerMock($sentPackets, 1);
		$clientAddress = new InternetAddress("203.0.113.9", 52002, 4);
		$handler = new UnconnectedMessageHandler($server, new SimpleProtocolAcceptor(self::RAKNET_PROTOCOL), false);

		$server->expects(self::once())
			->method('createSession')
			->with(self::identicalTo($clientAddress), 987654321, 1400)
			->willReturn(self::createStub(ServerSession::class));

		self::assertTrue($handler->handleRaw(self::encodePacket(self::createRequest2(null)), $clientAddress));
	}

	private static function createRequest1() : OpenConnectionRequest1{
		$request = new OpenConnectionRequest1();
		$request->protocol = self::RAKNET_PROTOCOL;
		$request->mtuSize = 1492;
		return $request;
	}

	private static function createRequest2(?int $cookie) : OpenConnectionRequest2{
		$request = new OpenConnectionRequest2();
		$request->serverAddress = new InternetAddress("127.0.0.1", 19132, 4);
		$request->cookie = $cookie;
		$request->mtuSize = 1400;
		$request->clientID = 987654321;
		return $request;
	}

	/**
	 * @param list<Packet> $sentPackets
	 */
	private function createServerMock(array &$sentPackets, int $sendPacketCount) : RakServer&MockObject{
		$server = $this->getMockBuilder(RakServer::class)
			->disableOriginalConstructor()
			->onlyMethods(['sendPacket', 'getID', 'getLogger', 'getPort', 'getMaxMtuSize', 'getSessionByAddress', 'createSession'])
			->getMock();

		$server->method('getID')->willReturn(123456);
		$server->method('getLogger')->willReturn(self::createStub(\Logger::class));
		$server->method('getPort')->willReturn(19132);
		$server->method('getMaxMtuSize')->willReturn(1492);
		$server->method('getSessionByAddress')->willReturn(null);
		$server->expects(self::exactly($sendPacketCount))
			->method('sendPacket')
			->willReturnCallback(static function(Packet $packet, InternetAddress $address) use (&$sentPackets) : void{
				$sentPackets[] = $packet;
			});

		return $server;
	}

	private static function encodePacket(Packet $packet) : string{
		$out = new PacketSerializer();
		$packet->encode($out);
		return $out->getBuffer();
	}
}
