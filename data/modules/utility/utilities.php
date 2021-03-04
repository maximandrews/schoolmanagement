<?php

class Utilites {
	public $App;

	function __construct() {
		$this->App =& KernelApplication::Instance();
	}

	function ExtractByMask($array, $mask, $key_id=1, $ret_mode=1) {
		$rets = Array();
		foreach ($array as $name => $val) {
			$regs = Array();
			if (preg_match("/".$mask."/is", $name, $regs)) {
				if ($ret_mode == 1)
					$rets[$regs[$key_id]] = $val;
				else {
					array_push($regs, $val);
					$a_key = $regs[$key_id];
					$i = 0;
					while (array_key_exists($a_key, $rets)) {
						$a_key.=$i;
						$i++;
					}
					$rets[$a_key] = $regs;
				}
			}
		}

		return $rets;
	}
}

?>