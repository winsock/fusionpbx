<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2014
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Andrew Querol <andrew@quero.me>
*/

include "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
if (permission_exists('device_template')) {
    //access granted
}
else {
    echo "access denied";
    exit;
}

$values = explode("/", $_REQUEST['template']);
if (sizeof($values) != 2) {
    header("HTTP/1.0 400 Bad Request");
    exit;
}
$vendor = $values[0];
$template = $values[1];

function stream_file($config_script) {
    $config_fp = fopen($config_script, 'rb');
    if ($config_fp === false) {
        return;
    }
    fpassthru($config_fp);
    fclose($config_fp);
}

$device = new device;
$template_dir = $device->get_template_dir();

$total_size = filesize("{$template_dir}/config.js")
    + filesize("{$template_dir}/{$vendor}/config.js")
    + filesize("{$template_dir}/{$vendor}/{$template}/config.js");

if ($total_size <= 0) {
    // No files found 404
    header("HTTP/1.0 404 Not Found");
    exit;
}

header("Content-Type: text/javascript");
header("Content-Length: ".$total_size);
// Stream the files in correct execution order, individual template configs have priority over vendor and default configs.
stream_file("{$template_dir}/config.js");
stream_file("{$template_dir}/{$vendor}/config.js");
stream_file("{$template_dir}/{$vendor}/{$template}/config.js");