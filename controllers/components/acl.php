<?php

class AclComponent extends Object {

	private $config = array();
	private $controller = null;
		
	function initialize(&$controller) {
		$this->config = Configure::read('Acl.rules');
		$this->controller =& $controller;
	}
	
	public function setUser($user) {
		$this->user = $user;
	}
	
	public function hasAccessTo($controller, $action=null) {

		if (!empty($this->user)) {
			$role = $this->user['Role']['role_key'];
		} else {
			$role = 'not_logged_in';
		}
		
		// Allow for hasAccessTo('action_name') to use the current controller by default
		if ($action === null) {
			$action = $controller;
			$controller = $this->controller->params['controller'];
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
	
	public function hasAccess() {
		$controller = $this->controller->params['controller'];
		$action = $this->controller->params['action'];
		return $this->hasAccessTo($controller, $action);
	}
	
	public function checkAccess($options=array()) {
		$defaults = array(
			'before_denied' => null
		);
		$options = array_merge($defaults, $options);
		
		$this->controller->Auth->allow('*');
		$user = $this->controller->Auth->user();
		if (!empty($user)) {
			$RoleModel = ClassRegistry::init('Role');
			$role = $RoleModel->findById($user['User']['role_id']);
			if (!empty($role)) {
				$user['Role'] = $role['Role'];
			}
		}
		$this->controller->set('currentUser', $user);
		
		$this->setUser($user);
		$this->refreshAuth();
		
		if (!$this->hasAccess()) {
			if (!$user) {
				$this->controller->redirect(array('controller' => 'users', 'action' => 'login'));
			}
			$this->controller->set('authorized', false);
			if (method_exists($this->controller, 'aclBeforeDenied')) {
				$this->controller->aclBeforeDenied();
			}
			$this->controller->viewPath = '../views/users';
			$this->controller->render('denied');
		} else {
			$this->controller->set('authorized', true);
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
	
	private function refreshAuth() {
		if (isset($this->controller->User)) {
			$this->controller->Auth->login($this->controller->User->read(false, $this->controller->Auth->user('id')));
		} else {
			$this->controller->Auth->login(ClassRegistry::init('User')->findById($this->controller->Auth->user('id')));
		}
	}
}

?>