<div class="user-actions">
	<ul>
<?php
if (!empty($currentUser)) {
	$displayName = empty($currentUser['User']['first_name']) ? $currentUser['User']['username'] : $currentUser['User']['first_name'];
	echo '<li>'.$this->Html->link($displayName, '/users/profile').'</li>';
	echo '<li>'.$this->Html->link('Logout', '/users/logout').'</li>';
} else {
	echo '<li>'.$this->Html->link('Login', '/users/login').'</li>';
}
?>
	</ul>
</div>