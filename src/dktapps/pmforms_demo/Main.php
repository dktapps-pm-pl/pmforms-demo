<?php

declare(strict_types=1);

namespace dktapps\pmforms_demo;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Slider;
use dktapps\pmforms\element\StepSlider;
use dktapps\pmforms\element\Toggle;
use dktapps\pmforms\FormIcon;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use function count;
use function print_r;

class Main extends PluginBase{

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($command->getName() === "form" and count($args) >= 1){
			$form = null;
			switch($args[0]){
				case "modal":
					$form = $this->createModalForm();
					break;
				case "menu":
					$form = $this->createMenuForm();
					break;
				case "custom":
					$form = $this->createCustomForm();
					break;
				default:
					return false;
			}

			$players = [];
			for($argIdx = 1; isset($args[$argIdx]); ++$argIdx){
				$player = $this->getServer()->getPlayer($args[$argIdx]);
				if($player === null){
					$sender->sendMessage(TextFormat::RED . "Can't find a player by partial name " . $args[$argIdx]);
					return true;
				}
			}
			if(empty($players)){
				if(!($sender instanceof Player)){
					$sender->sendMessage(TextFormat::RED . "Please provide some players to send the form to!");
				}
				$players[] = $sender;
			}
			foreach($players as $player){
				$player->sendForm($form);
			}
			return true;
		}
		return false;
	}

	private function createModalForm() : ModalForm{
		return new ModalForm(
			"Example Modal Form", /* title of the form */
			"Do you want to do something?", /* form body text */

			/**
			 * Called when any player who received this form submits a response. The player is passed to the
			 * callback,so you can send the same form to multiple players at the same time without any issues, as
			 * long as you don't tie the player to the form.
			 *
			 * @param Player $submitter the player who submitted this form
			 * @param bool   $choice the button clicked
			 */
			function(Player $submitter, bool $choice) : void{
				/* callback to execute when the player submits the form */
				$this->getServer()->broadcastMessage($submitter->getName() . " chose " . ($choice ? "YES" : "NO"));
			}
		);
	}

	private function createMenuForm() : MenuForm{
		return new MenuForm(
			"Example Menu", /* title of the form */
			"Please choose an option", /* body text, shown above the menu options */
			[
				/* menu option with no icon */
				new MenuOption("Option 1"),

				/* menu option with PATH icon - PATH types have to be relative to the root of a resource pack */
				new MenuOption("Option 2", new FormIcon("textures/blocks/acacia_trapdoor.png", FormIcon::IMAGE_TYPE_PATH)),

				/* menu option with URL icon - this should point to a valid online image URL */
				new MenuOption("Option 3", new FormIcon("https://pbs.twimg.com/profile_images/776551947595833345/Og1CSz_c_400x400.jpg", FormIcon::IMAGE_TYPE_URL))
			],

			/**
			 * Called when the player submits the form.
			 *
			 * @param Player $submitter
			 * @param int    $selected array offset of selected element: 0 => option 1, 1 => option 2, etc
			 */
			function(Player $submitter, int $selected) : void{
				$this->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE . $submitter->getName() . " chose option " . ($selected + 1) . " from the menu!");
			},
			/**
			 * Called when the player closes the form without selecting an option. This parameter is optional and
			 * can be left blank.
			 *
			 * @param Player $submitter
			 */
			function(Player $submitter) : void{
				$this->getServer()->broadcastMessage(TextFormat::RED . $submitter->getName() . " closed the menu :(");
			}
		);
	}

	private function createCustomForm() : CustomForm{
		return new CustomForm(
			"Example Custom Form", /* title of the form */
			[
				/* each element requires a string identifer that you'll use to fetch its value from the $response */
				new Label("this_is_a_label", "You can add a range of different types of elements to this type of form. This one is a label."),
				new Dropdown("dropdown_identifier", "This is a dropdown", [
					"hello",
					"world",
					"please",
					"choose",
					"an",
					"option"
				]),
				new Input("a_text_field", "Please don't type your password in here", "This will show as faint text in the box"),
				new Input("a_text_field_2", "Please don't type your password in here either", "", "This appears in the box and the user can edit it"),
				new Slider("my_slider", "Slide to choose a value", 1.0, 10.0, 0.01, 5.0 /* selector will show 5 when sent, default is minimum */),
				new StepSlider("a_step_slider", "Slide to choose a preset option", [
					"thing 1",
					"thing 2",
					"thing 3"
				], 1 /* this makes the default point to "thing 2" instead of "thing 1" */),
				new Toggle("switch", "I'm a toggle!", true /* default value is false by default, this parameter is optional */)
			],
			function(Player $submitter, CustomFormResponse $response) : void{
				$this->getServer()->broadcastMessage(TextFormat::GREEN . $submitter->getName() . " submitted custom form with values: " . print_r($response, true));
			},
			function(Player $submitter) : void{
				$this->getServer()->broadcastMessage(TextFormat::YELLOW . $submitter->getName() . " closed the form :(");
			}
		);
	}
}
