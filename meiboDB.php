<?php

/*
class meiboDB
{
	// コンストラクタ
	function __construct($dbName);
	// デストラクタ
	function __destruct();
	
	// 和暦情報の登録
	function registNengo($nengo, $year, $month, $daya);
	// 和暦情報の取得
	function getNengoData();
	// 和暦情報の取得
	function delNengoData($nengo) {
	
	// 住所録を登録,更新する[$data=Array[ID, childID, Name, Phonetic, Sex, Relationship, BirthYear, BirthMonth, BirthDay, JointBirthDay, ZIP, Pref, Address, TEL, EnterDay, Tokudo, Teacher, Diocese, Team, isLeader, Memo]]
	function regist($data);
	// 住所録の有無チェック
	function isExist($ID)
	// 住所録取得[Array[*All*]]
	function get($ID);
	// 住所録リスト取得[Array[ID, Name, Phonetic, Pref, Address, Diocese]]
	function get_list($initial);
	// 住所録検索[Array[*All*]]
	function find($condition) {
	// 住所録を削除する
	function del($ID);
	
	// 教区登録
	function addDiocese($name);
	// 教区一覧取得[Array[*All*]]
	function getDiocese();
	// 教区名取得[$name]
	function getDioceseName($ID);
	// 教区名順番変更
	function moveDioceseID($ID, $vector);
	// 教区削除
	function delDiocese($name);
	// 班番号取得
	function getTeamNo($ID);
	// 班員取得
	function getTeamMember($ID, $team);
	// 班長取得
	function getLeaderID($diocese, $team);
	
	// 得度授戒者数取得
	function getTokudoNum();
	// 教師拝受者数取得
	function getTeacherNum();

	// 変更履歴取得
	function getHistory();

	// トランザクションを開始する
	function beginTransaction();
	// トランザクションを終了する
	function endTransaction();
}
*/
class meiboDB
{
	private $m_pdo;
	private $m_autoTransaction;

	// コンストラクタ
	function __construct($dbName) {
		try {
			$this->m_pdo = new PDO("sqlite:".$dbName);
			$this->m_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$rs = $this->m_pdo->query("SELECT * FROM sqlite_master WHERE name='state'");
			$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
			if(count($ret) == 0) {
				$this->m_pdo->exec("CREATE TABLE state(Name TEXT PRIMARY KEY, Value TEXT)");
			}

			$rs = $this->m_pdo->query("SELECT * FROM sqlite_master WHERE name='address'");
			$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
			if(count($ret) == 0) {
				$this->m_pdo->exec("CREATE TABLE address(".
									"ID INTEGER NOT NULL, childID INTEGER NOT NULL, Name TEXT, Phonetic TEXT, Initial TEXT, Sex TEXT, Relationship TEXT, ".
									"BirthYear INTEGER, BirthMonth INTEGER, BirthDay INTEGER, JointBirthDay INTEGER, ZIP TEXT, Pref TEXT, Address TEXT, TEL TEXT, ".
									"EntryDay INTEGER, Tokudo INTEGER, Teacher INTEGER, Diocese INTEGER, Team INTEGER, isLeader BOOLEAN, Memo TEXT, ".
									"UNIQUE(ID, childID), PRIMARY KEY(ID,childID))");
			}
			
			$rs = $this->m_pdo->query("SELECT * FROM sqlite_master WHERE name='diocese'");
			$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
			if(count($ret) == 0) {
				$this->m_pdo->exec("CREATE TABLE diocese(ID INTEGER PRIMARY KEY AUTOINCREMENT, Name TEXT)");
			}
			
			$rs = $this->m_pdo->query("SELECT * FROM sqlite_master WHERE name='nengo'");
			$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
			if(count($ret) == 0) {
				$this->m_pdo->exec("CREATE TABLE nengo(Nengo TEXT PRIMARY KEY, Year INTEGER, Month INTEGER, Day INTEGER)");
				$this->m_pdo->exec("INSERT INTO nengo(Nengo, Year, Month, Day) VALUES('明治', 1868,  1, 25)");
				$this->m_pdo->exec("INSERT INTO nengo(Nengo, Year, Month, Day) VALUES('大正', 1912,  7, 30)");
				$this->m_pdo->exec("INSERT INTO nengo(Nengo, Year, Month, Day) VALUES('昭和', 1926, 12, 25)");
				$this->m_pdo->exec("INSERT INTO nengo(Nengo, Year, Month, Day) VALUES('平成', 1989,  1,  8)");
			}
			
			$rs = $this->m_pdo->query("SELECT * FROM sqlite_master WHERE name='history'");
			$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
			if(count($ret) == 0) {
				$this->m_pdo->exec("CREATE TABLE history(Date TEXT PRIMARY KEY, ID INTEGER, Contents TEXT)");
			}
		}
		catch(PDOException $err) {
			die("データベースへの接続を確立できませんでした。<BR>DBNAME:$dbName<BR>ERROR:".$err->getMessage()."<BR>");
		}
		$this->m_autoTransaction = false;
	}

	// デストラクタ
	function __destruct() {
		if($this->m_autoTransaction) {
			$this->m_pdo->rollBack();
		}
		unset($this->m_pdo);
	}

	// 和暦情報の登録
	function registNengo($nengo, $year, $month, $daya) {
		$this->m_pdo->exec("INSERT INTO nengo(Nengo, Year, Month, Day) VALUES('$nengo', $year, $month, $daya)");
	}
	
	// 和暦情報の取得
	function getNengoData() {
		$rs = $this->m_pdo->query("SELECT * FROM nengo ORDER BY Year ASC, Month ASC, Day ASC");
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}
	// 和暦情報の取得
	function delNengoData($nengo) {
		$this->m_pdo->exec("DELETE FROM nengo WHERE Nengo='$nengo'");
	}
	
	// 住所録を登録,更新する[$data=Array[ID, childID, Name, Phonetic, Sex, Relationship, BirthYear, BirthMonth, BirthDay, JointBirthDay, ZIP, Pref, Address, TEL, EnterDay, Tokudo, Teacher, Diocese, Leader, isLeader, Memo]]
	function regist($data) {
		if(!$this->m_autoTransaction) {
			$this->m_pdo->beginTransaction();
		}
		try {
			$historyContents = "";
			$ID = $data[0]['ID'];
			$isChange = $this->isExist($ID);
			if($isChange) {
				$orgData = $this->get($ID);
				for($num = 0 ; $num <= 10 ; $num++) {
					if(!array_key_exists($num, $orgData) && !array_key_exists($num, $data)) {
						break;
					}
					if(!array_key_exists($num, $data)) {
						$historyContents .= $data[0]['Name'] . "さんの家族欄[$num]「" . $orgData[$num]['Name'] . "」さんを削除しました。\n";
						continue;
					}
					if(!array_key_exists($num, $orgData)) {
						$historyContents .= $data[0]['Name'] . "さんの家族欄[$num]「" . $data[$num]['Name'] . "」さんを追加しました。\n";
						continue;
					}
					$contentHeader = ($num == 0 ? "筆頭者" : $data[0]['Name'] . "さんの家族欄[$num]");
					if($orgData[$num]['Name'] != $data[$num]['Name']) {
						$historyContents .= $contentHeader . "の氏名を変更しました。[ 変更前：" . $orgData[$num]['Name'] . " ⇒ 変更後：" . $data[$num]['Name'] ." ]\n";
					}

					$isLineChange = false;
					$contentsLine = "";
					if($this->isDifference($orgData[$num], $data[$num], "Phonetic"))			$contentsLine .= "ふりがな,";
					if($this->isDifference($orgData[$num], $data[$num], "Sex"))					$contentsLine .= "性別,";
					if($this->isDifference($orgData[$num], $data[$num], "JointBirthDay"))		$contentsLine .= "誕生日,";
					if($this->isDifference($orgData[$num], $data[$num], "ZIP"))					$contentsLine .= "郵便番号,";
					if($this->isDifference($orgData[$num], $data[$num], "Pref"))				$contentsLine .= "住所(県),";
					if($this->isDifference($orgData[$num], $data[$num], "Address"))				$contentsLine .= "住所(市町村以下),";
					if($this->isDifference($orgData[$num], $data[$num], "TEL"))					$contentsLine .= "電話番号,";
					if($this->isDifference($orgData[$num], $data[$num], "Tokudo"))				$contentsLine .= "得度,";
					if($this->isDifference($orgData[$num], $data[$num], "Teacher"))				$contentsLine .= "教師,";
					if($num == 0) {
						if($this->isDifference($orgData[$num], $data[$num], "EntryDay"))		$contentsLine .= "入信日,";
						if($this->isDifference($orgData[$num], $data[$num], "Diocese"))			$contentsLine .= "教区,";
						if($this->isDifference($orgData[$num], $data[$num], "Team"))			$contentsLine .= "班,";
					}
					else {
						if($this->isDifference($orgData[$num], $data[$num], "Relationship"))	$contentsLine .= "続柄,";
					}
					$len = mb_strlen($contentsLine);
					if($len > 0) {
						$contentsLine = mb_substr($contentsLine, 0, $len - 1);
						$historyContents .= $contentHeader . "「" . $data[$num]['Name'] . "」さんの $contentsLine の情報を変更しました。\n";
					}
				}
				$this->del($ID);
			}
			else {
				$familyCount = count($data) - 1;
				$historyContents .= $data[0]['Name'] . "さん" . ($familyCount == 0 ? "" : "(家族 $familyCount 人含む)") . "を新たに登録しました。\n";
			}
			if(strlen($historyContents)) {
				date_default_timezone_set('Asia/Tokyo');
				$historyContents = mb_substr($historyContents, 0, mb_strlen($historyContents) - 1);
				$this->m_pdo->exec("INSERT INTO history(Date, ID, Contents) VALUES('" . date('Y-m-d:H:i:s') . "', $ID, '$historyContents')");
			}

			$childID = 0;
			foreach($data as $tmpData) {
				$column = "ID, childID";
				$value  = "$ID, $childID";
				$this->createRegistData($tmpData, "Name",          $column, $value);
				$this->createRegistData($tmpData, "Phonetic",      $column, $value);
				$this->createRegistData($tmpData, "Initial",       $column, $value);
				$this->createRegistData($tmpData, "Sex",           $column, $value);
				$this->createRegistData($tmpData, "Relationship",  $column, $value);
				$this->createRegistData($tmpData, "BirthYear",     $column, $value);
				$this->createRegistData($tmpData, "BirthMonth",    $column, $value);
				$this->createRegistData($tmpData, "BirthDay",      $column, $value);
				$this->createRegistData($tmpData, "JointBirthDay", $column, $value);
				$this->createRegistData($tmpData, "ZIP",           $column, $value);
				$this->createRegistData($tmpData, "Pref",          $column, $value);
				$this->createRegistData($tmpData, "Address",       $column, $value);
				$this->createRegistData($tmpData, "TEL",           $column, $value);
				$this->createRegistData($tmpData, "EntryDay",      $column, $value);
				$this->createRegistData($tmpData, "Tokudo",        $column, $value);
				$this->createRegistData($tmpData, "Teacher",       $column, $value);
				$this->createRegistData($tmpData, "Diocese",       $column, $value);
				$this->createRegistData($tmpData, "Team",          $column, $value);
				$this->createRegistData($tmpData, "isLeader",      $column, $value);
				$this->createRegistData($tmpData, "Memo",          $column, $value);
				$this->m_pdo->exec("INSERT INTO address($column) VALUES($value)");
				$childID++;
			}
		}
		catch(PDOException $err) {
			$this->m_pdo->rollBack();
			die("住所録の登録に失敗しました".$err->getMessage()."<BR>");
		}
		if(!$this->m_autoTransaction) {
			$this->m_pdo->commit();
		}
		$this->m_pdo->exec("VACUUM");
	}

	// 住所録の有無チェック
	function isExist($ID) {
		$rs = $this->m_pdo->query("SELECT ID FROM address WHERE ID='$ID'");
		$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
		return count($ret) ? true : false;
	}
	
	// 住所録取得[Array[*All*]]
	function get($ID) {
		$rs = $this->m_pdo->query("SELECT * FROM address WHERE ID='$ID' ORDER BY childID ASC");
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}

	// 住所録リスト取得[Array[ID, Name, Phonetic, Pref, Address, Diocese]]
	function get_list($initial) {
		
		$rs = $this->m_pdo->query("SELECT ID, Name, Phonetic, Pref, Address, Diocese, Team FROM address WHERE childID='0' and Initial='$initial' ORDER BY Phonetic ASC");
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}

	// 住所録検索[Array[*All*]]
	function find($condition) {
		$where = "";
		if($condition["range"] == "master") {
			$where .= "childID = 0";
		}
		if(array_key_exists("ID", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "ID = " . $condition["ID"];
		}
		if(array_key_exists("ageMin", $condition) && array_key_exists("ageMax", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "JointBirthDay <= " . $condition["ageMin"] . " AND JointBirthDay >= " . $condition["ageMax"];
		}
		if(array_key_exists("schoolYear", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "JointBirthDay > " . $condition["schoolYear"];
		}
		if(array_key_exists("birthMonth", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "BirthMonth = " . $condition["birthMonth"];
		}
		if(array_key_exists("tokudo", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "Tokudo IS NOT NULL";
		}
		if(array_key_exists("teacher", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "Teacher IS NOT NULL";
		}
		if(array_key_exists("diocese", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "Diocese = " . $condition["diocese"];
		}
		if(array_key_exists("team", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "Team = " . $condition["team"];
		}
		if(array_key_exists("leader", $condition)) {
			if(strlen($where) != 0) {	$where .= " AND ";	}
			$where .= "isLeader=1";
		}

		if(strlen($where) != 0) {
			$where = "WHERE $where";
		}
//		$fp = fopen("F:\\wapache\\htdocs\\sample.txt", "w");
//		fwrite($fp, "$where\n");
//		fclose($fp);
		$rs = $this->m_pdo->query("SELECT * FROM address $where ORDER BY Phonetic ASC");
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}

	// 住所録を削除する
	function del($ID) {
		if($this->isExist($ID)) {
			$this->m_pdo->exec("DELETE FROM address WHERE ID=$ID");
		}
	}

	// 教区登録
	function addDiocese($name) {
		$rs  = $this->m_pdo->query("SELECT max(ID) FROM diocese");
		$IDs = $rs->fetchAll(PDO::FETCH_ASSOC);
		$ID = 1;
		if(count($IDs) > 0) {
			$ID = $IDs[0]["max(ID)"] + 1;
		}
		$this->m_pdo->exec("INSERT INTO diocese(ID, Name) VALUES($ID, '$name')");
	}
	
	// 教区一覧取得[Array[*All*]]
	function getDiocese() {
		$rs  = $this->m_pdo->query("SELECT * FROM diocese ORDER BY ID ASC");
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}

	// 教区名取得[$name]
	function getDioceseName($ID)
	{
		$rs = $this->m_pdo->query("SELECT * FROM diocese WHERE ID = $ID");
		$diocese = $rs->fetchAll(PDO::FETCH_ASSOC);
		return $diocese[0]['Name'];
	}
	
	// 教区名順番変更
	function moveDioceseID($ID, $vector)
	{
		if($vector == "up") {
			$changeID = $ID + 1;
		}
		else {
			$changeID = $ID - 1;
		}
		if(($changeID < 0) || ($changeID > count($this->getDiocese()))) {
			return;
		}
		
		if(!$this->m_autoTransaction) {
			$this->m_pdo->beginTransaction();
		}
		try {
			$this->m_pdo->exec("UPDATE diocese SET ID=999 WHERE ID=$ID");
			$this->m_pdo->exec("UPDATE address SET Diocese=999 WHERE Diocese=$ID");
			
			$this->m_pdo->exec("UPDATE diocese SET ID=$ID WHERE ID=$changeID");
			$this->m_pdo->exec("UPDATE address SET Diocese=$ID WHERE Diocese=$changeID");
			
			$this->m_pdo->exec("UPDATE diocese SET ID=$changeID WHERE ID=999");
			$this->m_pdo->exec("UPDATE address SET Diocese=$changeID WHERE Diocese=999");
		}
		catch(PDOException $err) {
			$this->m_pdo->rollBack();
			die("教区の更新に失敗しました".$err->getMessage()."<BR>");
		}
		if(!$this->m_autoTransaction) {
			$this->m_pdo->commit();
		}
		$this->m_pdo->exec("VACUUM");
	}
	
	// 教区名変更
	function renameDiocese($ID, $newName) {
		$this->m_pdo->exec("UPDATE diocese SET Name='$newName' WHERE ID = $ID");
	}

	// 教区削除
	function delDiocese($ID) {
		$this->m_pdo->exec("DELETE FROM diocese WHERE ID=$ID");
	}

	// 班番号取得
	function getTeamNo($ID) {
		$retAry = array();
		$max = 0;
		$rs = $this->m_pdo->query("SELECT max(Team) FROM address WHERE Diocese=$ID");
		$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($ret)) {
			$max = $ret[0]["max(Team)"];
		}
		for($i = 0 ; $i <= $max ; $i++) {
			$rs = $this->m_pdo->query("SELECT Team FROM address WHERE Diocese=$ID AND Team=$i");
			$ret = $rs->fetchAll(PDO::FETCH_ASSOC);
			if(count($ret)) {
				$retAry[] = $i;
			}
		}
		return $retAry;
	}
	
	// 班員取得
	function getTeamMember($ID, $team) {
		$rs = $this->m_pdo->query("SELECT * FROM address WHERE Diocese=$ID AND Team=$team ORDER BY isLeader DESC, Phonetic ASC");
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}
	
	// 班長取得
	function getLeaderID($diocese, $team) {
		$rs = $this->m_pdo->query("SELECT ID FROM address WHERE Diocese=$diocese AND Team=$team AND isLeader=1");
		$leader = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($leader)) {
			return intval($leader[0]["ID"]);
		}
		return 0;
	}

	// 得度授戒者数取得
	function getTokudoNum() {
		$rs = $this->m_pdo->query("SELECT ID FROM address WHERE Tokudo IS NOT NULL");
		$sqlOut = $rs->fetchAll(PDO::FETCH_ASSOC);
		return count($sqlOut);
	}

	// 教師拝受者数取得
	function getTeacherNum() {
		$rs = $this->m_pdo->query("SELECT ID FROM address WHERE Teacher IS NOT NULL");
		$sqlOut = $rs->fetchAll(PDO::FETCH_ASSOC);
		return count($sqlOut);
	}

	// 変更履歴取得
	function getHistory() {
		$rs = $this->m_pdo->query("SELECT * FROM history ORDER BY Date DESC");
		return $rs->fetchAll(PDO::FETCH_ASSOC);
	}

	// トランザクションを開始する
	function beginTransaction() {
		$this->m_autoTransaction = true;
		$this->m_pdo->beginTransaction();
	}

	// トランザクションを終了する
	function endTransaction() {
		$this->m_pdo->commit();
		$this->m_autoTransaction = false;
	}



	// DB登録データを作成する
	private function createRegistData($data, $key, &$column, &$value) {
		if(array_key_exists($key, $data) && strlen($data[$key])) {
			$column .= ", $key";
			$value  .= ", '$data[$key]'";
		}
	}
	// データの差分をチェックする
	private function isDifference($oldData, $newData, $key) {
		if(array_key_exists($key, $oldData) && array_key_exists($key, $newData)) {
			if($oldData[$key] == $newData[$key]) {
				return false;
			}
		}
		return true;
	}
}

?>
