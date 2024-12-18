<?php

/**
 * Class WPDispatchForge
 * Core functionality of the plugin.
 */
class WPDispatchForge {
    
    /**
     * Initialize the plugin's core functionality.
     */
    public static function init() {
        // Add any initialization logic here.
    }

    /**
     * Handle plugin activation tasks.
     */
    public static function activate() {
        // Example: Add options or create database tables.
        add_option('wpdispatchforge_activated', true);
    }

    /**
     * Handle plugin uninstallation tasks.
     */
    public static function uninstall() {
        // Example: Remove options or database tables.
        delete_option('wpdispatchforge_activated');
    }
}
