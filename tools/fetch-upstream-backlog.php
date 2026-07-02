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

namespace pocketmine\tools\fetch_upstream_backlog;

use function array_key_exists;
use function array_map;
use function count;
use function date;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function getenv;
use function implode;
use function is_array;
use function is_dir;
use function json_decode;
use function json_encode;
use function mkdir;
use function preg_match;
use function sprintf;
use function str_contains;
use function strtolower;
use function stream_context_create;
use function trim;
use function usort;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;
use const STDERR;

$argv ??= [];

$source = $argv[1] ?? "pmmp/PocketMine-MP";
$outputDir = $argv[2] ?? ".github/upstream-intake";

if(!preg_match('/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/', $source)){
	fwrite(STDERR, "Invalid repository name. Use owner/repo, for example pmmp/PocketMine-MP" . PHP_EOL);
	exit(1);
}

if(!is_dir($outputDir) && !mkdir($outputDir, 0777, true)){
	fwrite(STDERR, "Failed to create output directory $outputDir" . PHP_EOL);
	exit(1);
}

[$owner, $repo] = explode("/", $source, 2);

function fetchGithubJson(string $url) : array{
	$headers = [
		"Accept: application/vnd.github+json",
		"X-GitHub-Api-Version: 2022-11-28",
		"User-Agent: NhanAZ-PocketMine-MP-Upstream-Intake"
	];

	$token = getenv("GITHUB_TOKEN");
	if($token !== false && trim($token) !== ""){
		$headers[] = "Authorization: Bearer " . trim($token);
	}

	$context = stream_context_create([
		"http" => [
			"method" => "GET",
			"header" => implode("\r\n", $headers),
			"ignore_errors" => true
		]
	]);

	$response = file_get_contents($url, false, $context);
	if($response === false){
		fwrite(STDERR, "Failed to fetch $url" . PHP_EOL);
		exit(1);
	}

	$statusLine = $http_response_header[0] ?? "";
	if(!preg_match('/\s([0-9]{3})\s/', $statusLine, $matches)){
		fwrite(STDERR, "Could not read HTTP status for $url" . PHP_EOL);
		exit(1);
	}

	$status = (int) $matches[1];
	if($status < 200 || $status >= 300){
		fwrite(STDERR, "GitHub API returned HTTP $status for $url" . PHP_EOL);
		fwrite(STDERR, $response . PHP_EOL);
		exit(1);
	}

	$decoded = json_decode($response, true);
	if(!is_array($decoded)){
		fwrite(STDERR, "GitHub API returned invalid JSON for $url" . PHP_EOL);
		exit(1);
	}

	return $decoded;
}

function fetchPaged(string $owner, string $repo, string $endpoint) : array{
	$result = [];
	for($page = 1; ; ++$page){
		$url = sprintf(
			"https://api.github.com/repos/%s/%s/%s?state=open&per_page=100&page=%d",
			$owner,
			$repo,
			$endpoint,
			$page
		);
		$pageItems = fetchGithubJson($url);
		foreach($pageItems as $item){
			$result[] = $item;
		}
		if(count($pageItems) < 100){
			break;
		}
	}

	return $result;
}

function labelNames(array $labels) : array{
	return array_map(static fn(array $label) => (string) ($label["name"] ?? ""), $labels);
}

function classifyItem(string $type, string $title, array $labels) : string{
	$text = strtolower($title . " " . implode(" ", $labels));

	if(str_contains($text, "security") || str_contains($text, "exploit") || str_contains($text, "vulnerability")){
		return "security-sensitive-review";
	}
	if(preg_match('/protocol|bedrock|packet|network|mcpe|raklib|runtime id|block state|blockstate|leveldb|serializer|serialization|login|client|query|gs4/', $text)){
		return "protocol-and-network";
	}
	if(str_contains($text, "crash") || str_contains($text, "regression") || str_contains($text, "bug")){
		return "bug-regression-crash";
	}
	if(str_contains($text, "plugin") || str_contains($text, "api") || str_contains($text, "compat")){
		return "plugin-api-compatibility";
	}
	if($type === "pull_request"){
		return "upstream-pr-review";
	}
	if(str_contains($text, "feature") || str_contains($text, "proposal") || str_contains($text, "enhancement")){
		return "feature-ideas";
	}

	return "general-triage";
}

function normalizeIssue(array $item, string $type, ?array $pullDetails = null) : array{
	$labels = labelNames($item["labels"] ?? []);
	$title = (string) ($item["title"] ?? "");
	$normalized = [
		"type" => $type,
		"number" => (int) ($item["number"] ?? 0),
		"title" => $title,
		"url" => (string) ($item["html_url"] ?? ""),
		"author" => (string) ($item["user"]["login"] ?? ""),
		"labels" => $labels,
		"createdAt" => (string) ($item["created_at"] ?? ""),
		"updatedAt" => (string) ($item["updated_at"] ?? ""),
		"comments" => (int) ($item["comments"] ?? 0),
		"lane" => classifyItem($type, $title, $labels)
	];

	if($pullDetails !== null){
		$normalized["draft"] = (bool) ($pullDetails["draft"] ?? false);
		$normalized["baseRef"] = (string) ($pullDetails["base"]["ref"] ?? "");
		$normalized["headRef"] = (string) ($pullDetails["head"]["ref"] ?? "");
		$normalized["headRepo"] = (string) ($pullDetails["head"]["repo"]["full_name"] ?? "");
	}

	return $normalized;
}

function sortItems(array &$items) : void{
	usort($items, static function(array $a, array $b) : int{
		return [$a["lane"], $b["updatedAt"], $b["number"]] <=> [$b["lane"], $a["updatedAt"], $a["number"]];
	});
}

$issuesEndpointItems = fetchPaged($owner, $repo, "issues");
$pullEndpointItems = fetchPaged($owner, $repo, "pulls");

$pullDetailsByNumber = [];
foreach($pullEndpointItems as $pull){
	$pullDetailsByNumber[(int) ($pull["number"] ?? 0)] = $pull;
}

$issues = [];
$pullRequests = [];
foreach($issuesEndpointItems as $item){
	$number = (int) ($item["number"] ?? 0);
	if(array_key_exists("pull_request", $item)){
		$pullRequests[] = normalizeIssue($item, "pull_request", $pullDetailsByNumber[$number] ?? null);
	}else{
		$issues[] = normalizeIssue($item, "issue");
	}
}

sortItems($issues);
sortItems($pullRequests);

$generatedAt = date("c");
$jsonPath = $outputDir . "/open-items.json";

$snapshot = [
	"source" => $source,
	"generatedAt" => $generatedAt,
	"counts" => [
		"issues" => count($issues),
		"pullRequests" => count($pullRequests),
		"total" => count($issues) + count($pullRequests)
	],
	"issues" => $issues,
	"pullRequests" => $pullRequests
];

$json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if($json === false){
	fwrite(STDERR, "Failed to encode backlog JSON" . PHP_EOL);
	exit(1);
}

if(file_put_contents($jsonPath, $json . PHP_EOL) === false){
	fwrite(STDERR, "Failed to write $jsonPath" . PHP_EOL);
	exit(1);
}

echo "Fetched " . count($issues) . " open issues and " . count($pullRequests) . " open pull requests from $source" . PHP_EOL;
echo "Wrote $jsonPath" . PHP_EOL;
