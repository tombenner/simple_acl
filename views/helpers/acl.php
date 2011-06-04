<?php

class AclHelper extends AppHelper {

	private $config = array();
		
	function __construct() {
		parent::__construct();
		$this->config = Configure::read('Acl.rules');
		if (empty($this->view)) {
			$this->view =& ClassRegistry::getObject('view');
		}
		$this->user = empty($this->view->viewVars['currentUser']) ? null : $this->view->viewVars['currentUser'];
	}
	
	public function hasAccessTo($controller, $action) {

		if (!empty($this->user)) {
			$role = $this->user['Role']['role_key'];
		} else {
			$role = 'not_logged_in';
		}
		
		$rules = empty($this->config[$controller]) ? null : $this->config[$controller];
		
		if (!$rules || !$role) {
			return false;
		}
		
		$order = 'deny,allow';
		$deny_match = false;
		
		if (isset($rules['deny'])) {
			if ($this->rulesMatch($role, $action, $rules['deny'])) {
				$deny_match = true;
			}
			unset($rules['deny']);
		}
		
		if (isset($rules['order'])) {
			$order = $rules['order'];
			unset($rules['order']);
		}
		
		$allow_match = $this->rulesMatch($role, $action, $rules);
		
		if ($order == 'deny,allow') {
			if ($deny_match) {
				return false;
			}
			return $allow_match;
		} else {
			if ($allow_match) {
				return true;
			}
			return !$deny_match;
		}
		
	}
	
	private function rulesMatch($role, $action, $rules) {
		if ($rules == '*') {
			return true;
		}
		foreach($rules as $role_pattern => $action_pattern) {
			if ($this->patternMatch($role, $role_pattern) &&
				$this->patternMatch($action, $action_pattern)) {
				return true;
			}
		}
		return false;
	}
	
	private function patternMatch($string, $pattern) {
		if ($pattern == '*' || preg_match('/(,|^)'.$string.'(,|$)/', $pattern)) {
			return true;
		}
		return false;
	}
}

?>