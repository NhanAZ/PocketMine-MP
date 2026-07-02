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

namespace pocketmine\tools\audit_maintenance_sources;

use function array_filter;
use function array_is_list;
use function array_map;
use function count;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function getenv;
use function implode;
use function is_array;
use function is_dir;
use function is_string;
use function json_decode;
use function json_encode;
use function mkdir;
use function preg_match;
use function rawurlencode;
use function str_starts_with;
use function strtolower;
use function stream_context_create;
use function trim;
use function usort;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;
use const STDERR;
use const STDOUT;

enum SourceKind : string{
	case ROOT = "root";
	case IMPORTED_PACKAGE = "imported-package";
	case COMPOSER_PACKAGE = "composer-package";
	case PROTOCOL_REFERENCE = "protocol-reference";
}

final class SourceDefinition{
	public function __construct(
		public SourceKind $kind,
		public string $name,
		public string $repository,
		public ?string $branch,
		public ?string $pinnedRef,
		public ?string $path,
		public ?string $role,
		public ?string $declaredLicense
	){}
}

final class AuditResult{
	public function __construct(
		public SourceDefinition $source,
		public string $branch,
		public string $latestRef,
		public string $license,
		public string $url,
		public string $signal
	){}

	/** @return array<string, string|null> */
	public function toArray() : array{
		return [
			"kind" => $this->source->kind->value,
			"name" => $this->source->name,
			"repository" => $this->source->repository,
			"branch" => $this->branch,
			"pinned_ref" => $this->source->pinnedRef,
			"latest_ref" => $this->latestRef,
			"license" => $this->license,
			"url" => $this->url,
			"path" => $this->source->path,
			"role" => $this->source->role,
			"signal" => $this->signal
		];
	}
}

/** @return array<string, mixed> */
function requireObject(mixed $value, string $source) : array{
	if(!is_array($value) || array_is_list($value)){
		throw new \RuntimeException("Expected a JSON object in $source");
	}
	/** @var array<string, mixed> $value */
	return $value;
}

/** @return array<string, mixed> */
function decodeObject(string $json, string $source) : array{
	$decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
	return requireObject($decoded, $source);
}

/** @param array<string, mixed> $data */
function requiredString(array $data, string $key, string $source) : string{
	$value = $data[$key] ?? null;
	if(!is_string($value) || $value === ""){
		throw new \RuntimeException("Expected non-empty string '$key' in $source");
	}
	return $value;
}

/** @param array<string, mixed> $data */
function optionalString(array $data, string $key) : ?string{
	$value = $data[$key] ?? null;
	return is_string($value) && $value !== "" ? $value : null;
}

function fetchGithubJson(string $url) : mixed{
	$headers = [
		"Accept: application/vnd.github+json",
		"X-GitHub-Api-Version: 2022-11-28",
		"User-Agent: NhanAZ-PocketMine-MP-Source-Audit"
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
		throw new \RuntimeException("Failed to fetch $url");
	}
	$statusLine = $http_response_header[0] ?? "";
	if(preg_match('/\s([0-9]{3})\s/', $statusLine, $matches) !== 1){
		throw new \RuntimeException("Could not read HTTP status for $url");
	}
	$status = (int) $matches[1];
	if($status < 200 || $status >= 300){
		throw new \RuntimeException("GitHub API returned HTTP $status for $url");
	}
	return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
}

/** @return array<string, mixed> */
function fetchGithubObject(string $url) : array{
	return requireObject(fetchGithubJson($url), $url);
}

/** @return list<SourceDefinition> */
function loadConfiguredSources(string $path) : array{
	$contents = file_get_contents($path);
	if($contents === false){
		throw new \RuntimeException("Failed to read $path");
	}
	$config = decodeObject($contents, $path);
	$items = $config["sources"] ?? null;
	if(!is_array($items) || !array_is_list($items)){
		throw new \RuntimeException("Expected a source list in $path");
	}
	$result = [];
	foreach($items as $index => $item){
		$item = requireObject($item, "source index $index in $path");
		$kindValue = requiredString($item, "kind", $path);
		$kind = SourceKind::tryFrom($kindValue);
		if($kind === null || $kind === SourceKind::COMPOSER_PACKAGE){
			throw new \RuntimeException("Invalid configured source kind '$kindValue'");
		}
		$result[] = new SourceDefinition(
			$kind,
			requiredString($item, "name", $path),
			requiredString($item, "repository", $path),
			optionalString($item, "branch"),
			optionalString($item, "pinned_ref"),
			optionalString($item, "path"),
			optionalString($item, "role"),
			optionalString($item, "license")
		);
	}
	return $result;
}

function githubRepositoryFromUrl(string $url) : ?string{
	if(preg_match('~^https://github\.com/([^/]+/[^/]+?)(?:\.git)?$~', $url, $matches) !== 1){
		return null;
	}
	return $matches[1];
}

/** @return list<SourceDefinition> */
function loadComposerSources(string $path) : array{
	$contents = file_get_contents($path);
	if($contents === false){
		throw new \RuntimeException("Failed to read $path");
	}
	$lock = decodeObject($contents, $path);
	$packages = $lock["packages"] ?? null;
	if(!is_array($packages) || !array_is_list($packages)){
		throw new \RuntimeException("Expected a package list in $path");
	}
	$result = [];
	foreach($packages as $index => $package){
		$package = requireObject($package, "package index $index in $path");
		$name = optionalString($package, "name");
		if($name === null || !str_starts_with($name, "pocketmine/")){
			continue;
		}
		$dist = $package["dist"] ?? null;
		$dist = is_array($dist) && !array_is_list($dist) ? requireObject($dist, "dist for $name") : null;
		if($dist !== null && optionalString($dist, "type") === "path"){
			continue;
		}
		$source = $package["source"] ?? null;
		if(!is_array($source) || array_is_list($source)){
			continue;
		}
		$source = requireObject($source, "source for $name");
		$sourceUrl = optionalString($source, "url");
		$repository = $sourceUrl !== null ? githubRepositoryFromUrl($sourceUrl) : null;
		if($repository === null){
			continue;
		}
		$result[] = new SourceDefinition(
			SourceKind::COMPOSER_PACKAGE,
			$name,
			$repository,
			null,
			optionalString($source, "reference"),
			"vendor/" . $name,
			"Composer-managed PMMP dependency",
			is_array($package["license"] ?? null) && is_string($package["license"][0] ?? null) ? $package["license"][0] : null
		);
	}
	return $result;
}

function auditSource(SourceDefinition $source) : AuditResult{
	$baseUrl = "https://api.github.com/repos/" . $source->repository;
	$metadata = fetchGithubObject($baseUrl);
	$branch = $source->branch ?? requiredString($metadata, "default_branch", $baseUrl);
	$commitUrl = $baseUrl . "/commits/" . rawurlencode($branch);
	$commit = fetchGithubObject($commitUrl);
	$latestRef = requiredString($commit, "sha", $commitUrl);
	$license = $source->declaredLicense ?? "NOASSERTION";
	$licenseData = $metadata["license"] ?? null;
	if(is_array($licenseData) && !array_is_list($licenseData)){
		$licenseData = requireObject($licenseData, "license metadata for " . $source->repository);
		$githubLicense = optionalString($licenseData, "spdx_id");
		if($githubLicense !== null && $githubLicense !== "NOASSERTION"){
			$license = $githubLicense;
		}
	}
	$url = requiredString($metadata, "html_url", $baseUrl);
	$signal = match(true){
		$source->kind === SourceKind::PROTOCOL_REFERENCE => "reference-only",
		$source->pinnedRef === null => "observed",
		strtolower($source->pinnedRef) === strtolower($latestRef) => "at-branch-head",
		default => "review-needed"
	};
	return new AuditResult($source, $branch, $latestRef, $license, $url, $signal);
}

/** @var list<string> $arguments */
$arguments = $argv;
$configPath = $arguments[1] ?? ".github/maintenance-sources/sources.json";
$composerLockPath = $arguments[2] ?? "composer.lock";
$outputDir = $arguments[3] ?? ".github/maintenance-sources";

try{
	$sources = [...loadConfiguredSources($configPath), ...loadComposerSources($composerLockPath)];
	$results = array_map(auditSource(...), $sources);
	usort($results, static function(AuditResult $left, AuditResult $right) : int{
		$kindOrder = ["root" => 0, "imported-package" => 1, "composer-package" => 2, "protocol-reference" => 3];
		return [$kindOrder[$left->source->kind->value], strtolower($left->source->name)] <=> [$kindOrder[$right->source->kind->value], strtolower($right->source->name)];
	});
	if(!is_dir($outputDir) && !mkdir($outputDir, 0777, true)){
		throw new \RuntimeException("Failed to create $outputDir");
	}
	$json = json_encode(array_map(static fn(AuditResult $result) : array => $result->toArray(), $results), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
	if(file_put_contents($outputDir . "/report.json", $json . PHP_EOL) === false){
		throw new \RuntimeException("Failed to write source audit report");
	}
	$reviewCount = count(array_filter($results, static fn(AuditResult $result) : bool => $result->signal === "review-needed"));
	fwrite(STDOUT, "Audited " . count($results) . " sources; $reviewCount require review." . PHP_EOL);
}catch(\Throwable $e){
	fwrite(STDERR, $e->getMessage() . PHP_EOL);
	exit(1);
}
