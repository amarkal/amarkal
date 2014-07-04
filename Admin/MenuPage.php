<?php

namespace Amarkal\Admin;

/**
 * Implements a menu page for the WordPress admin sidebar.
 * 
 * @see http://codex.wordpress.org/Function_Reference/add_menu_page
 * @see http://codex.wordpress.org/Function_Reference/add_submenu_page
 * 
 * Example usage:
 * 
 * $page = new \Amarkal\Admin\MenuPage(array(
 *     'title'         => 'My Admin Page',
 *     'icon'          => 'my-page-icon.png',
 *     'class'         => 'my-icon',
 *     'style'         => array(
 *         'padding-top' => '7px'
 *     )
 * ));
 * $page->add_page(array(
 *     'title'         => 'My Admin Submenu',
 *     'capability'    => 'manage_options',
 *     'content'       => function(){include('my-page.php');}
 * ));
 * $page->register();
 * 
 */
class MenuPage
{
    /**
     * The menu page configuration.
     * 
     * @see MenuPage::get_defaults() for possible options.
     * @var mixed[] The configuraion.  
     */
    private $config;
    
    /**
     * Constructor.
     * 
     * @param mixed[] $config Page configuration.
     */
    public function __construct( array $config = array() )
    {
        $this->set_config( $config );
    }
    
    /**
     * Add a page to this menu.
     * 
     * If there is only one page set for this menu, no submenu will be created.
     * 
     * Possible options are:
     * title:       (Required) Submenu title AND submenu page title tag.
     * capability:  (Required) The capability required for this menu to be displayed to the user.
     * content:     (Required) The function that displays the page content for the menu page.
     * 
     * @param mixed[] $config The page's configuration.
     * @throws \RuntimeException If one of the required options was not set.
     */
    public function add_page( $config )
    {
        foreach( $this->required_params() as $param )
        {
            if( !isset( $config[$param] ) )
            {
                throw new \RuntimeException( 'Missing required parameter "'.$param.'" for submenu' );
            }
        }
        $this->config['submenu-pages'][] = $config;
    }
    
    /**
     * Register this page into the admin menu.
     * 
     * Hooks the menu to the admin_menu action.
     */
    public function register()
    {
        add_action( 'admin_menu', array( $this,'add_menu_page' ) );
    }
    
    /**
     * Get the default configuration for a menu page.
     * 
     * @return mixed[] The configuration.
     */
    private function get_defaults()
    {
        return array(
            'title'         => '',      // Menu title AND title tag
            'slug'          => null,    // The slug name to refer to this menu by (should be unique for this menu).
            'icon'          => '',      // The icon for this menu.
            'class'         => '',      // The class for the icon of this menu.
            'style'         => array(), // Custom style for the icon of this menu.
            'position'      => null,    // The position in the menu order this menu should appear.
            'submenu-pages' => array()  // Array of page submenus. Use self::add_submenu_page( $config ) to add new sub-pages.
        );
    }
    
    /**
     * Get the list of required parameter names when adding a page to this menu.
     * Used by MenuPage::add_page().
     * 
     * @return string[] The parameter names.
     */
    private function required_params()
    {
        return array( 'title', 'capability', 'content' );
    }
    
    /**
     * Set the configuration for this menu page.
     * 
     * Validates that all the required parameters have been provided.
     * Auto generates a slug if none was provided.
     * 
     * @param mixed[] $config The configuraion.
     * @throws \RuntimeException If one of the required options was not provided.
     */
    private function set_config( $config )
    {
        if( !isset( $config['title'] ) )
        {
            throw new \RuntimeException( 'Missing required parameter "title" for menu' );
        }
        
        if( !isset( $config['slug'] ) || '' == $config['slug'] )
        {
            $config['slug'] = $this->strtoslug( $config['title'] );
        }
        $this->config = array_merge( $this->get_defaults(), $config );
    }
    
    /**
     * String to slug.
     * 
     * Generate a slug from a string by converting all the special characters
     * into dashes.
     * 
     * @param string $str The string to convert.
     * @return string The resulting slug.
     */
    private function strtoslug( $str )
    {
        return preg_replace( '/[ .\-_@#$%^&*();\\/|<>"\'!+]+/', '-', strtolower( $str ) );
    }
    
    /**
     * Add this page, and all it's submenus, to the admin sidebar.
     * 
     * Internally used when the admin_menu action is called. 
     * This function is called by MenuPage::register().
     */
    public function add_menu_page()
    {
        extract( $this->config );
        $icon .= '' != $class ? '" class="'.$class : '';
        $icon .= array() != $style ? '" style="'.$this->array_to_css($style) : '';
        $first_page = true;
        $page_count = count($this->config['submenu-pages']);
        
        foreach( $this->config['submenu-pages'] as $page )
        {
            // Add a menu page
            if( $first_page )
            {
                \add_menu_page( 
                    $title, 
                    $title, 
                    $page['capability'], 
                    $slug, 
                    $page['content'],
                    $icon,
                    $position
                );
                
                // Add a submenu page if there are multiple pages
                if( $page_count > 1 )
                {
                    add_submenu_page( 
                        $slug, 
                        $page['title'], 
                        $page['title'], 
                        $page['capability'], 
                        $slug, 
                        $page['content']
                    );
                }
                $first_page = false;
            }
            
            // Pages after the first page
            else {
                add_submenu_page( 
                    $slug, 
                    $page['title'], 
                    $page['title'], 
                    $page['capability'], 
                    $this->strtoslug( $page['title'] ), 
                    $page['content']
                );
            }
        }
    }
    
    /**
     * Array to CSS.
     * 
     * Converts an array of rule => value into proper CSS formatted rules.
     * 
     * @param string[] $rules The CSS rules.
     * @return string The CSS formatted string.
     */
    private function array_to_css( array $rules )
    {
        $css = '';
        foreach( $rules as $rule => $value )
        {
            $css .= $rule.':'.$value.';';
        }
        return $css;
    }
}