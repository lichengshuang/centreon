<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Repository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class UserRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'contact';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'User';
    
    /**
     *
     * @var array Main database field to get
     */
    public static $datatableColumn = array(
        '<input id="allContact" class="allContact" type="checkbox">' => 'contact_id',
        'Alias / Login' => 'contact_alias',
        'Full name' => 'contact_name',
        'Email' => 'contact_email',
        'Notifications Period' => array(
            'Hosts' => 'contact_host_notification_options',
            'Services' => 'contact_service_notification_options'
        ),
        'Language' => 'contact_lang',
        'Access' => 'contact_oreon',
        'Admin' => 'contact_admin',
        'Status' => 'contact_activate'
    );
    
    /**
     *
     * @var array Column name for the search index
     */
    public static $researchIndex = array(
        'contact_id',
        'contact_alias',
        'contact_name',
        'contact_email',
        'contact_host_notification_options',
        'contact_service_notification_options',
        'contact_lang',
        'contact_oreon',
        'contact_admin',
        'contact_activate'
    );
    
    /**
     *
     * @var string 
     */
    public static $specificConditions = "contact_register = '1' ";
    
    /**
     * @inherit doc
     * @var array 
     */
    public static $columnCast = array(
        'contact_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::contact_name::'
            )
        ),
        'contact_admin' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">No</span>',
                '1' => '<span class="label label-success">Yes</span>'
            )
        ),
        'contact_oreon' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>'
            )
        ),
        'contact_activate' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-danger">Disabled</span>',
                '1' => '<span class="label label-success">Enabled</span>'
            )
        ),
        'contact_alias' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/configuration/user/[i:id]',
                'routeParams' => array(
                    'id' => '::contact_id::'
                ),
                'linkName' => '::contact_alias::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
        'text',
        'none',
        'none',
        'text',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'text',
        'text',
        'text',
        'none',
        'none',
        'text',
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        ),
        array(
            'select' => array(
                'Yes' => '1',
                'No' => '0'
            )
        ),
        array(
            'select' => array(
                'Enabled' => '1',
                'Disabled' => '0'
            )
        )
    );
    
    /**
     * 
     * @param array $resultSet
     */
    public static function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myUserSet) {
            insertAfter(
                $myUserSet,
                'contact_email',
                array(
                    'contact_host_notification_options' => self::getNotificationInfos(
                        $myUserSet['contact_id'],
                        'host'
                    ),
                    'contact_service_notification_options' => self::getNotificationInfos(
                        $myUserSet['contact_id'],
                        'service'
                    )
                )
            );

            $myUserSet['contact_alias'] = self::getUserIcon($myUserSet['contact_alias'], $myUserSet['contact_email']);
        }
    }
    
    /**
     * 
     * @param integer $contactId
     * @param string $object
     * @return string
     */
    public static function getNotificationInfos($contactId, $object)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        if ($object == 'host') {
            $ctp = 'timeperiod_tp_id';
        } elseif ($object == 'service') {
            $ctp = 'timeperiod_tp_id2';
        }
        
        $query = "SELECT tp_name, contact_".$object."_notification_options "
            . "FROM contact, timeperiod "
            . "WHERE contact_id='$contactId' "
            . "AND tp_id = $ctp" ;
        
        $stmt = $dbconn->query($query);
        $resultSet = $stmt->fetch();
        
        if ($resultSet === false) {
            $return = '';
        } else {
            $return = $resultSet['tp_name'].' ('.$resultSet['contact_'.$object.'_notification_options'].')';
        }
        
        return $return;
    }
    
    public static function getUserIcon($name, $email)
    {
        if ($email != "") {
            $name = "<img src='http://www.gravatar.com/avatar/".
                md5($email).
                "?rating=PG&size=16&default=' class='img-circle'>&nbsp;".
                $name;
        } else {
            $name = "<i class='fa fa-user'></i>&nbsp;".$name;
        }
        
        return $name;
    }

    public static function generate(& $filesList, $poller_id, $path, $filename)
    {
        
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Field to not display */
        //$disableField = static::getTripleChoice();
        $field = "contact_id, contact_name, contact_alias as alias, contact_email as email, "
            . "contact_pager as pager, contact_host_notification_options as host_notification_options, "
            . "contact_service_notification_options as service_notification_options, "
            . "contact_enable_notifications as host_notifications_enabled, "
            . "contact_enable_notifications as service_notifications_enabled, "
            . "timeperiod_tp_id as host_notification_period, timeperiod_tp_id2 as service_notification_period ";
        
        /* Init Content Array */
        $content = array();
        
        /* Get information into the database. */
        $query = "SELECT $field FROM contact WHERE contact_activate = '1' ORDER BY contact_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $tmp = array("type" => "contact");
            $tmpData = array();
            $args = "";
            foreach ($row as $key => $value) {
                if ($key == "contact_id") {
                    $contact_id = $row["contact_id"];
                } else {
                    if ($key == "host_notification_period" || $key == "service_notification_period") {
                        $value = TimeperiodRepository::getPeriodName($value);
                    }
                    if ($value != "") {
                        $tmpData[$key] = $value;
                    }
                }
            }

            /* Get contactgroups */
            $tmpData["contactgroups"] = static::getContactContactGroup($contact_id);
            
            /* Get commands */
            $tmpData["host_notification_commands"] = static::getNotificationCommand($contact_id, "host");
            $tmpData["service_notification_commands"] = static::getNotificationCommand($contact_id, "service");

            $tmp["content"] = $tmpData;
            $content[] = $tmp;
        }

        /* Write Check-Command configuration file */
        WriteConfigFileRepository::writeObjectFile($content, $path.$poller_id."/".$filename, $filesList, $user = "API");
        unset($content);
    }

    public static function getNotificationCommand($contact_id, $type)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        if ($type != "host" && $type != "service") {
            return "";
        }

        /* Launch Request */
        $query = "SELECT command_name FROM contact_".$type."commands_relation, command "
            . "WHERE contact_contact_id = $contact_id AND command_command_id = command_id";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $cmd = "";
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($cmd != "") {
                $cmd .= ",";
            }
            $cmd .= $row["command_name"];
        }
        return $cmd;
    }
    
    public static function getContactContactGroup($contact_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Launch Request */
        $query = "SELECT cg_name FROM contactgroup_contact_relation cgr, contactgroup cg "
            . "WHERE contact_contact_id = ".$contact_id." AND cgr.contactgroup_cg_id = cg.cg_id";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $cg = "";
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($cg != "") {
                $cg .= ",";
            }
            $cg .= $row["cg_name"];
        }
        return $cg;
    }
}
