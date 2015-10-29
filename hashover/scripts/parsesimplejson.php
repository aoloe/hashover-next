<?php
/*
 Copyright (C) 2015 Ale Rimoldi

This file could be part of HashOver.

HashOver is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

HashOver is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with HashOver.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Storage putting all the replies for a reference in one single json file.
 */

class ParseSimpleJson {

    private $storageMode = 'flat-file';
    private $setup = null;
    private $metadata = array();

    private $data = null; // the data inside of the json file
    private $replyFlat = null; // a flat list of all replies (since they are queried one after the other)

    private $pageDtd = array(
        'title' => "",
        'url' => "",
        'status' => "", // open
        'last_key' => 0,
        'reply' => array()
    );

    private $replyDtd = array(
        'key' => 0,
        'body' => "",
        'status' => "", // approved
        'date' => "",
        'name' => "",
        'email' => "",
        'likes' => 0,
        'reply' => array(),
    );

    public function __construct(Setup $setup) {
        // setting the metadata for the page attached to this comments
        $this->setup = $setup;
        $this->setup->metadata = array_merge ($this->setup->metadata, $this->getInstanceMetadata());
        // TODO: readfiles.php is checking and updating the title and the url in the metadata from the
        // one from the $setup. do we also want to do it?
    }

    public function save_metadata(array $data, $file) {
        // TODO: not sure if it is needed... we can store everything in the .json file
        $json = json_encode ($data);
        return file_put_contents ($file, $json);
    }

    public function loadFiles($extension, array $file = array(), $auto = true) {
        // TODO: not sure if it is needed
    }

    /**
     * @return <array> list of the keys for the comments
     */
    public function query($files = array(), $auto = true) {
        // TODO: the original is calling loadFiles... not sure what we are supposed to be doing
        // we should probably read the full thread if we can find out which one it is...
        $data = json_decode(file_get_contents($this->setup->dir.'.json'), true);
        if ($data) {
            $this->data = $data;
            $result = $this->getCommentKeys($data['reply']);
        } else {
            $this->data = array();
        }
        return $result;
    }

    /**
     * for backward compatibility return a flat list of all keys
     */
    private function getCommentKeys($data) {
        $result = array();
        foreach ($data as $item) {
            $result[$item['key']] = $item['key'];
            $this->replyFlat[$item['key']] = $item;
            if (array_key_exists('reply', $item)) {
                $result = $result + $this->getCommentKeys($item['reply']);
            }
        }
        return $result;
    }

    /**
     * read the $key comment
     * @return an array with the comment data
     */
    public function read($key, $fullpath = false) {
        return $this->replyFlat[$key];
    }

    public function save($contents, $file, $editing = false, $fullpath = false) {
    }

    public function delete ($file, $hardUnlink = false) {
    }

    private function getInstanceMetadata() {
        // TODO: this metadata can probably be stored in the same json file
        $json_metadata = array();
        // TODO: we should use a future $this->setup->dataDir defined as dir or as data-dir if given
        $metadata_file = $this->setup->dir.'/metadata';
        // $update_metadata = false;
        if (file_exists ($metadata_file)) {
            $json_metadata = json_decode (file_get_contents ($metadata_file), true);
        }
        return $json_metadata;
    }

}
