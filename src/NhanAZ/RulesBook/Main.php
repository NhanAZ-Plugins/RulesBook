<?php

declare(strict_types=1);

namespace NhanAZ\RulesBook;

use cooldogedev\libBook\LibBook;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {
	/** @var array<int, string> */
	private array $pages = [];

	protected function onEnable(): void {
		$this->saveDefaultConfig();
		try {
			$this->loadPages();
		} catch (InvalidArgumentException $error) {
			$this->getLogger()->error("Invalid RulesBook configuration: " . $error->getMessage());
			throw new DisablePluginException($error->getMessage(), 0, $error);
		}
		LibBook::register($this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	private function loadPages(): void {
		$rawPages = $this->getConfig()->get("pages");
		if (!is_array($rawPages) || $rawPages === []) {
			throw new InvalidArgumentException("Config key pages must be a non-empty map.");
		}
		foreach ($rawPages as $pageId => $pageText) {
			if (!is_string($pageId) || preg_match('/^page_([1-9][0-9]*)$/D', $pageId, $matches) !== 1 || !is_string($pageText)) {
				throw new InvalidArgumentException("Each rules page must have a page_N key and string text.");
			}
			$index = (int) $matches[1] - 1;
			if ($index >= 100) {
				throw new InvalidArgumentException("RulesBook supports at most 100 pages.");
			}
			$this->pages[$index] = TextFormat::colorize($pageText);
		}
		ksort($this->pages, SORT_NUMERIC);
	}

	private function sendPreview(Player $player): void {
		$item = VanillaItems::WRITTEN_BOOK();
		foreach ($this->pages as $index => $pageText) {
			$item->setPageText($index, $pageText);
		}
		LibBook::sendPreview($player, $item);
	}

	public function onJoin(PlayerJoinEvent $event): void {
		$player = $event->getPlayer();
		if ($player->hasPlayedBefore()) {
			return;
		}
		$this->sendPreview($player);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if ($command->getName() === "rulesbook") {
			if (!$sender instanceof Player) {
				$sender->sendMessage(TextFormat::RED . "You can't use this command in the terminal!");
				return true;
			}
			$this->sendPreview($sender);
			return true;
		}
		return false;
	}
}
