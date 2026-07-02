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

namespace pocketmine\tools\prioritize_upstream_backlog;

use DateTimeImmutable;
use Throwable;
use function array_count_values;
use function array_map;
use function count;
use function date;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function implode;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;
use function max;
use function str_contains;
use function strtolower;
use function time;
use function usort;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;
use const STDERR;

$argv ??= [];

$inputPath = $argv[1] ?? ".github/upstream-intake/open-items.json";
$outputJsonPath = $argv[2] ?? ".github/upstream-intake/priority-shortlist.json";
$limit = isset($argv[3]) ? max(1, (int) $argv[3]) : 40;

$input = file_get_contents($inputPath);
if($input === false){
	fwrite(STDERR, "Failed to read $inputPath" . PHP_EOL);
	exit(1);
}

$snapshot = json_decode($input, true);
if(!is_array($snapshot)){
	fwrite(STDERR, "Failed to decode $inputPath" . PHP_EOL);
	exit(1);
}

function normalizeLabels(array $labels) : array{
	return array_map(static fn(mixed $label) => strtolower((string) $label), $labels);
}

function hasLabel(array $labels, string $needle) : bool{
	return in_array(strtolower($needle), normalizeLabels($labels), true);
}

function containsAny(string $haystack, array $needles) : bool{
	foreach($needles as $needle){
		if(str_contains($haystack, $needle)){
			return true;
		}
	}

	return false;
}

function daysSince(string $date) : int{
	try{
		$timestamp = (new DateTimeImmutable($date))->getTimestamp();
	}catch(Throwable){
		return 9999;
	}

	return (int) ((time() - $timestamp) / 86400);
}

function scoreItem(array $item) : array{
	$lane = (string) ($item["lane"] ?? "general-triage");
	$type = (string) ($item["type"] ?? "issue");
	$title = (string) ($item["title"] ?? "");
	$labels = is_array($item["labels"] ?? null) ? $item["labels"] : [];
	$comments = (int) ($item["comments"] ?? 0);
	$text = strtolower($title . " " . implode(" ", $labels));

	$score = match($lane){
		"security-sensitive-review" => 120,
		"protocol-and-network" => 100,
		"bug-regression-crash" => 90,
		"upstream-pr-review" => 75,
		"plugin-api-compatibility" => 55,
		"general-triage" => 35,
		"feature-ideas" => 20,
		default => 25
	};

	$reasons = [$lane];

	if($type === "pull_request"){
		$score += 10;
		$reasons[] = "open-pr";
	}
	if((bool) ($item["draft"] ?? false)){
		$score -= 30;
		$reasons[] = "draft-pr";
	}
	if(hasLabel($labels, "Priority: High")){
		$score += 30;
		$reasons[] = "priority-high";
	}
	if(hasLabel($labels, "Priority: Low")){
		$score -= 10;
		$reasons[] = "priority-low";
	}
	if(hasLabel($labels, "Easy task")){
		$score += 20;
		$reasons[] = "easy-task";
	}
	if(hasLabel($labels, "Status: Reproduced")){
		$score += 30;
		$reasons[] = "reproduced";
	}
	if(hasLabel($labels, "Status: Debugged")){
		$score += 25;
		$reasons[] = "debugged";
	}
	if(hasLabel($labels, "Status: Blocked")){
		$score -= 35;
		$reasons[] = "blocked";
	}
	if(hasLabel($labels, "Status: Waiting on Author")){
		$score -= 25;
		$reasons[] = "waiting-on-author";
	}
	if(hasLabel($labels, "Type: Regression")){
		$score += 35;
		$reasons[] = "regression";
	}
	if(hasLabel($labels, "Type: Bug")){
		$score += 15;
		$reasons[] = "bug";
	}
	if(hasLabel($labels, "BC break")){
		$score -= 15;
		$reasons[] = "bc-break";
	}

	if(containsAny($text, ["protocol", "bedrock", "packet", "runtime id", "blockstate", "network", "raklib", "leveldb", "serializer", "serialization"])){
		$score += 15;
		$reasons[] = "protocol-keyword";
	}
	if(containsAny($text, ["crash", "regression", "fatal error"])){
		$score += 15;
		$reasons[] = "stability-keyword";
	}
	if(containsAny($text, ["dependabot", "github-actions", "bump "])){
		$score -= 25;
		$reasons[] = "automation-update";
	}
	if(containsAny($text, ["proposal", "opinions wanted", "feature", "enhancement"])){
		$score -= 8;
		$reasons[] = "discussion-heavy";
	}

	$ageDays = daysSince((string) ($item["updatedAt"] ?? ""));
	if($ageDays <= 30){
		$score += 25;
		$reasons[] = "recent";
	}elseif($ageDays <= 180){
		$score += 20;
		$reasons[] = "fresh";
	}elseif($ageDays <= 365){
		$score += 10;
		$reasons[] = "still-active";
	}elseif($ageDays > 1460){
		$score -= 35;
		$reasons[] = "very-old";
	}elseif($ageDays > 730){
		$score -= 20;
		$reasons[] = "old";
	}

	if($comments <= 3){
		$score += 5;
		$reasons[] = "small-thread";
	}elseif($comments >= 25){
		$score -= 10;
		$reasons[] = "large-thread";
	}

	return [$score, $reasons];
}

$items = [];
foreach(["issues", "pullRequests"] as $bucket){
	if(!is_array($snapshot[$bucket] ?? null)){
		continue;
	}
	foreach($snapshot[$bucket] as $item){
		if(!is_array($item)){
			continue;
		}
		[$score, $reasons] = scoreItem($item);
		$item["score"] = $score;
		$item["scoreReasons"] = $reasons;
		$items[] = $item;
	}
}

usort($items, static function(array $a, array $b) : int{
	return [$b["score"], $a["updatedAt"], $b["number"]] <=> [$a["score"], $b["updatedAt"], $a["number"]];
});

$shortlist = [];
foreach($items as $item){
	$shortlist[] = $item;
	if(count($shortlist) >= $limit){
		break;
	}
}

$counts = array_count_values(array_map(static fn(array $item) => (string) ($item["lane"] ?? "general-triage"), $items));
$counts["total"] = count($items);

$generatedAt = date("c");
$source = (string) ($snapshot["source"] ?? "pmmp/PocketMine-MP");
$output = [
	"source" => $source,
	"generatedAt" => $generatedAt,
	"limit" => $limit,
	"counts" => $counts,
	"items" => $shortlist
];

$json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if($json === false){
	fwrite(STDERR, "Failed to encode priority shortlist JSON" . PHP_EOL);
	exit(1);
}

if(file_put_contents($outputJsonPath, $json . PHP_EOL) === false){
	fwrite(STDERR, "Failed to write $outputJsonPath" . PHP_EOL);
	exit(1);
}

echo "Prioritized " . count($items) . " upstream items" . PHP_EOL;
echo "Selected " . count($shortlist) . " items" . PHP_EOL;
echo "Wrote $outputJsonPath" . PHP_EOL;
