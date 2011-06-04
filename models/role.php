<?php
class Role extends AppModel {
	var $name = 'Role';
	var $displayField = 'name';
	var $order = 'Role.sort';
	var $validate = array(
		'role_key' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);
}
?>