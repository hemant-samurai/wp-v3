<?php

/**
 * This file is part of Formstack's WordPress Plugin.
 *
 * Formstack's WordPress Plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * Formstack's WordPress Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * A quick interface to the Formstack API. Documentation to the API can be
 * found here: http://support.formstack.com/index.php?pg=kb.book&id=3
 *
 * I'm using JSON rather than the serialized PHP because I prefer the object
 * notation and wanted to avoid casting.
 *
 * @author michael
 */
class Formstack_API {

    private static $api_url = 'https://www.formstack.com/api';

    /**
     * Make a Formstack API request and decode the response.
     *
     * @param <string> The Formstack API key
     * @param <string> $method The API web method
     * @param <string> $args The parameters for the API request
     * @return <StdObject>
     */
    public static function request($api_key, $method, $args = array()) {

        $args['api_key'] = $api_key;
        $args['type'] = 'json';

        $url = self::$api_url . "/" . $method;
        
        $res = wp_remote_fopen("{$url}?".http_build_query($args));
        
        if($res === false)
            return false;

        return json_decode($res);
        //return json_decode($res)->status == "ok" ? json_decode($res)->response : null;
        
    }

}
?>
