#!/usr/bin/php
<?php
require(realpath(dirname(__FILE__)) . '/../../common/Loader.php');

$longopts = array(
    "config:",
    "seo:",
    "ngi:",
    "sites:",
    "admins:",
);
$opt = getopt("",$longopts);
libxml_use_internal_errors(true);
# Get site security contacts
$seo = DOMDocument::load($opt["seo"], LIBXML_NOWARNING | LIBXML_NOERROR);
if ($seo === false) {
        file_put_contents('php://stderr', "Cannot load list of Site Security Contats from the GOCDB\n");
        die();
} else {
        $seo_sites = $seo->getElementsByTagName('SITE');
}

$err = '';
# Get NGI security officers
$ngi_contacts = DOMDocument::load($opt["ngi"], LIBXML_NOWARNING | LIBXML_NOERROR);

if ($ngi_contacts === false) {
        file_put_contents('php://stderr', "Cannot load list of NGI Security Officers from the GOCDB\n");
        die();
} else {
        $ngis = $ngi_contacts->getElementsByTagName('ROC');
}
$gocdb_sites = DOMDocument::load($opt["sites"], LIBXML_NOWARNING | LIBXML_NOERROR);

if ($gocdb_sites === false) {
        file_put_contents('php://stderr', "Cannot load list of sites from the GOCDB\n");
        die();
} else {
        $sites = $gocdb_sites->getElementsByTagName('SITE');
}

function add_user($pakiti, $site_id, $contact) {
    $forename = $contact->getElementsByTagName('FORENAME')->item(0)->nodeValue;
    $surname = $contact->getElementsByTagName('SURNAME')->item(0)->nodeValue;
    $dn = $contact->getElementsByTagName('CERTDN')->item(0)->nodeValue;
    $email = $contact->getElementsByTagName('EMAIL')->item(0)->nodeValue;
    $name = $forename . " " . $surname;
    $user = new User();
    $user->setName($name);
    $user->setEmail($email);
    $user->setUid($dn);
    $pakiti->getManager('UsersManager')->storeUser($user);
    $user_id = $pakiti->getManager('UsersManager')->getUserIdByUid($dn);
    $pakiti->getManager('UsersManager')->assignHostGroupToUser($user_id, $site_id);
    return array($user_id,$name);
}

$deleted_users = array();
foreach ($sites as $site) {
    $hostGroup = new HostGroup();
    $site_name = $site->getAttribute('NAME');
    $site_ngi = $site->getAttribute('ROC');
    $hostGroup->setName($site_name);
    $pakiti->getManager('HostGroupsManager')->storeHostGroup($hostGroup);
    $site_id = $pakiti->getManager('HostGroupsManager')->getHostGroupIdByName($site_name);

    $added_users = array();

    foreach($seo_sites as $seo_site) {
        $seo_site_name = $seo_site->getAttribute('NAME');

        if ($seo_site_name != $site_name) continue;
        $scs_contact = $seo_site->getElementsByTagName('CONTACT');
        foreach ($scs_contact as $contact) {
            $user = add_user($pakiti, $site_id, $contact);
            printf("%s added to %s as site SO\n", $user[1], $site_name);
            $added_users[$user[0]] = 1;
        }
    }
    foreach ($ngis as $ngi) {
        $ngi_name = $ngi->getAttribute('ROC_NAME');
        if ($ngi_name != $site_ngi) continue;
        $contacts = $ngi->getElementsByTagName('CONTACT');
        foreach ($contacts as $contact) {
            $role = $contact->getElementsByTagName('ROLE_NAME')->item(0)->nodeValue;
            if ($role != 'NGI Security Officer') continue;
            $user = add_user($pakiti, $site_id, $contact);
            printf("%s added to %s as NGI SO\n", $user[1], $site_name);
            $added_users[$user[0]] = 1;
        }
    }
    $to_delete = array_diff($pakiti->getManager('HostGroupsManager')->getUsersAssignedToHostGroup($site_id), array_keys($added_users));
    foreach ($to_delete as $user_id) {
        $pakiti->getManager('UsersManager')->unassignHostGroupToUser($user_id, $site_id);
        printf("User %s unassigned from host group %s\n", $user_id, $site_id);
        $deleted_users[$user_id] = 1;
    }
}
foreach (array_keys($deleted_users) as $user_id) {
    $numberOfHostGroups = sizeof($pakiti->getManager('UsersManager')->getHostGroupsAssignedToUser($user_id));
    if ($numberOfHostGroups > 0) continue;
    $pakiti->getManager('UsersManager')->deleteUser($user_id);
    printf("Removing user %s\n", $user_id);
}

include($opt["admins"]);
foreach ($admin_dns as $admin) {
    $user = new User();
    $user->setUid($admin);
    $user->setAdmin(true);
    $pakiti->getManager('UsersManager')->storeUser($user);
}

?>

