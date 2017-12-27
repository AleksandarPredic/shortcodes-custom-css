#Shortcodes custom css

This code will help you make custom css for Visual Composer shortcodes. It can be used to avoid using inline styles and do it the proper WP way.

###How to use it
To add your shortcode to the Visual Composer content elements list, vc_map() function should be called with an array of special attributes describing your shortcode. $params Associative array which holds instructions for Visual Composer and is used in "mapping" process. After you describe all shortcode's attributes that should be editable with Visual Composer interface you can add code which generate custom css. Here you can add param_name, property, shortcode class sufix if you need it for that element and shortcode element which is class name.

<?php
/*
 * Map new shortcode
 */

vc_map( array(
    "name" => esc_html__("Shortcode name", "text-domain"),
    "base" => "shi_team_member_creative",
    "weight" => 11,
    "content_element" => true,
    "category" => esc_html__("Category name", 'text-domain'),
    "icon" =>  plugins_url('assets/icon.png', __FILE__),
    "params" => array(
        array(
            'type' => 'textfield',
            'heading' => esc_html__( 'Name of member.', 'text-domain' ),
            'param_name' => 'title',
            'value' => esc_html__('NAME LASTNAME', 'text-domain'),
            'description' => esc_html__( 'Please enter name and last name of member.', 'text-domain' ),
        ),
        array(
            "type" => "textarea",
            "heading" => esc_html__( "Member info", "text-domain" ),
            "param_name" => "content",
            "value" => esc_html__( "Info about this member.", "text-domain" ),
            "description" => esc_html__( "Enter informations about member.", "text-domain" )
        ),
	 )
) );
	Shi_Shortcodes_Custom_Css::add_to_queue( array(
    'domain_shortcode_name' => array(
        array(            
            "param_name" => "text_color",
            'shortcode_element' => '.class-name',
            'property' => 'color',
        ),
        array(            
            "param_name" => "text_color_hov",
            'shortcode_class_sufix' => ':hover',
            'shortcode_element' => '.class-name',
            'property' => 'color',
        ),
		
###Chengelog
1.0.0 (November 2017)
Official release

###Developers
Miss a feature? Pull requests are welcome.

The project is open-source and hopefully will receive contributions from awesome WordPress Developers throughout the world.

###Author information
The Shortcodes Custom Css was originally started and is maintained by Aleksandar Predic.

