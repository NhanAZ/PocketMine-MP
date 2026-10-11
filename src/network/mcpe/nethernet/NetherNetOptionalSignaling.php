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

namespace pocketmine\network\mcpe\nethernet;

use pocketmine\nethernet\signaling\Signaling;
use pocketmine\nethernet\signaling\SignalingContext;
use pocketmine\nethernet\signaling\SignalingException;

/**
 * Wraps an optional signaling transport. It does not stop the server from starting even if this signaling fails to start.
 */
final class NetherNetOptionalSignaling implements Signaling{

	private bool $failed = false;

	public function __construct(
		private Signaling $inner,
		private string $failureMessage,
		private \Logger $logger
	){}

	public function start(SignalingContext $context) : void{
		try{
			$this->inner->start($context);
		}catch(SignalingException $e){
			$this->failed = true;
			$this->logger->warning($this->failureMessage . ": " . $e->getMessage());
		}
	}

	public function tick(float $now) : void{
		if(!$this->failed){
			$this->inner->tick($now);
		}
	}

	public function shutdown() : void{
		$this->inner->shutdown();
	}
}
