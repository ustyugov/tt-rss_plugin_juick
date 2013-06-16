<?php
class Juick extends Plugin {
	private $host;

	function about() {
		return array(1.0,
			"Share to Juick.com",
			"ustyugov");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
	}

	function hook_article_button($line) {
		$int_id = $line['int_id'];
		$result = db_query("SELECT uuid, ref_id FROM ttrss_user_entries WHERE int_id = '$int_id'
			AND owner_uid = " . $_SESSION['uid']);
		
		if (db_num_rows($result) != 0) {
			$uuid = db_fetch_result($result, 0, "uuid");
			$ref_id = db_fetch_result($result, 0, "ref_id");
			
			$entry = db_query("SELECT title FROM ttrss_entries WHERE id = '" . $ref_id . "'");
			$label = db_fetch_result($entry, 0, "title");
			$link = db_fetch_result($entry, 0, "link");

			if (!$uuid) {
				$uuid = db_escape_string(sha1(uniqid(rand(), true)));
				db_query("UPDATE ttrss_user_entries SET uuid = '$uuid' WHERE int_id = '$int_id'
					AND owner_uid = " . $_SESSION['uid']);
			}

			$url_path = get_self_url_prefix();
			$url_path .= "/public.php?op=share&key=$uuid";
			$clck = file_get_contents("http://clck.ru/--?url=".urlencode($url_path));
			
			return "<a href=\"http://juick.com/post?body=/".urlencode($label)."/ --> ".$clck."\" target=\"_blank\">
				<img src=\"plugins/juick/juick.png\"
				class='tagsPic' style=\"cursor : pointer\"
				title='Share to Juick'></a>";
		}
	}

	function api_version() {
		return 2;
	}

}
?>
