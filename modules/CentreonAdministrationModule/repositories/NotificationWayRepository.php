<?php

/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonAdministration\Repository;

use Centreon\Internal\Di;
use CentreonAdministration\Models\ContactInfo;

/**
 * Description of NotificationWayRepository
 *
 * @author Kevin Duret <kduret@centreon.com>
 */
class NotificationWayRepository
{
    /**
     * Save contact notification ways
     *
     * @param int $id The contact id
     * @param string $action The action
     * @param array $params The parameters to save
     */
    public static function saveNotificationWays($id, $action = 'add', $listWays = array())
    {
        $dbconn = Di::getDefault()->get('db_centreon');

        if ($action == 'update') {
            $contactInfos = ContactInfo::getIdByParameter('contact_id', $id);
            foreach ($contactInfos as $contactInfo) {
                ContactInfo::delete($contactInfo);
            }
        }

        if (count($listWays) > 0) {
            foreach ($listWays as $name => $params) {
                ContactInfo::insert(array(
                    'contact_id' => $id,
                    'info_key' => $name,
                    'info_value' => $params['value']
                ));
            }
        }
    }

    /**
     *
     * @return array $notificationWays The list of existing notification ways
     */
    public static function getNotificationWays()
    {
        // @todo store notification ways in database
        $notificationWays = array('sms', 'email', 'twitter', 'whatsapp');
        return $notificationWays;
    }

    /**
     * 
     * @param type $objectId
     */
    public static function loadContactNotificationWay($objectId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        
        $getRequest = "SELECT info_key, info_value"
            . " FROM cfg_contacts_infos "
            . " WHERE contact_id = :contact";
        $stmtGet = $dbconn->prepare($getRequest);
        $stmtGet->bindParam(':contact', $objectId, \PDO::PARAM_INT);
        $stmtGet->execute();
        $rowWay = $stmtGet->fetchAll(\PDO::FETCH_ASSOC);
        return $rowWay;
    }
}