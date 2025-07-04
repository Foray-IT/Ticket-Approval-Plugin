<?php

include ('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

Html::header('Automatic Ticket Validation', $_SERVER['PHP_SELF'], 'config', 'plugins');
Html::header('Aprovação Automatica por email', $_SERVER['PHP_SELF'], 'config', 'plugins');

$plugin = new PluginValidationauto();

if (isset($_POST['add']) && isset($_POST['new_keyword'])) {
    $plugin->processConfigForm();
    Html::back();
}

Html::header("Validation Keywords", $_SERVER['PHP_SELF'], "config", "plugins");
Html::header("Palavras-chave de Validação", $_SERVER['PHP_SELF'], "config", "plugins");
echo "<div class='center'>";
echo "<h2>" . __("Manage Validation Keywords") . "</h2>";
echo "<h2>" . __("Gerenciar Palavras-chave de Aprovação") . "</h2>";

$plugin->showConfigForm();

echo "</div>";
Html::footer();