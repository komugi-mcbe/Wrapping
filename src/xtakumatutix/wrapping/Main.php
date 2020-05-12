<?php

namespace xtakumatutix\wrapping;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\Listener;

Class Main extends PluginBase implements Listener {

    public function onEnable() 
    {
        $this->getLogger()->notice("読み込み完了_ver.1.0.0");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool 
    {
        if ($sender instanceof Player) {
            if($sender->getInventory()->all(Item::get(Item::PAPER))) {
                $handitem = $sender->getInventory()->getItemInHand();
                $id = $handitem->getID();
                $itemname = $handitem->getName();
                $damage = $handitem->getDamage();
                $count = $handitem->getCount();

                $item = Item::get(378, 0);
                $tag = $item->getNamedTag() ?? new CompoundTag('', []);
                $tag->setTag(new StringTag("wrapping","{$id}"), true);
                $tag->setTag(new StringTag("wrapping2","{$damage}"), true);
                $tag->setTag(new StringTag("wrapping3","{$count}"), true);
                $item->setNamedTag($tag);
                $sender->getInventory()->addItem($item);
                $sender->sendMessage("ラッピングしました！！");

                return false;
            }else{
                $sender->sendMessage("3");
                return true;
            }
        }else{
            $sender->sendMessage("4");
            return false;
        }
    }
}