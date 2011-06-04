Simple ACL
==================================================
Extremely simple ACL functionality for CakePHP. 

Description
-----------

Simple ACL is a CakePHP plugin that provides very simple ACL functionality.  Each user is assigned to a role.  Roles can be created and edited using the conventional add, edit, delete, and index actions/views.  The access control list itself is defined by using a Configure::write() like the following:

	Configure::write('Acl.rules', array(
		'users' => array(
			'*' =>  'login,logout',
			'user' => 'profile',
			'admin' => '*',
			'deny' => array(
				'admin' => 'profile'
			),
			'order' => 'deny,allow'
		),
		'pages' => array(
			'*' => '*'
		),
		'posts' => array(
			'*' => 'view',
			'editor' => 'add,edit',
			'admin' => '*'
		)
	));

Each controller (users, pages, posts) has rules governing which roles can access which actions.  An asterisk is a wildcard that matches all roles or all actions, and deny rules can also be set.  An 'order' setting is also available; it sets whether the deny rules are run before the allow rules or vice versa (similar to Apache).

There are clearly advantages to having the ACL rules be database-driven, of course, but in some cases, a simpler setup like that of Simple ACL is sufficient and quicker to set up.

Installation
------------

1. Move `simple_acl/` into `app/plugins/`.
1. Copy the views in `simple_acl/views/users/` to `app/views/users/` and tweak as necessary.
1. Copy `simple_acl/views/elements/user_actions.ctp` to `app/views/elements/user_actions.ctp` and tweak as necessary if you'd like to show login/logout/profile links (see `views/layouts/default.ctp` below).
1. Run the following SQL to create the users and roles tables (MySQL is being assumed here):

		CREATE TABLE IF NOT EXISTS users(
		`id` INT(11) PRIMARY KEY AUTO_INCREMENT,
		`username` VARCHAR(50) NOT NULL,
		`password` VARCHAR(40) NOT NULL,
		`first_name` VARCHAR(50) NOT NULL,
		`last_name` VARCHAR(50) NOT NULL,
		`role_id` INT(4) NOT NULL,
		INDEX(`role_id`)
		);
		
		CREATE TABLE IF NOT EXISTS roles(
		`id` INT(11) PRIMARY KEY AUTO_INCREMENT,
		`role_key` VARCHAR(50) NOT NULL,
		`name` VARCHAR(50) NOT NULL
		);

1. Add the following code to the files specified:

	config/routes.php
	
		Router::connect('/users', array('plugin' => 'simple_acl', 'controller' => 'users', 'action' => 'index')); 
		Router::connect('/users/:action/*', array('plugin' => 'simple_acl', 'controller' => 'users'));
		
	app_controller.php
	
		var $components = array('SimpleAcl.Acl', 'Auth', 'Session');
		var $helpers = array('SimpleAcl.Acl');
		
		function beforeFilter() {
			$this->Acl->checkAccess();
		}
		
		function aclBeforeDenied() {
			// Add any code that should be executed before rendering the access denied view (optional)
		}
		
	views/layouts/default.ctp (optional)
	
		// This element displays log in, log out, and profile links, depending on whether the user is current logged in.
		echo $this->element('user_actions', array('plugin' => 'simple_acl'));


Rules
-----

The rules can be configured in config/bootstrap.php by setting `Acl.rules`:

	Configure::write('Acl.rules', array(
		'users' => array(
			'*' =>  'login,logout',
			'user' => 'profile',
			'admin' => '*',
			'deny' => array(
				'admin' => 'profile'
			),
			'order' => 'deny,allow'
		),
		'pages' => array(
			'*' => '*'
		)
	));

Each controller (e.g. 'users', 'pages') should have an entry here that configures which roles have access to which actions.

	'*' => '*'				// All roles have access to all actions
	'*' => 'login,logout'	// All roles have access to the login and logout actions.
	'user' => 'profile'		// Users with the role 'user' have access to the profile action.
	'admin' => '*'			// Users with the role 'admin' have access to all actions.
	'deny' => array(		
		'admin' => 'profile',		// Users with the role 'admin' do not have access to the profile action.
		'not_logged_in' => 'logout'	// Users who are not currently logged in ('not_logged_in' is the tokenized
									// role for this case) do not have access to the logout action.
	)
	'order' => 'deny,allow'	// Same as in Apache; the deny rules should be run before the allow rules.
							// 'allow,deny' is the other possible value.