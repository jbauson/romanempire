<?php
class Attack extends Common {
	public $playerInfo;
	public $server;
	public $key;
	public $heroList;
	public $attackList;

	# 2544 - not emough army

	public function __construct($server,$key,$playerInfo){
		$this->playerInfo 	= $playerInfo;
		$this->server 		= $server;
		$this->key 			= $key;
	}

	public function getAllAvailableHero(){
		# http://s102.romanempire.com/game/gen_conscribe_api.php?jsonpcallback=jsonp1454204110801&_=1454204112076&key=008b5221d00a0017c5466a256b58ee93&city=327552&action=gen_list&_l=en&_p=RE-IPHONE-EN
		for($ctr=0;$ctr<count($this->playerInfo['ret']['user']['city']);$ctr++){
			getHero:
			$time = time();
			$var = @file_get_contents($this->server."game/gen_conscribe_api.php?jsonpcallback=jsonp".$time."&_=".($time+1485495)."&key=".$this->key."&city=".$this->playerInfo['ret']['user']['city'][$ctr]['id']."&action=gen_list&_l=en&_p=RE");
			if (!(strpos($var,'})') !== false)) {
			    goto getHero;
			}
			parent::setTime($time);
			$var = parent::stripTojSon($var);

			for($j=0;$j<count($var['ret']['hero']);$j++){
				if($var['ret']['hero'][$j]['s']==0 && $var['ret']['hero'][$j]['fy']==0 && $var['ret']['hero'][$j]['e']!=0){
					$this->heroList[] = array('city' => $this->playerInfo['ret']['user']['city'][$ctr]['id'], 'gid' => $var['ret']['hero'][$j]['gid'] );
				}
			}

		}
		return $this->heroList;
	}

	public function getBarbs($level){
		# http://s102.romanempire.com/game/api_fav.php?jsonpcallback=jsonp1454202818637&_=1454202819142&key=008b5221d00a0017c5466a256b58ee93&act=getfavnpc&cat=2&_l=en&_p=RE-IPHONE-EN
		getBarbs:
		$time = time();
		$var = @file_get_contents($this->server."game/api_fav.php?jsonpcallback=jsonp".$time."&_=".($time+1485495)."&key=".$this->key."&act=getfavnpc&cat=2&_l=en&_p=RE");
		if (!(strpos($var,'})') !== false)) {
		    goto getBarbs;
		}

		parent::setTime($time);
		$var = parent::stripTojSon($var);
		$this->getLevel($var['ret']['favs'],$level);
	}

	private function getLevel($favs,$level){
		$total = 0;
		for($ctr=0;$ctr<count($favs);$ctr++){
			if($favs[$ctr][3]==$level && $favs[$ctr][4]<3){
				for($i=$favs[$ctr][4]+1;$i<=3;$i++){
					$this->attackList[] = array(
						'city'		=> $this->heroList[$total]['city'],
						'gid'	=> $this->heroList[$total]['gid'],
						'x'			=> $favs[$ctr][1],
						'y'			=> $favs[$ctr][2]
						);
					if(++$total==count($this->heroList)-1){
						break 2;
					}
				}
			}
		}
		#print_r($this->attackList);
		
		#print_r($this->attackList);
		#die();
	}

	public function executeAttack(){
		# http://s102.romanempire.com/game/armament_action_task_api.php?jsonpcallback=jsonp1454207375580&_=1454207385270&key=b4a3304a7846f9c3a27950ec30a0ecba&city=307877&action=war_task&attack_type=7&gen=22&area=221&area_x=173&soldier_num18=800&soldier_num17=300&carry=1200000&cost_food=425000&cost_wood=0&cost_iron=0&cost_gold=0&distance=2400&travel_sec=60&_l=en&_p=RE-IPHONE-EN
		#print_r($this->attackList);
		$attackTime = time();
		shuffle($this->attackList);
		for($ctr=0;$ctr<count($this->attackList);$ctr++){
			attack:
			$time = time();
			$var = @file_get_contents($this->server."game/armament_action_task_api.php?jsonpcallback=jsonp".$time."&_=".($time+1485495)."&key=".$this->key."&city=".$this->attackList[$ctr]['city']."&action=war_task&attack_type=7&gen=".$this->attackList[$ctr]['gid']."&area=".$this->attackList[$ctr]['x']."&area_x=".$this->attackList[$ctr]['y']."&soldier_num18=800&soldier_num17=300&carry=1200000&cost_food=425000&cost_wood=0&cost_iron=0&cost_gold=0&distance=2400&travel_sec=60&_l=en&_p=RE");
			if (!(strpos($var,'})') !== false)) {
			    goto attack;
			}
			echo $this->attackList[$ctr]['gid']." Sent to ".$this->attackList[$ctr]['x']." ".$this->attackList[$ctr]['y']."\n";
			sleep(rand (15,25)); // Random attack from 15secs to 25 secs
			# attack for 50mins and then break
			if(($attackTime-time())>3000){
				$f = @fopen($this->key.".txt", "w") or die("Unable to open file!\n");
				fwrite($f, time());
				fclose($f);
				die("Attack Exited\n");
			}

			parent::setTime($time);
			$var = parent::stripTojSon($var);
			if($var['code']>=20000){
				# Do not attack until user login
				$f = @fopen($this->key.".txt", "w") or die("Unable to open file!\n");
				fwrite($f, "9999999999");
				fclose($f);
				die("Attack require captcha\n");
				//goto attack;
			}

		}
	}
}
?>