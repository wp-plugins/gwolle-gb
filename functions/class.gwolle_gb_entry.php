<?php

/*
 * Gwolle Guestbook
 *
 * Class gwolle_gb_entry
 * Each instance is an entry in the guestbook.
 *
 * Variable:		Database field:			Type in db:		Description:									Value when saving in db:
 * $id				entry_id				int(10)			id of the entry/row/instance					required, autoincrement
 * $author_name		entry_author_name		text			name of the author								required
 * $authoradminid	entry_authorAdminId		int(5)			The author is also registered user				required, default 0
 * $author_email	entry_author_email		text			email address of the author						required
 * $author_origin	entry_author_origin		text			city of the author								required
 * $author_website	entry_author_website	text			website of the author, with or without http://	required
 * $author_ip		entry_author_ip			text			ip address of the author						required
 * $author_host		entry_author_host		text			hostname of that ip address						required
 * $content			entry_content			longtext		content of the entry							required
 * $date			entry_date				varchar(10)		date of posting the entry, timestamp			required
 * $ischecked		entry_isChecked			tinyint(1)		checked/moderated by an admin, 0 or 1			required
 * $checkedby		entry_checkedBy			int(5)			the admin who checked/moderated this entry		required
 * $isdeleted		entry_isDeleted			varchar(1)		entry is placed in the trashbin, 0 or 1			required, default 0
 * $isspam			entry_isSpam		 	varchar(1)		entry is considered as spam, 0 or 1				required, default 0
 *
 * FIXME: rename entry_id to id, and all the others as well
 * FIXME: use all lowercase for consistency, have an ORM idea
 * FIXME: rename is_deleted into istrash to avoid confusion. deletion is really removal
 * FIXME: date should be TIMESTAMP
 * FIXME: make id UNIQUE, so we can use SQL REPLACE
 * FIXME: use bool when appropriate (checkedby, isdeleted, isspam)
 */


class gwolle_gb_entry {

	protected $id, $author_name, $authoradminid, $author_email, $author_origin, $author_website,
		$author_ip, $author_host, $content, $date, $ischecked, $checkedby, $isdeleted, $isspam;

	/*
	 * Construct an instance
	 */

	public function __construct() {
		$this->id 				= (int) 0;
		$this->author_name 		= (string) "";
		$this->authoradminid	= (int) 0;
		$this->author_email		= (string) "";
		$this->author_origin	= (string) "";
		$this->author_website	= (string) "";
		$this->author_ip		= (string) "";
		$this->author_host		= (string) "";
		$this->content			= (string) "";
		$this->date				= (string) "";
		$this->ischecked		= (int) 0;
		$this->checkedby		= (int) 0;
		$this->isdeleted		= (int) 0;
		$this->isspam			= (int) 0;
	}


	/*
	 * function load
	 * Loads the entry from database and sets the data in the instance
	 *
	 * Parameter:
	 * $id id of the entry to be loaded
	 *
	 * Return: true or false, depending on success
	 */

	public function load( $id ) {
		global $wpdb;

		$where = " 1 = %d";
		$values = Array(1);

		if ( !is_numeric($id) ) {
			return false;
		}

		if ((int) $id > 0) {
			$where .= "
				AND
				entry_id = %d";
			$values[] = $id;
		} else {
			return false;
		}

		$tablename = $wpdb->prefix . "gwolle_gb_entries";

		$sql = "
				SELECT
					*
				FROM
					" . $tablename . "
				WHERE
					" . $where . "
				;";

		$sql = $wpdb->prepare( $sql, $values );

		$data = $wpdb->get_row( $sql, ARRAY_A );

		if ( empty($data) ) {
			return false;
		}

		// Use the fields that the setter method expects
		$item = array(
			'id' => (int) $data['entry_id'],
			'author_name' => stripslashes($data['entry_author_name']),
			'authoradminid' => (int) $data['entry_authorAdminId'],
			'author_email' => stripslashes($data['entry_author_email']),
			'author_origin' => stripslashes($data['entry_author_origin']),
			'author_website' => stripslashes($data['entry_author_website']),
			'author_ip' => $data['entry_author_ip'],
			'author_host' => $data['entry_author_host'],
			'content' => stripslashes($data['entry_content']),
			'date' => $data['entry_date'],
			'ischecked' => (int) $data['entry_isChecked'],
			'checkedby' => (int) $data['entry_checkedBy'],
			'isdeleted' => (int) $data['entry_isDeleted'],
			'isspam' => (int) $data['entry_isSpam']
		);

		$this->set_data( $item );

		return true;
	}


	/* function save
	 * Saves the current $entry to database
	 * Return:
	 * - entry_id: if saved
	 * - false: if not saved
	 */

	public function save() {
		global $wpdb;

		if ( $this->get_id() ) {
			// entry exists, use UPDATE

			if ( WP_DEBUG ) { echo "Saving ID:: "; var_dump($this->get_id()); }

			$sql = "
				UPDATE $wpdb->gwolle_gb_entries
				SET
					entry_author_name = %s,
					entry_authorAdminId = %d,
					entry_author_email = %s,
					entry_author_origin = %s,
					entry_author_website = %s,
					entry_author_ip = %s,
					entry_author_host = %s,
					entry_content = %s,
					entry_date = %s,
					entry_isSpam = %d,
					entry_isChecked = %s,
					entry_checkedBy = %d,
					entry_isDeleted = %d
				WHERE
					entry_id = %d
				";

			$values = array(
					$this->get_author_name(),
					$this->get_authoradminid(),
					$this->get_author_email(),
					$this->get_author_origin(),
					$this->get_author_website(),
					$this->get_author_ip(),
					$this->get_author_host(),
					$this->get_content(),
					$this->get_date(),
					$this->get_isspam(),
					$this->get_ischecked(),
					$this->get_checkedby(),
					$this->get_isdeleted(),
					$this->get_id()
				);

			$result = $wpdb->query(
					$wpdb->prepare( $sql, $values )
				);

		} else {
			// entry is new, use INSERT

			$result = $wpdb->query( $wpdb->prepare(
				"
				INSERT INTO $wpdb->gwolle_gb_entries
				(
					entry_author_name,
					entry_authorAdminId,
					entry_author_email,
					entry_author_origin,
					entry_author_website,
					entry_author_ip,
					entry_author_host,
					entry_content,
					entry_date,
					entry_isSpam,
					entry_isChecked,
					entry_checkedBy,
					entry_isDeleted
				) VALUES (
					%s,
					%d,
					%s,
					%s,
					%s,
					%s,
					%s,
					%s,
					%s,
					%d,
					%d,
					%d,
					%d
				)
				",
				array(
					$this->get_author_name(),
					$this->get_authoradminid(),
					$this->get_author_email(),
					$this->get_author_origin(),
					$this->get_author_website(),
					$this->get_author_ip(),
					$this->get_author_host(),
					$this->get_content(),
					$this->get_date(),
					$this->get_isspam(),
					$this->get_ischecked(),
					$this->get_checkedby(),
					$this->get_isdeleted()
				)
			) );

			if ($result > 0) {
				// Entry saved successfully.
				$this->set_id( $wpdb->insert_id );
			}
		}

		// Error handling
		//$wpdb->print_error();
		//if ( WP_DEBUG ) { echo "Result: " .$result; }

		if ($result > 0) {
			return $this->get_id();
		}

		return false;
	}


	/* The Setter methods */

	/*
	 * Set all fields, $args is an array with fields
	 * Can be used after a $_POST or by the gwolle_gb_get_entries function
	 *
	 * Array $args:
	 * - id
	 * - author_name
	 * - authoradminid
	 * - author_email
	 * - author_origin
	 * - author_website
	 * - author_ip
	 * - author_host
	 * - content
	 * - date
	 * - ischecked
	 * - checkedby
	 * - isdeleted
	 * - isspam
	 */

	public function set_data($args) {

		if ( isset( $args['id']) ) {
			$this->set_id( $args['id'] );
		}
		if ( isset( $args['author_name']) ) {
			$this->set_author_name( $args['author_name'] );
		}
		if ( isset( $args['authoradminid']) ) {
			$this->set_authoradminid( $args['authoradminid'] );
		}
		if ( isset( $args['author_email'] ) ) {
			$this->set_author_email( $args['author_email'] );
		}
		if ( isset( $args['author_origin'] ) ) {
			$this->set_author_origin( $args['author_origin'] );
		}
		if ( isset( $args['author_website'] ) ) {
			$this->set_author_website( $args['author_website'] );
		}
		if ( isset( $args['author_ip'] ) ) {
			$this->set_author_ip( $args['author_ip'] );
		} else if ( !$this->get_author_ip() ) {
			$this->set_author_ip(); // set as new
		}
		if ( isset( $args['author_host'] ) ) {
			$this->set_author_host( $args['author_host'] );
		} else if ( $this->get_author_host() == '' && $this->get_author_ip() ) {
			$this->set_author_host(); // set as new
		}
		if ( isset( $args['content'] ) ) {
			$this->set_content( $args['content'] );
		}
		if ( isset( $args['date'] ) ) {
			$this->set_date( $args['date'] );
		} else if ( !$this->get_date() ) {
			$this->set_date(); // set as new
		}
		if ( isset( $args['ischecked'] ) ) {
			$this->set_ischecked( $args['ischecked'] );
		}
		if ( isset( $args['checkedby'] ) ) {
			$this->set_checkedby( $args['checkedby'] );
		}
		if ( isset( $args['isdeleted'] ) ) {
			$this->set_isdeleted( $args['isdeleted'] );
		}
		if ( isset( $args['isspam'] ) ) {
			$this->set_isspam( $args['isspam'] );
		}

		return true;
	}
	// FIXME: integrate the setters and checkers? It's all the same anyway
	public function set_id($id) {
		$id = $this->check_id($id);
		if ($id) {
			$this->id = $id;
		}
	}
	public function set_author_name($author_name) {
		$author_name = $this->check_author_name($author_name);
		if ($author_name) {
			$this->author_name = $author_name;
		}
	}
	public function set_authoradminid($authoradminid) {
		$authoradminid = $this->check_authoradminid($authoradminid);
		if ($authoradminid) {
			$this->authoradminid = $authoradminid;
		}
	}
	public function set_author_email($author_email) {
		$author_email = $this->check_author_email($author_email);
		if ($author_email) {
			$this->author_email = $author_email;
		}
	}
	public function set_author_origin($author_origin) {
		$author_origin = $this->check_author_origin($author_origin);
		if ($author_origin) {
			$this->author_origin = $author_origin;
		}
	}
	public function set_author_website($author_website) {
		$author_website = $this->check_author_website($author_website);
		if ($author_website) {
			$this->author_website = $author_website;
		}
	}
	public function set_author_ip($author_ip = NULL) {
		$author_ip = $this->check_author_ip($author_ip);
		if ($author_ip) {
			$this->author_ip = $author_ip;
		}
	}
	public function set_author_host($author_host = NULL) {
		$author_host = $this->check_author_host($author_host);
		if ($author_host) {
			$this->author_host = $author_host;
		}
	}
	public function set_content($content) {
		$content = $this->check_content($content);
		if ($content) {
			$this->content = $content;
		}
	}
	public function set_date($date = NULL) {
		$date = $this->check_date($date);
		if ($date) {
			$this->date = $date;
		}
	}
	public function set_ischecked($ischecked) {
		// $ischecked means the message has been moderated
		$ischecked = $this->check_ischecked($ischecked);
		$this->ischecked = $ischecked;
	}
	public function set_checkedby($checkedby) {
		// $checkedby is a userid of the moderator
		$checkedby = $this->check_checkedby($checkedby);
		if ($checkedby) {
			$this->checkedby = $checkedby;
		}
	}
	public function set_isdeleted($isdeleted) {
		$isdeleted = $this->check_isdeleted($isdeleted);
		$this->isdeleted = $isdeleted;
	}
	public function set_isspam($isspam) {
		$isspam = $this->check_isspam($isspam);
		$this->isspam = $isspam;
	}


	/* The Getter methods */

	public function get_id() {
		return $this->id;
	}
	public function get_author_name() {
		return $this->author_name;
	}
	public function get_authoradminid() {
		return $this->authoradminid;
	}
	public function get_author_email() {
		return $this->author_email;
	}
	public function get_author_origin() {
		return $this->author_origin;
	}
	public function get_author_website() {
		return $this->author_website;
	}
	public function get_author_ip() {
		return $this->author_ip;
	}
	public function get_author_host() {
		return $this->author_host;
	}
	public function get_content() {
		return $this->content;
	}
	public function get_date() {
		return $this->date;
	}
	public function get_ischecked() {
		return $this->ischecked;
	}
	public function get_checkedby() {
		return $this->checkedby;
	}
	public function get_isdeleted() {
		return $this->isdeleted;
	}
	public function get_isspam() {
		return $this->isspam;
	}


	/*
	 * The Check methods.
	 * Check and normalize the data.
	 * Arg:    The data to be checked.
	 * Return: The data that has been validated and normalized.
	 *         Or false if it's not valid.
	 */

	public function check_id($id) {
		$id = intval($id);
		return $id;
	}
	public function check_author_name($author_name) {
		$author_name = trim($author_name);
		$author_name = addslashes($author_name);
		$author_name = strval($author_name);
		return $author_name;
	}
	public function check_authoradminid($authoradminid) {
		$authoradminid = intval($authoradminid);
		return $authoradminid;
	}
	public function check_author_email($author_email) {
		$author_email = trim($author_email);
		$author_email = addslashes($author_email);
		$author_email = strval($author_email);
		return filter_var($author_email, FILTER_VALIDATE_EMAIL);
	}
	public function check_author_origin($author_origin) {
		$author_origin = trim($author_origin);
		$author_origin = addslashes($author_origin);
		$author_origin = strval($author_origin);
		return $author_origin;
	}
	public function check_author_website($author_website) {
		$author_website = trim($author_website);
		$author_website = addslashes($author_website);
		$author_website = strval($author_website);
		$pattern = '/^http/';
		if ( !preg_match($pattern, $author_website, $matches) ) {
			$author_website = "http://" . $author_website;
		}
		return filter_var($author_website, FILTER_VALIDATE_URL);
	}
	public function check_author_ip($author_ip = NULL) {
		if ( empty($author_ip) ) {
			$author_ip = $_SERVER['REMOTE_ADDR'];
		}
		$author_ip = trim($author_ip);
		$author_ip = addslashes($author_ip);
		$author_ip = strval($author_ip);
		return $author_ip;
	}
	public function check_author_host($author_host = NULL) {
		$author_host = trim($author_host);
		$author_host = addslashes($author_host);
		if (strlen($author_host) > 0) {
			return $author_host;
		} else {
			$author_ip = $this->get_author_ip();
			if ( strlen($author_ip) > 0 ) {
				$author_host = gethostbyaddr( $author_ip );
				return $author_host;
			}
		}
		return false;
	}
	public function check_content($content) {
		$content = trim($content);
		$content = addslashes($content);
		$content = strval($content);
		$strlen = strlen($content);
		if ($strlen > 0) {
			return $content;
		} else {
			return false;
		}
	}
	public function check_date($date = NULL) {
		$date = trim($date);
		$date = addslashes($date);
		if ( !$date ) {
			$date = current_time( 'timestamp' );
		}
		return $date;
	}
	public function check_ischecked($ischecked) {
		$ischecked = intval($ischecked);
		return $ischecked;
	}
	public function check_checkedby($checkedby) {
		$checkedby = intval($checkedby);
		// FIXME: Check if user exists
		return $checkedby;
	}
	public function check_isdeleted($isdeleted) {
		$isdeleted = intval($isdeleted);
		return $isdeleted;
	}
	// This function does not check with Akismet, but simple checks if the value is valid
	public function check_isspam($isspam) {
		$isspam = intval($isspam);
		return $isspam;
	}


	/* function delete
	 * Deletes the current $entry from database
	 * $id id of the entry to be deleted
	 * Return:
	 * - true: deleted
	 * - false: not deleted
	 */

	public function delete( $id ) {
		global $wpdb;

		// FIXME, stub

		//  We need the old entry data as an argument.
		//if (!isset($id)) {
			return false;
		//}

		if ( $this->get_isspam() == 0 && $this->get_isdeleted() == 0 ) {
			// Do not delete the good stuff.
			return false;
		}

		// FIXME: use wpdb->prepare
		$sql = "
			DELETE
			FROM
				" . $wpdb -> gwolle_gb_entries . "
			WHERE
				entry_id = " . (int)$id . "
			LIMIT 1";
		$result = $wpdb->query($sql);

		if ($result == 1) {
			// Also remove the log entries? Probably. Needs a function for del_log though


			// FIXME: use unset?
			return true;
		}
		return false;
	}

}

