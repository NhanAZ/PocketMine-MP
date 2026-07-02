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

namespace pocketmine\lang;

use PHPUnit\Framework\TestCase;
use pocketmine\utils\TextFormat;
use function dirname;

class LanguageTest extends TestCase{
	private Language $language;

	protected function setUp() : void{
		$this->language = new Language("eng", dirname(__DIR__, 3) . "/resources/translations");
	}

	public function testBaseTextFormatContainsParameterFormatting() : void{
		$message = (new Translatable("Before {%0} after", [TextFormat::AQUA . "Legendary"]))
			->baseTextFormat(TextFormat::GRAY);

		$translated = $this->language->translate($message);

		self::assertStringStartsWith(TextFormat::RESET . TextFormat::GRAY . "Before ", $translated);
		self::assertStringContainsString(TextFormat::AQUA . "Legendary", $translated);
		self::assertStringEndsWith(TextFormat::RESET . TextFormat::GRAY . " after", $translated);
	}

	public function testBaseTextFormatPreservesClientTranslationParameters() : void{
		$untranslatedParameterCount = 0;
		$translated = $this->language->translateString(
			"commands.give.success",
			["Item", "1", "Player"],
			"pocketmine.",
			$untranslatedParameterCount,
			TextFormat::GRAY
		);

		self::assertSame(3, $untranslatedParameterCount);
		self::assertSame(TextFormat::addBase(TextFormat::GRAY, "%commands.give.success"), $translated);
	}
}
