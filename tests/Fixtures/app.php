<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

// phpcs:ignoreFile
return [
	'name'  => 'Themosis',
	'wp'    => [
		'home' => 'http://themosis.com',
		'site' => 'http://themosis.com/cms',
	],
	'debug' => true,
	'salts' => [
		'auth_key'         => 'hjkl',
		'secure_auth_key'  => 'hjkl',
		'logged_in_key'    => 'hjkl',
		'nonce_key'        => 'hjkl',
		'auth_salt'        => 'hjkl',
		'secure_auth_salt' => 'hjkl',
		'logged_in_salt'   => 'hjkl',
		'nonce_salt'       => 'hjkl',
	],
];
