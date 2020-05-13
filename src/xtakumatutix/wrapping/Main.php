<?php

namespace xtakumatutix\wrapping;

use pocketmine\nbt\tag\IntTag;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\Listener;
use pocketmine\event\Player\PlayerInteractEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

Class Main extends PluginBase implements Listener {

    public function onEnable() {
        $this->getLogger()->notice("読み込み完了_ver.1.0.0");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($sender instanceof Player) {
            if ($sender->getInventory()->all(Item::get(Item::PAPER))) {
                $handitem = $sender->getInventory()->getItemInHand();
                $id = $handitem->getID();
                $damage = $handitem->getDamage();
                $count = $handitem->getCount();

                $sender->getInventory()->removeItem(Item::get($id, $damage, $count));
                $sender->getInventory()->removeItem(Item::get(339, 0, 1));

                $name = $sender->getName();
                $item = Item::get(378, 0);
                $item->setLore(["中身はなにかな...?"]);
                $item->setCustomName("{$name}様より");
                $tag = $item->getNamedTag() ?? new CompoundTag('', []);
                $tag->setTag(new IntTag("wrapping", 1), true);
                $tag->setTag(new IntTag("wrapping1", $id), true);
                $tag->setTag(new IntTag("wrapping2", $damage), true);
                $tag->setTag(new IntTag("wrapping3", $count), true);
                $tag->setTag(new StringTag("wrapping4", "{$name}"), true);
                $item->setNamedTag($tag);
                $sender->getInventory()->addItem($item);
                $sender->sendMessage("§a >> ラッピングしました！！");

                return false;
            } else {
                $sender->sendMessage("§c >> 紙がありません");
                return true;
            }
        } else {
            $sender->sendMessage("ゲーム内で使用してください");
            return true;
        }
    }

    public function tap(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $itemid = $item->getID();
        if ($itemid===378) {
            $tag = $item->getNamedTag();
            if ($tag->offsetExists("wrapping")) {
                $comtag=$tag->getCompoundTag("wrapping");
                $id = $comtag->getInt('wrapping1');
                $damage = $comtag->getInt('wrapping2');
                $count = $comtag->getInt('wrapping3');
                $name = $comtag->getString('wrapping4');

                $player->getInventory()->removeItem($item);
                $player->getInventory()->addItem(Item::get($id, $damage, $count));
                $player->sendMessage("§a >> {$name}様からのプレゼントです！");

                $pk = new PlaySoundPacket();
                $pk->soundName = 'random.levelup';
                $pk->x = $player->x;
                $pk->y = $player->y;
                $pk->z = $player->z;
                $pk->volume = 1;
                $pk->pitch = 1;
                $player->dataPacket($pk);
            }
        }
    }
}