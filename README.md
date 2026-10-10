# RulesBook

Show a preview of server rules when a player first joins or runs `/rulesbook`.

## Requirements

- An Axolotl-PM server with a compatible API 5 runtime. `plugin.yml` declares API `5.0.0`. PHPStan at maximum level passes against the 5.0.0 and 5.49.1 source revisions; this does not establish compatibility with every intermediate release. See the [build workflow](.github/workflows/ci.yml) for the exact server revision used in CI.
- PHP 8.1 or newer, subject to the server's own PHP requirements.

## Installation

1. Download the verified RulesBook PHAR from a successful [build workflow](.github/workflows/ci.yml) run or the matching GitHub release.
2. Put the PHAR in the server's `plugins` directory and start the server.
3. Edit `plugins/RulesBook/config.yml` to replace the example rules, then restart the server.

The PHAR includes the pinned libBook virion and its MIT license. A separate libBook plugin is not required.

## Usage and configuration

RulesBook previews the book on a player's first join. Players can reopen it with `/rulesbook` or `/rules`. The `rulesbook.command` permission defaults to all players. Console senders receive an explanation instead of a book.

Set `pages` in `config.yml` to a non-empty map of `page_1`, `page_2` and subsequent numbered keys. Values must be strings. Page numbers must be between 1 and 100. Invalid keys or values stop plugin activation with a configuration error before any player joins. Axolotl-PM stops server startup when a plugin cannot enable; fix the config and restart. Minecraft formatting codes can be written with `&` and are converted when the configuration loads.

```yaml
pages:
  page_1: "Welcome to the server."
  page_2: "Be respectful to other players."
```

## Building and testing

The [CI workflow](.github/workflows/ci.yml) runs PHPStan at maximum level, prepares the pinned [libBook](https://github.com/cooldogepm/libBook) source, and uses [DevTools](https://github.com/NhanAZ/DevTools) to build and inspect the PHAR. To reproduce a local build after checking out submodules, run:

```shell
php tools/prepare-libbook.php
php -d phar.readonly=0 path/to/DevTools/bin/devtools.php build --project=. --virions=.devtools-virions --out=build --json
```

The preparation step quotes libBook's numeric PHP version in a temporary manifest so DevTools can parse it. It does not alter the pinned upstream source.

## License and support

RulesBook is licensed under GPL-3.0. The bundled libBook code is MIT licensed and credited in [third-party notices](THIRD_PARTY_NOTICES.md). Report issues in this repository or contact [NhanAZ Discord](https://discord.gg/j2X83ujT6c).
