<?php
use System\Web\Utils\HTMLUtils;
$fromtab = isset($fromtab) ? $fromtab :false;
$rows = isset($rows) ? $rows : null;
if (!$fromtab) {
?>
<form class="form-search"
	action="<?php echo BASE_URL . 'person/search/' ?>">
	<input autocomplete="off" class="input-medium search-query" type="text"
		maxlength="256" name="q" label="Find People" placeholder="Find People">
	<button type="submit" class="btn">Search</button>
</form>
<?php
}
if ($rows) {
	//ppd($rows);
}else{
	echo HTMLUtils::renderError(__('person.nocontacts'));
}

?>