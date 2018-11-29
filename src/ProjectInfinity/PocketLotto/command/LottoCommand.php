<?php

namespace ProjectInfinity\PocketLotto\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use ProjectInfinity\PocketLotto\PocketLotto;

class LottoCommand extends Command {

    private $lm;

    public function __construct($name, $description = '', $usageMessage = null, $aliases = []) {
        parent::__construct('lotto', 'Lotto general command', '/lotto [option]', ['lottery']);
        $this->lm = PocketLotto::getPlugin()->getLottoManager();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission('pocketlotto.user')) {
            $sender->sendMessage(TextFormat::RED.'§7[§6Lotto§7] §cYou do not have permission to purchase tickets.');
            return true;
        }

        if(count($args) < 1) {
            $sender->sendMessage(TextFormat::RED.'Incorrect usage. §aTry §b/lotto buy <amount>');
            return true;
        }

        switch(strtoupper($args[0])) {

            case 'BUY':
                if(!$sender->hasPermission('pocketlotto.user')) {
                    $sender->sendMessage(TextFormat::RED.'§7[§6Lotto§7] §cYou do not have permission to buy tickets.');
                    return true;
                }
                $amount = 1;
                if(count($args) > 1 && is_numeric($args[1])) $amount = (int) $args[1];
                if(!$this->lm->canAcquireMore($sender->getName())) {
                    $sender->sendMessage(TextFormat::RED.'§7[§6Lotto§7] §cYou cannot purchase more tickets. Please wait for a draw.');
                    return true;
                }
                $purchased = $this->lm->addTicket($sender->getName(), $amount);
                if($purchased === 0) {
                    $sender->sendMessage(TextFormat::RED.'§7[§6Lotto§7] §cYou can not afford more or you already have the maximum amount of tickets allowed.');
                    return true;
                }
                $sender->sendMessage(TextFormat::GREEN.'§7[§6Lotto§7] §dPurchased §5'.$purchased.' §dticket(s).');
                break;

            case 'TIME':
            case 'DRAW':
                $sender->sendMessage(TextFormat::YELLOW.'§7[§6Lotto§7] §3'.$this->lm->getTimeRemaining());
                break;

            default:
                $sender->sendMessage(TextFormat::RED.'§7[§6Lotto§7] §cInvalid option. §aValid options are: §bbuy, time/draw');
        }

        return true;
    }
}
