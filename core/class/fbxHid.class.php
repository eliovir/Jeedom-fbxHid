<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
//include_file('core', 'rudpconnexion', 'class', 'fbxHid');
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
		$cron = cron::byClassAndFunction('fbxHid', 'Telecommande');
		if(is_object($cron) && $cron->running())
			$return['state'] = 'ok';
		else
			$return['state'] = 'nok';
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
		$socket = socket_create(AF_INET, SOCK_DGRAM, 0);
		$r = fopen("/dev/urandom", "r");
		$packet="\x02\x01"+"\x00"*10;
		socket_sendto($socket,$packet , strlen($packet), 0, '127.0.0.1', 4242);
		while(true){
			$packet=fread($r,12);
			log::add('fbxHid','debug','Packet '.$packet);
			socket_sendto($socket,$packet , strlen($packet), 0, '127.0.0.1', 4242);
		}

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
		$this->AddCommande('AV','AV',0xc,0x63);	 
		$this->AddCommande('0','0',0x7,0x59);	 	 
		$this->AddCommande('1','1',0x7,0x5A);	 	 
		$this->AddCommande('2','2',0x7,0x5B);	  
		$this->AddCommande('3','2',0x7,0x5C);	 	 
		$this->AddCommande('4','4',0x7,0x5D);	 	 
		$this->AddCommande('5','5',0x7,0x5E);	 	 
		$this->AddCommande('6','6',0x7,0x5F);	 	 
		$this->AddCommande('7','7',0x7,0x60);	 	 
		$this->AddCommande('8','8',0x7,0x61);	 	 
		$this->AddCommande('9','9',0x7,0x62);	  
		$this->AddCommande('Up','Up',0x7,0x4F);	  
		$this->AddCommande('Down','Down',0x7,0x50);	  
		$this->AddCommande('Left','Left',0x7,0x51);	  
		$this->AddCommande('Right','Right',0x7,0x52);	 	 
		$this->AddCommande('OK','Enter',0x7,0x28);	 
		$this->AddCommande('Back','Back',0xc,0x204);	
		$this->AddCommande('Search','Search',0xc,0x221);	 
		$this->AddCommande('Menu','Menu',0x1,0x86);		 
		$this->AddCommande('Info','Info',0xc,0x209);		 
		$this->AddCommande('Free','Free',0xc,0x18f);		 
		$this->AddCommande('Vol +','Vol+',0xc,0xE9);		
		$this->AddCommande('Vol -','Vol-',0xc,0xEA);		 
		$this->AddCommande('Mute','Mute',0xc,0xe2);	 
		$this->AddCommande('Record','Record',0xc,0xb2);	 
		$this->AddCommande('Prog +','Chan+',0xc,0x9c);	 
		$this->AddCommande('Prog -','Chan-',0xc,0x9d);	 
		$this->AddCommande('Rewind','Rewind',0xc,0xb4);	 
		$this->AddCommande('Play/Pause','Play/Pause',0xc,0xcd);	 
		$this->AddCommande('Fast Forward','Fast Forward',0xc,0xb3);	 
		
		$this->AddCommande('Backspace','Backspace',0x7,0x2a);	 
		$this->AddCommande('Stop','Stop',0xc,0xb7);	 
		$this->AddCommande('Play','Play',0xc,0xb0);	 
		$this->AddCommande('Random/Shuffle','Random Play',0xc,0xb9);	 
		$this->AddCommande('Next','Next track',0xc,0xb5);	 
		$this->AddCommande('Prev','Previous track',0xc,0xb6);	 
		$this->AddCommande('Zoom IN','Zoom+',0xc,0x22d); 
		$this->AddCommande('Zoom OUT','Zoom-',0xc,0x22e);	 
		$this->AddCommande('Browser Back','Browser Back',0xc,0x224);	 
		$this->AddCommande('Browser Formward','Browser Forward',0xc,0x225);	 
		$this->AddCommande('Browser Refresh','Browser Refresh',0xc,0x227);	 
		$this->AddCommande('Browser Stop','Browser Stop',0xc,0x226); 
		$this->AddCommande('Video Track','Video Track',0xc,0x171);
		$this->AddCommande('Audio Track','Audio Track',0xc,0x173);
		$this->AddCommande('Subtitle Track','Subtitle Track',0xc,0x175);
		$this->AddCommande('System Sleep','System Sleep',0x1,0x82);	 
		$this->AddCommande('Wake-up','System Wakeup',0x1,0x83); 
		$this->AddCommande('Eject','Eject',0xc,0xb8);
	}
}
class fbxHidCmd extends cmd {
	public function execute($_options = array())	{
		$cmd = 'sudo python ' . dirname(__FILE__) . '/../remotefreebox/cmdFbx.py ";
		$cmd .= $this->getLogicalId();
		$cmd .= ' >> ' . log::getPathToLog('fbxHid') . ' 2>&1 &';
		exec($cmd);
	}
}
