<?php

	// Read and count comments
	class ParseSQL extends Database
	{
		public
		function query (array $files = array (), $auto = true)
		{
			$this->storageMode = 'sqlite';
			$results = $this->database->query ('SELECT id FROM \'' . $this->setup->threadDirectory . '\'');

			if ($results !== false) {
				$results->execute ();
				$fetchAll = $results->fetchAll (PDO::FETCH_NUM);
				$return_array = array ();

				for ($i = 0, $il = count ($fetchAll); $i < $il; $i++) {
					$return_array[($fetchAll[$i][0])] = (string) $fetchAll[$i][0];
					$this->countComments ($fetchAll[$i][0]);
				}

				return $return_array;
			} else {
				exit ($this->setup->escapeOutput ('SQL query fail!', 'single'));
			}
		}

		public
		function read ($id)
		{
			$result = $this->database->query (
				'SELECT'
				. ' body,'
				. ' status,'
				. ' date,'
				. ' name,'
				. ' password,'
				. ' login_id,'
				. ' email,'
				. ' email_hash,'
				. ' notifications,'
				. ' website,'
				. ' ipaddr,'
				. ' likes,'
				. ' dislikes'
				. ' FROM \'' . $this->setup->threadDirectory . '\''
				. ' WHERE id=\'' . $id . '\''
			);

			if ($result !== false) {
				return (array) $result->fetch (PDO::FETCH_ASSOC);
			} else {
				exit ($this->setup->escapeOutput ('SQL query fail!', 'single'));
			}

			return false;
		}

		public
		function save ($contents, $id, $editing = false)
		{
			if ($editing === true) {
				return $this->write ('update',
					array (
						'id' => $id,
						'body' => $contents['body'],
						'name' => $contents['name'],
						'password' => $contents['password'],
						'email' => $contents['email'],
						'email_hash' => $contents['email_hash'],
						'notifications' => $contents['notifications'],
						'website' => $contents['website'],
						'likes' => $contents['likes'],
						'dislikes' => $contents['dislikes']
					)
				);
			} else {
				return $this->write ('insert', array_merge ($contents, array ('id' => $id)));
			}
		}

		public
		function delete ($id)
		{
			return $this->write ('delete',
				array (
					'id' => $id,
					'status' => 'deleted'
				)
			);
		}
	}

?>
