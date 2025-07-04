<?php

include ('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

Html::header('Automatic Ticket Validation', $_SERVER['PHP_SELF'], 'config', 'plugins');

$plugin = new PluginValidationauto();

// Process the form when submitted
if (isset($_POST['add']) && isset($_POST['new_keyword'])) {
    $plugin->processConfigForm();
    Html::back();
}

// Displays the form
Html::header("Validation Keywords", $_SERVER['PHP_SELF'], "config", "plugins");
echo "<div class='center'>";
echo "<h2>" . __("Manage Validation Keywords") . "</h2>";

$plugin->showConfigForm();

echo "</div>";
Html::footer();