use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;

public class KitPvp extends PluginBase implements Listener{

    
  protected $kitMap;

public function onEnable(){

  $this->kitMap = [
    "swordsman" => ["iron_sword", "iron_chestplate"],
    "archer" => ["bow", "16xarrow", "leather_chestplate"],
    "tank" => ["iron_chestplate", "iron_leggings", "iron_boots"],
    "mage" => ["wooden_sword", "ender_pearlx4"],
    "assassin" => ["iron_sword", "invisibility_potion"]
  ];

  $this->getServer()->getPluginManager()->registerEvents($this, $this);

  $this->mysql = new \mysqli("localhost", "user", "password", "myplugin");

}

public function giveKitItems(Player $player, $kit){

  $items = $this->kitMap[$kit];

  foreach($items as $item){
    $player->getInventory()->addItem(Item::get($item, 0, 1));
  }

}

public function setKit(Player $player, $kit){

  $name = strtolower($player->getName());

  $stmt = $this->mysql->prepare("UPDATE players SET kit = ? WHERE player = ?");
  $stmt->bind_param('ss', $kit, $name);

  $stmt->execute();

  $this->giveKitItems($player, $kit);

}

public function onJoin(PlayerJoinEvent $event){

  $player = $event->getPlayer();
  $name = strtolower($player->getName());

  $result = $this->mysql->query("SELECT kit FROM players WHERE player='$name'");

  if($row = $result->fetch_assoc()){
    $kit = $row["kit"];
    $this->giveKitItems($player, $kit);
  }
  else{
    $this->setKit($player, "swordsman"); 
  }

  $player->sendMessage("You are using the {$kit} kit");

}

public function onDeath(PlayerDeathEvent $event){

  $player = $event->getPlayer();  
  $this->setKit($player, "swordsman");
  $player->sendMessage("You have been reset to the swordsman kit");

}

public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {

  switch($command->getName()){
    case "setkit":
      if(!$sender instanceof Player) return false;
      if(!isset($args[0])) return false;
      $this->setKit($sender, $args[0]); 
      return true;

    case "kits":
      $sender->sendMessage($this->getKits());
      return true;

    case "kitinfo":
      $kit = strtolower($args[0] ?? "");
      $sender->sendMessage($this->getKitInfo($kit));
      return true;
  }

  return false;
}

}