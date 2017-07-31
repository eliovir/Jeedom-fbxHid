<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'rudpconnexion', 'class', 'fbxHid');
class fbxHid extends eqLogic {
	public static $_widgetPossibility = array('custom' => array(
	        'visibility' => true,
	        'displayName' => true,
	        'displayObjectName' => true,
	        'optionalParameters' => true,
	        'background-color' => true,
	        'text-color' => true,
	        'border' => true,
	        'border-radius' => true
	));
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'fbxHid';	
		$return['launchable'] = 'ok';
		$return['state'] = 'ok';
		return $return;
	}
	public static function deamon_start($_debug = false) {
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') 
			return;
		log::remove('fbxHid');
		self::deamon_stop();
		$cron = cron::byClassAndFunction('fbxHid', 'Telecommande');
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('fbxHid');
			$cron->setFunction('Telecommande');
			$cron->setEnable(1);
			$cron->setDeamon(1);
			$cron->setSchedule('* * * * *');
			$cron->setTimeout('999999');
			$cron->save();
		}
		$cron->start();
		$cron->run();
	}
	public static function deamon_stop() {
		$cron = cron::byClassAndFunction('fbxHid', 'Telecommande');
		if (is_object($cron)) {
			$cron->stop();
			$cron->remove();
		}
	}
	public static function Telecommande() {
	}
	public function AddCommande($Name,$_logicalId,$Page,$Code) {
		$Commande = $this->getCmd(null,$_logicalId);
		if (!is_object($Commande))
		{
			$Commande = new fbxHidCmd();
			$Commande->setId(null);
			$Commande->setLogicalId($_logicalId);
			$Commande->setEqLogic_id($this->getId());
			$Commande->setType('action');
			$Commande->setSubType('other');
			$Commande->setName($Name);
		}
		$Commande->setConfiguration('Page',$Page);
		$Commande->setConfiguration('Code',$Code);
		$Commande->save();
		return $Commande;
	}
	public function toHtml($_version = 'mobile') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		foreach ($this->getCmd(null, null, true) as $cmd) {
			if($cmd->getIsVisible())	
				$masque[]=$cmd->getLogicalId();
			$replace['#'.$cmd->getLogicalId().'#'] = $cmd->toHtml($_version);
		}

		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'telecommande', 'fbxHid')));
	}
	public function postSave() {	
		$this->AddCommande('Power','Power',0xc,0x30);
		$this->AddCommande('AV','VCR/TV',0xc,0x63);	 
		$this->AddCommande('0','Keypad0',0x7,0x59);	 	 
		$this->AddCommande('1','Keypad1',0x7,0x5A);	 	 
		$this->AddCommande('2','Keypad2',0x7,0x5B);	  
		$this->AddCommande('3','Keypad3',0x7,0x5C);	 	 
		$this->AddCommande('4','Keypad4',0x7,0x5D);	 	 
		$this->AddCommande('5','Keypad5',0x7,0x5E);	 	 
		$this->AddCommande('6','Keypad6',0x7,0x5F);	 	 
		$this->AddCommande('7','Keypad7',0x7,0x60);	 	 
		$this->AddCommande('8','Keypad8',0x7,0x61);	 	 
		$this->AddCommande('9','Keypad9',0x7,0x62);	  
		$this->AddCommande('Up','Up',0x7,0x4F);	  
		$this->AddCommande('Down','Down',0x7,0x50);	  
		$this->AddCommande('Left','Left',0x7,0x51);	  
		$this->AddCommande('Right','Right',0x7,0x52);	 	 
		$this->AddCommande('OK','Enter',0x7,0x28);	 
		$this->AddCommande('Back','Exit',0xc,0x204);	
		$this->AddCommande('Search','Search',0xc,0x221);	 
		$this->AddCommande('Menu','Menu',0x1,0x86);		 
		$this->AddCommande('Info','Properties',0xc,0x209);		 
		$this->AddCommande('Free','manager',0xc,0x18f);		 
		$this->AddCommande('Vol +','VolInc',0xc,0xE9);		
		$this->AddCommande('Vol -','VolDec',0xc,0xEA);		 
		$this->AddCommande('Mute','Mute',0xc,0xe2);	 
		$this->AddCommande('Record','Record',0xc,0xb2);	 
		$this->AddCommande('Prog +','ChannelInc',0xc,0x9c);	 
		$this->AddCommande('Prog -','ChannelDec',0xc,0x9d);	 
		$this->AddCommande('Rewind','Rewind',0xc,0xb4);	 
		$this->AddCommande('Play/Pause','Play/Pause',0xc,0xcd);	 
		$this->AddCommande('Fast Forward','Fast Forward',0xc,0xb3);	 
		$this->AddCommande('Backspace','Backspace',0x7,0x2a);	 
		$this->AddCommande('Stop','Stop',0xc,0xb7);	 
		$this->AddCommande('Play','Play',0xc,0xb0);	 
		$this->AddCommande('Random/Shuffle','Random play',0xc,0xb9);	 
		$this->AddCommande('Next','Scan_Next_Track',0xc,0xb5);	 
		$this->AddCommande('Prev','Scan_Previous_Track',0xc,0xb6);	 
		$this->AddCommande('Zoom IN','Zoom In',0xc,0x22d); 
		$this->AddCommande('Zoom OUT','Zoom Out',0xc,0x22e);	 
		$this->AddCommande('Browser Back','Back',0xc,0x224);	 
		$this->AddCommande('Browser Formward','Forward',0xc,0x225);	 
		$this->AddCommande('Browser Refresh','Refresh',0xc,0x227);	 
		$this->AddCommande('Browser Stop','Stop',0xc,0x226); 
		$this->AddCommande('Next video track','Sub_Channel_Increment',0xc,0x171);
		$this->AddCommande('Next audio track','Alt_Audio_Increment',0xc,0x173);
		$this->AddCommande('Next subtitle track','Alt_Subtitle_Increment',0xc,0x175);
		$this->AddCommande('Hibernate','System Sleep',0x1,0x82);	 
		$this->AddCommande('Wake-up','System Wakeup',0x1,0x83);
	}
}
class fbxHidCmd extends cmd {
	public function execute($_options = array())	{
		
	}
}
