<?php
/**
Plugin Name: bread-virtual-meetings
Plugin URI: none
Description: Reformat virtual meeting listings in bread
Author: Alan B
Version: 1.0
*/

add_filter( 'Bread_Enrich_Meeting_Data', 'fixVirtualMeetings', 10, 2 );

/*
The default presentation in bread for virtual meetings that are replacing an in-person meeting seems potentially
misleading, since it may be easy to overlook the TC key and go to a closed meeting location. This extension fixes up
those listings by adding a note in front of the facility name, and removing the address. It also removes the
bus information to save space on the printed schedules, since this isn't needed to get to a virtual meeting.
*/
function fixVirtualMeetings($value, $formats_by_key) {
    if (in_array('TC', explode( ",", $value['formats']))) {
        if (trim($value['location_text']) != '') {
            $value['location_text'] = 'Currently online only -- normally at ' . $value['location_text'];
        }
        $value['location_info'] = '';
        $value['location_street'] = '';
        // could also delete the zip code ....
        // $value['location_postal_code_1'] = '';
        $value['bus_lines'] = '';
    }
    if (!empty($value['virtual_meeting_link'])) {
        if (stristr($value['virtual_meeting_link'], 'zoom')) {
            if ((stristr($value['virtual_meeting_additional_info'], 'pw')) or (stristr($value['virtual_meeting_additional_info'], 'pass')) or (stristr($value['virtual_meeting_additional_info'], 'code'))) {
                list($link, $password) = explode("?pwd=", $value['virtual_meeting_link']);
                $value['virtual_meeting_link'] = $link;
            }
        }
    }
    return $value;
}
?>
