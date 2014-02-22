<?php defined('DIRECT_ACCESS_CHECK') or die('DIRECT ACCESS NOT ALLOWED');
/**
 * Copyright (c) 2013 EIRL DEVAUX J. - Medialoha.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the GNU Public License v3.0
 * which accompanies this distribution, and is available at
 * http://www.gnu.org/licenses/gpl.html
 *
 * Contributors:
 *     EIRL DEVAUX J. - Medialoha - initial API and implementation
 */


class Milestone {

	private $id;
	public $name;
	public $description;
	public $app_id;
	public $duedate;
	
	// optional attrs
	private $app_name;
	public $count_all;
	public $count_closed;
	public $count_testing;
	
	
	// CONSTRUCTOR
	public function Milestone() {
		
	}
	
	public static function createFromArr($arr) {
		$m = new Milestone();
		
		foreach ($arr as $k=>$v) {
			$m->{str_replace('mile_', '', $k)} = $v;
		}
		
		return $m;
	}
	

	public function getId() { return $this->id; }
	
	public function getCountOpen() {
		if ($this->count_all>0)
			return $this->count_all-$this->count_closed;
		
		return 0;
	}
	
	public function getPercentClosed($round=true) {
		return $this->getPercent($this->count_closed, $round);
	}
		
	public function getPercentOpen($round=true) {
		return $this->getPercent($this->getCountOpen(), $round);		
	}
		
	public function getPercentTesting($round=true) {
		return $this->getPercent($this->count_testing, $round);
	}
	
	private function getPercent($value, $round=true) {
		$res = 0;
		
		if ($this->count_all>0) {
			$res = ($value*100)/$this->count_all; 
			
			if ($round)
				$res = round($res, 2);
		}
		
		return $res;
	}
	
	public function printRemainingTime() {
		if ($this->duedate==0)
			return null;
		
		$diff = abs(time()-$this->duedate);
		
		$arr['years'] = floor($diff/(365*60*60*24));
		$arr['months'] = floor(($diff-$arr['years']*365*60*60*24)/(30*60*60*24));
		$arr['days'] = floor(($diff-$arr['years']*365*60*60*24-$arr['months']*30*60*60*24)/(60*60*24));
		
		$sep = '';
		foreach ($arr as $k=>$v)
			if ($v>0) {
				echo $sep, $v, ' ', $v==1?substr($k, -1):$k;
				$sep = ', ';
			} 
	}
	
	public function printDueDate($format='D, d M Y') {
		if ($this->duedate==0)
			return null;
		
		return date($format, $this->duedate);
	}
	
	public function printProgressBar($stacked=true) {
		if ($stacked)
			$pTesting = $this->getPercentTesting();
		else 
			$pTesting = 0;
		
		$pClosed = $this->getPercentClosed()-$pTesting;
		
	?>
<div class="progress">
	<div class="bar bar-success" style="width:<?php echo $pClosed; ?>%;"><?php echo $pClosed; ?>%</div>
	  <?php if ($pTesting>0) { ?>
	  	<div class="bar bar-info" style="width:<?php echo $pTesting; ?>%;" ><?php echo $pTesting; ?>%</div>
	  <?php } ?>
	</div>
<?php
	}
	
	public function printOverview() {
		echo $this->name, ',<span class="muted" > due in </span>';
		$this->printRemainingTime();
		echo '&nbsp;:&nbsp;', $this->getPercentClosed(), '%&nbsp;&nbsp;(', $this->count_closed, ' closed &mdash; ', $this->getCountOpen(), ' open )';
	}
}