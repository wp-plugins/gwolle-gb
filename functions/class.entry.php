<?php

/*
 * Gwolle Guestbook
 *
 * Class gwolle_gb_entry
 * Each instance is an entry in the guestbook.
 *
 * Variable:        Database field:  Type in db:   Description:                            Value when saving in db:
 * $id              id               int(10)       id of the entry/row/instance            required, autoincrement
 * $author_name     author_name      text          name of the author                      required
 * $author_id       author_id        int(5)        author is also registered user          required, default 0
 * $author_email    author_email     text          email address of the author             required
 * $author_origin   author_origin    text          city of the author                      required
 * $author_website  author_website   text          website of the author                   required
 * $author_ip       author_ip        text          ip address of the author                required
 * $author_host     author_host      text          hostname of that ip address             required
 * $content         content          longtext      content of the entry                    required
 * $date            date             varchar(10)   date of posting the entry, timestamp    required
 * $ischecked       ischecked        tinyint(1)    checked/moderated by an admin, 0 or 1   required
 * $checkedby       checkedby        int(5)        admin who checked/moderated this entry  required
 * $istrash         istrash          varchar(1)    entry is placed in the trashbin, 0 or 1 required, default 0
 * $isspam          isspam           varchar(1)    entry is considered as spam, 0 or 1     required, default 0
 *
 * FIXME: date should be TIMESTAMP
 * FIXME: use bool when appropriate (checkedby, istrash, isspam)
 */


class gwolle_gb_entry {

	protected $id, $author_name, $author_id, $author_email, $author_origin, $author_website,
		$author_ip, $author_host, $content, $date, $ischecked, $checkedby, $istrash, $isspam;

	/*
	 * Construct an instance
	 */

	public function __construct() {
		$this->id             = (int) 0;
		$this->author_name    = (string) "";
		$this->author_id      = (int) 0;
		$this->author_email   = (string) "";
		$this->author_origin  = (string) "";
		$this->author_website = (string) "";
		$this->author_ip      = (string) "";
		$this->author_host    = (string) "";
		$this->content        = (string) "";
		$this->date           = (string) "";
		$this->ischecked      = (int) 0;
		$this->checkedby      = (int) 0;
		$this->istrash        = (int) 0;
		$this->isspam         = (int) 0;
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
				id = %d";
			$values[] = $id;
		} else {
			return false;
		}

		$tablename = $wpdb->prefix . "gwolle_gb_entries";

		$sql = "
				SELECT
					`id`,
					`author_name`,
					`author_id`,
					`author_email`,
					`author_origin`,
					`author_website`,
					`author_ip`,
					`author_host`,
					`content`,
					`date`,
					`ischecked`,
					`checkedby`,
					`istrash`,
					`isspam`
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
			'id' => (int) $data['id'],
			'author_name' => stripslashes($data['author_name']),
			'author_id' => (int) $data['author_id'],
			'author_email' => stripslashes($data['author_email']),
			'author_origin' => stripslashes($data['author_origin']),
			'author_website' => stripslashes($data['author_website']),
			'author_ip' => $data['author_ip'],
			'author_host' => $data['author_host'],
			'content' => stripslashes($data['content']),
			'date' => $data['date'],
			'ischecked' => (int) $data['ischecked'],
			'checkedby' => (int) $data['checkedby'],
			'istrash' => (int) $data['istrash'],
			'isspam' => (int) $data['isspam']
		);

		$this->set_data( $item );

		return true;
	}


	/* function save
	 * Saves the current $entry to database
	 * Return:
	 * - id:    if saved
	 * - false: if not saved
	 */

	public function save() {
		global $wpdb;

		// FIXME: add filter for the entry before saving, so devs can manipulate it. This is probably the right place.

		if ( $this->get_id() ) {
			// entry exists, use UPDATE

			//if ( WP_DEBUG ) { echo "Saving ID:: "; var_dump($this->get_id()); }

			$sql = "
				UPDATE $wpdb->gwolle_gb_entries
				SET
					author_name = %s,
					author_id = %d,
					author_email = %s,
					author_origin = %s,
					author_website = %s,
					author_ip = %s,
					author_host = %s,
					content = %s,
					date = %s,
					isspam = %d,
					ischecked = %s,
					checkedby = %d,
					istrash = %d
				WHERE
					id = %d
				";

			$values = array(
					$this->get_author_name(),
					$this->get_author_id(),
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
					$this->get_istrash(),
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
					author_name,
					author_id,
					author_email,
					author_origin,
					author_website,
					author_ip,
					author_host,
					content,
					date,
					isspam,
					ischecked,
					checkedby,
					istrash
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
					$this->get_author_id(),
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
					$this->get_istrash()
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
	 * - author_id
	 * - author_email
	 * - author_origin
	 * - author_website
	 * - author_ip
	 * - author_host
	 * - content
	 * - date
	 * - ischecked
	 * - checkedby
	 * - istrash
	 * - isspam
	 */

	public function set_data($args) {

		if ( isset( $args['id']) ) {
			$this->set_id( $args['id'] );
		}
		if ( isset( $args['author_name']) ) {
			$this->set_author_name( $args['author_name'] );
		}
		if ( isset( $args['author_id']) ) {
			$this->set_author_id( $args['author_id'] );
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
		if ( isset( $args['istrash'] ) ) {
			$this->set_istrash( $args['istrash'] );
		}
		if ( isset( $args['isspam'] ) ) {
			$this->set_isspam( $args['isspam'] );
		}

		return true;
	}

	public function set_id($id) {
		$id = intval($id);
		if ($id) {
			$this->id = $id;
		}
	}
	public function set_author_name($author_name) {
		// User input
		$author_name = trim($author_name);
		$author_name = addslashes($author_name);
		$author_name = strval($author_name);
		if ($author_name) {
			$this->author_name = $author_name;
		}
	}
	public function set_author_id($author_id) {
		$author_id = intval($author_id);
		if ($author_id) {
			$this->author_id = $author_id;
		}
	}
	public function set_author_email($author_email) {
		// User input
		$author_email = trim($author_email);
		$author_email = addslashes($author_email);
		$author_email = strval($author_email);
		$author_email = filter_var($author_email, FILTER_VALIDATE_EMAIL);
		if ($author_email) {
			$this->author_email = $author_email;
		}
	}
	public function set_author_origin($author_origin) {
		// User input
		$author_origin = trim($author_origin);
		$author_origin = addslashes($author_origin);
		$author_origin = strval($author_origin);
		if ($author_origin) {
			$this->author_origin = $author_origin;
		}
	}
	public function set_author_website($author_website) {
		// User input
		$author_website = trim($author_website);
		$author_website = addslashes($author_website);
		$author_website = strval($author_website);
		$pattern = '/^http/';
		if ( !preg_match($pattern, $author_website, $matches) ) {
			$author_website = "http://" . $author_website;
		}
		$author_website = filter_var($author_website, FILTER_VALIDATE_URL);
		if ($author_website) {
			$this->author_website = $author_website;
		}
	}
	public function set_author_ip($author_ip = NULL) {
		if ( empty($author_ip) ) {
			$author_ip = $_SERVER['REMOTE_ADDR'];
		}
		$author_ip = trim($author_ip);
		$author_ip = addslashes($author_ip);
		$author_ip = strval($author_ip);
		if ($author_ip) {
			$this->author_ip = $author_ip;
		}
	}
	public function set_author_host($author_host = NULL) {
		$author_host = trim($author_host);
		$author_host = addslashes($author_host);
		// Don't use this here, only when it is really needed, like on a new entry
		// $author_host = gethostbyaddr( $author_ip );
		if ($author_host) {
			$this->author_host = $author_host;
		}
	}
	public function set_content($content) {
		// User input
		$content = trim($content);
		$content = stripslashes($content); // Make sure we're not just adding lots of slashes.
		$content = addslashes($content);
		$content = strval($content);
		$content = strip_tags($content);
		if ( strlen($content) > 0 ) {
			$this->content = $content;
		}
	}
	public function set_date($date = NULL) {
		$date = trim($date);
		$date = addslashes($date);
		if ( !$date ) {
			$date = current_time( 'timestamp' );
		}
		if ($date) {
			$this->date = $date;
		}
	}
	public function set_ischecked($ischecked) {
		// $ischecked means the message has been moderated
		$ischecked = intval($ischecked);
		$this->ischecked = $ischecked;
	}
	public function set_checkedby($checkedby) {
		// $checkedby is a userid of the moderator
		$checkedby = intval($checkedby);
		// FIXME: Check if user exists

		if ($checkedby) {
			$this->checkedby = $checkedby;
		}
	}
	public function set_istrash($istrash) {
		$istrash = intval($istrash);
		$this->istrash = $istrash;
	}
	public function set_isspam($isspam) {
		$isspam = intval($isspam);
		$this->isspam = $isspam;
	}


	/* The Getter methods */

	public function get_id() {
		return $this->id;
	}
	public function get_author_name() {
		return $this->author_name;
	}
	public function get_author_id() {
		return $this->author_id;
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
	public function get_istrash() {
		return $this->istrash;
	}
	public function get_isspam() {
		return $this->isspam;
	}


	/* function delete
	 * Deletes the current $entry from database
	 *
	 * Return:
	 * - true: deleted
	 * - false: not deleted
	 *
	 */

	public function delete() {
		global $wpdb;

		if ( $this->get_isspam() == 0 && $this->get_istrash() == 0 ) {
			// Do not delete the good stuff.
			return false;
		}

		$id = $this->get_id();

		$sql = "
			DELETE
			FROM
				$wpdb->gwolle_gb_entries
			WHERE
				id = %d
			LIMIT 1";

		$values = array(
				$id
			);

		$result = $wpdb->query(
				$wpdb->prepare( $sql, $values )
			);


		if ($result == 1) {
			// Also remove the log entries
			gwolle_gb_del_log_entries( $id );

			// FIXME: use unset? or set_id(0) if that even works with the setter
			// unset $this
			return true;
		}
		return false;
	}

}

