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

namespace  CentreonConfiguration\Repository;

/**
 * Factory for ConfigTest Engine
 *
 * @author Julien Mathis <jmathis@merethis.com>
 * @version 3.0.0
 */
class ConfigCorrelationRepository
{
    /**
     * 
     * @param int $poller_id
     * @return bool
     */
    public function generate($poller_id)
    {
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $xml = new \XMLWriter();
        $xml->openURI('/etc/centreon-broker/correlation_1.xml');

        $xml->startDocument('1.0', 'UTF-8');
        
        /* Start Element conf */
        $xml->startElement('conf');

        /* Declare Host */
        $query = "SELECT host_id, nagios_server_id "
            . "FROM host, ns_host_relation "
            . "WHERE host_host_id = host_id ORDER BY host_id";
        $stmt = $dbconn->query($query);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $xml->startElement('host');
            $xml->writeAttribute('id', $row["host_id"]);
            $xml->writeAttribute('instance_id', $row["nagios_server_id"]);
            $xml->endElement();
        }
        
        /* Declare Service */
        $query = "SELECT service_id, host_id, nagios_server_id "
            . "FROM host, service, host_service_relation ns, ns_host_relation hp "
            . "WHERE host_id = ns.host_host_id "
            . "AND service_id = ns.service_service_id AND hp.host_host_id = host_id";
        $stmt = $dbconn->query($query);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $xml->startElement('service');
            $xml->writeAttribute('id', $row["service_id"]);
            $xml->writeAttribute('host', $row["host_id"]);
            $xml->writeAttribute('instance_id', $row["nagios_server_id"]);
            $xml->endElement();
        }
        
        /* End conf Element */
        $xml->endElement();
        $xml->endDocument();

        return true;
    }
}
