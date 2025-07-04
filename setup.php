<?php
use Glpi\Plugin\Hooks;

define('PLUGIN_VALIDATIONAUTO_VERSION', '1.0.0');

function plugin_init_validationauto() {
    global $PLUGIN_HOOKS;
    
    // Necessary for Security
    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['validationauto'] = true;
    
    if (Session::haveRight('config', UPDATE)) {
        // Adds the settings menu
        $PLUGIN_HOOKS['config_page']['validationauto'] = 'front/config.php';
        
        // Adds the menu to GLPI
        $PLUGIN_HOOKS['menu_toadd']['validationauto'] = [
            'config' => 'PluginValidationautoConfig'
        ];
    }
    
    // Registers the hook for processing followups
    $PLUGIN_HOOKS['item_add']['validationauto'] = [
        'ITILFollowup' => 'plugin_validationauto_process_followup'
    ];
}

function plugin_version_validationauto() {
    return [
        'name'           => 'Automatic Ticket Validation',
        'version'        => PLUGIN_VALIDATIONAUTO_VERSION,
        'author'         => 'Adriano Marinho, Jay (English Translation)',
        'license'        => 'GLPv3+',
        'homepage'       => 'https://github.com/Foray-IT/Ticket-Approval-Plugin/',
        'requirements'   => [
            'glpi' => [
                'min' => '9.5',
                'max' => '10.1',
                'dev' => false
            ],
            'php' => [
                'min' => '7.4'
            ]
        ]
    ];
}

function plugin_validationauto_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '9.5', 'lt')) {
        echo "This plugin requires a GLPI version >= 9.5";
        return false;
    }
    return true;
}

function plugin_validationauto_check_config($verbose = false) {
    if ($verbose) {
        echo 'Settings OK';
    }
    return true;
}