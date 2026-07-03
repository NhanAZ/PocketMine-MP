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
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginManager;
use pocketmine\Server;
use pocketmine\ServerConfigGroup;
use pocketmine\utils\Config;
use pocketmine\world\WorldManager;
use pocketmine\YmlServerProperties;
use ReflectionClass;

final class QueryInfoTest extends TestCase{
	public function testPlayerListEnabledByDefault() : void{
		$queryInfo = $this->createQueryInfo(["Steve", "Alex"]);

		self::assertTrue($queryInfo->canListPlayers());
		self::assertSame(["Steve", "Alex"], $queryInfo->getPlayerList());
		self::assertSame(2, $queryInfo->getPlayerCount());
		self::assertStringContainsString("Steve\x00Alex\x00", $queryInfo->getLongQuery());
	}

	public function testPlayerListCanBeHiddenFromLongQuery() : void{
		$queryInfo = $this->createQueryInfo(["Steve", "Alex"], false);
		$longQuery = $queryInfo->getLongQuery();

		self::assertFalse($queryInfo->canListPlayers());
		self::assertSame(["Steve", "Alex"], $queryInfo->getPlayerList());
		self::assertSame(2, $queryInfo->getPlayerCount());
		self::assertStringContainsString("numplayers\x002\x00", $longQuery);
		self::assertStringNotContainsString("Steve\x00", $longQuery);
		self::assertStringNotContainsString("Alex\x00", $longQuery);
		self::assertStringContainsString("\x00\x01player_\x00\x00\x00", $longQuery);
	}

	public function testSetListPlayersClearsLongQueryCache() : void{
		$queryInfo = $this->createQueryInfo(["Steve"]);

		self::assertStringContainsString("Steve\x00", $queryInfo->getLongQuery());

		$queryInfo->setListPlayers(false);

		self::assertFalse($queryInfo->canListPlayers());
		self::assertStringNotContainsString("Steve\x00", $queryInfo->getLongQuery());
	}

	/**
	 * @param string[] $playerNames
	 */
	private function createQueryInfo(array $playerNames, ?bool $listPlayers = null) : QueryInfo{
		$pocketmineYml = $this->createMock(Config::class);
		$pocketmineYml->method("getNested")->willReturnCallback(function(string $key, mixed $default = null) use ($listPlayers) : mixed{
			return match($key){
				YmlServerProperties::SETTINGS_QUERY_PLUGINS => true,
				YmlServerProperties::SETTINGS_QUERY_PLAYER_LIST => $listPlayers ?? $default,
				default => $default
			};
		});

		$serverProperties = $this->createMock(Config::class);
		$configGroup = new ServerConfigGroup($pocketmineYml, $serverProperties);

		$server = $this->createMock(Server::class);
		$server->method("getMotd")->willReturn("Test Server");
		$server->method("getConfigGroup")->willReturn($configGroup);
		$server->method("getPluginManager")->willReturn(new PluginManager($server, null));
		$server->method("getOnlinePlayers")->willReturn($this->createPlayers($playerNames));
		$server->method("getGamemode")->willReturn(GameMode::SURVIVAL);
		$server->method("getVersion")->willReturn("1.2.3");
		$server->method("getName")->willReturn("PocketMine-MP");
		$server->method("getPocketMineVersion")->willReturn("5.0.0");
		$worldManager = $this->createMock(WorldManager::class);
		$worldManager->method("getDefaultWorld")->willReturn(null);
		$server->method("getWorldManager")->willReturn($worldManager);
		$server->method("getMaxPlayers")->willReturn(20);
		$server->method("hasWhitelist")->willReturn(false);
		$server->method("getPort")->willReturn(19132);
		$server->method("getIp")->willReturn("0.0.0.0");

		return new QueryInfo($server);
	}

	/**
	 * @param string[] $names
	 *
	 * @return Player[]
	 */
	private function createPlayers(array $names) : array{
		$players = [];
		foreach($names as $name){
			/** @var QueryInfoTestPlayer $player */
			$player = (new ReflectionClass(QueryInfoTestPlayer::class))->newInstanceWithoutConstructor();
			$player->setQueryInfoTestName($name);
			$players[] = $player;
		}

		return $players;
	}
}

final class QueryInfoTestPlayer extends Player{
	private string $queryInfoTestName;

	public function setQueryInfoTestName(string $name) : void{
		$this->queryInfoTestName = $name;
	}

	public function getName() : string{
		return $this->queryInfoTestName;
	}

	public function __destruct(){
		//NOOP
	}
}
