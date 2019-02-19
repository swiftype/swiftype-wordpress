<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

/**
 * Instantiate Swiftype Admin menu
 *
 * @author  Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 * @since 1.0
 */
class Menu
{
    const MENU_TITLE = 'Swiftype Search';
    const MENU_SLUG  = 'swiftype';
    const MENU_ICON  = 'assets/swiftype_logo_menu.png';

    private $adminPage;

    public function __construct(Page $adminPage)
    {
        $this->adminPage = $adminPage;

        \add_action('admin_menu', [$this, 'addMenu']);
    }

    public function addMenu()
    {
        \add_menu_page(self::MENU_TITLE, SELF::MENU_TITLE, 'manage_options', SELF::MENU_SLUG, [$this->adminPage, 'getContent'], $this->getIconUrl());
    }

    private function getIconUrl()
    {
        return \plugins_url(self::MENU_ICON, __DIR__ . '/../swiftype.php');
    }

}
