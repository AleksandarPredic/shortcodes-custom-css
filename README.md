# Visual Composer Shortcodes custom css

This code will help you make custom css for Visual Composer shortcodes. It can be used to avoid using inline styles and do it the proper WP way.

### How to use it
To add your shortcode to the Visual Composer content elements list, vc_map() function should be called with an array of special attributes describing your shortcode. $params Associative array which holds instructions for Visual Composer and is used in "mapping" process. In params array we have only one element as array with list of params.
 
 ```
<?php
/*
 * Map new shortcode
 */

vc_map( array(
    "name" => esc_html__("Shortcode name", "text-domain"),
    "base" => "team_member_creative",
    "weight" => 11,
    "content_element" => true,
    "category" => esc_html__("Category name", 'text-domain'),
    "icon" =>  plugins_url('assets/icon.png', __FILE__),
    "params" => array(
        array(
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_html__( "Overlay background", "text-domain" ),
            "param_name" => "overlay_background",
            "value" => 'transparent',
            "description" => esc_html__( "Choose background color for overlay.", "text-domain" )
        ),
        array(
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_html__( "Text color", "text-domain" ),
            "param_name" => "text_color",
            "value" => '#222',
            "description" => esc_html__( "Choose text color for name.", "text-domain" )
        ),        
        array(
            "type" => "colorpicker",
            "class" => "",
            "heading" => esc_html__( "Hover text color", "text-domain" ),
            "param_name" => "text_color_hov",
            "value" => '#222',
            "description" => esc_html__( "Choose hover text color for name.", "text-domain" )
        ),
	 )
) );

```
After you describe all shortcode's attributes that should be editable with Visual Composer interface you can add code which generate custom css. Here you can add array of elements such as param_name, property... param_name must be the same as your parameter name and property could be color or background depending of that for which property you want to add css. In this array you need shortcode_element which is class name. You can also use alpha parameter and the function will convert it to rgba.

```
Shortcodes_Custom_Css::add_to_queue( array(
'domain_shortcode_name' => array(
	array(            
		"param_name" => "overlay_background",
		'shortcode_element' => '.team-member__info',
		'property' => 'background',
		'alpha' => 0.5
	),
```

Shortcode class sufix will add some sufix after class name or even new class. You can use thsi if you need hover on some element. In that case you need two elements with the same shortcode_element and one of them should have shortcode_class_sufix :hover.
```
	array(            
		"param_name" => "text_color",
		'shortcode_element' => '.team-member__info h3',
		'property' => 'color',
	),
	array(            
		"param_name" => "text_color_hov",
		'shortcode_class_sufix' => ':hover',
		'shortcode_element' => '.team-member__info h3',
		'property' => 'color',
	),
	)
) );
```		
		
### Chengelog
1.0.0 (November 2017)
Official release

### Developers
Miss a feature? Pull requests are welcome.

The project is open-source and hopefully will receive contributions from awesome WordPress Developers throughout the world.

### Author information
The Shortcodes Custom Css was originally started and is maintained by Aleksandar Predic.

