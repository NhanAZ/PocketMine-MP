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

namespace pocketmine\network\query;

use PHPUnit\Framework\TestCase;
use pocketmine\network\NetworkInterfaceStartException;
use function socket_bind;
use function socket_close;
use function socket_create;
use function socket_getsockname;
use const AF_INET;
use const SOCK_DGRAM;
use const SOL_UDP;

class DedicatedQueryNetworkInterfaceTest extends TestCase{
	public function testBindFailureThrowsNetworkInterfaceStartException() : void{
		$boundSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		self::assertNotFalse($boundSocket);
		self::assertTrue(socket_bind($boundSocket, "127.0.0.1", 0));

		$ip = "";
		$port = 0;
		self::assertTrue(socket_getsockname($boundSocket, $ip, $port));

		$interface = new DedicatedQueryNetworkInterface($ip, $port, false, $this->createMock(\Logger::class));
		try{
			$interface->start();
			self::fail("Expected the occupied port to reject the dedicated Query interface");
		}catch(NetworkInterfaceStartException $e){
			self::assertStringContainsString("Failed to bind", $e->getMessage());
		}finally{
			$interface->shutdown();
			socket_close($boundSocket);
		}
	}
}
