<?php
class EItemView extends ItemView {
	function main() {
		if($id = $this->Item->GetId())
			$this->Item->Load($id);
		$err = $this->Item->GetErrors();
		return Array(
			'errors' => $err,
			'success' => count($err) ? false : true
			);
	}
}

class EGridView extends GridView {
	function main() {
		return Array(
			'values' => $this->Grid->Records,
			'total' => $this->Grid->CountRecs(),
			'success' => $this->Grid->Conn->ErrorNo() === 0 ? 'true':'false'
			);
	}

	function modify() {
		return Array(
			'errors' => $this->Controller->errors,
			'success' => count($this->Controller->errors) > 0 ? 'false':'true'
		);
	}
}

?>