<?php
/**
Plugin Name: bread-virtual-meetings
Plugin URI: none
Description: Reformat virtual meeting listings in bread
Author: Alan B
Version: 1.1
*/

add_filter( 'Bread_Enrich_Meeting_Data', 'fixVirtualMeetings', 10, 2 );

/*
The default presentation in bread for virtual meetings that are replacing an in-person meeting seems potentially
misleading, since it may be easy to overlook the TC key and go to a closed meeting location. This extension provides
ways to fix up those listings.
*/

function fixVirtualMeetings($value, $formats_by_key) {
    $options = get_option('breadxtn_options', ['breadxtn_field_option' => 'add-online-only']);
    // $option should be one of 'no-changes', 'omit-location', or 'add-online-only'.  The default is 'add-online-only'.
    $option = $options['breadxtn_field_option'];
    if ($option === 'omit-location' || $option === 'add-online-only') {
        $format_array = explode(',', $value['formats']);
        $i = array_search('TC', $format_array);   // $i will be false if no TC format, otherwise its index
        if (is_int($i)) {
            if ($option === 'omit-location') {
                $value['location_text'] = '';
                unset($format_array[$i]);  // remove TC format, since it's not meaningful in this case
                $value['formats'] = implode(',', $format_array);
            } else {  // $option is 'add-online-only'
                if (trim($value['location_text']) != '') {
                    $value['location_text'] = 'Currently online only -- normally at ' . $value['location_text'];
                }
            }
            $value['location_info'] = '';
            $value['location_street'] = '';
            $value['bus_lines'] = '';
            // could also omit the post code (zip code) for all virtual meetings
            // if (in_array('VM', $format_array)) {
            //     $value['location_postal_code_1'] = '';
            // }
        }
    }
    return $value;
}

/**
 * custom option and settings
 * Adapted from the example at https://developer.wordpress.org/plugins/settings/custom-settings-page/
 */
function breadxtn_settings_init() {
    // Register a new setting for "breadxtn" page.
    register_setting( 'breadxtn', 'breadxtn_options' );

    // Register a new section in the "breadxtn" page.
    add_settings_section(
        'breadxtn_section_developers',
        __( 'Select an option for handling virtual meetings with a temporarily closed facility:', 'breadxtn' ), 'breadxtn_section_developers_callback',
        'breadxtn'
    );

    // Register a new field in the "breadxtn_section_developers" section, inside the "breadxtn" page.
    add_settings_field(
        'breadxtn_field_option',
        __( 'Option', 'breadxtn' ),
        'breadxtn_field_option_cb',
        'breadxtn',
        'breadxtn_section_developers',
        array(
            'label_for'         => 'breadxtn_field_option',
            'class'             => 'breadxtn_row',
            'breadxtn_custom_data' => 'custom',
        )
    );
}

/**
 * Register our breadxtn_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'breadxtn_settings_init' );


/**
 * Custom option and settings:
 *  - callback functions
 */


/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function breadxtn_section_developers_callback( $args ) {
    null;
}

/**
 * option field callback function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function breadxtn_field_option_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'breadxtn_options' );
    ?>
    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['breadxtn_custom_data'] ); ?>"
            name="breadxtn_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="add-online-only" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'add-online-only', false ) ) : ( '' ); ?>>
            <?php esc_html_e( "Add 'Currently online only' to location name", 'breadxtn' ); ?>
        </option>
        <option value="omit-location" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'omit-location', false ) ) : ( '' ); ?>>
            <?php esc_html_e( "Omit location name and 'Temporarily Closed' entirely", 'breadxtn' ); ?>
        </option>
        <option value="no-changes" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'no-changes', false ) ) : ( '' ); ?>>
            <?php esc_html_e( "Don't change anything", 'breadxtn' ); ?>
        </option>
    </select>
    <p class="description">
        <?php esc_html_e( "Location information, address, and bus lines are also omitted for the first two options.", 'breadxtn' ); ?>
    </p>
    <?php
}

/**
 * Add the top level menu page.
 */
function breadxtn_options_page() {
    add_menu_page(
        'Bread Extension for Virtual Meetings',
        'Meeting List Extn',
        'manage_bread',
        'breadxtn',
        'breadxtn_options_page_html'
    );
}


/**
 * Register our breadxtn_options_page to the admin_menu action hook.
 */
add_action( 'admin_menu', 'breadxtn_options_page' );


/**
 * Top level menu callback function
 */
function breadxtn_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // add error/update messages

    // check if the user have submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'breadxtn_messages', 'breadxtn_message', __( 'Settings Saved', 'breadxtn' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'breadxtn_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "breadxtn"
            settings_fields( 'breadxtn' );
            // output setting sections and their fields
            // (sections are registered for "breadxtn", each field is registered to a specific section)
            do_settings_sections( 'breadxtn' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}
