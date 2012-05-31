<?php
if ($rows) {
	foreach ( $rows as $row ) {
		//ppd($rows);
		echo PersonData::parseContact($row);
	}
}
?>