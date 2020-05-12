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

                $sender->getInventory()->removeItem(Item::get($id,$damage,$count));
                $sender->getInventory()->removeItem(Item::get(339,0,1));

                $name = $sender->getName();
                $item = Item::get(378, 0);
                $item->setLore(["中身はなにかな...?"]);
                $item->setCustomName("{$name}様より");
                $tag = $item->getNamedTag() ?? new CompoundTag('', []);
                $tag->setTag(new StringTag("wrapping","1"), true);
                $tag->setTag(new StringTag("wrapping1","{$id}"), true);
                $tag->setTag(new StringTag("wrapping2","{$damage}"), true);
                $tag->setTag(new StringTag("wrapping3","{$count}"), true);
                $item->setNamedTag($tag);
                $sender->getInventory()->addItem($item);
                $sender->sendMessage("ラッピングしました！！");

                return false;
            }else{
                $sender->sendMessage("紙がありません");
                return true;
            }
        }else{
            $sender->sendMessage("ゲーム内で使用してください");
            return false;
        }
    }
}