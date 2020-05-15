<?php

namespace xtakumatutix\wrapping;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\Listener;
use pocketmine\event\Player\PlayerInteractEvent;
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

                $sender->getInventory()->removeItem($itemhand);
                $sender->getInventory()->removeItem(Item::get(Item::PAPER));

                $name = $sender->getName();

                $item = Item::get(378, 0);

                $item->setLore(["中身はなにかな...?"]);
                $item->setCustomName("{$name}様より");

                $tag = $item->getNamedTag() ?? new CompoundTag('', []);
                $tag->setTag(new StringTag("item", $itemhand), true);
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
                $nbtitem = $tag->getString('item');
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