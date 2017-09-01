<?php

namespace ProjectInfinity\PocketLotto\util;

use DateTime;
use ProjectInfinity\PocketLotto\PocketLotto;

class LottoManager {

    /** @var PocketLotto $plugin */
    private $plugin;
    private $players;
    private $prizePool;

    private $drawTimer, $nextTime;

    public function __construct(PocketLotto $plugin) {
        $this->plugin = $plugin;
        $this->players = [];
        $this->prizePool = ConfigManager::getStartPool();

        $this->drawTimer = ConfigManager::getDrawTimer() > 3600 ? 3600 : ConfigManager::getDrawTimer();
        $this->nextTime = new DateTime();
    }

    public function ticketCount($player): int {
        $player = strtolower($player);
        if(!isset($this->players[$player])) return 0;
        return (int) $this->players[$player];
    }

    public function canAcquireMore($player): bool {
        return ConfigManager::getMaxTickets() > $this->ticketCount($player);
    }

    public function addTicket(string $player, int $amount, bool $isFree = false): int {
        $player = strtolower($player);
        $added = 0;
        for($i = 0; $i < $amount; $i++) {
            if(!$this->canAcquireMore($player)) break;
            if(!$isFree) {} # TODO: Check if they actually can afford it.
            $tickets = $this->players[$player] ?? 0;
            $this->players[$player] = $tickets + 1;
            $this->prizePool += ConfigManager::getPrice();
            $added++;
        }
        return $added;
    }

    public function getPrizePool(): int {
        return $this->prizePool;
    }

    public function getPlayers(): array {
        return $this->players;
    }

    public function getPlayer($player) {
        return $this->players[$player] ?? false;
    }

    public function getDrawTimer(): int {
        return $this->drawTimer;
    }

    public function getNextDraw(): DateTime {
        return $this->nextTime;
    }

    public function setNextDraw() {
        $this->nextTime = new DateTime();
        $this->nextTime->setTimestamp(time() + $this->drawTimer);
    }

    public function countPlayers(): int {
        return count($this->players);
    }

    public function reset() {
        $this->prizePool = ConfigManager::getStartPool();
        $this->players = [];
    }

    public function refundAll() {
        # TODO: All monetary things.

    }

    /**
     * Calculates the remaining time using DateTime::diff() and returns a string that represents the remaining time.
     *
     * @return string
     */
    public function getTimeRemaining(): string {
        $now = new DateTime('now');
        $diff = $now->diff($this->nextTime);
        $min = $diff->i;
        $sec = $diff->s;
        return ($min > 0 ? ($min > 1 ? $min.' minutes ' : $min.' minute ') : '').($sec > 0 ? ($sec > 1 ? $sec.' seconds' : $sec.' second') : '');
    }

}