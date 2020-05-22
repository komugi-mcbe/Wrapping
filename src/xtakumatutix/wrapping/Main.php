<?php

namespace xtakumatutix\wrapping;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

Class Main extends PluginBase implements Listener {

    public function onEnable() 
    {
        $this->getLogger()->notice("読み込み完了_ver.1.0.0");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool 
    {
        if ($sender instanceof Player) {
            if ($sender->getInventory()->all(Item::get(Item::PAPER))) {
                $itemhand = $sender->getInventory()->getItemInHand();
                if ($itemhand->getId()===0) {
                    $sender->sendMessage("§c >> 空気のラッピングを行うことは出来ません。");
                    return true;
                }
                if ($itemhand->getNamedTag()->offsetExists("name")) {
                    $sender->sendMessage("§c >> 2重ラッピングを行うことは出来ません。");
                    return true;
                }
                $sender->getInventory()->removeItem(Item::get(Item::PAPER));
                $itemhand = $sender->getInventory()->getItemInHand();//紙の数が減った後、再度手持ちを取得する
                $sender->getInventory()->removeItem($itemhand);

                $name = $sender->getName();

                $item = Item::get(378, 0);

                $item->setLore(["中身はなにかな...?"]);
                $item->setCustomName("{$name}様より");

                $tag = $item->getNamedTag() ?? new CompoundTag('', []);
                $tag->setTag($itemhand->nbtSerialize(-1, "item"), true);
                $tag->setTag(new StringTag("name","{$name}"), true);
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

    public function tap(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $itemid = $item->getID();
        $itemdamage = $item->getDamage();
        if ($itemid===378) {
            $tag = $item->getNamedTag();
            if ($tag->offsetExists("item")) {
                $itemtag = $tag->getTag("item");
                //旧バージョンで作成されたラッピングへの対応をするための処理
                if ($itemtag instanceof CompoundTag) {
                    //新方式のタグを使用している場合
                    $nbtitem = Item::nbtDeserialize($itemtag);
                } else if ($itemtag instanceof StringTag) {
                    //旧方式(JSON)のタグを使用している場合
                    $nbtitem = Item::jsonDeserialize(json_decode($itemtag->getValue(), true));
                }
                $name = $tag->getString('name');

                $player->getInventory()->removeItem(Item::get($itemid,$itemdamage,1,$tag));
                $player->getInventory()->addItem($nbtitem);
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
