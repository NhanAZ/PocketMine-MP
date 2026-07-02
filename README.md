<p align="center">
	<a href="https://github.com/NhanAZ/PocketMine-MP">
		<!--[if IE]>
			<img src="https://github.com/pmmp/PocketMine-MP/blob/stable/.github/readme/pocketmine.png" alt="The PocketMine-MP logo" title="PocketMine" loading="eager" />
		<![endif]-->
		<picture>
			<source srcset="https://raw.githubusercontent.com/pmmp/PocketMine-MP/stable/.github/readme/pocketmine-dark-rgb.gif" media="(prefers-color-scheme: dark)">
			<img src="https://raw.githubusercontent.com/pmmp/PocketMine-MP/stable/.github/readme/pocketmine-rgb.gif" loading="eager" />
		</picture>
	</a><br>
	<b>A highly customisable, open source server software for Minecraft: Bedrock Edition written in PHP</b>
</p>

<p align="center">
	<a href="https://github.com/NhanAZ/PocketMine-MP/actions/workflows/main.yml"><img src="https://github.com/NhanAZ/PocketMine-MP/actions/workflows/main.yml/badge.svg" alt="CI" /></a>
	<a href="https://github.com/NhanAZ/PocketMine-MP/releases/latest"><img alt="GitHub release (latest SemVer)" src="https://img.shields.io/github/v/release/NhanAZ/PocketMine-MP?label=release&sort=semver"></a>
	<br>
	<a href="https://github.com/NhanAZ/PocketMine-MP/releases"><img alt="GitHub all releases" src="https://img.shields.io/github/downloads/NhanAZ/PocketMine-MP/total?label=downloads%40total"></a>
	<a href="https://github.com/NhanAZ/PocketMine-MP/releases/latest"><img alt="GitHub release (latest by SemVer)" src="https://img.shields.io/github/downloads/NhanAZ/PocketMine-MP/latest/total?sort=semver"></a>
</p>

> [!WARNING]
> This is a personal, experimental fork of PocketMine-MP maintained by NhanAZ.
> It is not an official PocketMine-MP release and may contain unstable changes,
> security issues, protocol bugs, or breaking behaviour. Review changes carefully
> before production use.

## Fork status
This fork is maintained as a personal, community-oriented continuation focused on practical maintenance, faster protocol updates, and open contribution.
It is not affiliated with or endorsed by the upstream PocketMine-MP project, and should not be read as criticism of upstream maintainers or contributors.

The original LGPL-3.0 license and attribution are retained.
Fork-specific direction is documented in [FORK_POLICY.md](FORK_POLICY.md), planned work is tracked in [ROADMAP.md](ROADMAP.md), and AI agent workflow rules are documented in [AGENTS.md](AGENTS.md).

## What is this?
PocketMine-MP is a highly customisable server software for Minecraft: Bedrock Edition, built from scratch in PHP, with over 10 years of history.

If you're looking to create a Minecraft: Bedrock server with **custom functionality**, look no further.

- 🧩 **Powerful plugin API** - extend and customise gameplay as you see fit
- 🗺️ **Rich ecosystem** and **large developer community** - find plugins easily and learn to develop your own
- 🌐 **Multi-world support** - offer a more varied game experience to players without transferring them to other server nodes
- 🏎️ **Performance** - get 100+ players onto one server (depending on hardware and plugins)
- ⤴️ **Continuously updated** - new Minecraft versions are usually supported within days

## :x: PocketMine-MP is NOT a vanilla Minecraft server software.
**It is poorly suited to hosting vanilla survival servers.**
It doesn't have many features from the vanilla game, such as vanilla world generation, redstone, mob AI, and various other things.

If you just want to play **vanilla survival multiplayer**, consider using the [official Minecraft: Bedrock server software](https://minecraft.net/download/server/bedrock) instead of PocketMine-MP.

If that's not an option for you, you may be able to add some of PocketMine-MP's missing features using plugins from [Poggit](https://poggit.pmmp.io/plugins), or write plugins to implement them yourself.

## Getting Started
- [Documentation](http://pmmp.readthedocs.org/)
- [Installation instructions](https://pmmp.readthedocs.io/en/rtfd/installation.html)
- [Upstream Docker image](https://github.com/pmmp/PocketMine-MP/pkgs/container/pocketmine-mp)
- [Plugin repository](https://poggit.pmmp.io/plugins)

## Community & Support
This fork does not currently have an official support server.
For fork-specific bugs, regressions, and contribution discussion, use this repository's GitHub issues and pull requests.

The upstream PocketMine-MP community can be found on [Discord](https://discord.gg/bmSAZBG), but please do not ask them to support fork-specific changes.

You can also post questions on [StackOverflow](https://stackoverflow.com/tags/pocketmine) under the tag `pocketmine`.

## Developing Plugins
If you want to write your own plugins, the following resources may be useful.
For fork-specific behaviour, include the fork version and commit hash when asking for help or reporting issues.

 * [Developer documentation](https://devdoc.pmmp.io) - General documentation for PocketMine-MP plugin developers
 * [Latest release API documentation](https://apidoc.pmmp.io) - Doxygen API documentation generated for each release
 * [Latest bleeding-edge API documentation](https://apidoc-dev.pmmp.io) - Doxygen API documentation generated weekly from `major-next` branch
 * [DevTools](https://github.com/pmmp/DevTools/) - Development tools plugin for creating plugins
 * [ExamplePlugin](https://github.com/pmmp/ExamplePlugin/) - Example plugin demonstrating some basic API features

## Contributing to this fork
This fork accepts community contributions on a best-effort basis.
The following resources will be useful if you want to contribute.
 * [Building and running PocketMine-MP from source](BUILDING.md)
 * [Contributing Guidelines](CONTRIBUTING.md)
 * [Fork Policy](FORK_POLICY.md)

New here? Start with small issues, focused bug fixes, documentation improvements, or protocol compatibility reports.

## Upstream donations
PocketMine-MP is free, but it requires a lot of time and effort from unpaid volunteers to develop.
If you want to support the upstream PocketMine-MP project, you can use the following methods:

- [Patreon](https://www.patreon.com/pocketminemp)
- Bitcoin (BTC): `bc1q2v5ngyf8ugyd55kqa9ep35g2rv342ueqm6ks33`
- Stellar Lumens (XLM): `GAAC5WZ33HCTE3BFJFZJXONMEIBNHFLBXM2HJVAZHXXPYA3HP5XPPS7T`

Thanks for your support!

## Licensing information
This project is licensed under LGPL-3.0. Please see the [LICENSE](/LICENSE) file for details.

pmmp/PocketMine are not affiliated with Mojang. All brands and trademarks belong to their respective owners. PocketMine-MP is not a Mojang-approved software, nor is it associated with Mojang.
