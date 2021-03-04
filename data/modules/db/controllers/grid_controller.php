<?php

include_once(MODULES_PATH.'/db/controllers/controller.php');
include_once(MODULES_PATH.'/db/views/grid_view.php');

class GridController extends Controller {
	var $Grid;
	var $App;
	var $S;
	var $Prefix;
	var $Queried = false;

	var $DoneActions = Array();

	function ProcessControlFlow() {
		parent::ProcessControlFlow();
		$this->QueryGrid();
	}

	function QueryGrid() {
		if (!$this->Queried) {
			$this->Grid->CountMainTotals();
			$this->Grid->Query();
			$this->Queried = true;
			// echo " records_count : ".$this->Grid->records_count." <br>";
		}
	}

	function CreateView() {
		$this->AddView( new GridView($this));
	}

	function CreateModel() {
		$this->Grid = new FilteredDBList('', 0, $this);
	}

	function InitModel() {
		$this->CreateModel();
		$this->CreateView();
		$this->SetGridDefaults();
		$this->SetSorting();
		$this->SetPagination();
	}
		
	function InitGrid() { //maintaining backward compatibility
		$this->InitModel();
	}

	function SetGridDefaults() {
		$this->S->StoreVarDefault($this->GetObjName('page'), 1);
		$per_page_name = $this->GetObjName('per_page');
		$value = $this->App->ConfigOption($per_page_name);

		settype($value, 'integer');
		$this->S->StoreVar($per_page_name, $value < 1 && $value != -1 ? 10:$value);

		$filters = $this->App->GetVar('filter');
		if(isset($filters) && strlen($filters)) {
			$filters = json_decode($filters, true);

			if(is_array($filters)) {
				$anItem =& $this->Grid->GetDummy();
				foreach($filters as $flt) {
					if(array_key_exists($flt['property'], $anItem->FieldValues)) $this->Grid->AddStdFilter('equals', $flt['property'], $flt['value']);
				}
			}
		}
	}

	// Actions

	function actDelete($notskip=0) {
		if(!defined('ADMIN') && !$notskip)
			return false;
		
		$ids = json_decode($this->App->GetVar('ids'), true);
		$dummy =& $this->Grid->GetDummy();
		if(array_key_exists($dummy->id_field, $ids))
			$ids = Array($ids);
		foreach ($ids as $rec) {
			$dummy->id = intval($rec[$dummy->id_field]);
			$dummy->Delete();
		}
	}

	function SetSorting() {
		$this->Grid->ClearOrderFields();
		$sorters = $this->App->GetVar('sort');
		if(isset($sorters) && strlen($sorters)) {
			$sorters = json_decode($sorters, true);

			if(is_array($sorters)) {
				$anItem =& $this->Grid->GetDummy();
				foreach($sorters as $srt) {
					if(array_key_exists($srt['property'], $anItem->FieldValues)) $this->Grid->AddOrderField($srt['property'], $srt['direction']);
				}
			}
		}
	}

	function SetPagination($page=null) {
		if ($perPage = $this->App->GetVar('limit'))
			$this->Grid->SetPerPage( $perPage );
		else
			$this->Grid->SetPerPage( $this->App->RecallVar($this->GetObjName('per_page')));	

		if ($page === null) 
			$page = $this->App->GetVar('page');

		$this->Grid->CountRecs();
		
		if(!$page) $page = 1;
		
		if(!$totalpages = $this->Grid->GetTotalPages())
			$totalpages = 1;

		$this->SetPage($page);
	}

	function SetPage($page) {
		$this->Grid->SetPage($page);
	}
}
?>